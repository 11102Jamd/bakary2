<?php

namespace App\Services;

use App\Models\Order;
use App\Models\InputBatches;
use App\Models\Input;
use Illuminate\Support\Facades\DB;

class OrderService
{

    protected function createInputBatch(int $orderId, array $itemData): InputBatches
    {
        $input = Input::findOrFail($itemData['input_id']);
        $originalUnit = $itemData['unit'];
        $originalQuantity = $itemData['quantity_total'];

        $converionResult = $this->convertToStandardUnit($originalQuantity, $originalUnit);

        return InputBatches::create([
            'input_id' => $itemData['input_id'],
            'order_id' => $orderId,
            'quantity_total' => $originalQuantity,
            'unit' => $originalUnit,
            'quantity_remaining' => round($converionResult['converted_quantity'], 2),
            'unit_converted' => $converionResult['standard_unit'],
            'unit_price' => round($itemData['unit_price'], 3),
            'subtotal_price' => round($itemData['quantity_total'] * $itemData['unit_price'], 3),
            'batch_number' => $this->getNextBatchNumber($itemData['input_id']),
            'received_date' => now()
        ]);
    }

    protected function getNextBatchNumber(int $inputId): int
    {
        $lastBatch = InputBatches::where('input_id', $inputId)
            ->orderByDesc('batch_number')
            ->first();

        return $lastBatch ? $lastBatch->batch_number + 1 : 1;
    }

    protected function convertToStandardUnit(float $quantity, string $unit): array
    {
        $unit = strtolower($unit);

        // Lógica de conversión
        if (in_array($unit, ['kg', 'g', 'lb', 'oz'])) {
            // Unidades de MASA -> Convertir a gramos (g)
            $converted = match ($unit) {
                'kg' => $quantity * 1000,
                'g' => $quantity,
                'lb' => $quantity * 453.592,
                'oz' => $quantity * 28.3495,
            };
            return ['converted_quantity' => $converted, 'standard_unit' => 'g'];
        } elseif (in_array($unit, ['l', 'ml'])) {
            // Unidades de VOLUMEN -> Convertir a mililitros (ml)
            $converted = match ($unit) {
                'l' => $quantity * 1000,
                'ml' => $quantity,
            };
            return ['converted_quantity' => $converted, 'standard_unit' => 'ml'];
        } elseif ($unit == 'un') {
            // Unidades (piezas) -> No se convierte, queda en 'un'
            return ['converted_quantity' => $quantity, 'standard_unit' => 'un'];
        } else {
            throw new \Exception("Unidad no válida: $unit");
        }
    }

    public function createOrderWithBatches(array $orderData): Order
    {
        return DB::transaction(function () use ($orderData) {
            // Calcular total de la orden
            $orderTotal = collect($orderData['items'])->sum(function ($item) {
                return round($item['quantity_total'] * $item['unit_price'], 3);
            });

            // Crear la orden principal con el total
            $order = Order::create([
                'supplier_name' => $orderData['supplier_name'],
                'order_date' => $orderData['order_date'],
                'order_total' => round($orderTotal, 3)
            ]);

            // Procesar cada item del pedido
            foreach ($orderData['items'] as $item) {
                $this->createInputBatch($order->id, $item);
            }

            return $order->load('batches.input');
        });
    }
}
