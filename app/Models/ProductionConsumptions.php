<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Production;
use App\Models\Input;
use App\Models\InputBatches;
use App\Models\ProductProduction;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionConsumptions extends Model
{
    protected $table = 'production_consumptions';

    protected $fillable = [
        'production_id',
        'input_id',
        'input_batches_id',
        'quantity_used',
        'unit_price',
        'total_cost'
    ];

    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class, 'production_id');
    }

    public function input(): BelongsTo
    {
        return $this->belongsTo(Input::class, 'input_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InputBatches::class, 'input_batches_id');
    }

    public function productProductions(): HasMany
    {
        return $this->hasMany(ProductProduction::class, 'production_id');
    }
}
