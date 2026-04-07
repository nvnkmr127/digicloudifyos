<?php

namespace App\Services;

class PayloadRedactor
{
    public function redactArray(array $data): array
    {
        $out = [];

        foreach ($data as $key => $value) {
            $k = is_string($key) ? $key : (string) $key;

            if ($this->isSensitiveKey($k)) {
                $out[$key] = '[redacted]';
                continue;
            }

            if (is_array($value)) {
                $out[$key] = $this->redactArray($value);
                continue;
            }

            $out[$key] = $value;
        }

        return $out;
    }

    public function truncateString(?string $value, int $maxLength = 1000): ?string
    {
        if ($value === null) {
            return null;
        }

        if (mb_strlen($value) <= $maxLength) {
            return $value;
        }

        return mb_substr($value, 0, $maxLength);
    }

    protected function isSensitiveKey(string $key): bool
    {
        $k = mb_strtolower($key);

        foreach (['token', 'secret', 'password', 'authorization', 'api_key', 'key'] as $needle) {
            if (str_contains($k, $needle)) {
                return true;
            }
        }

        return in_array($k, [
            'email',
            'phone',
            'name',
            'full_name',
            'address',
            'street',
            'postal_code',
            'zip',
        ], true);
    }
}

