<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FeedFetcher
{
    public function __construct(
        private readonly FeedParser $feedParser,
        private readonly DnsResolver $dnsResolver,
    ) {}

    public function fetch(string $url): array
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);

        if (
            filter_var($url, FILTER_VALIDATE_URL) === false
            || ! is_string($scheme)
            || strtolower($scheme) !== 'https'
            || ! is_string($host)
        ) {
            throw new \InvalidArgumentException(
                'フィードURLは有効なHTTPS URLを指定してください。'
            );
        }

        $ips = $this->resolveHost($host);

        foreach ($ips as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new \InvalidArgumentException(
                    'このURLには接続できません。'
                );
            }
        }

        $pinnedIp = $ips[0];
        $resolveDirective = filter_var($pinnedIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
            ? "{$host}:443:[{$pinnedIp}]"
            : "{$host}:443:{$pinnedIp}";

        $xml = Http::timeout(10)
            ->retry(2, 500)
            ->withOptions([
                'allow_redirects' => false,
                'curl' => [
                    CURLOPT_RESOLVE => [$resolveDirective],
                ],
            ])
            ->get($url)
            ->throw()
            ->body();

        return $this->feedParser->parse($xml);
    }

    private function resolveHost(string $host): array
    {
        // parse_url keeps brackets on IPv6 literals (e.g. "[::1]") — strip them
        // before IP detection and DNS lookup so both use the same normalized value.
        $bareHost = trim($host, '[]');

        if (filter_var($bareHost, FILTER_VALIDATE_IP)) {
            return [$bareHost];
        }

        $ips = [
            ...$this->dnsResolver->resolveIpv4($bareHost),
            ...$this->dnsResolver->resolveIpv6($bareHost),
        ];

        if (empty($ips)) {
            throw new \InvalidArgumentException(
                'フィードURLのホスト名を解決できませんでした。'
            );
        }

        return $ips;
    }
}
