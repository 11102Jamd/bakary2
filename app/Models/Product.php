<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'product';

    protected  $fillable = [
        'name',
        'unit_cost',
        'unit_price'
    ];
}
