<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientCompetitor extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'client_id',
        'platform',
        'identifier',
        'label',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function adLibraryAds(): HasMany
    {
        return $this->hasMany(MetaAdLibraryAd::class, 'client_competitor_id');
    }

    public function adLibrarySummaries(): HasMany
    {
        return $this->hasMany(MetaAdLibraryDailySummary::class, 'client_competitor_id');
    }

    public function socialListeningSources(): HasMany
    {
        return $this->hasMany(SocialListeningSource::class, 'client_competitor_id');
    }

    public function socialListeningEvents(): HasMany
    {
        return $this->hasMany(SocialListeningEvent::class, 'client_competitor_id');
    }
}
