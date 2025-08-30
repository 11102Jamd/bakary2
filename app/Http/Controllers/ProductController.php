<?php

namespace App\Http\Controllers;

use App\Http\Controllers\crud\BaseCrudController;
use App\Models\Product;
use App\Services\ProductProductionService;
use Illuminate\Http\Request;
use Illuminate\Support\Js;

class ProductController extends BaseCrudController
{
    protected $model = Product::class;

    protected $productProductionService;

    protected $validationRules = [
        'name' => 'required|string|max:255|unique:product,name',
        'unit_price' => 'required|numeric|min:0',
    ];

    public function __construct(ProductProductionService $productProductionService)
    {
        $this->productProductionService = $productProductionService;
    }

    public function index()
    {
        try {
            $product = $this->model::with(['productProductions','productProductions.production'])
                ->orderBy('id', 'desc')
                ->get();

            return response()->json($product);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'No se pudo obtener la lista de Productos',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validationRules['name'] = 'required|string|unique:product,name,' . $id;
            parent::update($request, $id);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'No se pudo actualizar el insumo',
                'message' => $th->getMessage()
            ], 422);
        }
    }

    public function linkProductionToProduct(Request $request)
    {
        $validated = $request->validate([
            'production_id' => 'required|exists:production,id',
            'product_id' => 'required|exists:product,id'
        ]);

        try {
            $result = $this->productProductionService->linkProductionToProduct(
                $validated['production_id'],
                $validated['product_id']
            );

            return response()->json([
                'message' => 'Producción vinculada al producto exitosamente',
                'data' => $result
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Error al vincular producción con producto',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
