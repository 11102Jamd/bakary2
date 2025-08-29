<?php

namespace App\Http\Controllers;

use App\Http\Controllers\crud\BaseCrudController;
use App\Models\Recipe;
use App\Services\RecipeService;
use Illuminate\Http\Request;

class RecipeController extends BaseCrudController
{
    protected $model = Recipe::class;

    protected $recipeService;
    protected $validationRules = [
        'name' => 'required|string|max:255|unique:recipe,name',
        'yield_quantity' => 'required|numeric|min:0.001',
        'unit' => 'required|string|max:20',
        'ingredients' => 'required|array|min:1',
        'ingredients.*.input_id' => 'required|exists:input,id',
        'ingredients.*.quantity_required' => 'required|numeric|min:0.001'
    ];

    public function __construct(RecipeService $recipeService)
    {
        $this->recipeService = $recipeService;
    }

    public function show($id)
    {
        try {
            $recipe = $this->model::with(['recipeIngredients.input'])->findOrFail($id);
            return response()->json($recipe);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al crear la receta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $this->validationRequest($request);

        try {
            $recipe = $this->recipeService->createRecipe($validated);

            return response()->json([
                'message' => 'Receta base creada exitosamente',
                'data' => $recipe->load('recipeIngredients.input')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la receta',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
