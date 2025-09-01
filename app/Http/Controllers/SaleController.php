<?php

namespace App\Http\Controllers;

use App\Http\Controllers\crud\BaseCrudController;
use App\Models\ProductProduction;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends BaseCrudController
{
    protected $model = Sale::class;
    protected $saleService;

    protected $validationRules = [
        'user_id' => 'required|exists:users,id',
        'sale_date' => 'required|date',
        'sale_total' => 'nullable|numeric|min:0'
    ];

    public function __construct(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }

    /**
     * Sobrescribir el método index para incluir relaciones
     */
    public function index()
    {
        try {
            $sales = $this->model::with(['user', 'saleProducts.product'])
                ->orderBy('id', 'desc')
                ->get();

            return response()->json($sales);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => "Error al obtener las ventas",
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Sobrescribir el método show para incluir relaciones
     */
    public function show($id)
    {
        try {
            $sale = $this->model::with(['user', 'saleProducts.product'])
                ->findOrFail($id);

            return response()->json($sale);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => "Error: Venta no encontrada",
                "message" => $th->getMessage(),
            ], 404);
        }
    }

    /**
     * Sobrescribir el método store para usar el servicio de ventas
     */
    public function store(Request $request)
    {
        try {
            // Validación específica para ventas con productos
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|exists:product,id',
                'products.*.quantity_requested' => 'required|numeric|min:0.01'
            ]);

            $result = $this->saleService->registerSale($validated);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['sale']
            ], 201);
        } catch (\Exception $th) {
            return response()->json([
                'success' => false,
                'error' => 'Error de validación',
                'messages' => $th->getMessage()
            ], 422);
        } catch (\Exception $th) {
            return response()->json([
                'success' => false,
                'error' => 'Error al registrar la venta',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Buscar la venta
            $sale = Sale::findOrFail($id);

            // Revertir la venta (restaurar stock)
            $this->saleService->revertSale($sale);

            // Eliminar la venta
            $sale->delete();

            return response()->json([
                'success' => true,
                'message' => 'Venta eliminada exitosamente y stock restaurado'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar la venta',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
