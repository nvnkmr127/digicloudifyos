<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use OrganizationScoped;

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
