<?php

namespace App\Services\Seo;

use App\Models\SeoSiteAudit;
use App\Models\SeoSiteAuditIssue;
use Illuminate\Support\Facades\Http;

class SiteAuditService
{
    public function run(string $orgId, string $clientId, string $date, string $baseUrl): ?SeoSiteAudit
    {
        $baseUrl = $this->normalizeUrl($baseUrl);
        if (! $baseUrl) {
            return null;
        }

        $audit = SeoSiteAudit::updateOrCreate(
            [
                'organization_id' => $orgId,
                'client_id' => $clientId,
                'audit_date' => $date,
            ],
            [
                'base_url' => $baseUrl,
                'summary' => null,
            ]
        );

        $audit->issues()->delete();

        $html = $this->fetchHtml($baseUrl);
        if ($html === null) {
            $this->issue($audit, 'critical', 'fetch_failed', $baseUrl, 'Homepage fetch failed', []);
            $audit->update(['summary' => ['ok' => false]]);

            return $audit;
        }

        $doc = new \DOMDocument;
        libxml_use_internal_errors(true);
        $loaded = $doc->loadHTML($html);
        libxml_clear_errors();

        if (! $loaded) {
            $this->issue($audit, 'high', 'parse_failed', $baseUrl, 'Homepage HTML parse failed', []);
            $audit->update(['summary' => ['ok' => false]]);

            return $audit;
        }

        $xpath = new \DOMXPath($doc);

        $title = trim((string) ($xpath->query('//title')->item(0)?->textContent ?? ''));
        if ($title === '') {
            $this->issue($audit, 'high', 'missing_title', $baseUrl, 'Missing <title>', []);
        } else {
            $len = mb_strlen($title);
            if ($len < 10 || $len > 65) {
                $this->issue($audit, 'medium', 'title_length', $baseUrl, 'Title length out of range', [
                    'title' => $title,
                    'length' => $len,
                ]);
            }
        }

        $metaDesc = $this->metaContent($xpath, 'description');
        if ($metaDesc === '') {
            $this->issue($audit, 'medium', 'missing_meta_description', $baseUrl, 'Missing meta description', []);
        } else {
            $len = mb_strlen($metaDesc);
            if ($len < 50 || $len > 170) {
                $this->issue($audit, 'low', 'meta_description_length', $baseUrl, 'Meta description length out of range', [
                    'length' => $len,
                ]);
            }
        }

        $robots = strtolower($this->metaContent($xpath, 'robots'));
        if (str_contains($robots, 'noindex')) {
            $this->issue($audit, 'critical', 'noindex_detected', $baseUrl, 'Robots meta contains noindex', [
                'robots' => $robots,
            ]);
        }

        $canonical = $xpath->query("//link[@rel='canonical']")->item(0)?->attributes?->getNamedItem('href')?->nodeValue;
        $canonical = is_string($canonical) ? trim($canonical) : '';
        if ($canonical === '') {
            $this->issue($audit, 'medium', 'missing_canonical', $baseUrl, 'Missing canonical tag', []);
        }

        $h1 = trim((string) ($xpath->query('//h1')->item(0)?->textContent ?? ''));
        if ($h1 === '') {
            $this->issue($audit, 'medium', 'missing_h1', $baseUrl, 'Missing H1 on homepage', []);
        }

        $schemaNodes = $xpath->query("//script[@type='application/ld+json']");
        $schemaRaw = '';
        if ($schemaNodes) {
            foreach ($schemaNodes as $n) {
                $schemaRaw .= "\n".(string) $n->textContent;
            }
        }
        $schemaLower = strtolower($schemaRaw);
        $hasLocalBusiness = str_contains($schemaLower, 'localbusiness');
        $hasOrganization = str_contains($schemaLower, '"@type"') && str_contains($schemaLower, 'organization');
        $hasFaq = str_contains($schemaLower, 'faqpage');

        if (! $hasOrganization) {
            $this->issue($audit, 'low', 'missing_org_schema', $baseUrl, 'Missing Organization schema', []);
        }
        if (! $hasLocalBusiness) {
            $this->issue($audit, 'medium', 'missing_localbusiness_schema', $baseUrl, 'Missing LocalBusiness schema (local SEO)', []);
        }
        if (! $hasFaq) {
            $this->issue($audit, 'low', 'missing_faq_schema', $baseUrl, 'Missing FAQPage schema (AEO)', []);
        }

        $links = $this->extractInternalLinks($xpath, $baseUrl);
        $checked = 0;
        $broken = 0;
        $samples = [];

        foreach (array_slice($links, 0, 10) as $link) {
            $checked++;
            $code = $this->checkUrl($link);
            if ($code === null || $code >= 400) {
                $broken++;
                $samples[] = ['url' => $link, 'status' => $code];
            }
        }

        if ($broken > 0) {
            $this->issue($audit, $broken >= 3 ? 'high' : 'medium', 'broken_internal_links', $baseUrl, 'Broken internal links detected', [
                'checked' => $checked,
                'broken' => $broken,
                'samples' => $samples,
            ]);
        }

        $audit->update([
            'summary' => [
                'ok' => true,
                'title' => $title,
                'meta_description_present' => $metaDesc !== '',
                'canonical_present' => $canonical !== '',
                'h1_present' => $h1 !== '',
                'schema_org_present' => $hasOrganization,
                'schema_localbusiness_present' => $hasLocalBusiness,
                'schema_faq_present' => $hasFaq,
                'internal_links_checked' => $checked,
                'broken_internal_links' => $broken,
            ],
        ]);

        return $audit;
    }

