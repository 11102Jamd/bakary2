<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Recipe;
use App\Models\Input;

class RecipeIngredients extends Model
{
    protected $table = 'recipe_ingredients';

    protected $fillable = [
        'recipe_id',
        'input_id',
        'quantity_required',
        'unit_used'
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }

    public function input(): BelongsTo
    {
        return $this->belongsTo(Input::class, 'input_id');
    }
}
