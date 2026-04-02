<?php

namespace App\Services\Integrations;

use Illuminate\Support\Facades\Http;

class AmazonSpApiClient
{
    public function __construct(protected AwsSigV4Signer $signer) {}

    public function get(
        string $endpoint,
        string $path,
        array $query,
        string $lwaAccessToken,
        string $accessKeyId,
        string $secretAccessKey,
        string $region
    ): array {
        $url = rtrim($endpoint, '/').$path;

        $headersToSign = [
            'x-amz-access-token' => $lwaAccessToken,
            'accept' => 'application/json',
        ];

        $sig = $this->signer->sign(
            'GET',
            $url,
            $query,
            '',
            $accessKeyId,
            $secretAccessKey,
            $region,
            'execute-api',
            $headersToSign
        );

        $headers = array_merge($headersToSign, $sig);

        $response = Http::timeout(20)->retry(2, 200)->withHeaders($headers)->get($url, $query);

        if ($response->failed()) {
            throw new \RuntimeException('Amazon SP-API request failed: '.$response->status());
        }

        $data = $response->json();

        return is_array($data) ? $data : [];
    }
}
