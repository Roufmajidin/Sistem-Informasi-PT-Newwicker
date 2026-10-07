@extends('master.master')

@section('title', 'Network Monitor')

@section('content')

<style>

    .network-page {
        padding: 20px;
        background: #f5f7fb;
        min-height: calc(100vh - 70px);
    }

    .network-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 20px;
    }

    .network-title {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
    }

    .network-subtitle {
        margin-top: 4px;
        color: #6b7280;
        font-size: 13px;
    }

    .network-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 20px;
        background: #fff;
        border: 1px solid #e5e7eb;
        font-size: 13px;
        color: #6b7280;
        white-space: nowrap;
    }

    .status-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #9ca3af;
    }

    .status-dot.online {
        background: #22c55e;
        box-shadow: 0 0 0 4px rgba(34,197,94,.12);
    }

    .status-dot.offline {
        background: #ef4444;
        box-shadow: 0 0 0 4px rgba(239,68,68,.12);
    }

    .router-error {
        display: none;
        margin-bottom: 18px;
        padding: 12px 15px;
        border-radius: 10px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #b91c1c;
        font-size: 13px;
    }

    .router-error.show {
        display: block;
    }

    /*
    |--------------------------------------------------------------------------
    | NETWORK CARDS
    |--------------------------------------------------------------------------
    */

    .network-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .network-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(15,23,42,.05);
    }

    .network-card-header {
        padding: 16px 18px;
        border-bottom: 1px solid #eef0f4;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .interface-info {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 0;
    }

    .interface-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 18px;
        color: #334155;
    }

    .interface-name {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
    }

    .interface-description {
        margin-top: 3px;
        color: #9ca3af;
        font-size: 11px;
    }

    .interface-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 9px;
        border-radius: 20px;
        background: #f3f4f6;
        color: #6b7280;
        font-size: 11px;
        font-weight: 600;
    }

    .interface-status .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #9ca3af;
    }

    .interface-status.online {
        background: #ecfdf5;
        color: #15803d;
    }

    .interface-status.online .dot {
        background: #22c55e;
    }

    .interface-status.offline {
        background: #fef2f2;
        color: #dc2626;
    }

    .interface-status.offline .dot {
        background: #ef4444;
    }

    .network-card-body {
        padding: 18px;
    }

    .speed-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 18px;
    }

    .speed-box {
        padding: 12px 14px;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #eef2f7;
    }

    .speed-label {
        color: #94a3b8;
        font-size: 11px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .speed-value {
        color: #1e293b;
        font-size: 20px;
        font-weight: 700;
    }

    .speed-unit {
        font-size: 11px;
        color: #94a3b8;
        margin-left: 2px;
    }

    .speed-note {
        margin-top: 4px;
        color: #94a3b8;
        font-size: 9px;
    }

    .chart-container {
        position: relative;
        height: 240px;
    }

    .chart-empty {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 12px;
        pointer-events: none;
    }

    .network-card-footer {
        padding: 10px 18px;
        border-top: 1px solid #eef0f4;
        color: #9ca3af;
        font-size: 11px;
        display: flex;
        justify-content: space-between;
    }

    /*
    |--------------------------------------------------------------------------
    | USERS
    |--------------------------------------------------------------------------
    */

    .wifi-card {
        margin-top: 20px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(15,23,42,.05);
    }

    .wifi-card-header {
        padding: 16px 18px;
        border-bottom: 1px solid #eef0f4;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .wifi-title-wrapper {
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .wifi-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #334155;
    }

    .wifi-title {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #111827;
    }

    .wifi-subtitle {
        margin-top: 3px;
        color: #9ca3af;
        font-size: 11px;
    }

    .wifi-count {
        min-width: 34px;
        height: 30px;
        padding: 0 10px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
    }

    .wifi-table-wrapper {
        overflow-x: auto;
    }

    .wifi-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
    }

    .wifi-table th {
        padding: 11px 14px;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        color: #64748b;
        font-size: 10px;
        font-weight: 700;
        text-align: left;
        white-space: nowrap;
    }

    .wifi-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 11px;
        white-space: nowrap;
    }

    .wifi-table tbody tr:hover {
        background: #f8fafc;
    }

    .mac-address {
        font-family: monospace;
    }

    .user-online {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: #15803d;
        font-weight: 600;
    }

    .user-online .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #22c55e;
    }

    .source-badge {
        display: inline-flex;
        padding: 4px 8px;
        border-radius: 20px;
        background: #f1f5f9;
        color: #475569;
        font-size: 9px;
        font-weight: 700;
    }

    .wifi-empty,
    .wifi-loading {
        padding: 35px;
        text-align: center;
        color: #94a3b8;
        font-size: 12px;
    }

    .wifi-footer {
        padding: 10px 18px;
        border-top: 1px solid #eef0f4;
        color: #94a3b8;
        font-size: 10px;
        display: flex;
        justify-content: space-between;
    }

    @media(max-width:1100px) {

        .network-grid {
            grid-template-columns: 1fr;
        }

    }

    @media(max-width:600px) {

        .network-page {
            padding: 12px;
        }

        .network-header {
            align-items: flex-start;
            flex-direction: column;
        }

    }

