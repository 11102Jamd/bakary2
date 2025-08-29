<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Services\ProductionService;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    protected $productionService;

    public function __construct(ProductionService $productionService)
    {
        $this->productionService = $productionService;
    }

    public function index()
    {
        try {
            $productions = Production::with(['recipe', 'consumptions.batch.input'])
                ->orderBy('id', 'desc')
                ->get();

            return response()->json($productions);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener el historial de producción',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $production = Production::with(['consumptions.input', 'consumptions.batch'])->findOrFail($id);
            return response()->json($production);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'error al obtener el registro',
                'error' => $th->getMessage()
            ], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $production = Production::findOrFail($id);

            $this->productionService->revertProduction($production);

            $production->delete();

            return response()->json([
                'message' => 'Producción eliminada exitosamente y stock restaurado'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la producción',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function calculateRequirements(Request $request)
    {
        $validated = $request->validate([
            'recipe_id' => 'required|exists:recipe,id',
            'quantity_to_produce' => 'required|numeric|min:0.001'
        ]);

        try {
            $result = $this->productionService->calculateRequirements(
                $validated['recipe_id'],
                $validated['quantity_to_produce']
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error en cálculo de requerimientos',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function executeProduction(Request $request)
    {
        $validated = $request->validate([
            'recipe_id' => 'required|exists:recipe,id',
            'quantity_to_produce' => 'required|numeric|min:0.001'
        ]);

        try {
            $production = $this->productionService->executeProduction(
                $validated['recipe_id'],
                $validated['quantity_to_produce']
            );

            return response()->json([
                'message' => 'Producción ejecutada exitosamente',
                'data' => $production,
                'total_cost' => round($production->total_cost, 3),
                'cost_per_unit' => round($production->total_cost / $production->quantity_to_produce, 3)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al ejecutar producción',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
