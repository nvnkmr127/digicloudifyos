<?php

namespace App\Jobs\SiteHealth;

use App\Models\Client;
use App\Models\DomainExpiryCheck;
use App\Models\Organization;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class CheckDomainExpiry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(public ?string $date = null)
    {
        $this->onQueue('intelligence');
    }

    public function handle(): void
    {
        $date = $this->date ?? now()->subDay()->toDateString();

        foreach (Organization::all() as $org) {
            $clients = Client::where('organization_id', $org->id)
                ->active()
                ->whereNotNull('website_url')
                ->get(['id', 'organization_id', 'website_url']);

            foreach ($clients as $client) {
                $domain = $this->extractDomain((string) $client->website_url);
                if (! $domain) {
                    continue;
                }

                $rdap = Http::get('https://rdap.org/domain/'.rawurlencode($domain));
                if ($rdap->failed()) {
                    continue;
                }

                $json = $rdap->json();
                if (! is_array($json)) {
                    continue;
                }

                $expires = $this->extractExpiryDate($json);
                $registrar = $this->extractRegistrar($json);
                $daysRemaining = $expires ? Carbon::now()->startOfDay()->diffInDays($expires->startOfDay(), false) : null;

                DomainExpiryCheck::updateOrCreate(
                    [
                        'organization_id' => $org->id,
                        'client_id' => $client->id,
                        'check_date' => $date,
                        'domain' => $domain,
                    ],
                    [
                        'expires_on' => $expires?->toDateString(),
                        'days_remaining' => $daysRemaining,
                        'registrar' => $registrar,
                        'raw_data' => $json,
                    ]
                );

                if ($daysRemaining !== null && $daysRemaining <= 45) {
                    $this->createAlert($org->id, $client->id, $domain, $daysRemaining);
                }
            }
        }
    }

    protected function createAlert(string $orgId, string $clientId, string $domain, int $daysRemaining): void
    {
        $title = 'Renew domain';

        $exists = Task::where('organization_id', $orgId)
            ->where('client_id', $clientId)
            ->where('title', $title)
            ->where('status', '!=', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->exists();

        if ($exists) {
            return;
        }

        Task::create([
            'organization_id' => $orgId,
            'client_id' => $clientId,
            'title' => $title,
            'description' => 'Domain '.$domain.' expires in '.$daysRemaining.' days. Renew to avoid downtime and SEO impact.',
            'status' => 'pending',
            'priority' => $daysRemaining <= 14 ? 'high' : 'medium',
            'deadline' => now()->addDays(7),
        ]);
    }

    protected function extractDomain(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            $url = 'https://'.$url;
        }

        $parts = parse_url($url);
        $host = $parts['host'] ?? null;
        if (! is_string($host) || $host === '') {
            return null;
        }
        $host = strtolower($host);
        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        return $host;
    }

    protected function extractExpiryDate(array $json): ?Carbon
    {
        $events = $json['events'] ?? null;
        if (! is_array($events)) {
            return null;
        }

        foreach ($events as $event) {
            if (! is_array($event)) {
                continue;
            }
            $action = $event['eventAction'] ?? null;
            $date = $event['eventDate'] ?? null;
            if (! is_string($action) || ! is_string($date)) {
                continue;
            }
            $action = strtolower($action);
            if ($action === 'expiration' || $action === 'expiry' || $action === 'expiration date') {
                try {
                    return Carbon::parse($date);
                } catch (\Throwable $e) {
                    return null;
                }
            }
        }

        return null;
    }

    protected function extractRegistrar(array $json): ?string
    {
        $entities = $json['entities'] ?? null;
        if (! is_array($entities)) {
            return null;
        }

        foreach ($entities as $entity) {
            if (! is_array($entity)) {
                continue;
            }
            $roles = $entity['roles'] ?? [];
            if (! is_array($roles)) {
                continue;
            }
            $isRegistrar = collect($roles)->map(fn ($r) => strtolower((string) $r))->contains('registrar');
            if (! $isRegistrar) {
                continue;
            }

            $vcard = $entity['vcardArray'] ?? null;
            if (is_array($vcard) && isset($vcard[1]) && is_array($vcard[1])) {
                foreach ($vcard[1] as $row) {
                    if (! is_array($row) || ($row[0] ?? null) !== 'fn') {
                        continue;
                    }
                    $name = $row[3] ?? null;
                    if (is_string($name) && $name !== '') {
                        return $name;
                    }
                }
            }
        }

        return null;
    }
}
