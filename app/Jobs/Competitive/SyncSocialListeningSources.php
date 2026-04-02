<?php

namespace App\Jobs\Competitive;

use App\Models\SocialListeningEvent;
use App\Models\SocialListeningSource;
use App\Services\Competitive\RssFeedService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncSocialListeningSources implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(
        public string $organizationId,
        public string $clientId,
        public ?string $date = null
    ) {
        $this->onQueue('intelligence');
    }

    public function handle(RssFeedService $rss): void
    {
        $date = $this->date ?? now()->subDay()->toDateString();

        $sources = SocialListeningSource::where('organization_id', $this->organizationId)
            ->where('client_id', $this->clientId)
            ->where('is_active', true)
            ->where('source_type', 'rss')
            ->get();

        foreach ($sources as $source) {
            if (! $source->source_url) {
                continue;
            }

            $items = $rss->fetch($source->source_url);

            foreach ($items as $item) {
                $externalId = $item['external_id'] ?? null;
                if (! is_string($externalId) || $externalId === '') {
                    continue;
                }

                $publishedAt = $item['published_at'] ?? null;
                $publishedAtTs = is_string($publishedAt) ? date_create($publishedAt) : null;
                $eventDate = $publishedAtTs ? $publishedAtTs->format('Y-m-d') : $date;

                SocialListeningEvent::updateOrCreate(
                    [
                        'organization_id' => $this->organizationId,
                        'client_id' => $this->clientId,
                        'source_type' => 'rss',
                        'external_id' => $externalId,
                    ],
                    [
                        'client_competitor_id' => $source->client_competitor_id,
                        'title' => $item['title'] ?? null,
                        'url' => $item['url'] ?? null,
                        'content' => $item['content'] ?? null,
                        'author' => $item['author'] ?? null,
                        'published_at' => $publishedAtTs ? $publishedAtTs->format('Y-m-d H:i:s') : null,
                        'event_date' => $eventDate,
                        'raw_data' => $item,
                    ]
                );
            }

            $source->update([
                'last_checked_at' => now(),
            ]);
        }
    }
}
