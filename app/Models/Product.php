<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'organization_id',
        'name',
        'sku',
        'price',
        'stock',
        'description',
        'status',
    ];
}