</style>


<div class="network-page">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="network-header">

        <div>

            <h1 class="network-title">
                Network Monitor
            </h1>

            <div class="network-subtitle">
                Realtime traffic monitoring MikroTik
            </div>

        </div>


        <div class="network-status">

            <span
                id="globalStatusDot"
                class="status-dot">
            </span>

            <span id="globalStatusText">
                Connecting...
            </span>

        </div>

    </div>


    {{-- =========================================================
         ERROR
    ========================================================== --}}

    <div
        id="routerError"
        class="router-error">
    </div>


    {{-- =========================================================
         INTERFACE CARDS
    ========================================================== --}}

    <div class="network-grid">

        {{-- ETHER1 --}}

        <div class="network-card">

            <div class="network-card-header">

                <div class="interface-info">

                    <div class="interface-icon">
                        <i class="fas fa-network-wired"></i>
                    </div>

                    <div>

                        <div class="interface-name">
                            ETHER1
                        </div>

                        <div class="interface-description">
                            WAN / Internet
                        </div>

                    </div>

                </div>

                <div
                    id="status-ether1"
                    class="interface-status">

                    <span class="dot"></span>

                    <span class="status-text">
                        Checking
                    </span>

                </div>

            </div>


            <div class="network-card-body">

                <div class="speed-row">

                    <div class="speed-box">

                        <div class="speed-label">
                            DOWNLOAD
                        </div>

                        <div>

                            <span
                                id="download-ether1"
                                class="speed-value">
                                0.00
                            </span>

                            <span class="speed-unit">
                                Mbps
                            </span>

                        </div>

                        <div class="speed-note">
                            RX traffic
                        </div>

                    </div>


                    <div class="speed-box">

                        <div class="speed-label">
                            UPLOAD
                        </div>

                        <div>

                            <span
                                id="upload-ether1"
                                class="speed-value">
                                0.00
                            </span>

                            <span class="speed-unit">
                                Mbps
                            </span>

                        </div>

                        <div class="speed-note">
                            TX traffic
                        </div>

                    </div>

                </div>


                <div class="chart-container">

                    <canvas id="chart-ether1"></canvas>

                    <div
                        id="empty-ether1"
                        class="chart-empty">
                        Menunggu data...
                    </div>

                </div>

            </div>


            <div class="network-card-footer">

                <span>
                    Realtime
                </span>

                <span id="updated-ether1">
                    --
                </span>

            </div>

        </div>


        {{-- BRIDGE1 --}}

        <div class="network-card">

            <div class="network-card-header">

                <div class="interface-info">

                    <div class="interface-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>

                    <div>

                        <div class="interface-name">
                            BRIDGE1
                        </div>

                        <div class="interface-description">
                            Main Network
                        </div>

                    </div>

                </div>

                <div
                    id="status-bridge1"
                    class="interface-status">

                    <span class="dot"></span>

                    <span class="status-text">
                        Checking
                    </span>

                </div>

            </div>


            <div class="network-card-body">

                <div class="speed-row">

                    <div class="speed-box">

                        <div class="speed-label">
                            RX
                        </div>

                        <div>

                            <span
                                id="download-bridge1"
                                class="speed-value">
                                0.00
                            </span>

                            <span class="speed-unit">
                                Mbps
                            </span>

                        </div>

                    </div>


                    <div class="speed-box">

                        <div class="speed-label">
                            TX
                        </div>

                        <div>

                            <span
                                id="upload-bridge1"
                                class="speed-value">
                                0.00
                            </span>

                            <span class="speed-unit">
                                Mbps
                            </span>

                        </div>

                    </div>

                </div>


                <div class="chart-container">

                    <canvas id="chart-bridge1"></canvas>

                    <div
                        id="empty-bridge1"
                        class="chart-empty">
                        Menunggu data...
                    </div>

                </div>

            </div>


            <div class="network-card-footer">

                <span>
                    Realtime
                </span>

                <span id="updated-bridge1">
                    --
                </span>

            </div>

        </div>


        {{-- BRIDGEPRODUKSI --}}

        <div class="network-card">

            <div class="network-card-header">

                <div class="interface-info">

                    <div class="interface-icon">
                        <i class="fas fa-industry"></i>
                    </div>

                    <div>

                        <div class="interface-name">
                            BRIDGEPRODUKSI
                        </div>

                        <div class="interface-description">
                            Production Network
                        </div>

                    </div>

                </div>

                <div
                    id="status-bridgeproduksi"
                    class="interface-status">

                    <span class="dot"></span>

                    <span class="status-text">
                        Checking
                    </span>

                </div>

            </div>


            <div class="network-card-body">

                <div class="speed-row">

                    <div class="speed-box">

                        <div class="speed-label">
                            RX
                        </div>

                        <div>

                            <span
                                id="download-bridgeproduksi"
                                class="speed-value">
                                0.00
                            </span>

                            <span class="speed-unit">
                                Mbps
                            </span>

                        </div>

                    </div>


                    <div class="speed-box">

                        <div class="speed-label">
                            TX
                        </div>

                        <div>

                            <span
                                id="upload-bridgeproduksi"
                                class="speed-value">
                                0.00
                            </span>

                            <span class="speed-unit">
                                Mbps
                            </span>

                        </div>

                    </div>

                </div>


                <div class="chart-container">

                    <canvas id="chart-bridgeproduksi"></canvas>

                    <div
                        id="empty-bridgeproduksi"
                        class="chart-empty">
                        Menunggu data...
                    </div>

                </div>

            </div>


            <div class="network-card-footer">

                <span>
                    Realtime
                </span>

                <span id="updated-bridgeproduksi">
                    --
                </span>

            </div>

        </div>

    </div>


    {{-- =========================================================
         ACTIVE USERS
    ========================================================== --}}

    <div class="wifi-card">

        <div class="wifi-card-header">

            <div class="wifi-title-wrapper">

                <div class="wifi-icon">
                    <i class="fas fa-users"></i>
                </div>

                <div>

                    <h2 class="wifi-title">
                        Active Network Users
                    </h2>

                    <div class="wifi-subtitle">
                        DHCP bound + local LAN clients
                    </div>

                </div>

            </div>


            <div
                id="wifiUserCount"
                class="wifi-count">
                0
            </div>

        </div>


        <div class="wifi-table-wrapper">

            <table class="wifi-table">

                <thead>

                    <tr>

                        <th>
                            NO
                        </th>

                        <th>
                            INTERFACE
                        </th>

                        <th>
                            IP ADDRESS
                        </th>

                        <th>
                            MAC ADDRESS
                        </th>

                        <th>
                            HOSTNAME
                        </th>

                        <th>
                            SIGNAL
                        </th>

                        <th>
                            LAST SEEN
                        </th>

                        <th>
                            SOURCE
                        </th>

                        <th>
                            STATUS
                        </th>

                    </tr>

                </thead>


                <tbody id="wifiUsersBody">

                    <tr>

                        <td colspan="9">

                            <div class="wifi-loading">
                                Memuat active users...
                            </div>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>


        <div class="wifi-footer">

            <span>
                Auto refresh setiap 5 detik
            </span>

            <span id="wifiUpdated">
                --
            </span>

        </div>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | INTERFACES
        |--------------------------------------------------------------------------
        */

        const interfaces = {

            ether1: {
                name: 'ETHER1',
                chart: null,
                labels: [],
                download: [],
                upload: []
            },

            bridge1: {
                name: 'BRIDGE1',
                chart: null,
                labels: [],
                download: [],
                upload: []
            },

            bridgeproduksi: {
                name: 'BRIDGEPRODUKSI',
                chart: null,
                labels: [],
                download: [],
                upload: []
            }

        };


        const MAX_POINTS = 60;

        let trafficRequestRunning = false;

        let wifiRequestRunning = false;


        /*
        |--------------------------------------------------------------------------
        | CREATE CHART
        |--------------------------------------------------------------------------
        */

        function createChart(interfaceName) {

            const canvas =
                document.getElementById(
                    'chart-' + interfaceName
                );

            if (!canvas) {
                return;
            }

            const ctx =
                canvas.getContext('2d');


            interfaces[
                interfaceName
            ].chart = new Chart(
                ctx,
                {
                    type: 'line',

                    data: {

                        labels: [],

                        datasets: [

                            {
                                label: 'RX / Download',

                                data: [],

                                borderWidth: 2,

                                tension: .35,

                                fill: false,

                                pointRadius: 0

                            },

                            {
                                label: 'TX / Upload',

                                data: [],

                                borderWidth: 2,

                                tension: .35,

                                fill: false,

                                pointRadius: 0

                            }

                        ]

                    },

                    options: {

                        responsive: true,

                        maintainAspectRatio: false,

                        animation: false,

                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },

                        plugins: {

                            legend: {
                                display: true,
                                position: 'top'
                            }

                        },

                        scales: {

                            x: {
                                display: true,
                                ticks: {
                                    maxTicksLimit: 8
                                }
                            },

                            y: {

                                beginAtZero: true,

                                ticks: {

                                    callback: function(value) {

                                        return value
                                            + ' Mbps';

                                    }

                                }

                            }

                        }

                    }

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | INITIAL CHART
        |--------------------------------------------------------------------------
        */

        Object.keys(
            interfaces
        ).forEach(
            function (name) {

                createChart(name);

            }
        );


        /*
        |--------------------------------------------------------------------------
        | UPDATE INTERFACE STATUS
        |--------------------------------------------------------------------------
        */

        function updateInterfaceStatus(
            name,
            online
        ) {

            const element =
                document.getElementById(
                    'status-' + name
                );

            if (!element) {
                return;
            }

            element.classList.remove(
                'online',
                'offline'
            );

            element.classList.add(
                online
                    ? 'online'
                    : 'offline'
            );

            const text =
                element.querySelector(
                    '.status-text'
                );

            if (text) {

                text.textContent =
                    online
                        ? 'Online'
                        : 'Offline';

            }

        }


        /*
        |--------------------------------------------------------------------------
        | TRAFFIC
        |--------------------------------------------------------------------------
        */

        async function loadTraffic() {

            if (trafficRequestRunning) {
                return;
            }

            trafficRequestRunning = true;

            try {

                const response =
                    await fetch(
                        '{{ route('network.realtime') }}',
                        {
                            cache: 'no-store'
                        }
                    );

                const result =
                    await response.json();


                if (!response.ok || !result.success) {

                    throw new Error(
                        result.message
                        || 'Gagal mengambil traffic'
                    );

                }


                document
                    .getElementById(
                        'routerError'
                    )
                    .classList.remove(
                        'show'
                    );


                /*
                |--------------------------------------------------------------------------
                | GLOBAL STATUS
                |--------------------------------------------------------------------------
                */

                const globalDot =
                    document.getElementById(
                        'globalStatusDot'
                    );

                const globalText =
                    document.getElementById(
                        'globalStatusText'
                    );


                globalDot.classList.remove(
                    'online',
                    'offline'
                );

                globalDot.classList.add(
                    'online'
                );

                globalText.textContent =
                    'MikroTik Connected';


                /*
                |--------------------------------------------------------------------------
                | INTERFACES
                |--------------------------------------------------------------------------
                */

                Object.keys(
                    interfaces
                ).forEach(
                    function (name) {

                        const item =
                            result.interfaces?.[
                                name
                            ] || {};


                        const online =
                            item.status === true;


                        updateInterfaceStatus(
                            name,
                            online
                        );


                        const download =
                            Number(
                                item.download
                                || 0
                            );

                        const upload =
                            Number(
                                item.upload
                                || 0
                            );


                        const downloadElement =
                            document.getElementById(
                                'download-' + name
                            );

                        const uploadElement =
                            document.getElementById(
                                'upload-' + name
                            );


                        if (downloadElement) {

                            downloadElement.textContent =
                                download.toFixed(2);

                        }


                        if (uploadElement) {

                            uploadElement.textContent =
                                upload.toFixed(2);

                        }


                        const updatedElement =
                            document.getElementById(
                                'updated-' + name
                            );


                        if (updatedElement) {

                            updatedElement.textContent =
                                new Date()
                                    .toLocaleTimeString();

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | CHART
                        |--------------------------------------------------------------------------
                        */

                        const obj =
                            interfaces[name];

                        const now =
                            new Date()
                                .toLocaleTimeString();


                        obj.labels.push(
                            now
                        );

                        obj.download.push(
                            download
                        );

                        obj.upload.push(
                            upload
                        );


                        if (
                            obj.labels.length
                            > MAX_POINTS
                        ) {

                            obj.labels.shift();
                            obj.download.shift();
                            obj.upload.shift();

                        }


                        if (obj.chart) {

                            obj.chart.data.labels =
                                obj.labels;

                            obj.chart.data.datasets[0].data =
                                obj.download;

                            obj.chart.data.datasets[1].data =
                                obj.upload;

                            obj.chart.update(
                                'none'
                            );

                        }


                        const empty =
                            document.getElementById(
                                'empty-' + name
                            );

                        if (
                            empty
                            &&
                            obj.labels.length > 1
                        ) {

                            empty.style.display =
                                'none';

                        }

                    }
                );


            } catch (error) {

                console.error(
                    'Traffic error:',
                    error
                );


                const dot =
                    document.getElementById(
                        'globalStatusDot'
                    );

                const text =
                    document.getElementById(
                        'globalStatusText'
                    );


                dot.classList.remove(
                    'online'
                );

                dot.classList.add(
                    'offline'
                );

                text.textContent =
                    'MikroTik Offline';


                document
                    .getElementById(
                        'routerError'
                    )
                    .classList.add(
                        'show'
                    );

                document
                    .getElementById(
                        'routerError'
                    )
                    .textContent =
                    'Tidak dapat mengambil data traffic MikroTik. '
                    + error.message;

            } finally {

                trafficRequestRunning = false;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | ESCAPE HTML
        |--------------------------------------------------------------------------
        */

        function escapeHtml(value) {

            return String(
                value ?? ''
            )
                .replaceAll(
                    '&',
                    '&amp;'
                )
                .replaceAll(
                    '<',
                    '&lt;'
                )
                .replaceAll(
                    '>',
                    '&gt;'
                )
                .replaceAll(
                    '"',
                    '&quot;'
                )
                .replaceAll(
                    "'",
                    '&#039;'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | WIFI USERS
        |--------------------------------------------------------------------------
        */

        async function loadWifiUsers() {

            if (wifiRequestRunning) {
                return;
            }

            wifiRequestRunning = true;

            try {

                const response =
                    await fetch(
                        '{{ route('network.wifi-users') }}',
                        {
                            cache: 'no-store'
                        }
                    );


                const result =
                    await response.json();


                if (
                    !response.ok
                    || !result.success
                ) {

                    throw new Error(
                        result.message
                        || 'Gagal mengambil users'
                    );

                }


                const users =
                    Array.isArray(
                        result.users
                    )
                        ? result.users
                        : [];


                renderWifiUsers(
                    users
                );


                document
                    .getElementById(
                        'wifiUserCount'
                    )
                    .textContent =
                    users.length;


                document
                    .getElementById(
                        'wifiUpdated'
                    )
                    .textContent =
                    result.updated_at
                    || new Date()
                        .toLocaleTimeString();


            } catch (error) {

                console.error(
                    'WiFi users error:',
                    error
                );


                document
                    .getElementById(
                        'wifiUsersBody'
                    )
                    .innerHTML = `

                        <tr>

                            <td colspan="9">

                                <div class="wifi-empty">

                                    Gagal mengambil active users.

                                    <br>

                                    ${escapeHtml(
                                        error.message
                                    )}

                                </div>

                            </td>

                        </tr>

                    `;

            } finally {

                wifiRequestRunning = false;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | RENDER USERS
        |--------------------------------------------------------------------------
        */

        function renderWifiUsers(
            users
        ) {

            const body =
                document.getElementById(
                    'wifiUsersBody'
                );


            if (!users.length) {

                body.innerHTML = `

                    <tr>

                        <td colspan="9">

                            <div class="wifi-empty">

                                Tidak ada active network users.

                            </div>

                        </td>

                    </tr>

                `;

                return;
            }


            body.innerHTML =
                users.map(
                    function (
                        user,
                        index
                    ) {

                        const source =
                            user.source
                            || 'mikrotik';


                        const sourceLabel =
                            source === 'local_lan'
                                ? 'LOCAL LAN'
                                : 'MIKROTIK';


                        return `

                            <tr>

                                <td>
                                    ${index + 1}
                                </td>

                                <td>
                                    ${escapeHtml(
                                        user.interface
                                    )}
                                </td>

                                <td>
                                    ${escapeHtml(
                                        user.ip
                                    )}
                                </td>

                                <td class="mac-address">
                                    ${escapeHtml(
                                        user.mac
                                    )}
                                </td>

                                <td>
                                    ${escapeHtml(
                                        user.hostname
                                    )}
                                </td>

                                <td>
                                    ${escapeHtml(
                                        user.signal
                                        || '-'
                                    )}
                                </td>

                                <td>
                                    ${escapeHtml(
                                        user.uptime
                                        || '-'
                                    )}
                                </td>

                                <td>

                                    <span class="source-badge">

                                        ${escapeHtml(
                                            sourceLabel
                                        )}

                                    </span>

                                </td>

                                <td>

                                    <span class="user-online">

                                        <span class="dot"></span>

                                        ${escapeHtml(
                                            user.status
                                            || 'Online'
                                        )}

                                    </span>

                                </td>

                            </tr>

                        `;

                    }
                )
                .join('');

        }


        /*
        |--------------------------------------------------------------------------
        | INITIAL LOAD
        |--------------------------------------------------------------------------
        */

        loadTraffic();

        loadWifiUsers();


        /*
        |--------------------------------------------------------------------------
        | REALTIME TRAFFIC
        |--------------------------------------------------------------------------
        |
        | 1 detik
        |
        */

        setInterval(
            loadTraffic,
            1000
        );


        /*
        |--------------------------------------------------------------------------
        | WIFI USER
        |--------------------------------------------------------------------------
        |
        | 5 detik supaya tidak terlalu berat
        |
        */

        setInterval(
            loadWifiUsers,
            5000
        );

    }
);

</script>

@endsection