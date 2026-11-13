@extends('master.master')

@section('title', 'Portal NewWicker')

@section('content')

    @php

        $auth = auth()->user();

        $email = strtolower($auth->email ?? '');

        // Akun yang boleh melihat BOM dan BOM Produksi

        $isFactoryOfficeInfo = false;

        foreach (['factory@', 'office@', 'info@'] as $prefix) {
            if (str_starts_with($email, $prefix)) {
                $isFactoryOfficeInfo = true;

                break;
            }
        }

        // Tetap pertahankan pengecekan factory untuk fitur absensi

        $isFactory = str_starts_with($email, 'factory@');

        $karyawan = \App\Models\Karyawan::find($auth->karyawan_id);

        $divisiId = $karyawan->divisi_id ?? null;

        $allowedDivisi = [26, 38, 32, 46, 37];

        // Hak akses berdasarkan tabel access

        $canPengajuanHarga = $auth->accessModules()->where('name', 'Pengajuan Harga')->exists();

        $canSourcingMonitoring = $auth->accessModules()->where('name', 'Sourching Monitoring Issue')->exists();

        $canTtdSpk = $auth

            ->accessModules()

            ->whereRaw('LOWER(TRIM(name)) = ?', ['ttd spk'])

            ->exists();

    @endphp

    @php

        $managementPrefixes = ['info@', 'office@', 'factory@', 'manager@', 'director@', 'admin@'];

        $isPengajuanManagement = collect($managementPrefixes)->contains(
            fn($prefix) => str_starts_with($email, $prefix),
        );

        $pengajuanHargaUrl = $isPengajuanManagement ? url('/pengajuan-harga/directur') : route('pengajuan-harga.index');

    @endphp



    @php

        /*

    |--------------------------------------------------------------------------

    | AKSES SHORTCUT TTD SPK

    |--------------------------------------------------------------------------

    | Shortcut ditampilkan jika user memiliki akses modul

    | atau terdaftar sebagai penandatangan SPK.

    */

        $hasSignatureAssignment = \Illuminate\Support\Facades\DB::table('signature_spks')

            ->where(function ($query) use ($auth) {
                $query

                    ->where('made_by', $auth->id)

                    ->orWhere('checked_by', $auth->id)

                    ->orWhere('checked_by_2', $auth->id)

                    ->orWhere('approved_by', $auth->id);
            })

            ->exists();

        $showTtdSpk = $canTtdSpk || $hasSignatureAssignment;

        /*

    |--------------------------------------------------------------------------

    | COUNTER PENDING TTD SPK

    |--------------------------------------------------------------------------

    | Tanggal mulai : 08 November 2026

    | Sumber tanggal: spk.created_at

    | Status SPK    : diajukan

    |

    | Menghitung seluruh dokumen yang masih memiliki

    | minimal satu tahap approval pending.

    */

        $signatureCounters = [
            'NW' => 0,

            'NWR' => 0,

            'NWD' => 0,

            'NWS' => 0,
        ];

        $signatureStartDate = '2026-11-08 00:00:00';

        if ($showTtdSpk) {
            $userId = (int) $auth->id;

            /*

        |--------------------------------------------------------------------------

        | AMBIL NOMOR SPK DAN PO DARI JSON

        |--------------------------------------------------------------------------

        */

            $spkNumberSql = "

            UPPER(

                CONCAT(

                    COALESCE(

                        JSON_UNQUOTE(

                            JSON_EXTRACT(spk.data, '$.no_spk')

                        ),

                        ''

                    ),

                    ' ',

                    COALESCE(

                        JSON_UNQUOTE(

                            JSON_EXTRACT(spk.data, '$.no_po')

                        ),

                        ''

                    )

                )

            )

        ";

            /*

        |--------------------------------------------------------------------------

        | IDENTIFIKASI DIVISI

        |--------------------------------------------------------------------------

        */

            $prefixSql = "

            CASE

                WHEN {$spkNumberSql}

                    REGEXP '(^|[^A-Z0-9])NWR([^A-Z0-9]|$)'

                    THEN 'NWR'



                WHEN {$spkNumberSql}

                    REGEXP '(^|[^A-Z0-9])NWD([^A-Z0-9]|$)'

                    THEN 'NWD'



                WHEN {$spkNumberSql}

                    REGEXP '(^|[^A-Z0-9])NWS([^A-Z0-9]|$)'

                    THEN 'NWS'



                WHEN {$spkNumberSql}

                    REGEXP '(^|[^A-Z0-9])NW([^A-Z0-9]|$)'

                    THEN 'NW'



                ELSE NULL

            END

        ";

            /*

        |--------------------------------------------------------------------------

        | QUERY TOTAL PENDING

        |--------------------------------------------------------------------------

        */

            $signatureRows = \Illuminate\Support\Facades\DB::table('signature_spks')

                ->join('spk', 'spk.id', '=', 'signature_spks.spk_id')

                // Filter tanggal berdasarkan SPK dibuat

                ->where('spk.created_at', '>=', $signatureStartDate)

                // Hanya SPK yang masih diajukan

                ->whereRaw('LOWER(TRIM(spk.status)) = ?', ['diajukan'])

                // SPK yang berkaitan dengan user login

                ->where(function ($query) use ($userId) {
                    $query

                        ->where('signature_spks.made_by', $userId)

                        ->orWhere('signature_spks.checked_by', $userId)

                        ->orWhere('signature_spks.checked_by_2', $userId)

                        ->orWhere('signature_spks.approved_by', $userId);
                })

                // Minimal satu tahapan approval masih pending

                ->where(function ($query) use ($spkNumberSql) {
                    $query

                        ->where(function ($q) use ($spkNumberSql) {
                            $q->whereRaw("{$spkNumberSql} REGEXP ?", ['(^|[^A-Z0-9])(NW|NWR)([^A-Z0-9]|$)'])->whereNull(
                                'signature_spks.checked_at',
                            );
                        })

                        ->orWhereNull('signature_spks.checked_at_2')

                        ->orWhereNull('signature_spks.approved_at');
                })

                // Kelompokkan berdasarkan divisi

                ->selectRaw(
                    "

                {$prefixSql} AS prefix,

                COUNT(DISTINCT signature_spks.spk_id) AS total

            ",
                )

                ->groupByRaw($prefixSql)

                ->get();

            /*

        |--------------------------------------------------------------------------

        | MASUKKAN HASIL KE COUNTER

        |--------------------------------------------------------------------------

        */

            foreach ($signatureRows as $signatureRow) {
                $prefix = $signatureRow->prefix ?? '';

                if (array_key_exists($prefix, $signatureCounters)) {
                    $signatureCounters[$prefix] = (int) $signatureRow->total;
                }
            }
        }

        /*

    |--------------------------------------------------------------------------

    | VARIABLE UNTUK BLADE

    |--------------------------------------------------------------------------

    */

        $pendingNw = $signatureCounters['NW'];

        $pendingNwr = $signatureCounters['NWR'];

        $pendingNwd = $signatureCounters['NWD'];

        $pendingNws = $signatureCounters['NWS'];

    @endphp



    <style>
        .nw-signature-counters {

            display: grid;

            grid-template-columns: repeat(2, minmax(0, 1fr));

            gap: 8px;

            margin-top: 10px;

        }



        .nw-counter {

            display: flex;

            min-width: 0;

            text-decoration: none !important;

            align-items: center;

            justify-content: space-between;

            gap: 12px;

            padding: 5px 10px;

            border-radius: 6px;

            font-size: 11px;

            font-weight: 700;

        }



        .nw-counter strong {

            background: rgba(255, 255, 255, .85);

            color: #26394e;

            border-radius: 5px;

            padding: 2px 7px;

        }



        .nw-counter.nw {

            background: #eaf1ff;

            color: #285bc4;

        }



        .nw-counter.nwr {

            background: #fff3e5;

            color: #ad661a;

        }



        .nw-counter:hover {

            filter: brightness(.96);

            transform: translateY(-1px);

        }



        .nw-counter.nwd {

            background: #dbeafe;

            color: #1d4ed8;

        }



        .nw-counter.nws {

            background: #dcfce7;

            color: #15803d;

        }



        .nw-dashboard {

            padding: 18px 20px 26px;

            background: #f6f8fc;

            color: #17233c;

            font-family: inherit
        }



        .nw-dashboard * {

            box-sizing: border-box
        }



        .nw-dash-heading {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 12px;

            margin: 0 0 16px
        }



        .nw-dash-heading h2 {

            font-size: 20px;

            font-weight: 700;

            letter-spacing: -.4px;

            margin: 0;

            color: #17233c
        }



        .nw-dash-heading p {

            margin: 4px 0 0;

            color: #8290a4;

            font-size: 12px
        }



        .nw-shortcuts {

            display: grid;

            grid-template-columns: repeat(3, minmax(0, 1fr));

            gap: 12px;

            margin-bottom: 18px
        }



        .nw-shortcut {

            display: flex;

            align-items: center;

            gap: 12px;

            min-height: 78px;

            padding: 15px;

            background: #fff;

            border: 1px solid #e8edf5;

            border-radius: 12px;

            color: inherit;

            text-decoration: none !important;

            transition: border-color .2s, transform .2s, box-shadow .2s;

            min-width: 0
        }



        .nw-shortcut:hover {

            transform: translateY(-2px);

            border-color: #cad7ec;

            box-shadow: 0 8px 20px rgba(30, 48, 78, .06);

            color: inherit
        }



        .nw-shortcut-icon {

            width: 42px;

            height: 42px;

            border-radius: 12px;

            display: flex;

            align-items: center;

            justify-content: center;

            flex: none;

            font-size: 20px
        }



        .nw-shortcut-icon.purple {

            background: #f1edff;

            color: #8061d9
        }



        .nw-shortcut-icon.teal {

            background: #e5faf6;

            color: #00a995
        }



        .nw-shortcut-icon.blue {

            background: #eaf1ff;

            color: #3775e9
        }



        .nw-shortcut-icon.orange {

            background: #fff3e5;

            color: #e99935
        }



        .nw-shortcut-body {

            min-width: 0;

            flex: 1
        }



        .nw-shortcut-title {

            font-size: 13px;

            font-weight: 700;

            color: #253450;

            margin-bottom: 3px
        }



        .nw-shortcut-sub {

            font-size: 11px;

            color: #8995a8;

            line-height: 1.4
        }



        .nw-shortcut-arrow {

            color: #a9b5c5;

            font-size: 17px
        }



        .nw-approval-content {

            font-size: 11px;

            color: #68778e;

            margin-top: 5px
        }



        .nw-approval-content hr {

            margin: 6px 0
        }



        .nw-timeline-grid {

            display: grid;

            grid-template-columns: minmax(0, 1fr);

            gap: 14px;

            align-items: start
        }



        /* SPK Timeline full width; PO Timeline remains commented out. */
        .nw-timeline-grid>.nw-timeline-card {
            width: 100%;
            min-width: 0;
        }

        .nw-spk-table-scroll {
            width: 100%;
            max-width: 100%;
            max-height: 430px;
            overflow: auto;
            -webkit-overflow-scrolling: touch;
        }

        .nw-spk-history-table {
            width: 100%;
            min-width: 940px;
            border-collapse: collapse;
        }

        .nw-timeline-card {

            background: #fff;

            border: 1px solid #e7ecf3;

            border-radius: 13px;

            overflow: hidden;

            min-width: 0;

            box-shadow: 0 3px 14px rgba(18, 38, 72, .025)
        }



        .nw-timeline-head {

            padding: 15px 17px 12px;

            border-bottom: 1px solid #edf1f6;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 8px
        }



        .nw-timeline-head h3 {

            margin: 0;

            font-size: 14px;

            font-weight: 700;

            color: #24314a
        }



        .nw-timeline-head p {

            margin: 4px 0 0;

            color: #98a3b3;

            font-size: 11px
        }



        .nw-head-icon {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            width: 31px;

            height: 31px;

            border-radius: 9px;

            background: #edf3ff;

            color: #3c6cdb;

            font-size: 15px
        }



        .nw-timeline-scroll {

            height: 330px;

            max-height: 330px;

            overflow-y: auto;

            overflow-x: hidden;

            overscroll-behavior: contain;

            padding: 14px 17px 12px;

            scrollbar-width: thin;

            scrollbar-color: #cad5e5 transparent
        }



        .nw-timeline-scroll::-webkit-scrollbar {

            width: 6px
        }



        .nw-timeline-scroll::-webkit-scrollbar-thumb {

            background: #cbd5e1;

            border-radius: 10px
        }



        .nw-timeline-scroll .streamline {

            margin-bottom: 0 !important;

            padding-left: 16px !important;

            border-left: 1px solid #e6ebf2 !important
        }



        .nw-timeline-scroll .sl-item {

            margin-bottom: 0 !important;

            padding: 0 0 15px 5px;

            position: relative;

            min-width: 0
        }



        .nw-timeline-scroll .sl-item:last-child {

            padding-bottom: 0
        }



        .nw-timeline-scroll .sl-item:before {

            width: 8px;

            height: 8px;

            background: #70b69b;

            border: 2px solid #fff;

            box-shadow: 0 0 0 1px #dce5ee;

            left: -21px;

            top: 5px
        }



        .nw-timeline-scroll .sl-content {

            font-size: 12px;

            color: #334155;

            line-height: 1.5;

            overflow-wrap: anywhere
        }



        .nw-timeline-scroll .sl-content p {

            margin: 3px 0 0
        }



        .nw-timeline-scroll .sl-date {

            font-size: 10px;

            color: #9aa5b5 !important;

            margin-bottom: 3px
        }



        .nw-timeline-scroll .badge {

            font-size: 9px;

            padding: 4px 6px;

            border-radius: 5px
        }



        .nw-timeline-scroll .d-flex {

            display: flex;

            flex-wrap: wrap;

            align-items: center;

            justify-content: space-between;

            gap: 5px
        }



        .nw-timeline-scroll .text-muted {

            color: #8a97a9 !important
        }



        .nw-timeline-scroll .collapse {

            margin-top: 8px;

            padding: 10px;

            background: #f7f9fd;

            border: 1px solid #e9eef6;

            border-radius: 7px;

            overflow-wrap: anywhere
        }



        .nw-timeline-scroll code {

            white-space: normal;

            overflow-wrap: anywhere
        }



        .nw-timeline-foot {

            padding: 10px 17px;

            border-top: 1px solid #edf1f6;

            background: #fbfcff;

            font-size: 10px;

            color: #95a0b2;

            display: flex;

            align-items: center;

            gap: 6px
        }



        .nw-empty {

            padding: 40px 10px;

            text-align: center;

            color: #9ba6b7;

            font-size: 12px
        }



        @media(max-width:900px) {

            .nw-shortcuts {

                grid-template-columns: repeat(2, minmax(0, 1fr))
            }



            .nw-timeline-grid {

                grid-template-columns: 1fr
            }

        }



        @media(max-width:520px) {

            .nw-dashboard {

                padding: 12px
            }



            .nw-shortcuts {

                grid-template-columns: 1fr
            }



            .nw-timeline-scroll {

                height: 300px;

                max-height: 300px
            }

        }



        /* SPK overview: four responsive KPI cards, dummy values only */

        .nw-spk-section {

            margin: 0 0 18px
        }



        .nw-spk-section-head {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 10px;

            margin: 0 0 11px
        }



        .nw-spk-section-head h3 {

            font-size: 14px;

            font-weight: 750;

            color: #253450;

            margin: 0
        }



        .nw-spk-dummy {

            font-size: 10px;

            color: #8794a8;

            background: #eef2f8;

            border: 1px solid #e1e8f1;

            padding: 5px 9px;

            border-radius: 20px
        }



        .nw-spk-stats {

            display: grid;

            grid-template-columns: repeat(4, minmax(0, 1fr));

            gap: 12px
        }



        .nw-spk-stat {

            min-width: 0;

            display: flex;

            flex-direction: column;

            gap: 12px;

            background: #fff;

            border: 1px solid #e7ecf3;

            border-radius: 13px;

            padding: 16px;

            box-shadow: 0 3px 14px rgba(18, 38, 72, .025)
        }



        .nw-spk-stat-top {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 8px
        }



        .nw-spk-stat-icon {

            width: 35px;

            height: 35px;

            border-radius: 10px;

            display: flex;

            align-items: center;

            justify-content: center
        }



        .nw-spk-stat-icon .material-icons {

            font-size: 19px
        }



        .nw-spk-stat-icon.nw {

            background: #edf3ff;

            color: #3265dc
        }



        .nw-spk-stat-icon.nws {

            background: #e5faf6;

            color: #0caa93
        }



        .nw-spk-stat-icon.nwd {

            background: #f4edff;

            color: #9060d5
        }



        .nw-spk-stat-icon.total {

            background: #fff2e5;

            color: #e58b2c
        }



        .nw-spk-stat-name {

            font-size: 11px;

            font-weight: 700;

            color: #53627b
        }



        .nw-spk-stat-number {

            font-size: 28px;

            font-weight: 800;

            line-height: 1.1;

            letter-spacing: -.8px;

            color: #17233c;

            font-variant-numeric: tabular-nums
        }



        .nw-spk-stat-number small {

            font-size: 11px;

            font-weight: 500;

            color: #94a0b2;

            letter-spacing: 0
        }



        .nw-spk-stat-sub {

            font-size: 10px;

            color: #97a2b2;

            margin-top: 3px
        }



        @media(max-width:900px) {

            .nw-spk-stats {

                grid-template-columns: repeat(2, minmax(0, 1fr))
            }

        }



        @media(max-width:520px) {

            .nw-spk-stats {

                gap: 9px
            }



            .nw-spk-stat {

                padding: 12px
            }



            .nw-spk-stat-number {

                font-size: 24px
            }

        }



        /* Signature links only: existing dashboard components unchanged */

        .nw-signature-counters {

            display: grid;

            grid-template-columns: repeat(2, minmax(0, 1fr));

            gap: 8px;

            margin-top: 10px;

        }



        .nw-signature-counters .nw-counter {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 8px;

            padding: 8px 10px;

            border-radius: 8px;

            font-size: 12px;

            font-weight: 700;

            text-decoration: none !important;

            transition: transform .2s, filter .2s;

            min-width: 0;

        }



        .nw-signature-counters .nw-counter:hover {

            transform: translateY(-2px);

            filter: brightness(.96);

        }



        .nw-signature-counters .nw-counter strong {

            background: #fff;

            color: #26394e;

            border-radius: 6px;

            padding: 3px 8px;

        }



        .nw-signature-counters .nw-counter.nwd {

            background: #dbeafe;

            color: #1d4ed8;

        }



        .nw-signature-counters .nw-counter.nws {

            background: #dcfce7;

            color: #15803d;

        }


        /* SPK Timeline: compact item preview + expandable details */
        .nw-spk-history-table .spk-items-cell {
            min-width: 210px;
            max-width: 360px;
            vertical-align: top;
        }

        .nw-spk-history-table .spk-items-preview {
            display: flex;
            align-items: center;
            gap: 7px;
            flex-wrap: wrap;
        }

        .nw-spk-history-table .spk-item {
            display: block;
            margin: 3px 0;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }

        .nw-spk-history-table .spk-items-preview .spk-item {
            margin: 0;
        }

        .nw-spk-history-table .spk-items-more {
            display: inline-block;
            border: 1px solid #cbd9f3;
            border-radius: 6px;
            background: #eef4ff;
            color: #2455b5;
            padding: 3px 8px;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
        }

        .nw-spk-history-table .spk-items-more:hover {
            background: #dce9ff;
        }

        .nw-spk-history-table .spk-items-details {
            padding: 7px 0 2px 10px;
            margin-top: 6px;
            border-left: 2px solid #cbd9f3;
        }

        .nw-spk-history-table .spk-items-details[hidden] {
            display: none !important;
        }

        .nw-spk-history-table .spk-muted {
            display: block;
            margin-top: 4px;
            font-size: 11px;
            color: #7b879b;
        }

        .nw-spk-history-table .spk-note-badge {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.35;
        }

        .nw-spk-history-table .spk-note-created {
            background: #e0f7e8;
            color: #176a39;
        }

        .nw-spk-history-table .spk-note-updated {
            background: #fff0d9;
            color: #a4510a;
        }

        .nw-spk-history-table .spk-note-other {
            background: #edf2f7;
            color: #475569;
        }
    </style>

    <div class="nw-dashboard">

        <div class="nw-dash-heading">

            <div>

                <h2>Dashboard</h2>

                <p></p>

            </div>

        </div>

        <div class="nw-shortcuts">

            @if (in_array($divisiId, $allowedDivisi))
                <div class="nw-shortcut"><span class="nw-shortcut-icon blue"><i
                            class="material-icons">assignment_turned_in</i></span>

                    <div class="nw-shortcut-body">

                        <div class="nw-shortcut-title"><a href="/riwayat-absen">Pengajuan</a></div>

                        <div id="approval-box" class="nw-approval-content"></div>

                    </div>

                </div>

                <a class="nw-shortcut" href="/spk/request-r"><span class="nw-shortcut-icon orange"><i
                            class="material-icons">receipt_long</i></span>

                    <div class="nw-shortcut-body">

                        <div class="nw-shortcut-title">Pengajuan Pembayaran SPK</div>

                        <div class="nw-shortcut-sub">Buka halaman pengajuan</div>

                    </div><span class="nw-shortcut-arrow">›</span>

                </a>
            @endif

            @if (!$isFactory)
                <a class="nw-shortcut" href="/karyawan-scan"><span class="nw-shortcut-icon purple"><i
                            class="material-icons">event_available</i></span>

                    <div class="nw-shortcut-body">

                        <div class="nw-shortcut-title">Absen Sekarang</div>

                        <div class="nw-shortcut-sub">Catat kehadiran hari ini</div>

                    </div><span class="nw-shortcut-arrow">›</span>

                </a>
            @endif

            {{-- BOM dan BOM Produksi: berdasarkan email --}}

            @if ($isFactoryOfficeInfo)
                <a class="nw-shortcut" href="/bom">

                    <span class="nw-shortcut-icon teal">

                        <i class="material-icons">inventory_2</i>

                    </span>

                    <div class="nw-shortcut-body">

                        <div class="nw-shortcut-title">BOM Samples</div>

                        <div class="nw-shortcut-sub">Navigasi ke BOM Samples</div>

                    </div>

                    <span class="nw-shortcut-arrow">›</span>

                </a>

                <a class="nw-shortcut" href="/bom-produksi">

                    <span class="nw-shortcut-icon blue">

                        <i class="material-icons">settings</i>

                    </span>

                    <div class="nw-shortcut-body">

                        <div class="nw-shortcut-title">BOM Produksi</div>

                        <div class="nw-shortcut-sub">Navigasi ke BOM Produksi</div>

                    </div>

                    <span class="nw-shortcut-arrow">›</span>

                </a>
            @endif

            {{-- Sourching Monitoring: berdasarkan tabel access --}}

            @if ($canSourcingMonitoring)
                <a class="nw-shortcut" href="/sm">

                    <span class="nw-shortcut-icon blue">

                        <i class="material-icons">assignment</i>

                    </span>

                    <div class="nw-shortcut-body">

                        <div class="nw-shortcut-title">Sourching Monitoring Issue</div>

                        <div class="nw-shortcut-sub">Go to list monitoring issue</div>

                    </div>

                    <span class="nw-shortcut-arrow">›</span>

                </a>
            @endif

            {{-- Pengajuan Harga: berdasarkan tabel access --}}

            {{-- Pengajuan Harga: berdasarkan tabel access --}}

            @if ($canPengajuanHarga)
                <a class="nw-shortcut" href="{{ $pengajuanHargaUrl }}">

                    <span class="nw-shortcut-icon blue">

                        <i class="material-icons">store</i>

                    </span>

                    <div class="nw-shortcut-body">

                        <div class="nw-shortcut-title">

                            Pengajuan Harga

                        </div>

                        <div class="nw-shortcut-sub">

                            {{ $isPengajuanManagement ? 'Go to Director Price Monitoring' : 'Go to list pengajuan harga' }}

                        </div>

                    </div>

                    <span class="nw-shortcut-arrow">›</span>

                </a>
            @endif

            {{-- Shortcut TTD SPK: hanya untuk user dengan akses modul ttd spk --}}

            @if ($showTtdSpk)

                <div class="nw-shortcut">

                    <span class="nw-shortcut-icon blue"><i class="material-icons">draw</i></span>

                    <div class="nw-shortcut-body">

                        <div class="nw-shortcut-title">Pengajuan TTD SPK</div>

                        <div class="nw-shortcut-sub">Pengajuan signature SPK</div>

                        <div class="nw-signature-counters">

                            @foreach (['NW', 'NWR', 'NWD', 'NWS'] as $key)
                                <a class="nw-counter {{ strtolower($key) }}"
                                    href="{{ url('/produksi/inventor/signature') .
                                        '?' .
                                        http_build_query([
                                            'q' => '',
                                    
                                            'key' => $key,
                                    
                                            'date_from' => '2026-11-08',
                                        ]) }}">

                                    <span>{{ $key }}</span>

                                    <strong>{{ $signatureCounters[$key] ?? 0 }}</strong>

                                </a>
                            @endforeach

                        </div>

                    </div>

                </div>

            @endif

            {{-- Riwayat Absen dan Ubah Password --}}

            @if (!$isFactory)
                <a class="nw-shortcut" href="/riwayat-absen">

                    <span class="nw-shortcut-icon teal">

                        <i class="material-icons">history</i>

                    </span>

                    <div class="nw-shortcut-body">

                        <div class="nw-shortcut-title">Riwayat Absen</div>

                        <div class="nw-shortcut-sub">Lihat riwayat kehadiran</div>

                    </div>

                    <span class="nw-shortcut-arrow">›</span>

                </a>

                <a class="nw-shortcut" href="#">

                    <span class="nw-shortcut-icon blue">

                        <i class="material-icons">lock_outline</i>

                    </span>

                    <div class="nw-shortcut-body">

                        <div class="nw-shortcut-title">Ubah Password</div>

                        <div class="nw-shortcut-sub">Pengaturan keamanan akun</div>

                    </div>

                    <span class="nw-shortcut-arrow">›</span>

                </a>
            @endif

        </div>

        {{-- SPK counters are intentionally dummy data until the database query is ready. --}}

        @php

            $spkDummy = [
                [
                    'label' => 'SPK NW',

                    'count' => 00000,

                    'icon' => 'precision_manufacturing',

                    'type' => 'nw',

                    'desc' => 'SPK Produksi',
                ],

                [
                    'label' => 'SPK NWS',

                    'count' => 00000,

                    'icon' => 'inventory_2',

                    'type' => 'nws',

                    'desc' => 'SPK Samples',
                ],

                ['label' => 'SPK NWD', 'count' => 00000, 'icon' => 'layers', 'type' => 'nwd', 'desc' => 'SPK NWD'],
            ];

            $spkDummyTotal = array_sum(array_column($spkDummy, 'count'));

        @endphp

        {{-- <section class="nw-spk-section" aria-label="Ringkasan SPK">

            <div class="nw-spk-section-head">

                <h3>Ringkasan SPK</h3><span class="nw-spk-dummy">On Progress</span>

            </div>

            <div class="nw-spk-stats">

                @foreach ($spkDummy as $stat)

                    <div class="nw-spk-stat">

                        <div class="nw-spk-stat-top"><span class="nw-spk-stat-name">{{ $stat['label'] }}</span><span

                                class="nw-spk-stat-icon {{ $stat['type'] }}"><i

                                    class="material-icons">{{ $stat['icon'] }}</i></span></div>

                        <div>

                            <div class="nw-spk-stat-number">{{ number_format($stat['count']) }} <small>SPK</small></div>

                            <div class="nw-spk-stat-sub">{{ $stat['desc'] }}</div>

                        </div>

                    </div>

                @endforeach

                <div class="nw-spk-stat">

                    <div class="nw-spk-stat-top"><span class="nw-spk-stat-name">TOTAL SPK</span><span

                            class="nw-spk-stat-icon total"><i class="material-icons">assessment</i></span></div>

                    <div>

                        <div class="nw-spk-stat-number">{{ number_format($spkDummyTotal) }} <small>SPK</small></div>

                        <div class="nw-spk-stat-sub">NW + NWS + NWD</div>

                    </div>

                </div>

            </div>

        </section> --}}

        <div class="nw-timeline-grid">

            {{-- PO Timeline dinonaktifkan sesuai permintaan. Kode asli tetap disimpan.
            <section class="nw-timeline-card">

                <div class="nw-timeline-head">

                    <div>

                        <h3>PO Timeline</h3>

                        <p>Riwayat aktivitas Purchase Order</p>

                    </div><span class="nw-head-icon"><i class="material-icons">history</i></span>

                </div>

                <div class="nw-timeline-scroll">

                    <div class="streamline b-l m-b" id="timelineContainer">

                        <div class="nw-empty">Memuat aktivitas PO...</div>

                    </div>

                </div>

                <div class="nw-timeline-foot"><i class="material-icons" style="font-size:13px">unfold_more</i>Scroll

                    untuk

                    melihat aktivitas lainnya</div>

            </section>

            --}}

            <section class="nw-timeline-card">

                <div class="nw-timeline-head">

                    <div>

                        <h3>SPK Timeline</h3>

                        <p>Riwayat pembuatan dan perubahan SPK</p>

                    </div><span class="nw-head-icon"><i class="material-icons">assignment</i></span>

                </div>

                <div class="nw-spk-table-scroll" id="spkTimelineContainer">
                    <div class="nw-spk-table-empty">Memuat aktivitas SPK...</div>
                </div>

                <div class="nw-timeline-foot"><i class="material-icons" style="font-size:13px">unfold_more</i>Scroll

                    untuk

                    melihat aktivitas lainnya</div>

            </section>

        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // SPK Timeline: one row per CREATE/UPDATE event, no changes to existing endpoint.
        // Event delegation: works after the timeline is reloaded via AJAX.
        $(document).on('click', '#spkTimelineContainer [data-spk-toggle]', function() {
            const button = $(this);
            const detail = document.getElementById(button.attr('data-spk-toggle'));
            if (!detail) return;
            const opening = detail.hidden;
            detail.hidden = !opening;
            button.attr('aria-expanded', String(opening));
            const count = detail.querySelectorAll('.spk-item').length;
            button.text(opening ? 'Less' : '+' + count + ' More');
        });

        function loadSpkTimeline() {
            const target = $('#spkTimelineContainer');
            const esc = value => String(value === null || value === undefined || value === '' ? '-' : value)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            const obj = value => {
                if (!value) return {};
                if (typeof value === 'string') {
                    try {
                        return obj(JSON.parse(value));
                    } catch (_) {
                        return {};
                    }
                }
                return typeof value === 'object' && !Array.isArray(value) ? value : {};
            };
            const first = (...values) => values.find(value =>
                value !== undefined && value !== null && String(value).trim() !== '' && String(value) !== '-'
            ) ?? '-';
            const date = value => {
                if (!value || value === '-') return '-';
                const d = new Date(String(value).replace(' ', 'T'));
                return Number.isNaN(d.getTime()) ? esc(value) : esc(d.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                }));
            };
            const itemsOf = source => {
                const data = obj(source);
                if (Array.isArray(data.items)) return data.items;
                if (typeof data.items === 'string') {
                    try {
                        const parsed = JSON.parse(data.items);
                        if (Array.isArray(parsed)) return parsed;
                    } catch (_) {}
                }
                return [];
            };
            const itemKey = (item, index) => String(first(
                item.detail_po_id, item.detail_id, item.id, item.kode, item.nama, index
            ));
            const qty = item => first(item.qty, item.pcs, item.quantity, item.jumlah,
                item.set, 0);
            const itemName = (item, index) => first(item.kode, item.nama, item.name,
                item.description, 'Item ' + (index + 1));
            const itemLines = (row, after, before, changes, isUpdate, rowIndex) => {
                const current = itemsOf(after);
                const previous = itemsOf(before);
                const fallback = Array.isArray(row.items) ? row.items : itemsOf(row);
                const items = current.length ? current : (previous.length ? previous : fallback);
                const previousMap = new Map(previous.map((item, i) => [itemKey(item, i), item]));
                const isFallback = !current.length && !previous.length && fallback.length;
                let lines = [];

                if (items.length) {
                    lines = items.map((item, i) => {
                        const old = previousMap.get(itemKey(item, i));
                        const oldQty = old ? qty(old) : null;
                        const newQty = qty(item);
                        const revised = isUpdate && current.length && oldQty !== null &&
                            String(oldQty) !== String(newQty);
                        return '<span class="spk-item">• ' + esc(itemName(item, i)) +
                            ' <b>(' + esc(newQty) + ' ' + esc(first(item.satuan, item.unit, 'pcs')) + ')</b>' +
                            (revised ? ' <span class="spk-qty-change">[' + esc(oldQty) + ' → ' + esc(newQty) +
                                ']</span>' : '') +
                            '</span>';
                    });
                } else {
                    lines = Object.entries(changes)
                        .filter(([key]) => /qty|jumlah|item/i.test(key))
                        .slice(0, 12)
                        .map(([key, value]) => {
                            const change = obj(value);
                            return '<span class="spk-item">• ' + esc(key) + ': ' +
                                '<span class="spk-qty-change">' + esc(first(change.before, change.old)) +
                                ' → ' + esc(first(change.after, change.new)) + '</span></span>';
                        });
                }

                if (!lines.length) {
                    return '<span class="spk-muted">Item tidak tersedia dalam snapshot</span>';
                }

                // Satu item langsung tampil; dua item atau lebih: satu item + tombol More.
                const fallbackNote = isFallback ?
                    '<small class="spk-muted">Qty saat ini (snapshot event tidak tersedia)</small>' :
                    '';
                if (lines.length === 1) {
                    return '<div class="spk-items-preview">' + lines[0] + '</div>' + fallbackNote;
                }

                const detailId = 'spk-items-detail-' + rowIndex;
                return '<div class="spk-items-preview">' + lines[0] +
                    ' <button type="button" class="spk-items-more" aria-expanded="false" aria-controls="' +
                    detailId + '" data-spk-toggle="' + detailId + '">+' + (lines.length - 1) +
                    ' More</button></div>' +
                    '<div class="spk-items-details" id="' + detailId + '" hidden>' +
                    lines.slice(1).join('') + '</div>' + fallbackNote;
            };

            $.get("{{ route('spk.time') }}")
                .done(function(response) {
                    const rows = Array.isArray(response) ? response :
                        (response && Array.isArray(response.data) ? response.data : []);
                    if (!rows.length) {
                        target.html('<div class="nw-spk-table-empty">Belum ada aktivitas SPK.</div>');
                        return;
                    }
                    let html = '<table class="nw-spk-history-table"><thead><tr>' +
                        '<th>#</th><th>No PO</th><th>No SPK</th><th>Sub Name</th><th>Ketert</th>' +
                        '<th>Items</th><th>Jenis SPK</th><th>Tanggal Buat</th><th>Deadline</th></tr></thead><tbody>';
                    rows.forEach((row, index) => {
                        const after = obj(row.after);
                        const before = obj(row.before);
                        const changes = obj(row.changes);
                        const eventType = String(row.type ?? '').toLowerCase();
                        const isUpdate = /update|edit|ubah|revis/i.test(eventType);
                        const snapshot = Object.keys(after).length ? after : before;
                        const count = Object.keys(changes).length;
                        const remark = first(row.remark, row.keterangan, row.note,
                            isUpdate ? 'Edited SPK' + (count ? ' (' + count + ' perubahan)' : '') :
                            'SPK dibuat');
                        const po = first(snapshot.no_po, row.no_po, before.no_po);
                        const spk = first(snapshot.no_spk, row.no_spk, before.no_spk, row.spk_id);
                        const supplier = first(snapshot.sup, snapshot.supplier, snapshot.sub_name,
                            row.sup, row.supplier, row.sub_name, before.sup);
                        const deadline = first(snapshot.tgl_selesai, snapshot.deadline,
                            snapshot.tanggal_deadline, snapshot.due_date, row.tgl_selesai,
                            row.deadline, before.tgl_selesai, before.deadline);
                        html += '<tr>' +
                            '<td>' + (index + 1) + '</td>' +
                            '<td>' + esc(po) + '</td>' +
                            '<td class="spk-code">' + esc(spk) + '</td>' +
                            '<td>' + esc(supplier) + '</td>' +
                            '<td class="spk-note"><span class="spk-note-badge ' +
                            (isUpdate ? 'spk-note-updated' : 'spk-note-created') + '">' +
                            esc(remark) + '</span>' +
                            (row.user ? '<small class="spk-muted">' + esc(row.user) + '</small>' : '') +
                            '</td>' +
                            '<td class="spk-items-cell">' + itemLines(row, after, before, changes, isUpdate,
                                index) + '</td>' +
                            '<td><span class="spk-pill ' + (isUpdate ? 'spk-pill-update' : 'spk-pill-create') +
                            '">' +
                            (isUpdate ? 'UPDATE SPK' : 'NEW SPK') + '</span></td>' +
                            '<td class="spk-date">' + date(first(row.time, row.created_at)) + '</td>' +
                            '<td class="spk-date">' + date(deadline) + '</td></tr>';
                    });
                    target.html(html + '</tbody></table>');
                })
                .fail(function() {
                    target.html(
                        '<div class="nw-spk-table-empty">Gagal memuat aktivitas SPK. Periksa endpoint spk.time.</div>'
                        );
                });
        }

        function renderChanges(changes) {

            if (!changes) return '';

            let html = '<ul class="list-unstyled small mb-0">';

            Object.keys(changes).forEach(key => {

                let before = formatValue(changes[key].before);

                let after = formatValue(changes[key].after);

                html += `

            <li>

                <code>${key}</code> :

                <span class="text-danger">${before}</span>

                →

                <span class="text-success">${after}</span>

            </li>

        `;

            });

            html += '</ul>';

            return html;

        }



        function formatValue(val) {

            if (val === null) return '<i>null</i>';

            // IMAGE DETECT

            if (typeof val === 'string' && val.match(/\.(jpg|png|jpeg|webp)$/)) {

                return `<img src="${val}" style="height:30px;border:1px solid #ccc">`;

            }

            if (typeof val === 'object') {

                return JSON.stringify(val);

            }

            return val;

        }



        function formatTanggal(date) {

            return new Date(date).toLocaleString('id-ID');

        }

        loadSpkTimeline();

        // timeline lama

        function loadTimeline() {

            $.ajax({

                url: "{{ route('timeline.data') }}",

                type: "GET",

                dataType: "json",

                success: function(data) {

                    let html = '';

                    console.log(data)

                    data.forEach(function(item) {

                        let userName = item.user?.name ?? 'Unknown';

                        let remark = item.isi?.remark ?? '-';

                        let created = item.isi?.timestamp ?

                            new Date(item.isi.timestamp).toLocaleString() :

                            new Date(item.created_at).toLocaleString();

                        let jenisClass = item.jenis === 'edit pfi' ? 'b-success' : '';

                        html += `

                        <div class="sl-item ${jenisClass}">

                            <div class="sl-content">

                                <div class="sl-date text-muted">${created}</div>

                                <p><strong>${userName}</strong>: ${remark}</p>

                            </div>

                        </div>

                    `;

                    });

                    $('#timelineContainer').html(html);

                }

            });

        }

        // 🔥 WAJIB DIPANGGIL

        // loadTimeline(); // PO Timeline dinonaktifkan

        // loadSpkTimeline();

        function loadMyApproval() {

            $.get('/dashboard/pending-approval', function(res) {

                let html = '';

                html += `

        <b>${res.total_pengajuan} pengajuan</b><br>

        <small>${res.total_step} approval menunggu persetujuanmu</small>

        <hr>

    `;

                res.data.forEach(item => {

                    html += `

            <div>

               <a href="/pengajuan?pengajuan_id=${item.id}">

                    Pengajuan #${item.id}

                </a>

            </div>

        `;

                });

                $('#approval-box').html(html);

            });

        }

        loadMyApproval();
    </script>

@endsection
