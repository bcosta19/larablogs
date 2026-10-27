<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\HttpFoundation\IpUtils;

class PublicArticleUrl
{
    /** @return array{host: string, port: int, addresses: list<string>} */
    public function resolve(string $url): array
    {
        $parts = parse_url($url);
        if ($parts === false || ! in_array($parts['scheme'] ?? '', ['http', 'https'], true)
            || isset($parts['user']) || isset($parts['pass']) || preg_match('/[\x00-\x20]/', $url)) {
            throw new RuntimeException('Invalid article URL.');
        }

        $host = trim($parts['host'] ?? '', '[]');
        $port = $parts['port'] ?? ($parts['scheme'] === 'https' ? 443 : 80);
        if ($host === '' || ! in_array($port, [80, 443], true)) {
            throw new RuntimeException('Invalid article host.');
        }

        $addresses = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : $this->addresses($host);
        if ($addresses === []) {
            throw new RuntimeException('Article host is unavailable.');
        }
        foreach ($addresses as $address) {
            if (! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
                || IpUtils::checkIp($address, ['100.64.0.0/10', '192.0.0.0/24', '192.0.2.0/24', '198.18.0.0/15', '198.51.100.0/24', '203.0.113.0/24', '224.0.0.0/4'])
                || (str_contains($address, ':') && (! IpUtils::checkIp($address, '2000::/3') || IpUtils::checkIp($address, '2001:db8::/32')))) {
                throw new RuntimeException('Only public article hosts are allowed.');
            }
        }

        return ['host' => $host, 'port' => $port, 'addresses' => $addresses];
    }

    /** @return list<string> */
    protected function addresses(string $host): array
    {
        $addresses = gethostbynamel($host) ?: [];
        foreach (dns_get_record($host, DNS_AAAA) ?: [] as $record) {
            if (isset($record['ipv6'])) {
                $addresses[] = $record['ipv6'];
            }
        }

        return array_values(array_unique($addresses));
    }
}
