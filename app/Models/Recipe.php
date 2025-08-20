<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\RecipeIngredients;
use App\Models\Production;

class Recipe extends Model
{
    protected $table = 'recipe';

    protected $fillable = [
        'name',
        'yield_quantity',
        'unit'
    ];

    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredients::class, 'recipe_id');
    }

    public function productions(): HasMany
    {
        return $this->hasMany(Production::class, 'recipe_id');
    }
}
