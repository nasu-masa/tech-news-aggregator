<?php

namespace App\Services;

class DnsResolver
{
    /** @return string[] */
    public function resolveIpv4(string $host): array
    {
        return gethostbynamel($host) ?: [];
    }

    /** @return string[] */
    public function resolveIpv6(string $host): array
    {
        $ips = [];
        foreach (dns_get_record($host, DNS_AAAA) ?: [] as $record) {
            $ips[] = $record['ipv6'];
        }

        return $ips;
    }
}
