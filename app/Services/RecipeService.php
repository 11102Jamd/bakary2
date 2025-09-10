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
            ]);

            foreach ($data['ingredients'] as $ingredient) {
                RecipeIngredients::create([
                    'recipe_id' => $recipe->id,
                    'input_id' => $ingredient['input_id'],
                    'quantity_required' => $ingredient['quantity_required'],
                    'unit_used' => $ingredient['unit_used']
                ]);
            }

            return $recipe;
        });
    }

    public function syncRecipeIngredients(Recipe $recipe, array $ingredients)
    {
        $currentIngredients = $recipe->recipeIngredients->pluck('id')->toArray();
        $newIngredients = [];

        foreach ($ingredients as $ingredientData) {
            $ingredient = RecipeIngredients::updateOrCreate(
                [
                    'recipe_id' => $recipe->id,
                    'input_id' => $ingredientData['input_id']
                ],
                [
                    'quantity_required' => $ingredientData['quantity_required'],
                    'unit_used' => $ingredientData['unit_used']
                ]
            );

            $newIngredients[] = $ingredient->id;
        }

        $ingredientsToDelete = array_diff($currentIngredients, $newIngredients);
        if (!empty($ingredientsToDelete)) {
            RecipeIngredients::whereIn('id', $ingredientsToDelete)->delete();
        }
    }

    public function updateRecipe(Recipe $recipe, array $validatedData)
    {
        return DB::transaction(function () use ($recipe, $validatedData) {
            $recipe->update([
                'name' => $validatedData['name'],
                'yield_quantity' => $validatedData['yield_quantity']
            ]);

            if (isset($validatedData['ingredients'])) {
                $this->syncRecipeIngredients($recipe, $validatedData['ingredients']);
            }

            return $recipe->fresh();
        });
    }
}
