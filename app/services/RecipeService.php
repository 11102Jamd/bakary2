<?php

namespace App\Services;

use App\Models\Recipe;
use App\Models\RecipeIngredients;
use Illuminate\Support\Facades\DB;

class RecipeService
{
    public function createRecipe(array $data): Recipe
    {
        return DB::transaction(function () use ($data) {
            $recipe = Recipe::create([
                'name' => $data['name'],
                'yield_quantity' => $data['yield_quantity'],
                'unit' => $data['unit']
            ]);

            foreach ($data['ingredients'] as $ingredient) {
                RecipeIngredients::create([
                    'recipe_id' => $recipe->id,
                    'input_id' => $ingredient['input_id'],
                    'quantity_required' => $ingredient['quantity_required']
                ]);
            }

            return $recipe;
        });
    }
}
