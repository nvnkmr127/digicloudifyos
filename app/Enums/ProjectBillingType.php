<?php

namespace App\Enums;

enum ProjectBillingType: string
{
    case Fixed = 'fixed';
    case Hourly = 'hourly';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'Fixed',
            self::Hourly => 'Hourly',
        };
    }

    public static function values(): array
    {
        return array_map(fn (self $t) => $t->value, self::cases());
    }
}
