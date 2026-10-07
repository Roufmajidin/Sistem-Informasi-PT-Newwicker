<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;
use Illuminate\Support\Facades\Log;

class MikrotikService
{
    /*
    |--------------------------------------------------------------------------
    | CONNECT TO MIKROTIK
    |--------------------------------------------------------------------------
    */

    protected function client(): Client
    {
        $config = new Config([
            'host' => env('MIKROTIK_HOST'),
            'user' => env('MIKROTIK_USER'),
            'pass' => env('MIKROTIK_PASSWORD'),
            'port' => (int) env('MIKROTIK_PORT', 8728),
        ]);

        return new Client($config);
    }


    /*
    |--------------------------------------------------------------------------
    | GET ALL INTERFACES
    |--------------------------------------------------------------------------
    */

    public function getInterfaces(): array
    {
        $client = $this->client();

        return $client
            ->query(new Query('/interface/print'))
            ->read();
    }


    /*
    |--------------------------------------------------------------------------
    | REALTIME INTERFACE TRAFFIC
    |--------------------------------------------------------------------------
    |
    | Interface yang dimonitor:
    |
    | ether1
    | bridge1
    | bridgeproduksi
    |
    */

    public function monitorInterfaces(array $interfaces): array
    {
        $client = $this->client();

        $result = [];

        foreach ($interfaces as $interface) {

            try {

                $query = new Query('/interface/monitor-traffic');

                $query->equal('interface', $interface);
                $query->equal('once');

                $data = $client
                    ->query($query)
                    ->read();

                $item = $data[0] ?? [];

                $rx = (float) ($item['rx-bits-per-second'] ?? 0);
                $tx = (float) ($item['tx-bits-per-second'] ?? 0);

                $result[$interface] = [
                    'download' => round($rx / 1_000_000, 2),
                    'upload' => round($tx / 1_000_000, 2),

                    'rx_bps' => $rx,
                    'tx_bps' => $tx,

                    'rx_packets' => (float) (
                        $item['rx-packets-per-second'] ?? 0
                    ),

                    'tx_packets' => (float) (
                        $item['tx-packets-per-second'] ?? 0
                    ),

                    'status' => true,
                ];

            } catch (\Throwable $e) {

                $result[$interface] = [
                    'download' => 0,
                    'upload' => 0,

                    'rx_bps' => 0,
                    'tx_bps' => 0,

                    'rx_packets' => 0,
                    'tx_packets' => 0,

                    'status' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $result;
    }


    /*
    |--------------------------------------------------------------------------
    | DEBUG WIFI / NETWORK SOURCES
    |--------------------------------------------------------------------------
    |
    | Ini HARUS tetap menggunakan command yang sudah terbukti bekerja
    | di /network/debug-wifi
    |
    */

    public function debugWifiUsers(): array
    {
        $client = $this->client();

        $sources = [

            'wifi' => [
                'path' => '/interface/wifi/registration-table/print',
            ],

            'wireless' => [
                'path' => '/interface/wireless/registration-table/print',
            ],

            'capsman' => [
                'path' => '/caps-man/registration-table/print',
            ],

            'dhcp' => [
                'path' => '/ip/dhcp-server/lease/print',
            ],

            'arp' => [
                'path' => '/ip/arp/print',
            ],

        ];

        $result = [];

        foreach ($sources as $name => $config) {

            try {

                $query = new Query($config['path']);

                $data = $client
                    ->query($query)
                    ->read();

                $result[$name] = [
                    'success' => true,
                    'count' => count($data),
                    'data' => $data,
                ];

            } catch (\Throwable $e) {

                $result[$name] = [
                    'success' => false,
                    'count' => 0,
                    'data' => [],
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $result;
    }


    /*
    |--------------------------------------------------------------------------
    | ACTIVE WIFI / NETWORK USERS
    |--------------------------------------------------------------------------
    |
    | PRIORITAS:
    |
    | 1. WiFi registration
    | 2. Wireless registration
    | 3. CAPsMAN
    | 4. DHCP bound
    | 5. Local LAN ARP
    |
    */

    public function getActiveWifiUsers(): array
    {
        $client = $this->client();

        $users = [];

        /*
        |--------------------------------------------------------------------------
        | 1. DHCP LEASE
        |--------------------------------------------------------------------------
        |
        | PENTING:
        |
        | Jangan gunakan:
        |
        | /ip/dhcp-server/lease
        |
        | Gunakan persis seperti debug:
        |
        | /ip/dhcp-server/lease/print
        |
        */

        $dhcpData = [];

        try {

            $dhcpQuery = new Query(
                '/ip/dhcp-server/lease/print'
            );

            $dhcpData = $client
                ->query($dhcpQuery)
                ->read();

            if (!is_array($dhcpData)) {
                $dhcpData = [];
            }

            Log::info(
                'MIKROTIK DHCP ACTIVE USERS',
                [
                    'count' => count($dhcpData),
                ]
            );

        } catch (\Throwable $e) {

            Log::error(
                'MIKROTIK DHCP ERROR',
                [
                    'error' => $e->getMessage(),
                ]
            );

            $dhcpData = [];
        }


        /*
        |--------------------------------------------------------------------------
        | 2. ARP MIKROTIK
        |--------------------------------------------------------------------------
        */

        $arpData = [];

        try {

            $arpQuery = new Query(
                '/ip/arp/print'
            );

            $arpData = $client
                ->query($arpQuery)
                ->read();

            if (!is_array($arpData)) {
                $arpData = [];
            }

        } catch (\Throwable $e) {

            Log::error(
                'MIKROTIK ARP ERROR',
                [
                    'error' => $e->getMessage(),
                ]
            );

            $arpData = [];
        }


        /*
        |--------------------------------------------------------------------------
        | 3. INDEX ARP BY IP
        |--------------------------------------------------------------------------
        */

        $arpByIp = [];

        foreach ($arpData as $arp) {

            if (!is_array($arp)) {
                continue;
            }

            $ip = trim(
                $arp['address'] ?? ''
            );

            if ($ip === '') {
                continue;
            }

            $arpByIp[$ip] = $arp;
        }


        /*
        |--------------------------------------------------------------------------
        | 4. DHCP SERVER -> INTERFACE
        |--------------------------------------------------------------------------
        */

        $serverInterfaceMap = [

            'dhcp1' => 'bridge1',

            'dhcp2' => 'bridgeproduksi',

        ];


        /*
        |--------------------------------------------------------------------------
        | 5. DHCP CLIENT
        |--------------------------------------------------------------------------
        */

        foreach ($dhcpData as $item) {

            if (!is_array($item)) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | HANYA BOUND
            |--------------------------------------------------------------------------
            */

            if (
                strtolower(
                    trim(
                        $item['status'] ?? ''
                    )
                ) !== 'bound'
            ) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | IP
            |--------------------------------------------------------------------------
            */

            $ip = trim(
                $item['address'] ?? ''
            );

            if ($ip === '') {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | MAC
            |--------------------------------------------------------------------------
            */

            $mac = $item['mac-address']
                ?? ($arpByIp[$ip]['mac-address'] ?? '-');

            $mac = strtoupper(
                trim($mac)
            );

            if ($mac === '') {
                $mac = '-';
            }

            /*
            |--------------------------------------------------------------------------
            | HOSTNAME
            |--------------------------------------------------------------------------
            */

            $hostname = trim(
                $item['host-name'] ?? ''
            );

            if ($hostname === '') {
                $hostname = '-';
            }

            /*
            |--------------------------------------------------------------------------
            | DHCP SERVER
            |--------------------------------------------------------------------------
            */

            $server = trim(
                $item['server'] ?? ''
            );

            /*
            |--------------------------------------------------------------------------
            | INTERFACE
            |--------------------------------------------------------------------------
            */

            $interface =
                $serverInterfaceMap[$server]
                ?? (
                    $arpByIp[$ip]['interface']
                    ?? '-'
                );

            /*
            |--------------------------------------------------------------------------
            | LAST SEEN
            |--------------------------------------------------------------------------
            */

            $lastSeen = trim(
                $item['last-seen'] ?? ''
            );

            $uptime = '-';

            if ($lastSeen !== '') {
                $uptime = 'Last seen ' . $lastSeen;
            }

            /*
            |--------------------------------------------------------------------------
            | TAMBAHKAN USER
            |--------------------------------------------------------------------------
            */

            $users[] = [

                'interface' => $interface,

                'ip' => $ip,

                'mac' => $mac,

                'hostname' => $hostname,

                'signal' => '-',

                'uptime' => $uptime,

                'status' => 'Online',

                'source' => 'mikrotik',

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | 6. LOCAL WINDOWS ARP
        |--------------------------------------------------------------------------
        |
        | Untuk client yang berada di belakang:
        |
        | MikroTik
        |    ↓
        | Archer
        |    ↓
        | 192.168.0.x
        |
        */

        $localArp = [];

        if (PHP_OS_FAMILY === 'Windows') {

            $output = [];

            @exec(
                'arp -a',
                $output
            );

            foreach ($output as $line) {

                /*
                |--------------------------------------------------------------------------
                | Contoh Windows:
                |
                | 192.168.0.110    68-55-d4-e0-b0-1b    dynamic
                |--------------------------------------------------------------------------
                */

                if (
                    preg_match(
                        '/^\s*(\d{1,3}(?:\.\d{1,3}){3})\s+([0-9a-fA-F-]{17})\s+(\w+)/',
                        $line,
                        $matches
                    )
                ) {

                    $ip = $matches[1];

                    $mac = strtoupper(
                        str_replace(
                            '-',
                            ':',
                            $matches[2]
                        )
                    );

                    $type = strtolower(
                        $matches[3]
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | HANYA 192.168.0.x
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !str_starts_with(
                            $ip,
                            '192.168.0.'
                        )
                    ) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | SKIP GATEWAY
                    |--------------------------------------------------------------------------
                    */

                    if ($ip === '192.168.0.1') {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | SKIP BROADCAST
                    |--------------------------------------------------------------------------
                    */

                    if ($ip === '192.168.0.255') {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | VALID ARP
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !in_array(
                            $type,
                            [
                                'dynamic',
                                'static',
                            ],
                            true
                        )
                    ) {
                        continue;
                    }

                    $localArp[$ip] = [

                        'ip' => $ip,

                        'mac' => $mac,

                        'type' => $type,

                    ];
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 7. LOCAL HOSTNAME
        |--------------------------------------------------------------------------
        */

        foreach (
            $localArp
            as $ip => $device
        ) {

            $hostname = '-';

            /*
            |--------------------------------------------------------------------------
            | DNS / HOSTNAME
            |--------------------------------------------------------------------------
            */

            $resolved = @gethostbyaddr(
                $ip
            );

            if (
                $resolved
                && $resolved !== $ip
            ) {

                $hostname = $resolved;

            } else {

                /*
                |--------------------------------------------------------------------------
                | FALLBACK WINDOWS PING -A
                |--------------------------------------------------------------------------
                */

                if (
                    PHP_OS_FAMILY === 'Windows'
                ) {

                    $pingOutput = [];

                    @exec(
                        'ping -a -n 1 -w 300 '
                        . escapeshellarg($ip),
                        $pingOutput
                    );

                    foreach (
                        $pingOutput
                        as $line
                    ) {

                        if (
                            preg_match(
                                '/Pinging\s+(.+?)\s+\['
                                . preg_quote($ip, '/')
                                . '\]/i',
                                $line,
                                $matches
                            )
                        ) {

                            $hostname = trim(
                                $matches[1]
                            );

                            break;
                        }
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | CEK DUPLIKAT DENGAN MIKROTIK
            |--------------------------------------------------------------------------
            */

            $alreadyExists = false;

            foreach (
                $users
                as $existing
            ) {

                $sameIp =
                    (
                        ($existing['ip'] ?? null)
                        === $ip
                    );

                $sameMac =
                    (
                        $device['mac'] !== '-'
                        &&
                        strtoupper(
                            $existing['mac'] ?? ''
                        )
                        === strtoupper(
                            $device['mac']
                        )
                    );

                if (
                    $sameIp
                    || $sameMac
                ) {

                    $alreadyExists = true;

                    break;
                }
            }


            if ($alreadyExists) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | ADD LOCAL CLIENT
            |--------------------------------------------------------------------------
            */

            $users[] = [

                'interface' => 'local_lan',

                'ip' => $ip,

                'mac' => $device['mac'],

                'hostname' => $hostname,

                'signal' => '-',

                'uptime' => 'Detected locally',

                'status' => 'Online',

                'source' => 'local_lan',

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | 8. DEDUPLICATE
        |--------------------------------------------------------------------------
        */

        $unique = [];

        foreach ($users as $user) {

            $key = strtolower(
                ($user['ip'] ?? '')
                . '|'
                . ($user['mac'] ?? '')
            );

            if (
                $key === '|'
            ) {
                continue;
            }

            $unique[$key] = $user;
        }

        $users = array_values(
            $unique
        );


        /*
        |--------------------------------------------------------------------------
        | 9. SORT IP
        |--------------------------------------------------------------------------
        */

        usort(
            $users,
            function ($a, $b) {

                return ip2long(
                    $a['ip']
                ) <=> ip2long(
                    $b['ip']
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | 10. LOG FINAL
        |--------------------------------------------------------------------------
        */

        Log::info(
            'MIKROTIK ACTIVE WIFI USERS RESULT',
            [
                'total' => count($users),

                'mikrotik' => count(
                    array_filter(
                        $users,
                        fn ($u) =>
                            ($u['source'] ?? '')
                            === 'mikrotik'
                    )
                ),

                'local_lan' => count(
                    array_filter(
                        $users,
                        fn ($u) =>
                            ($u['source'] ?? '')
                            === 'local_lan'
                    )
                ),
            ]
        );


        return $users;
    }
}