<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case Planning = 'planning';
    case CreativeRequested = 'creative_requested';
    case Ready = 'ready';
    case Running = 'running';
    case Optimizing = 'optimizing';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Planning => 'Planning',
            self::CreativeRequested => 'Creative requested',
            self::Ready => 'Ready',
            self::Running => 'Running',
            self::Optimizing => 'Optimizing',
            self::Completed => 'Completed',
        };
    }

    public static function values(): array
    {
        return array_map(fn (self $s) => $s->value, self::cases());
    }

    public static function activeValues(): array
    {
        return [
            self::Running->value,
            self::Optimizing->value,
        ];
    }
}
