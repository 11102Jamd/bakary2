<?php

namespace App\Http\Controllers;

use App\Http\Controllers\crud\BaseCrudController;
use App\Models\Input;
use Illuminate\Http\Request;

class InputController extends BaseCrudController
{
    protected $model = Input::class;

    protected $validationRules = [
        'name' => 'required|string|max:255|unique:input,name',
        'unit' => 'required|string|max:20'
    ];

    public function index()
    {
        try {
            $inputs = $this->model::with('batches')->orderBy('id', 'desc')->get();

            return response()->json($inputs);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'error al obtener la lista de insumos',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validationRules['name'] = 'required|string|unique:input,name,' . $id;
            return parent::update($request, $id); // ← AGREGA 'return' AQUÍ
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'No se pudo actualizar el insumo',
                'message' => $th->getMessage()
            ], 422);
        }
    }


    public function batches($inputId)
    {
        try {
            $input = $this->model::with(['batches' => function ($query) {
                $query->where('quantity_remaining', '>', 0)
                    ->orderBy('received_date', 'asc'); // FIFO: más viejo primero
            }])->findOrFail($inputId);

            return response()->json([
                'input' => $input,
                'current_stock' => $input->batches->sum('quantity_remaining'),
                'next_batch_to_use' => $input->batches->first() // El más antiguo
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'error al obtener la lista de insumos con sus \n
                                respectivos lotes',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function disable($id)
    {
        try {
            $input = $this->model::findOrFail($id);
            $input->delete();
            return response()->json([
                'message' => 'insumo inhabilitado correctamente',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'error no sep udo encontrar el insumo con ese registro',
                'message' => $th->getMessage()
            ], 404);
        }
    }


    public function enable($id)
    {
        try {
            $input = $this->model::withTrashed()->findOrFail($id);

            if ($input->trashed()) {
                $input->restore();
                return response()->json([
                    'message' => 'insumo reactivado correctamente',
                    'input_id' => $id
                ], 200);
            }

            return response()->json([
                'message' => 'El insumo ya estaba activo',
                'input_id' => $id
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'No se pudo reactivar el insumo',
                'message' => $th->getMessage()
            ], 404);
        }
    }
}
