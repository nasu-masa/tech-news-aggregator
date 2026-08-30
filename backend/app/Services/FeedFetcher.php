<?php

namespace App\Services;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Support\Facades\Http;

class FeedFetcher
{
    private const MAX_REDIRECTS = 5;

    private const MAX_BODY_BYTES = 10 * 1024 * 1024; // 10 MB

    public function __construct(
        private readonly FeedParser $feedParser,
        private readonly DnsResolver $dnsResolver,
    ) {}

    public function fetchXml(string $url): string
    {
        $seen = [];

        for ($redirect = 0; ; $redirect++) {
            [$url, $resolveDirective] = $this->validateAndPin($url);

            if (isset($seen[$url])) {
                throw new \InvalidArgumentException('リダイレクトループを検出しました。');
            }
            $seen[$url] = true;

            $response = Http::timeout(10)
                ->retry(2, 500)
                ->withOptions([
                    'allow_redirects' => false,
                    'connect_timeout' => 5,
                    'stream' => true,
                    'curl' => [
                        CURLOPT_RESOLVE => [$resolveDirective],
                        CURLOPT_MAXFILESIZE => self::MAX_BODY_BYTES,
                    ],
                ])
                ->get($url);

            if ($response->redirect()) {
                $response->toPsrResponse()->getBody()->close();

                if ($redirect >= self::MAX_REDIRECTS) {
                    throw new \InvalidArgumentException('リダイレクトの上限に達しました。');
                }

                $location = $response->header('Location');

                if (! $location) {
                    throw new \InvalidArgumentException('リダイレクト先URLがありません。');
                }

                $url = (string) UriResolver::resolve(
                    new Uri($url),
                    new Uri($location),
                );

                continue;
            }

            $response->throw();

            $stream = $response->toPsrResponse()->getBody();
            $body = '';

            while (! $stream->eof()) {
                $body .= $stream->read(65536);

                if (strlen($body) > self::MAX_BODY_BYTES) {
                    $stream->close();
                    throw new \InvalidArgumentException('レスポンスが大きすぎます。');
                }
            }

            return $body;
        }
    }

    public function fetch(string $url): array
    {
        return $this->feedParser->parse($this->fetchXml($url));
    }

    private function isIpv6LinkLocal(string $ip): bool
    {
        $binary = inet_pton($ip);

        if ($binary === false || strlen($binary) !== 16) {
            return false;
        }

        $firstByte = ord($binary[0]);
        $secondByte = ord($binary[1]);

        if ($firstByte === 0xFE && ($secondByte & 0xC0) === 0x80) {
            return true;
        }

        return false;
    }

    private function validateAndPin(string $url): array
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT) ?? 443;

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
            if (
                ! filter_var(
                    $ip,
                    FILTER_VALIDATE_IP,
                    FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
                )
                    || $this->isIpv6LinkLocal($ip)
            ) {
                throw new \InvalidArgumentException(
                    'このURLには接続できません。'
                );
            }
        }

        $pinnedIp = $ips[0];
        $resolveDirective = filter_var($pinnedIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
            ? "{$host}:{$port}:[{$pinnedIp}]"
            : "{$host}:{$port}:{$pinnedIp}";

        return [$url, $resolveDirective];
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
