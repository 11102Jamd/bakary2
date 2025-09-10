<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $table = 'sale';

    protected $fillable = [
        'user_id',
        'sale_date',
        'sale_total'
    ];

    protected $attributes = [
        'sale_date' =>  null,
        'sale_total' => 0
    ];

    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function saleProducts(): HasMany
    {
        return $this->hasMany(SaleProduct::class, 'sale_id');
    }
}
