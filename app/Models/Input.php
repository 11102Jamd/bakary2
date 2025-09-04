<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\InputBatches;
use App\Models\ProductionConsumptions;
use Illuminate\Database\Eloquent\SoftDeletes;

class Input extends Model
{
    use SoftDeletes;

    protected $table = 'input';

    protected $fillable = [
        'name',
        'category'
    ];

    protected $dates = ['deleted_at'];

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
