<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\RecipeIngredients;
use App\Models\Production;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use SoftDeletes;
    
    protected $table = 'recipe';

    protected $fillable = [
        'name',
        'yield_quantity',
    ];

    protected $dates = ['deleted_at'];

    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredients::class, 'recipe_id');
    }

    public function productions(): HasMany
    {
        return $this->hasMany(Production::class, 'recipe_id');
    }
}
