<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\InputBatches;

class Order extends Model
{
    protected $table = 'order';

    protected $fillable = [
        'supplier_name',
        'order_date',
        'order_total'
    ];

    public function batches(): HasMany
    {
        return $this->hasMany(InputBatches::class, 'order_id');
    }
}
