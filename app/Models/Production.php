<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Recipe;
use App\Models\ProductionConsumptions;
use App\Models\Input;
use App\Models\ProductProduction;

class Production extends Model
{
    protected $table = 'production';

    protected $fillable = [
        'recipe_id',
        'quantity_to_produce',
        'price_for_product',
        'total_cost',
        'production_date'
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }

    public function consumptions(): HasMany
    {
        return $this->hasMany(ProductionConsumptions::class, 'production_id');
    }

    public function inputs()
    {
        return $this->hasManyThrough(
            Input::class,            // Modelo final que queremos alcanzar
            ProductionConsumptions::class, // Modelo intermedio
            'production_id',         // FK en la tabla intermedia que apunta a Production
            'id',                    // PK en el modelo final (Input)
            'id',                    // PK en el modelo actual (Production)
            'input_id'               // FK en la tabla intermedia que apunta a Input
        );
    }

    public function productProduction(): HasMany
    {
        return $this->hasMany(ProductProduction::class, 'production_id');
    }
}
