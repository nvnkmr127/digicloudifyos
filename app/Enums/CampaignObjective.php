<?php

namespace App\Enums;

enum CampaignObjective: string
{
    case Awareness = 'AWARENESS';
    case Traffic = 'TRAFFIC';
    case Engagement = 'ENGAGEMENT';
    case Leads = 'LEADS';
    case Sales = 'SALES';
    case AppPromotion = 'APP_PROMOTION';

    public function label(): string
    {
        return match ($this) {
            self::Awareness => 'Awareness',
            self::Traffic => 'Traffic',
            self::Engagement => 'Engagement',
            self::Leads => 'Leads',
            self::Sales => 'Sales',
            self::AppPromotion => 'App promotion',
        };
    }

    public static function values(): array
    {
        return array_map(fn (self $o) => $o->value, self::cases());
    }
}

