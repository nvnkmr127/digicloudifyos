<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadSyncLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'ad_account_id',
        'source',
        'status',
        'leads_processed',
        'error_message',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];
}
