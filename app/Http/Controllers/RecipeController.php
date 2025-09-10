<?php

namespace App\Http\Controllers;

use App\Http\Controllers\crud\BaseCrudController;
use App\Services\RecipeService;
use App\Models\Recipe;
use Illuminate\Http\Request;

class RecipeController extends BaseCrudController
{
    protected $model = Recipe::class;

    protected $recipeService;
    protected $validationRules = [
        'name' => 'required|string|max:255|unique:recipe,name',
        'yield_quantity' => 'required|numeric|min:0.001',
        'ingredients' => 'required|array|min:1',
        'ingredients.*.input_id' => 'required|exists:input,id',
        'ingredients.*.quantity_required' => 'required|numeric|min:0.001',
        'ingredients.*.unit_used' => 'required|string|max:10'
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

    public function update(Request $request, $id)
    {
        try {
            $recipe = $this->model::findOrFail($id);

            $this->validationRules['name'] = 'required|string|max:255|unique:recipe,name,' . $id;

            $this->validationRules['ingredients'] = 'sometimes|array|min:1';

            $validated = $this->validationRequest($request, $this->validationRules);

            $updatedRecipe = $this->recipeService->updateRecipe($recipe, $validated);

            return response()->json([
                'message' => 'Receta actualizada exitosamente',
                'data' => $updatedRecipe->load('recipeIngredients.input')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error de validación',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function disable($id)
    {
        try {
            $recipe = $this->model::findOrFail($id);
            $recipe->delete();//Ejecuta softdelete y no un delete como tal
            return response()->json([
                'message' => 'receta inhabilitado temporalmete',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'error no sep udo encontrar el usuario con ese registro',
                'message' => $th->getMessage()
            ], 404);
        }
    }
}
