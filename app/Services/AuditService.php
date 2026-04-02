<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditService
{
    public function log(string $organizationId, ?string $userId, string $action, ?object $subject = null, array $metadata = [], ?string $ip = null, ?string $userAgent = null): void
    {
        AuditLog::create([
            'organization_id' => $organizationId,
            'user_id' => $userId,
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject && isset($subject->id) ? (string) $subject->id : null,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'metadata' => $metadata ?: null,
        ]);
    }
}