    protected function issue(SeoSiteAudit $audit, string $severity, string $type, ?string $url, string $title, array $payload): void
    {
        SeoSiteAuditIssue::create([
            'organization_id' => $audit->organization_id,
            'client_id' => $audit->client_id,
            'seo_site_audit_id' => $audit->id,
            'severity' => $severity,
            'issue_type' => $type,
            'url' => $url,
            'title' => $title,
            'payload' => $payload ?: null,
        ]);
    }

    protected function fetchHtml(string $url): ?string
    {
        $resp = Http::timeout(15)->withHeaders([
            'User-Agent' => 'DCOS-SiteAudit/1.0',
            'Accept' => 'text/html,application/xhtml+xml',
        ])->get($url);

        if ($resp->failed()) {
            return null;
        }

        return $resp->body();
    }

    protected function metaContent(\DOMXPath $xpath, string $name): string
    {
        $node = $xpath->query("//meta[translate(@name,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')='{$name}']")->item(0);
        $content = $node?->attributes?->getNamedItem('content')?->nodeValue;

        return is_string($content) ? trim($content) : '';
    }

    protected function extractInternalLinks(\DOMXPath $xpath, string $baseUrl): array
    {
        $baseHost = parse_url($baseUrl, PHP_URL_HOST);
        $nodes = $xpath->query('//a[@href]');
        $links = [];

        foreach ($nodes as $node) {
            $href = $node->attributes?->getNamedItem('href')?->nodeValue;
            if (! is_string($href)) {
                continue;
            }
            $href = trim($href);
            if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                continue;
            }

            $abs = $this->toAbsoluteUrl($baseUrl, $href);
            if (! $abs) {
                continue;
            }
            $host = parse_url($abs, PHP_URL_HOST);
            if (! $host || $host !== $baseHost) {
                continue;
            }

            $links[$abs] = true;
        }

        return array_keys($links);
    }

    protected function checkUrl(string $url): ?int
    {
        try {
            $resp = Http::timeout(10)->head($url);

            return $resp->status();
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function normalizeUrl(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }
        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            $url = 'https://'.$url;
        }

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }

    protected function toAbsoluteUrl(string $baseUrl, string $href): ?string
    {
        if (filter_var($href, FILTER_VALIDATE_URL)) {
            return $href;
        }

        $base = parse_url($baseUrl);
        if (! is_array($base)) {
            return null;
        }
        $scheme = $base['scheme'] ?? 'https';
        $host = $base['host'] ?? null;
        if (! is_string($host) || $host === '') {
            return null;
        }

        if (str_starts_with($href, '//')) {
            return $scheme.':'.$href;
        }

        if (str_starts_with($href, '/')) {
            return $scheme.'://'.$host.$href;
        }

        $path = $base['path'] ?? '/';
        $dir = rtrim(str_replace('\\', '/', dirname($path)), '/');
        $dir = $dir === '' ? '' : $dir;

        return $scheme.'://'.$host.$dir.'/'.$href;
    }
}
