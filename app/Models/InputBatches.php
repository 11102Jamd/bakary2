<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Input;
use App\Models\Order;
use App\Models\ProductionConsumptions;

class InputBatches extends Model
{
    protected $table = 'input_batches';

    protected $fillable = [
        'input_id',
        'order_id',
        'quantity_total',
        'quantity_remaining',
        'unit_price',
        'subtotal_price',
        'batch_number',
        'original_unit',
        'received_date'
    ];

    protected $attributes = [
        'original_unit' => 'g'
    ];
    public function input(): BelongsTo
    {
        return $this->belongsTo(Input::class, 'input_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function productionConsumptions(): HasMany
    {
        return $this->hasMany(ProductionConsumptions::class, 'input_batches_id');
    }

    public function scopeAvailableForInput($query, $inputId)
    {
        return $query->where('input_id', $inputId)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('received_date'); // Orden FIFO
    }
}
