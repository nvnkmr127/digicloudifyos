<?php

namespace App\Services\Integrations;

class AwsSigV4Signer
{
    public function sign(
        string $method,
        string $url,
        array $query,
        string $payload,
        string $accessKeyId,
        string $secretAccessKey,
        string $region,
        string $service,
        array $headersToSign
    ): array {
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';
        $path = $parsed['path'] ?? '/';

        $amzDate = gmdate('Ymd\THis\Z');
        $dateStamp = gmdate('Ymd');

        $headers = array_change_key_case($headersToSign, CASE_LOWER);
        $headers['host'] = $host;
        $headers['x-amz-date'] = $amzDate;

        ksort($headers);

        $canonicalHeaders = '';
        foreach ($headers as $k => $v) {
            $canonicalHeaders .= $k.':'.trim((string) $v)."\n";
        }

        $signedHeaders = implode(';', array_keys($headers));

        $canonicalQuery = $this->buildCanonicalQuery($query);
        $payloadHash = hash('sha256', $payload);

        $canonicalRequest = implode("\n", [
            strtoupper($method),
            $this->encodePath($path),
            $canonicalQuery,
            $canonicalHeaders,
            $signedHeaders,
            $payloadHash,
        ]);

        $algorithm = 'AWS4-HMAC-SHA256';
        $credentialScope = "{$dateStamp}/{$region}/{$service}/aws4_request";
        $stringToSign = implode("\n", [
            $algorithm,
            $amzDate,
            $credentialScope,
            hash('sha256', $canonicalRequest),
        ]);

        $signingKey = $this->getSignatureKey($secretAccessKey, $dateStamp, $region, $service);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        $authorization = "{$algorithm} Credential={$accessKeyId}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

        return [
            'Authorization' => $authorization,
            'x-amz-date' => $amzDate,
            'host' => $host,
        ];
    }

    protected function buildCanonicalQuery(array $query): string
    {
        $pairs = [];
        foreach ($query as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    $pairs[] = [$key, (string) $v];
                }
            } else {
                $pairs[] = [$key, (string) $value];
            }
        }

        usort($pairs, function ($a, $b) {
            if ($a[0] === $b[0]) {
                return $a[1] <=> $b[1];
            }

            return $a[0] <=> $b[0];
        });

        $encoded = [];
        foreach ($pairs as [$k, $v]) {
            $encoded[] = rawurlencode((string) $k).'='.rawurlencode((string) $v);
        }

        return implode('&', $encoded);
    }

    protected function encodePath(string $path): string
    {
        $segments = explode('/', $path);
        $encoded = array_map(fn ($s) => rawurlencode($s), $segments);
        $result = implode('/', $encoded);

        return $result === '' ? '/' : $result;
    }

    protected function getSignatureKey(string $key, string $dateStamp, string $regionName, string $serviceName): string
    {
        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4'.$key, true);
        $kRegion = hash_hmac('sha256', $regionName, $kDate, true);
        $kService = hash_hmac('sha256', $serviceName, $kRegion, true);

        return hash_hmac('sha256', 'aws4_request', $kService, true);
    }
}
