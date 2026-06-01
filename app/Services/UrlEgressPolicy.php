<?php

namespace App\Services;

use Illuminate\Support\Str;

class UrlEgressPolicy
{
    public function assertAllowed(string $url): string
    {
        $normalized = $this->normalize($url);
        if (! $this->isAllowed($normalized)) {
            throw new \InvalidArgumentException('URL is not allowed.');
        }

        return $normalized;
    }

    public function normalize(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            throw new \InvalidArgumentException('URL is required.');
        }

        $parts = parse_url($url);
        if ($parts === false) {
            throw new \InvalidArgumentException('Invalid URL.');
        }

        $scheme = $parts['scheme'] ?? null;
        if (! is_string($scheme) || $scheme === '') {
            throw new \InvalidArgumentException('URL scheme is required.');
        }

        $scheme = mb_strtolower($scheme);
        if (! in_array($scheme, ['https', 'http'], true)) {
            throw new \InvalidArgumentException('URL scheme is not allowed.');
        }

        $host = $parts['host'] ?? null;
        if (! is_string($host) || $host === '') {
            throw new \InvalidArgumentException('URL host is required.');
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            throw new \InvalidArgumentException('URL userinfo is not allowed.');
        }

        return $url;
    }

    public function isAllowed(string $url): bool
    {
        $parts = parse_url($url);
        if ($parts === false) {
            return false;
        }

        $scheme = mb_strtolower((string) ($parts['scheme'] ?? ''));
        $host = (string) ($parts['host'] ?? '');
        $port = $parts['port'] ?? null;

        if ($scheme === 'http' && ! app()->environment(['local', 'testing'])) {
            return false;
        }

        if ($host === '' || $this->isBlockedHost($host)) {
            return false;
        }

        if (is_int($port) && ! $this->isAllowedPort($port)) {
            return false;
        }

        $ips = $this->resolveIps($host);
        if ($ips === []) {
            return false;
        }

        foreach ($ips as $ip) {
            if (! $this->isPublicIp($ip)) {
                return false;
            }
        }

        return true;
    }

    protected function isAllowedPort(int $port): bool
    {
        if (in_array($port, [80, 443], true)) {
            return true;
        }

        return app()->environment(['local', 'testing']);
    }

    protected function isBlockedHost(string $host): bool
    {
        $h = mb_strtolower(rtrim($host, '.'));

        if ($h === 'localhost') {
            return true;
        }

        if (Str::endsWith($h, ['.local', '.internal'])) {
            return true;
        }

        return false;
    }

    protected function resolveIps(string $host): array
    {
        $ips = [];

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return [$host];
        }

        if (function_exists('dns_get_record')) {
            $records = @dns_get_record($host, DNS_A + DNS_AAAA);
            if (is_array($records)) {
                foreach ($records as $r) {
                    if (isset($r['ip']) && is_string($r['ip'])) {
                        $ips[] = $r['ip'];
                    }
                    if (isset($r['ipv6']) && is_string($r['ipv6'])) {
                        $ips[] = $r['ipv6'];
                    }
                }
            }
        }

        $ipv4 = gethostbyname($host);
        if (is_string($ipv4) && $ipv4 !== $host && filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ips[] = $ipv4;
        }

        return array_values(array_unique($ips));
    }

    protected function isPublicIp(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $long = ip2long($ip);
            if ($long === false) {
                return false;
            }

            $ranges = [
                ['0.0.0.0', '0.255.255.255'],
                ['10.0.0.0', '10.255.255.255'],
                ['100.64.0.0', '100.127.255.255'],
                ['127.0.0.0', '127.255.255.255'],
                ['169.254.0.0', '169.254.255.255'],
                ['172.16.0.0', '172.31.255.255'],
                ['192.0.0.0', '192.0.0.255'],
                ['192.168.0.0', '192.168.255.255'],
                ['198.18.0.0', '198.19.255.255'],
                ['224.0.0.0', '239.255.255.255'],
                ['240.0.0.0', '255.255.255.255'],
            ];

            foreach ($ranges as [$start, $end]) {
                $s = ip2long($start);
                $e = ip2long($end);
                if ($s !== false && $e !== false && $long >= $s && $long <= $e) {
                    return false;
                }
            }

            return true;
        }

        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return false;
        }

        $packed = @inet_pton($ip);
        if (! is_string($packed) || strlen($packed) !== 16) {
            return false;
        }

        $blocked = [
            ['::', 128],
            ['::1', 128],
            ['fe80::', 10],
            ['fc00::', 7],
            ['ff00::', 8],
        ];

        foreach ($blocked as [$cidrIp, $prefix]) {
            if ($this->ipv6InCidr($ip, $cidrIp, $prefix)) {
                return false;
            }
        }

        return true;
    }

    protected function ipv6InCidr(string $ip, string $cidrIp, int $prefix): bool
    {
        $ipBin = @inet_pton($ip);
        $cidrBin = @inet_pton($cidrIp);
        if (! is_string($ipBin) || ! is_string($cidrBin)) {
            return false;
        }

        $bytes = intdiv($prefix, 8);
        $bits = $prefix % 8;

        if ($bytes > 0 && substr($ipBin, 0, $bytes) !== substr($cidrBin, 0, $bytes)) {
            return false;
        }

        if ($bits === 0) {
            return true;
        }

        $mask = chr((0xFF << (8 - $bits)) & 0xFF);

        return (ord($ipBin[$bytes]) & ord($mask)) === (ord($cidrBin[$bytes]) & ord($mask));
    }
}
