<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\ProductProduction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class SaleService
{
    public function registerSale(array $saleData)
    {
        return DB::transaction(function () use ($saleData) {
            try {
                // 1. Crear la venta
                $sale = Sale::create([
                    'user_id' => $saleData['user_id'],
                    'sale_date' => now(),
                    'sale_total' => 0
                ]);

                $totalSale = 0;

                // 2. Procesar cada producto de la venta
                foreach ($saleData['products'] as $productData) {
                    $productId = $productData['product_id'];
                    $quantityRequested = $productData['quantity_requested'];

                    // 3. Verificar stock disponible
                    $availableStock = $this->getAvailableStockSafe($productId);

                    if ($availableStock < $quantityRequested) {
                        throw new \Exception("Stock insuficiente para el producto ID: {$productId}. Disponible: {$availableStock}, Solicitado: {$quantityRequested}");
                    }

                    // 4. Calcular subtotal
                    $product = Product::findOrFail($productId);
                    $subtotal = $product->unit_price * $quantityRequested;
                    $totalSale += $subtotal;

                    // 5. Crear registro de producto vendido
                    SaleProduct::create([
                        'sale_id' => $sale->id,
                        'product_id' => $productId,
                        'quantity_requested' => $quantityRequested,
                        'subtotal_price' => $subtotal
                    ]);

                    // 6. RESTAR FÍSICAMENTE del stock de producción
                    $this->deductFromProductionStock($productId, $quantityRequested);
                }

                // 7. Actualizar el total de la venta
                $sale->update(['sale_total' => round($totalSale, 3)]);

                return [
                    'sale' => $sale->load(['saleProducts.product', 'user']),
                    'message' => 'Venta registrada exitosamente'
                ];

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error al registrar venta: ' . $e->getMessage());
                throw new \Exception('Error al procesar la venta: ' . $e->getMessage());
            }
        });
    }

    /**
     * Restar físicamente del stock de producción (FIFO)
     */
    private function deductFromProductionStock(int $productId, float $quantityRequested): void
    {
        $productions = ProductProduction::where('product_id', $productId)
            ->where('quantity_produced', '>', 0)
            ->orderBy('created_at', 'asc') // FIFO: Primero en entrar, primero en salir
            ->get();

        $remainingQuantity = $quantityRequested;

        foreach ($productions as $production) {
            if ($remainingQuantity <= 0) break;

            $quantityToDeduct = min($remainingQuantity, $production->quantity_produced);

            // RESTAR físicamente de la producción
            $production->quantity_produced -= $quantityToDeduct;
            $production->save();

            $remainingQuantity -= $quantityToDeduct;
        }

        if ($remainingQuantity > 0) {
            throw new \Exception("Error inesperado: No se pudo deducir todo el stock del producto: {$productId}");
        }
    }

    /**
     * Obtener stock disponible
     */
    private function getAvailableStockSafe(int $productId): float
    {
        return ProductProduction::where('product_id', $productId)
            ->sum('quantity_produced');
    }

    /**
     * Obtener reporte de stock
     */
    public function getStockReportSafe(int $productId = null): Collection
    {
        $query = Product::with(['productProductions']);

        if ($productId) {
            $query->where('id', $productId);
        }

        return $query->get()->map(function ($product) {
            $totalProduced = $product->productProductions->sum('quantity_produced');

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'total_produced' => $totalProduced,
                'available_stock' => $totalProduced, // Ahora available_stock = total_produced
                'unit_price' => $product->unit_price
            ];
        });
    }

    public function revertSale(Sale $sale)
    {
        return DB::transaction(function () use ($sale) {
            try {
                // Cargar los productos de la venta
                $sale->load('saleProducts');

                foreach ($sale->saleProducts as $saleProduct) {
                    $this->revertSaleProduct($saleProduct);
                }

                return true;

            } catch (\Exception $e) {
                Log::error('Error al revertir venta: ' . $e->getMessage());
                throw new \Exception('Error al revertir la venta: ' . $e->getMessage());
            }
        });
    }

    /**
     * Revertir un producto de venta al stock de producción
     */
    protected function revertSaleProduct($saleProduct)
    {
        // Buscar producciones existentes del mismo producto (FIFO: más antiguas primero)
        $productions = ProductProduction::where('product_id', $saleProduct->product_id)
            ->orderBy('created_at', 'asc') // Más antiguas primero
            ->get();

        $quantityToRestore = $saleProduct->quantity_requested;

        foreach ($productions as $production) {
            if ($quantityToRestore <= 0) break;

            // Restaurar el stock a esta producción
            $production->quantity_produced += $quantityToRestore;
            $production->save();

            $quantityToRestore = 0; // Todo restaurado
        }

        // Si por alguna razón queda cantidad por restaurar, manejarlo
        if ($quantityToRestore > 0) {
            Log::warning("No se pudo restaurar completamente el producto {$saleProduct->product_id}. Quedan: {$quantityToRestore} unidades");
        }

        return true;
    }
}
