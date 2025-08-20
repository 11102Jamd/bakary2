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
            $inputs = Input::with(['oldestActiveBatch'])
                ->orderBy('id', 'desc')
                ->get();

            return response()->json($inputs);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'error al obtener la lista de insumos',
                'error' => $th->getMessage()
            ], 500);
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
}
