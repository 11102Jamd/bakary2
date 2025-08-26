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
        $quantityInGrams = $this->convertToGrams($itemData['quantity_total'], $input->unit);

        return InputBatches::create([
            'input_id' => $itemData['input_id'],
            'order_id' => $orderId,
            'quantity_total' => round($itemData['quantity_total'], 3),
            'quantity_remaining' => $quantityInGrams,
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

    protected function convertToGrams(float $quantity, string $unit): float
    {
        return match (strtolower($unit)) {
            'kg' => $quantity * 1000,
            'lb' => $quantity * 453.592,
            'oz' => $quantity * 28.3495,
            'l' => $quantity * 1000,
            'un' => $quantity * 1,
            default => $quantity
        };
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
