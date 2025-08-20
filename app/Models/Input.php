<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\InputBatches;
use App\Models\ProductionConsumptions;

class Input extends Model
{
    protected $table = 'input';

    protected $fillable = [
        'name',
        'unit'
    ];

    protected static function booted()
    {
        static::saving(function ($input) {
            if (!in_array(strtolower($input->unit), ['kg', 'g', 'lb', 'oz', 'l','un'])) {
                throw new \Exception("Unidad no válida. Use: kg, g, lb, oz");
            }
        });
    }

    public function batches(): HasMany
    {
        return $this->hasMany(InputBatches::class, 'input_id')
            ->where('quantity_remaining', '>', 0)
            ->orderBy('created_at', 'asc');
    }

    public function productionConsumptions(): HasMany
    {
        return $this->hasMany(ProductionConsumptions::class, 'input_id');
    }

    public function oldestActiveBatch()
    {
        return $this->batches()->first(); // Simplemente toma el primero (el más antiguo)
    }
}
