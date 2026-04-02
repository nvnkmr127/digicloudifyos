<?php

namespace App\Models;

use App\Models\Traits\OrganizationScoped;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoSiteAuditIssue extends Model
{
    use HasFactory, HasUuids, OrganizationScoped;

    protected $fillable = [
        'organization_id',
        'client_id',
        'seo_site_audit_id',
        'severity',
        'issue_type',
        'url',
        'title',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(SeoSiteAudit::class, 'seo_site_audit_id');
    }
}
