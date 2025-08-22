<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Sale;
use App\Models\Product;

class SaleProduct extends Model
{
    protected $table = 'sale_product';

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity_requested',
        'subtotal_price'
    ];

    protected $attributes = [
        'quantity_requested' => 0,
        'subtotal_price' => 0
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
