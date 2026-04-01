<?php

namespace App\Jobs\Integrations;

use App\Models\ClientChannelConnection;
use App\Models\GoogleMerchantCenterDailyMetric;
use App\Models\IntegrationSyncRun;
use App\Services\Integrations\GoogleTokenService;
use App\Services\Integrations\IntegrationAlertService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SyncGoogleMerchantCenterDailyMetrics implements ShouldQueue
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

    public function handle(GoogleTokenService $tokenService, IntegrationAlertService $alertService): void
    {
        $date = $this->date ?? now()->subDay()->toDateString();

        $connection = ClientChannelConnection::where('organization_id', $this->organizationId)
            ->where('client_id', $this->clientId)
            ->where('channel_type', 'google_merchant_center')
            ->active()
            ->with('credential')
            ->first();

        if (! $connection || ! $connection->credential) {
            return;
        }

        $run = IntegrationSyncRun::updateOrCreate(
            [
                'organization_id' => $this->organizationId,
                'client_id' => $this->clientId,
                'channel_type' => 'google_merchant_center',
                'run_date' => $date,
            ],
            [
                'client_channel_connection_id' => $connection->id,
                'status' => 'running',
                'attempt' => 0,
                'started_at' => now(),
                'finished_at' => null,
                'next_retry_at' => null,
                'error_message' => null,
            ]
        );

        $run->increment('attempt');

        try {
            $accessToken = $tokenService->getValidAccessToken($connection->credential);
            $merchantId = $this->resolveMerchantId($connection, $accessToken);

            if (! $merchantId) {
                throw new \RuntimeException('No Merchant Center account available.');
            }

            $maxPages = 20;
            $maxResults = 250;
            $pagesFetched = 0;
            $recordsFetched = 0;
            $truncated = false;

            $itemsChecked = 0;
            $itemsDisapproved = 0;
            $itemsPending = 0;
            $itemsApproved = 0;
            $issueCount = 0;
            $issueBreakdown = [];
            $topIssueExamples = [];

            $pageToken = null;
            do {
                $params = [
                    'maxResults' => $maxResults,
                ];
                if (is_string($pageToken) && $pageToken !== '') {
                    $params['pageToken'] = $pageToken;
                }

                $response = Http::withToken($accessToken)->get(
                    "https://shoppingcontent.googleapis.com/content/v2.1/{$merchantId}/productstatuses",
                    $params
                );

                if ($response->failed()) {
                    throw new \RuntimeException('Merchant Center product status fetch failed.');
                }

                $payload = $response->json();
                $resources = $payload['resources'] ?? [];
                $pageToken = $payload['nextPageToken'] ?? null;

                $pagesFetched++;

                foreach ($resources as $status) {
                    if (! is_array($status)) continue;
                    $itemsChecked++;
                    $recordsFetched++;
                    $productId = isset($status['productId']) ? (string) $status['productId'] : null;

                    $destinationStatuses = $status['destinationStatuses'] ?? [];
                    $overall = null;
                    foreach ($destinationStatuses as $ds) {
                        if (! is_array($ds)) continue;
                        $overall = $overall ?? ($ds['status'] ?? null);
                        if (($ds['destination'] ?? null) === 'Shopping') {
                            $overall = $ds['status'] ?? $overall;
                            break;
                        }
                    }

                    if ($overall === 'disapproved') $itemsDisapproved++;
                    elseif ($overall === 'pending') $itemsPending++;
                    elseif ($overall === 'approved') $itemsApproved++;

                    $issues = $status['itemLevelIssues'] ?? [];
                    if (is_array($issues)) {
                        foreach ($issues as $issue) {
                            if (! is_array($issue)) continue;
                            $issueCount++;
                            $code = $issue['code'] ?? 'unknown';
                            $code = is_string($code) && $code !== '' ? $code : 'unknown';
                            $issueBreakdown[$code] = ($issueBreakdown[$code] ?? 0) + 1;

                            if ($productId) {
                                $topIssueExamples[$code] = $topIssueExamples[$code] ?? [];
                                if (count($topIssueExamples[$code]) < 5) {
                                    $topIssueExamples[$code][] = $productId;
                                }
                            }
                        }
                    }
                }

                if ($pagesFetched >= $maxPages && $pageToken) {
                    $truncated = true;
                    break;
                }
            } while ($pageToken);

            [$feedCount, $feedIssueCount, $feedStatuses] = $this->fetchFeedStatuses($merchantId, $accessToken);

            GoogleMerchantCenterDailyMetric::updateOrCreate(
                [
                    'organization_id' => $this->organizationId,
                    'client_id' => $this->clientId,
                    'metric_date' => $date,
                    'merchant_id' => $merchantId,
                ],
                [
                    'client_channel_connection_id' => $connection->id,
                    'items_checked' => $itemsChecked,
                    'items_disapproved' => $itemsDisapproved,
                    'items_pending' => $itemsPending,
                    'items_approved' => $itemsApproved,
                    'issue_count' => $issueCount,
                    'issue_breakdown' => $issueBreakdown,
                    'feed_count' => $feedCount,
                    'feed_issue_count' => $feedIssueCount,
                    'feed_statuses' => $feedStatuses,
                    'top_issue_examples' => $topIssueExamples,
                    'pages_fetched' => $pagesFetched,
                    'records_fetched' => $recordsFetched,
                    'truncated' => $truncated,
                    'raw_data' => [
                        'merchant_id' => $merchantId,
                        'max_pages' => $maxPages,
                        'max_results' => $maxResults,
                    ],
                ]
            );

            $connection->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'success',
                'account_id' => $merchantId,
                'account_name' => 'Merchant ' . $merchantId,
            ]);

            $run->update([
                'status' => 'success',
                'finished_at' => now(),
                'error_message' => null,
                'metrics' => [
                    'items_checked' => $itemsChecked,
                    'items_disapproved' => $itemsDisapproved,
                    'issue_count' => $issueCount,
                    'feed_count' => $feedCount,
                    'feed_issue_count' => $feedIssueCount,
                    'truncated' => $truncated,
                ],
            ]);
        } catch (\Throwable $e) {
            $connection->update([
                'last_sync_status' => 'failed',
            ]);

            $run->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error_message' => $e->getMessage(),
                'next_retry_at' => now()->addMinutes(15),
            ]);

            if ($this->attempts() >= $this->tries) {
                $alertService->notifySyncFailure(
                    $this->organizationId,
                    $this->clientId,
                    'google_merchant_center',
                    $date,
                    $e->getMessage()
                );
            }

            throw $e;
        }
    }

    protected function fetchFeedStatuses(string $merchantId, string $accessToken): array
    {
        $feedsResponse = Http::withToken($accessToken)->get("https://shoppingcontent.googleapis.com/content/v2.1/{$merchantId}/datafeeds");
        if ($feedsResponse->failed()) {
            return [0, 0, null];
        }

        $feeds = $feedsResponse->json()['resources'] ?? [];
        if (! is_array($feeds)) {
            return [0, 0, null];
        }

        $statuses = [];
        $feedIssueCount = 0;

        foreach (array_slice($feeds, 0, 25) as $feed) {
            if (! is_array($feed) || ! isset($feed['id'])) continue;
            $feedId = (string) $feed['id'];
            $feedName = isset($feed['name']) ? (string) $feed['name'] : null;

            $statusResponse = Http::withToken($accessToken)->get("https://shoppingcontent.googleapis.com/content/v2.1/{$merchantId}/datafeedstatuses/{$feedId}");
            if ($statusResponse->failed()) {
                $feedIssueCount++;
                $statuses[] = [
                    'id' => $feedId,
                    'name' => $feedName,
                    'status' => 'error',
                ];
                continue;
            }

            $s = $statusResponse->json();
            $processingStatus = $s['processingStatus'] ?? null;
            $itemsTotal = (int) ($s['itemsTotal'] ?? 0);
            $itemsValid = (int) ($s['itemsValid'] ?? 0);
            $itemsInvalid = (int) ($s['itemsInvalid'] ?? 0);
            $errors = $s['errors'] ?? [];
            $errorsCount = is_array($errors) ? count($errors) : 0;
            $feedIssueCount += $errorsCount;

            $statuses[] = [
                'id' => $feedId,
                'name' => $feedName,
                'processing_status' => $processingStatus,
                'items_total' => $itemsTotal,
                'items_valid' => $itemsValid,
                'items_invalid' => $itemsInvalid,
                'errors_count' => $errorsCount,
            ];
        }

        return [count($feeds), $feedIssueCount, $statuses];
    }

    protected function resolveMerchantId(ClientChannelConnection $connection, string $accessToken): ?string
    {
        if (is_string($connection->account_id) && $connection->account_id !== '') {
            return $connection->account_id;
        }

        $response = Http::withToken($accessToken)->get('https://shoppingcontent.googleapis.com/content/v2.1/accounts/authinfo');
        if ($response->failed()) {
            return null;
        }

        $ids = $response->json()['accountIdentifiers'] ?? [];
        $first = $ids[0] ?? null;
        if (! is_array($first) || ! isset($first['merchantId'])) {
            return null;
        }

        $merchantId = (string) $first['merchantId'];

        $metadata = $connection->metadata ?? [];
        $metadata['authinfo'] = $first;

        $connection->update([
            'account_id' => $merchantId,
            'account_name' => 'Merchant ' . $merchantId,
            'metadata' => $metadata,
        ]);

        return $merchantId;
    }
}
