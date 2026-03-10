<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacebookLead extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'facebook_lead_id',
        'form_id',
        'form_name',
        'campaign_id',
        'ad_set_id',
        'ad_id',
        'full_name',
        'email',
        'phone_number',
        'custom_questions',
        'raw_data',
    ];

    protected $casts = [
        'custom_questions' => 'array',
        'raw_data' => 'array',
    ];

    public function adAccount(): BelongsTo
    {
        return $this->belongsTo(AdAccount::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function adSet(): BelongsTo
    {
        return $this->belongsTo(AdSet::class);
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }
}
