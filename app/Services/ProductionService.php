<?php

namespace App\Services;

use App\Models\Recipe;
use App\Models\InputBatches;
use App\Models\Production;
use App\Models\ProductionConsumptions;
use Illuminate\Support\Facades\DB;

/***
 * Calcular precio por cantidad gastada del productionConsumptio
 * y ademas de eso el procentaje de productcicion y alamcenar el
 * total del costo a produuction
 */
class ProductionService
{
    public function executeProduction(int $recipeId, float $quantityToProduce)
    {
        return DB::transaction(function () use ($recipeId, $quantityToProduce) {
            $recipe = Recipe::with('recipeIngredients.input')->findOrFail($recipeId);
            $scaleFactor = $quantityToProduce / $recipe->yield_quantity;

            // Crear producción con costo inicial 0
            $production = Production::create([
                'recipe_id' => $recipeId,
                'quantity_to_produce' => $quantityToProduce,
                'production_date' => now(),
                'price_for_product' => 0,
                'total_cost' => 0,
            ]);

            $totalCost = 0;

            foreach ($recipe->recipeIngredients as $ingredient) {
                $requiredGrams = $ingredient->quantity_required * $scaleFactor;
                $consumptionResult = $this->consumeIngredient(
                    $production->id,
                    $ingredient->input_id,
                    $requiredGrams
                );
                $totalCost += $consumptionResult['total_cost'];
            }

            // Actualizar con el costo total calculado
            $production->update([
                'total_cost' => round($totalCost, 3),
                'price_for_product' => round($totalCost / $quantityToProduce, 3)
            ]);

            return $production->load('consumptions.batch.input');
        });
    }

    protected function consumeIngredient(int $productionId, int $inputId, float $requiredGrams): array
    {
        $remaining = $requiredGrams;
        $totalCost = 0;
        $batchesUsed = [];

        $batches = InputBatches::with('input')
            ->where('input_id', $inputId)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('received_date', 'asc')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($remaining <= 0) break;

            $consumedGrams = min($batch->quantity_remaining, $remaining);
            $batchCost = $this->calculateBatchCost($consumedGrams, $batch);

            // Crear registro de consumo con el costo calculado
            ProductionConsumptions::create([
                'production_id' => $productionId,
                'input_id' => $inputId,
                'input_batches_id' => $batch->id,
                'quantity_used' => round($consumedGrams, 3),
                'unit_price' => round($batch->unit_price, 3),
                'total_cost' => round($batchCost, 3)
            ]);

            $batch->decrement('quantity_remaining', $consumedGrams);
            $remaining -= $consumedGrams;
            $totalCost += $batchCost;

            $batchesUsed[] = [
                'batch_id' => $batch->id,
                'grams_used' => round($consumedGrams, 3),
                'cost' => round($batchCost, 3)
            ];
        }

        if ($remaining > 0) {
            throw new \Exception("Stock insuficiente para el insumo ID: $inputId. Faltan: {$remaining} gramos");
        }

        return [
            'total_grams_used' => $requiredGrams - $remaining,
            'total_cost' => round($totalCost, 3),
            'batches' => $batchesUsed
        ];
    }

    protected function convertFromStandardUnit(float $standardAmount, string $originalUnit): float
    {
        $originalUnit = strtolower($originalUnit);

        if (in_array($originalUnit, ['kg', 'g', 'lb', 'oz'])) {
            return match ($originalUnit) {
                'kg' => $standardAmount / 1000,
                'g' => $standardAmount,
                'lb' => $standardAmount / 453.592,
                'oz' => $standardAmount / 28.3495,
            };
        } elseif (in_array($originalUnit, ['l', 'ml'])) {
            return match ($originalUnit) {
                'l' => $standardAmount / 1000,
                'ml' => $standardAmount,
            };
        } elseif ($originalUnit == 'un') {
            return $standardAmount;
        } else {
            throw new \Exception("Unidad original no válida: $originalUnit");
        }
    }

    protected function calculateBatchCost(float $amountUsedInStandardUnit, InputBatches $batch): float
    {
        $originalUnit = $batch->unit;

        $originalUnitUsed = $this->convertFromStandardUnit($amountUsedInStandardUnit, $originalUnit);

        return $originalUnitUsed * $batch->unit_price;
    }

    public function calculateRequirements(int $recipeId, float $quantityToProduce): array
    {
        $recipe = Recipe::with(['recipeIngredients' => function ($query) {
            $query->orderBy('id'); // Ordenar ingredientes consistentemente
        }, 'recipeIngredients.input'])->findOrFail($recipeId);

        $scaleFactor = $quantityToProduce / $recipe->yield_quantity;

        $totalCost = 0;
        $requirements = [];

        foreach ($recipe->recipeIngredients->sortBy('input_id') as $ingredient) {
            $requiredGrams = $ingredient->quantity_required * $scaleFactor;

            // Obtener batches ordenados por received_date (FIFO)
            $batches = InputBatches::with('input')
                ->where('input_id', $ingredient->input_id)
                ->where('quantity_remaining', '>', 0)
                ->orderBy('received_date', 'asc')
                ->orderBy('id', 'asc') // Orden secundario por ID
                ->get();

            $ingredientCost = 0;
            $remaining = $requiredGrams;
            $batchDetails = [];

            foreach ($batches as $batch) {
                if ($remaining <= 0) break;

                $usable = min($batch->quantity_remaining, $remaining);
                $batchCost = $this->calculateBatchCost($usable, $batch);

                $batchDetails[] = [
                    'batch_id' => $batch->id,
                    'received_date' => $batch->received_date, // Para verificación
                    'grams_to_use' => $usable,
                    'unit_price' => $batch->unit_price,
                    'unit' => $batch->input->unit,
                    'cost_for_batch' => $batchCost,
                    'remaining_stock' => $batch->quantity_remaining - $usable
                ];

                $ingredientCost += $batchCost;
                $remaining -= $usable;
            }

            $requirements[] = [
                'input_id' => $ingredient->input_id,
                'input_name' => $ingredient->input->name,
                'required_grams' => round($requiredGrams, 3),
                'estimated_cost' => round($ingredientCost, 3),
                'batches' => $batchDetails // Ya ordenados por received_date
            ];

            $totalCost += $ingredientCost;
        }

        usort($requirements, function ($a, $b) {
            return $a['input_id'] <=> $b['input_id'];
        });

        return [
            'recipe' => $recipe->only('id', 'name', 'yield_quantity', 'unit'),
            'quantity_to_produce' => $quantityToProduce,
            'requirements' => $requirements,
            'total_estimated_cost' => round($totalCost, 3),
            'cost_per_unit' => round($quantityToProduce > 0 ? $totalCost / $quantityToProduce : 0, 3)
        ];
    }

    public function revertProduction(Production $production)
    {
        return DB::transaction(function () use ($production) {
            $consumptions = ProductionConsumptions::with('batch')
                ->where('production_id', $production->id)
                ->get();

            foreach ($consumptions as $consumption) {
                $this->revertConsumption($consumption);
            }
            return true;
        });
    }

    protected function revertConsumption(ProductionConsumptions $consumption)
    {
        $batch = InputBatches::findOrFail($consumption->input_batches_id);
        $batch->increment('quantity_remaining', $consumption->quantity_used);
        $consumption->delete();
        return true;
    }
}
