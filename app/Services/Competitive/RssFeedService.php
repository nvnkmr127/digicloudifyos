<?php

namespace App\Services\Competitive;

use App\Services\UrlEgressPolicy;
use Illuminate\Support\Facades\Http;

class RssFeedService
{
    public function fetch(string $url): array
    {
        $url = app(UrlEgressPolicy::class)->assertAllowed($url);

        $response = Http::timeout(20)
            ->retry(2, 200)
            ->withOptions(['allow_redirects' => false])
            ->get($url);
        if ($response->failed()) {
            throw new \RuntimeException('RSS fetch failed.');
        }

        $body = $response->body();
        $xml = @simplexml_load_string($body);

        if (! $xml) {
            throw new \RuntimeException('Invalid RSS XML.');
        }

        $items = [];

        if (isset($xml->channel->item)) {
            foreach ($xml->channel->item as $item) {
                $items[] = $this->normalizeItem($item);
            }
        }

        if (isset($xml->entry)) {
            foreach ($xml->entry as $entry) {
                $items[] = $this->normalizeAtomEntry($entry);
            }
        }

        return array_values(array_filter($items));
    }

    protected function normalizeItem($item): ?array
    {
        $title = isset($item->title) ? (string) $item->title : null;
        $link = isset($item->link) ? (string) $item->link : null;
        $guid = isset($item->guid) ? (string) $item->guid : null;
        $pubDate = isset($item->pubDate) ? (string) $item->pubDate : null;
        $author = isset($item->author) ? (string) $item->author : null;
        $description = isset($item->description) ? (string) $item->description : null;

        $publishedAt = $pubDate ? date_create($pubDate) : null;

        return [
            'external_id' => $guid ?: $link ?: null,
            'title' => $title,
            'url' => $link,
            'content' => $description,
            'author' => $author,
            'published_at' => $publishedAt ? $publishedAt->format('c') : null,
        ];
    }

    protected function normalizeAtomEntry($entry): ?array
    {
        $title = isset($entry->title) ? (string) $entry->title : null;
        $id = isset($entry->id) ? (string) $entry->id : null;
        $link = null;
        if (isset($entry->link)) {
            foreach ($entry->link as $l) {
                $href = $l->attributes()?->href;
                if ($href) {
                    $link = (string) $href;
                    break;
                }
            }
        }

        $published = isset($entry->published) ? (string) $entry->published : null;
        $updated = isset($entry->updated) ? (string) $entry->updated : null;
        $author = isset($entry->author->name) ? (string) $entry->author->name : null;
        $content = isset($entry->content) ? (string) $entry->content : (isset($entry->summary) ? (string) $entry->summary : null);

        $publishedAt = $published ?: $updated;

        return [
            'external_id' => $id ?: $link ?: null,
            'title' => $title,
            'url' => $link,
            'content' => $content,
            'author' => $author,
            'published_at' => $publishedAt ?: null,
        ];
    }
}
