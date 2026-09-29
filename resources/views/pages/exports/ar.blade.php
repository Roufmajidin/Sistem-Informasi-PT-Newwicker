@extends('master.master')

@section('content')

    <style>
        /* =========================================================
               PAGE
            ========================================================= */

        .ar-page {
            padding: 20px;
        }

        .ar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .ar-title {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }

        .ar-subtitle {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }

        .ar-actions {
            display: flex;
            gap: 8px;
        }

        .ar-btn {
            border: 0;
            border-radius: 7px;
            padding: 9px 15px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .ar-btn-primary {
            background: #2563eb;
            color: #fff;
        }

        .ar-btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }


        /* =========================================================
               SUMMARY
            ========================================================= */

        .ar-summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .ar-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
        }

        .ar-card-label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 7px;
        }

        .ar-card-value {
            font-size: 21px;
            font-weight: 700;
            color: #111827;
        }

        .ar-card-small {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 4px;
        }


        /* =========================================================
               FILTER
            ========================================================= */

        .ar-filter {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .ar-filter-row {
            display: grid;
            grid-template-columns: 180px 180px 180px 1fr auto;
            gap: 10px;
            align-items: end;
        }

        .ar-field label {
            display: block;
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .ar-field input,
        .ar-field select {
            width: 100%;
            height: 36px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 0 10px;
            font-size: 13px;
        }


        /* =========================================================
               TABLE
            ========================================================= */

        .ar-table-wrapper {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
        }

        .ar-table-scroll {
            overflow-x: auto;
        }

        .ar-table {
            width: 100%;
            min-width: 2500px;
            border-collapse: collapse;
            font-size: 12px;
        }

        .ar-table th,
        .ar-table td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            white-space: nowrap;
            vertical-align: middle;
        }

        .ar-table thead th {
            text-align: center;
            font-weight: 700;
        }

        .ar-table tbody tr:hover {
            background: #f9fafb;
        }


        /* =========================================================
               HEADER WARNA
            ========================================================= */

        .th-customer {
            background: #dbeafe;
            color: #1e40af;
        }

        .th-document {
            background: #e0e7ff;
            color: #3730a3;
        }

        .th-export {
            background: #dcfce7;
            color: #166534;
        }

        .th-value {
            background: #fef3c7;
            color: #92400e;
        }

        .th-payment {
            background: #fce7f3;
            color: #9d174d;
        }

        .th-ar {
            background: #ede9fe;
            color: #5b21b6;
        }


        /* =========================================================
               ALIGNMENT
            ========================================================= */

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .currency {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }


        /* =========================================================
               MONTH GROUP
            ========================================================= */

        .month-group-row td {
            background: #dbeafe !important;
            color: #1e3a8a;
            font-weight: 700;
            font-size: 12px;
            padding: 8px 10px !important;
            border-top: 2px solid #93c5fd !important;
            border-bottom: 1px solid #93c5fd !important;
        }

        .month-total-row td {
            background: #f8fafc !important;
            font-weight: 700;
            color: #374151;
            border-top: 2px solid #cbd5e1 !important;
        }

        .month-total-label {
            text-align: right;
        }


        /* =========================================================
               PO
            ========================================================= */

        .po-list {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .po-item {
            background: #f3f4f6;
            border-radius: 4px;
            padding: 2px 6px;
            display: inline-block;
        }


        /* =========================================================
               EDITABLE
               STYLE DIKEMBALIKAN:
               - warna hitam/abu normal
               - underline dashed
               - focus biru
            ========================================================= */

        .ar-editable-input {
            width: 100%;
            min-width: 90px;
            height: 30px;

            border: 0 !important;
            border-bottom: 1px dashed #9ca3af !important;

            border-radius: 0 !important;

            padding: 3px 2px;

            background: transparent !important;

            color: #374151 !important;

            font-size: 12px;
            font-style: normal !important;

            outline: none !important;

            box-shadow: none !important;

            transition: .15s ease;
        }


        .ar-editable-input:hover {
            border: 0 !important;
            border-bottom: 1px dashed #6b7280 !important;

            background: #f9fafb !important;

            color: #374151 !important;
        }


        .ar-editable-input:focus {
            border: 0 !important;
            border-bottom: 2px solid #2563eb !important;

            background: #fff !important;

            color: #374151 !important;

            outline: none !important;

            box-shadow: none !important;
        }


        .ar-editable-input.number {
            text-align: right;
        }

        .ar-editable-input.date {
            min-width: 125px;
        }

        .ar-editable-input.text {
            min-width: 110px;
        }


        /* Pastikan tidak diwarisi warna merah */

        .field-save-status {
            display: inline-block;
            min-width: 70px;
            font-size: 11px;
            color: #6c757d;
            margin-top: 4px;
        }

        .field-save-status.success {
            color: #198754;
        }

        .field-save-status.error {
            color: #dc3545;
        }

        .ar-editable-input.is-saving {
            opacity: .65;
        }

        .ar-editable-input.is-saved {
            box-shadow: 0 0 0 1px #198754 !important;
        }

        .ar-editable-input.is-save-error {
            box-shadow: 0 0 0 1px #dc3545 !important;
        }

        .ar-table input.ar-editable-input,
        .ar-table input.ar-editable-input:visited,
        .ar-table input.ar-editable-input:hover,
        .ar-table input.ar-editable-input:focus {
            color: #374151 !important;
            font-style: normal !important;
            text-decoration: none !important;
        }


        /* =========================================================
               MONEY INPUT
            ========================================================= */

        .money-input {
            text-align: right;
            min-width: 120px;
        }

        .money-cell {
            display: flex;
            align-items: center;
            gap: 5px;
            min-width: 125px;
        }

        .money-cell .currency-prefix {
            flex: 0 0 auto;
            font-weight: 600;
            color: #26364a;
            white-space: nowrap;
        }

        .money-cell .money-input {
            flex: 1 1 auto;
            min-width: 0;
            text-align: right;
        }



        /* =========================================================
               CALCULATED
            ========================================================= */

        .calculated-rupiah {
            font-weight: 700;
            color: #111827;
            text-align: right;
        }

        .calc-note {
            font-size: 9px;
            color: #9ca3af;
            margin-top: 2px;
        }


        /* =========================================================
               STATUS
            ========================================================= */

        .ar-status {
            display: inline-flex;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
        }

        .status-lunas {
            background: #dcfce7;
            color: #166534;
        }

        .status-belum {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-sebagian {
            background: #fef3c7;
            color: #92400e;
        }

        .status-overdue {
            background: #fee2e2;
            color: #b91c1c;
        }


        /* =========================================================
               SAVE
            ========================================================= */

        .btn-save-row {
            border: 0;
            border-radius: 5px;

            padding: 4px 8px;

            font-size: 11px;

            background: #2563eb;
            color: #fff;

            cursor: pointer;

            display: none;
        }

        .ar-row.is-dirty .btn-save-row {
            display: inline-block;
        }

        .btn-save-row:hover {
            background: #1d4ed8;
        }

        .btn-save-row:disabled {
            opacity: .6;
            cursor: wait;
        }


        /* =========================================================
               PAYMENT BUTTON
            ========================================================= */

        .btn-add-payment {
            border: 0;
            border-radius: 5px;

            padding: 3px 7px;

            font-size: 10px;

            background: #f3f4f6;
            color: #374151;

            cursor: pointer;

            margin-top: 3px;
        }

        .btn-add-payment:hover {
            background: #e5e7eb;
        }


        /* =========================================================
               EMPTY
            ========================================================= */

        .ar-empty {
            text-align: center;
            padding: 50px;
            color: #9ca3af;
        }


        /* =========================================================
               DIRTY
            ========================================================= */

        .ar-row.is-dirty {
            background: #fffdf0;
        }


        @media (max-width: 900px) {

            .ar-summary {
                grid-template-columns: repeat(2, 1fr);
            }

            .ar-filter-row {
                grid-template-columns: 1fr 1fr;
            }

        }
    </style>


    @php

        /* =========================================================
       FORMAT
    ========================================================= */

        $formatUsd = function ($value) {
            return '$ ' . number_format((float) ($value ?? 0), 3, '.', ',');
        };

        $formatRupiah = function ($value) {
            return 'Rp ' . number_format((float) ($value ?? 0), 2, ',', '.');
        };

        /* =========================================================
       BULAN
    ========================================================= */

        $bulanIndonesia = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        /* =========================================================
       GROUP PER BULAN
    ========================================================= */

        $arsByMonth = $ars
            ->filter(function ($ar) {
                return !empty($ar->tanggal_invoice);
            })
            ->sortBy(function ($ar) {
                return $ar->tanggal_invoice;
            })
            ->groupBy(function ($ar) {
                return $ar->tanggal_invoice->format('Y-m');
            });

        /* =========================================================
       SUMMARY
    ========================================================= */

        $totalInvoice = $ars->count();

        /*
    |--------------------------------------------------------------------------
    | TOTAL PENJUALAN
    |--------------------------------------------------------------------------
    |
    | FOB PEB USD × KURS KEMENKEU
    |
    */

        $totalPenjualan = $ars->sum(function ($ar) {
            return (float) $ar->fob_peb_usd * (float) $ar->kurs_kemenkeu;
        });

        $totalDibayar = $ars->sum(function ($ar) {
            return (float) $ar->total_dibayar;
        });

        $totalPiutang = $ars->sum(function ($ar) {
            $jumlahRp = (float) $ar->fob_peb_usd * (float) $ar->kurs_kemenkeu;

            return $jumlahRp + (float) $ar->total_surcharge - (float) $ar->total_dibayar;
        });
    @endphp


    <div class="ar-page">


        {{-- =========================================================
         HEADER
    ========================================================== --}}

        <div class="ar-header">

        @section('btn')
            <div>

                <div class="ar-title">
                    Rekap AR Buyer / Penjualan Export
                </div>

                <div class="ar-subtitle">
                    Monitoring invoice, pembayaran, dan sisa piutang customer
                </div>

            </div>
        @endsection


        <div class="ar-actions mt-4">

            {{-- Tombol tambahan jika diperlukan --}}

        </div>

    </div>


    {{-- =========================================================
         SUMMARY
    ========================================================== --}}

    <div class="ar-summary">

        <div class="ar-card">

            <div class="ar-card-label">
                TOTAL INVOICE
            </div>

            <div class="ar-card-value">

                {{ number_format($totalInvoice, 0, ',', '.') }}

            </div>

            <div class="ar-card-small">
                Invoice / AR
            </div>

        </div>


        <div class="ar-card">

            <div class="ar-card-label">
                TOTAL PENJUALAN
            </div>

            <div class="ar-card-value">

                {{ $formatRupiah($totalPenjualan) }}

            </div>

        </div>


        <div class="ar-card">

            <div class="ar-card-label">
                TOTAL DIBAYAR
            </div>

            <div class="ar-card-value">

                {{ $formatRupiah($totalDibayar) }}

            </div>

        </div>


        <div class="ar-card">

            <div class="ar-card-label">
                SISA PIUTANG
            </div>

            <div class="ar-card-value">

                {{ $formatRupiah($totalPiutang) }}

            </div>

        </div>

    </div>


    {{-- =========================================================
         FILTER
    ========================================================== --}}

    <div class="ar-filter">

        <div class="ar-filter-row">


            <div class="ar-field">

                <label>
                    Periode
                </label>

                <select id="filterYear">

                    <option value="">
                        Semua Tahun
                    </option>

                    @foreach ($ars->filter(fn($ar) => !empty($ar->tanggal_invoice))->map(fn($ar) => $ar->tanggal_invoice->format('Y'))->unique()->sortDesc() as $year)
                        <option value="{{ $year }}">
                            {{ $year }}
                        </option>
                    @endforeach

                </select>

            </div>


            <div class="ar-field">

                <label>
                    Status AR
                </label>

                <select id="filterStatus">

                    <option value="">
                        Semua Status
                    </option>

                    <option value="belum_dibayar">
                        Belum Dibayar
                    </option>

                    <option value="deposit">
                        Deposit
                    </option>

                    <option value="sebagian">
                        Sebagian
                    </option>

                    <option value="lunas">
                        Lunas
                    </option>

                    <option value="overdue">
                        Overdue
                    </option>

                </select>

            </div>


            <div class="ar-field">

                <label>
                    Customer
                </label>

                <input type="text" id="filterCustomer" placeholder="Nama customer...">

            </div>


            <div class="ar-field">

                <label>
                    Pencarian
                </label>

                <input type="text" id="filterSearch" placeholder="Invoice / PO / PEB...">

            </div>


            <button type="button" class="ar-btn ar-btn-primary" onclick="filterAr()">
                Filter
            </button>


        </div>

    </div>


    {{-- =========================================================
         TABLE
    ========================================================== --}}

    <div class="ar-table-wrapper">

        <div class="ar-table-scroll">

            <table class="ar-table">

                <thead>


                    {{-- GROUP HEADER --}}

                    <tr>

                        <th rowspan="2">
                            No
                        </th>


                        <th colspan="5" class="th-customer">
                            CUSTOMER
                        </th>


                        <th colspan="2" class="th-document">
                            DOKUMEN EXPORT
                        </th>


                        <th colspan="5" class="th-value">
                            NILAI PENJUALAN
                        </th>


                        <th colspan="3" class="th-payment">
                            PEMBAYARAN
                        </th>


                        <th colspan="3" class="th-ar">
                            ACCOUNT RECEIVABLE
                        </th>


                        <th rowspan="2">
                            Keterangan
                        </th>


                        <th rowspan="2">
                            Action
                        </th>

                    </tr>


                    {{-- COLUMN HEADER --}}

                    <tr>

                        <th>
                            Nama Pelanggan
                        </th>

                        <th>
                            No. PO
                        </th>

                        <th>
                            No. Invoice
                        </th>

                        <th>
                            Tanggal Invoice
                        </th>

                        <th>
                            Tanggal Shipment
                        </th>


                        <th>
                            No. Pengajuan PEB
                        </th>

                        <th>
                            No. PEB
                        </th>


                        <th>
                            FOB (USD)
                        </th>

                        <th>
                            FOB PEB (USD)
                        </th>

                        <th>
                            Kurs Kemenkeu
                        </th>

                        <th>
                            Jumlah Container
                        </th>

                        <th>
                            Jumlah (Rp)
                        </th>


                        <th>
                            Deposit / Uang Muka
                        </th>

                        <th>
                            Pelunasan
                        </th>

                        <th>
                            Surcharge
                        </th>


                        <th>
                            Total Dibayar
                        </th>

                        <th>
                            Sisa Piutang
                        </th>

                        <th>
                            Status AR
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @if ($arsByMonth->isEmpty())
                        <tr>
                            <td colspan="21" class="ar-empty">
                                Belum ada data AR.
                            </td>
                        </tr>
                    @else
                        @foreach ($arsByMonth as $monthKey => $monthArs)
                            @php

                                [$year, $month] = explode('-', $monthKey);

                                $monthName = $bulanIndonesia[(int) $month];

                                /*
            |--------------------------------------------------------------------------
            | TOTAL BULAN
            |--------------------------------------------------------------------------
            */

                                $monthFobUsd = $monthArs->sum(function ($ar) {
                                    return (float) $ar->fob_usd;
                                });

                                $monthFobPebUsd = $monthArs->sum(function ($ar) {
                                    return (float) $ar->fob_peb_usd;
                                });

                                $monthContainer = $monthArs->sum(function ($ar) {
                                    return (int) $ar->jumlah_container;
                                });

                                /*
            |--------------------------------------------------------------------------
            | JUMLAH RP
            | FOB PEB USD × KURS KEMENKEU
            |--------------------------------------------------------------------------
            */

                                $monthJumlahRp = $monthArs->sum(function ($ar) {
                                    return (float) $ar->fob_peb_usd * (float) $ar->kurs_kemenkeu;
                                });

                                $monthDeposit = $monthArs->sum(function ($ar) {
                                    return (float) $ar->total_deposit;
                                });

                                $monthPelunasan = $monthArs->sum(function ($ar) {
                                    return (float) $ar->total_pelunasan;
                                });

                                $monthSurcharge = $monthArs->sum(function ($ar) {
                                    return (float) $ar->total_surcharge;
                                });

                                $monthDibayar = $monthArs->sum(function ($ar) {
                                    return (float) $ar->total_dibayar;
                                });

                                $monthPiutang = $monthArs->sum(function ($ar) {
                                    $jumlahRp = (float) $ar->fob_peb_usd * (float) $ar->kurs_kemenkeu;

                                    return $jumlahRp + (float) $ar->total_surcharge - (float) $ar->total_dibayar;
                                });
                            @endphp


                            {{-- =====================================================
             HEADER BULAN
        ====================================================== --}}

                            <tr class="month-group-row" data-month="{{ $monthKey }}">

                                <td colspan="21">

                                    <i class="fa fa-calendar mr-1"></i>

                                    {{ strtoupper($monthName) }}
                                    {{ $year }}

                                    <span style="font-weight:500;margin-left:8px;">
                                        ({{ $monthArs->count() }} Invoice)
                                    </span>

                                </td>

                            </tr>


                            {{-- =====================================================
             DATA INVOICE
        ====================================================== --}}

                            @foreach ($monthArs as $monthIndex => $ar)
                                @php

                                    $ipl = $ar->exportIpl;

                                    /*
                |--------------------------------------------------------------------------
                | STATUS
                |--------------------------------------------------------------------------
                */

                                    $status = $ar->status_ar_calculated;

                                    $statusLabel = match ($status) {
                                        'lunas' => 'Lunas',

                                        'belum_dibayar' => 'Belum Dibayar',

                                        'deposit' => 'Deposit',

                                        'sebagian' => 'Sebagian',

                                        'overdue' => 'Overdue',

                                        default => ucfirst($status),
                                    };

                                    $statusClass = match ($status) {
                                        'lunas' => 'status-lunas',

                                        'overdue' => 'status-overdue',

                                        'sebagian', 'deposit' => 'status-sebagian',

                                        default => 'status-belum',
                                    };

                                    /*
                |--------------------------------------------------------------------------
                | PO
                |--------------------------------------------------------------------------
                */

                                    $poNos = collect();

                                    if ($ipl && $ipl->pos) {
                                        $poNos = $ipl->pos
                                            ->pluck('po_no')
                                            ->filter()
                                            ->map(function ($value) {
                                                return trim($value);
                                            })
                                            ->filter()
                                            ->unique()
                                            ->values();
                                    }

                                    /*
                |--------------------------------------------------------------------------
                | FALLBACK KE ITEMS
                |--------------------------------------------------------------------------
                */

                                    if ($poNos->isEmpty() && $ipl && $ipl->items) {
                                        $poNos = $ipl->items
                                            ->pluck('po_no')
                                            ->filter()
                                            ->map(function ($value) {
                                                return trim($value);
                                            })
                                            ->filter()
                                            ->unique()
                                            ->values();
                                    }

                                    /*
                |--------------------------------------------------------------------------
                | NILAI MONEY PER BARIS
                |--------------------------------------------------------------------------
                */

                                    $fobUsdValue = (float) ($ar->fob_usd ?? 0);
                                    $fobPebUsdValue = (float) ($ar->fob_peb_usd ?? 0);
                                    $kursValue = (float) ($ar->kurs_kemenkeu ?? 0);

                                    /*
                |--------------------------------------------------------------------------
                | JUMLAH RUPIAH
                |--------------------------------------------------------------------------
                */

                                    $jumlahRp = $fobPebUsdValue * $kursValue;

                                    /*
                |--------------------------------------------------------------------------
                | SISA PIUTANG
                |--------------------------------------------------------------------------
                */

                                    $sisaPiutang =
                                        $jumlahRp + (float) $ar->total_surcharge - (float) $ar->total_dibayar;
                                @endphp


                                <tr class="ar-row" data-id="{{ $ar->id }}"
                                    data-year="{{ optional($ar->tanggal_invoice)->format('Y') }}"
                                    data-status="{{ $status }}"
                                    data-customer="{{ strtolower($ipl->buyer ?? '') }}"
                                    data-search="{{ strtolower(
                                        ($ipl->invoice_no ?? '') .
                                            ' ' .
                                            $poNos->implode(' ') .
                                            ' ' .
                                            ($ar->no_peb ?? '') .
                                            ' ' .
                                            ($ar->no_pengajuan_peb ?? ''),
                                    ) }}">

                                    {{-- NO --}}

                                    <td class="text-center">
                                        {{ $monthIndex + 1 }}
                                    </td>


                                    {{-- CUSTOMER --}}

                                    <td>

                                        <input type="text" class="ar-editable-input text ar-field-ipl"
                                            data-id="{{ $ipl->id ?? '' }}" data-field="buyer"
                                            value="{{ $ipl->buyer ?? '' }}">

                                    </td>


                                    {{-- PO --}}

                                    <td>

                                        <div class="po-list">

                                            @forelse($poNos as $poNo)
                                                <span class="po-item">
                                                    {{ $poNo }}
                                                </span>

                                            @empty

                                                -
                                            @endforelse

                                        </div>

                                    </td>


                                    {{-- INVOICE --}}

                                    <td>

                                        <input type="text" class="ar-editable-input text ar-field-ipl"
                                            data-id="{{ $ipl->id ?? '' }}" data-field="invoice_no"
                                            value="{{ $ipl->invoice_no ?? '' }}">

                                    </td>


                                    {{-- TANGGAL INVOICE --}}

                                    <td>

                                        <input type="date" class="ar-editable-input date ar-field"
                                            data-id="{{ $ar->id }}" data-field="tanggal_invoice"
                                            value="{{ optional($ar->tanggal_invoice)->format('Y-m-d') }}">

                                    </td>


                                    {{-- TANGGAL SHIPMENT --}}

                                    <td>

                                        <input type="date" class="ar-editable-input date ar-field-ipl"
                                            data-id="{{ $ipl->id ?? '' }}" data-field="release_date"
                                            value="{{ !empty($ipl->release_date) ? \Carbon\Carbon::parse($ipl->release_date)->format('Y-m-d') : '' }}">

                                    </td>


                                    {{-- PENGAJUAN PEB --}}

                                    <td>

                                        <input type="text" class="ar-editable-input text ar-field"
                                            data-id="{{ $ar->id }}" data-field="no_pengajuan_peb"
                                            value="{{ $ar->no_pengajuan_peb ?? '' }}">

                                    </td>


                                    {{-- NO PEB --}}

                                    <td>

                                        <input type="text" class="ar-editable-input text ar-field"
                                            data-id="{{ $ar->id }}" data-field="no_peb"
                                            value="{{ $ar->no_peb ?? '' }}">

                                    </td>


                                    {{-- FOB USD --}}

                                    <td class="currency">
                                        <div class="money-cell">
                                            <span class="currency-prefix">$</span>
                                            <input type="text"
                                                class="ar-editable-input money-input ar-field"
                                                data-id="{{ $ar->id }}"
                                                data-field="fob_usd"
                                                data-money="usd"
                                                value="{{ $fobUsdValue }}">
                                        </div>
                                    </td>


                                    {{-- FOB PEB USD --}}

                                    <td class="currency">
                                        <div class="money-cell">
                                            <span class="currency-prefix">$</span>
                                            <input type="text"
                                                class="ar-editable-input money-input ar-field calc-trigger"
                                                data-id="{{ $ar->id }}"
                                                data-field="fob_peb_usd"
                                                data-money="usd"
                                                value="{{ $fobPebUsdValue }}">
                                        </div>
                                    </td>
                                    {{-- KURS KEMENKEU --}}

                                    <td class="currency">
                                        <div class="money-cell">
                                            <span class="currency-prefix">Rp</span>
                                            <input type="text"
                                                class="ar-editable-input money-input ar-field calc-trigger"
                                                data-id="{{ $ar->id }}" data-field="kurs_kemenkeu"
                                                data-money="idr" data-raw-value="{{ $kursValue }}"
                                                value="{{ number_format($kursValue, 2, ',', '.') }}">
                                        </div>
                                    </td>


                                    {{-- JUMLAH CONTAINER --}}

                                    <td class="text-center">

                                        <input type="number" min="0" step="1"
                                            class="ar-editable-input number ar-field" data-id="{{ $ar->id }}"
                                            data-field="jumlah_container" value="{{ $ar->jumlah_container }}">

                                    </td>


                                    {{-- JUMLAH RP --}}

                                    <td class="calculated-rupiah" data-jumlah-rupiah="{{ $ar->id }}">

                                        {{ $formatRupiah($jumlahRp) }}

                                        <div class="calc-note">
                                            FOB PEB × Kurs
                                        </div>

                                    </td>


                                    {{-- DEPOSIT --}}

                                    <td class="currency">

                                        {{ $formatRupiah($ar->total_deposit) }}

                                        <br>

                                        <button type="button" class="btn-add-payment"
                                            onclick="addPayment({{ $ar->id }}, 'deposit')">
                                            + Deposit
                                        </button>

                                    </td>


                                    {{-- PELUNASAN --}}

                                    <td class="currency">

                                        {{ $formatRupiah($ar->total_pelunasan) }}

                                        <br>

                                        <button type="button" class="btn-add-payment"
                                            onclick="addPayment({{ $ar->id }}, 'pelunasan')">
                                            + Pelunasan
                                        </button>

                                    </td>


                                    {{-- SURCHARGE --}}

                                    <td class="currency">
                                        <div class="money-cell">
                                            <span class="currency-prefix">Rp</span>
                                            <input type="text"
                                                class="ar-editable-input money-input ar-field calc-trigger"
                                                data-id="{{ $ar->id }}" data-field="total_surcharge"
                                                data-money="idr"
                                                value="{{ number_format((float) $ar->total_surcharge, 2, ',', '.') }}">
                                        </div>
                                    </td>


                                    {{-- TOTAL DIBAYAR --}}

                                    <td class="currency" data-total-dibayar="{{ $ar->id }}"
                                        data-value="{{ $ar->total_dibayar }}">

                                        {{ $formatRupiah($ar->total_dibayar) }}

                                    </td>


                                    {{-- SISA PIUTANG --}}

                                    <td class="currency" data-sisa-piutang="{{ $ar->id }}">

                                        {{ $formatRupiah($sisaPiutang) }}

                                    </td>


                                    {{-- STATUS --}}

                                    <td class="text-center">

                                        <span class="ar-status {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>

                                    </td>


                                    {{-- KETERANGAN --}}

                                    <td>

                                        <input type="text" class="ar-editable-input text ar-field"
                                            data-id="{{ $ar->id }}" data-field="keterangan"
                                            value="{{ $ar->keterangan ?? '' }}">

                                    </td>


                                    {{-- ACTION --}}

                                    <td class="text-center">
                                        <span class="field-save-status"
                                            data-save-status="{{ $ar->id }}"></span>
                                    </td>

                                </tr>
                            @endforeach


                            {{-- =====================================================
             TOTAL BULAN
        ====================================================== --}}

                            <tr class="month-total-row">

                                <td></td>

                                <td colspan="7" class="month-total-label">

                                    TOTAL
                                    {{ $monthName }}
                                    {{ $year }}

                                </td>


                                <td class="currency">
                                    {{ $formatUsd($monthFobUsd) }}
                                </td>


                                <td class="currency">
                                    {{ $formatUsd($monthFobPebUsd) }}
                                </td>


                                <td class="currency">
                                    -
                                </td>


                                <td class="text-center">
                                    {{ number_format($monthContainer, 0, ',', '.') }}
                                </td>


                                <td class="currency">
                                    {{ $formatRupiah($monthJumlahRp) }}
                                </td>


                                <td class="currency">
                                    {{ $formatRupiah($monthDeposit) }}
                                </td>


                                <td class="currency">
                                    {{ $formatRupiah($monthPelunasan) }}
                                </td>


                                <td class="currency">
                                    {{ $formatRupiah($monthSurcharge) }}
                                </td>


                                <td class="currency">
                                    {{ $formatRupiah($monthDibayar) }}
                                </td>


                                <td class="currency">
                                    {{ $formatRupiah($monthPiutang) }}
                                </td>


                                <td class="text-center">
                                    -
                                </td>


                                <td>
                                    -
                                </td>


                                <td></td>

                            </tr>
                        @endforeach
                    @endif

                </tbody>

            </table>

        </div>

    </div>

</div>


<script>
    /* =========================================================
       PARSE NUMBER
    ========================================================= */

    function parseNumber(value) {
        if (value === null || value === undefined) return 0;

        let str = String(value).trim();
        if (!str) return 0;

        str = str.replace(/Rp/gi, '').replace(/\$/g, '').trim();

        // Format Indonesia: 18.062,00 -> 18062.00
        if (str.includes('.') && str.includes(',')) {
            const lastDot = str.lastIndexOf('.');
            const lastComma = str.lastIndexOf(',');

            if (lastComma > lastDot) {
                str = str.replace(/\./g, '').replace(',', '.');
            } else {
                // Format US: 19,430.00 -> 19430.00
                str = str.replace(/,/g, '');
            }
        } else if (str.includes(',')) {
            // Ambiguous single comma: treat as decimal only when the
            // comma is followed by 1-2 digits; otherwise it is thousands.
            const parts = str.split(',');
            const last = parts[parts.length - 1];
            if (last.length <= 2) {
                str = str.replace(/,/g, '.');
            } else {
                str = str.replace(/,/g, '');
            }
        }

        str = str.replace(/[^0-9.-]/g, '');
        const result = Number(str);
        return Number.isFinite(result) ? result : 0;
    }


    /* =========================================================
       FORMAT USD
    ========================================================= */

    function formatUsd(value) {
        value =
            parseFloat(value) || 0;


        return '$ ' +
            value.toLocaleString(
                'en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );
    }


    /* =========================================================
       FORMAT RUPIAH
    ========================================================= */

    function formatRupiah(value) {
        value =
            parseFloat(value) || 0;


        return 'Rp ' +
            value.toLocaleString(
                'id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );
    }


    /* =========================================================
       FORMAT KURS
    ========================================================= */

    function formatKurs(value) {
        value =
            parseFloat(value) || 0;


        return 'Rp ' +
            value.toLocaleString(
                'id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );
    }


    /* =========================================================
       FORMAT INPUT MONEY
    ========================================================= */

    function formatMoneyInput(input) {
        if (!input) {
            return;
        }


        const type =
            input.dataset.money;


        const raw = input.dataset.rawValue;
        const value = raw !== undefined && raw !== '' ?
            (Number(raw) || 0) :
            parseNumber(input.value);


        // DEBUG SEMENTARA:
        // USD sengaja TIDAK diformat agar kita bisa melihat nilai mentah.
        // Contoh: 19430 tetap tampil 19430, bukan 19.43 / 19,430.00.
        if (type === 'usd') {
            return;
        } else if (type === 'idr') {

            input.value =
                value.toLocaleString(
                    'id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }
                );

        }

    }


    /* =========================================================
       CALCULATE JUMLAH RP
    ========================================================= */

    function calculateJumlahRp(row) {
        if (!row) {
            return 0;
        }


        const fobPebInput =
            row.querySelector(
                '[data-field="fob_peb_usd"]'
            );


        const kursInput =
            row.querySelector(
                '[data-field="kurs_kemenkeu"]'
            );


        const fobPeb =
            parseNumber(
                fobPebInput ?
                fobPebInput.value :
                0
            );


        const kurs =
            parseNumber(
                kursInput ?
                kursInput.value :
                0
            );


        const jumlahRp =
            fobPeb * kurs;


        const target =
            row.querySelector(
                '[data-jumlah-rupiah]'
            );


        if (target) {

            target.innerHTML =
                formatRupiah(
                    jumlahRp
                ) +
                `
                <div class="calc-note">
                    FOB PEB × Kurs
                </div>
                `;

        }


        updateSisaPiutang(
            row,
            jumlahRp
        );


        return jumlahRp;
    }


    /* =========================================================
       TOTAL DIBAYAR
    ========================================================= */

    function getTotalDibayar(row) {
        const cell =
            row.querySelector(
                '[data-total-dibayar]'
            );


        if (!cell) {
            return 0;
        }


        return parseFloat(
            cell.dataset.value
        ) || 0;
    }


    /* =========================================================
       UPDATE SISA
    ========================================================= */

    function updateSisaPiutang(
        row,
        jumlahRp = null
    ) {
        if (!row) {
            return;
        }


        if (jumlahRp === null) {

            jumlahRp =
                calculateJumlahRpOnly(
                    row
                );

        }


        const surchargeInput =
            row.querySelector(
                '[data-field="total_surcharge"]'
            );


        const surcharge =
            parseNumber(
                surchargeInput ?
                surchargeInput.value :
                0
            );


        const totalDibayar =
            getTotalDibayar(row);


        const sisa =
            jumlahRp +
            surcharge -
            totalDibayar;


        const target =
            row.querySelector(
                '[data-sisa-piutang]'
            );


        if (target) {

            target.innerText =
                formatRupiah(
                    sisa
                );

        }

    }


    /* =========================================================
       CALCULATE TANPA DOM
    ========================================================= */

    function calculateJumlahRpOnly(row) {
        const fobPebInput =
            row.querySelector(
                '[data-field="fob_peb_usd"]'
            );


        const kursInput =
            row.querySelector(
                '[data-field="kurs_kemenkeu"]'
            );


        const fobPeb =
            parseNumber(
                fobPebInput ?
                fobPebInput.value :
                0
            );


        const kurs =
            parseNumber(
                kursInput ?
                kursInput.value :
                0
            );


        return fobPeb * kurs;
    }


    /* =========================================================
       INPUT MONEY
    ========================================================= */

    // DEBUG: USD input tidak diformat sementara.
    // Nilai yang tampil adalah nilai mentah dari Blade/database.
    // parseNumber() tetap dipakai saat proses save/perhitungan.



    document.addEventListener(
        'blur',
        function(e) {

            if (
                !e.target.classList.contains(
                    'money-input'
                )
            ) {
                return;
            }


            formatMoneyInput(
                e.target
            );

        },
        true
    );


    /* =========================================================
       INPUT EVENT
    ========================================================= */

    document.addEventListener(
        'input',
        function(e) {

            const field =
                e.target;

            if (field.classList.contains('money-input')) {
                delete field.dataset.rawValue;
            }


            if (
                field.classList.contains(
                    'ar-field'
                ) ||
                field.classList.contains(
                    'ar-field-ipl'
                )
            ) {

                const row =
                    field.closest(
                        '.ar-row'
                    );


                if (row) {

                    row.classList.add(
                        'is-dirty'
                    );

                }

            }


            /*
            |--------------------------------------------------------------------------
            | REALTIME CALCULATION
            |--------------------------------------------------------------------------
            */

            if (
                field.dataset.field ===
                'fob_peb_usd' ||
                field.dataset.field ===
                'kurs_kemenkeu' ||
                field.dataset.field ===
                'total_surcharge'
            ) {

                const row =
                    field.closest(
                        '.ar-row'
                    );


                calculateJumlahRp(
                    row
                );

            }

        }
    );


    /* =========================================================
       CHANGE
    ========================================================= */

    document.addEventListener(
        'change',
        function(e) {

            if (
                e.target.classList.contains(
                    'ar-field'
                ) ||
                e.target.classList.contains(
                    'ar-field-ipl'
                )
            ) {

                const row =
                    e.target.closest(
                        '.ar-row'
                    );


                if (row) {

                    row.classList.add(
                        'is-dirty'
                    );

                }

            }

        }
    );


    /* =========================================================
       INLINE SAVE PER FIELD
       Klik field -> edit -> Enter / Tab / blur -> otomatis simpan
    ========================================================= */

    const savingFields = new WeakMap();

    function setFieldSaveStatus(row, type = 'saving', message = '') {
        if (!row) return;

        const status = row.querySelector('.field-save-status');
        if (!status) return;

        status.className = 'field-save-status ' + type;

        if (type === 'saving') {
            status.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Menyimpan';
        } else if (type === 'success') {
            status.innerHTML = '<i class="fa fa-check"></i> Tersimpan';
            setTimeout(() => {
                if (status.classList.contains('success')) {
                    status.innerHTML = '';
                    status.className = 'field-save-status';
                }
            }, 1200);
        } else if (type === 'error') {
            status.innerHTML = '<i class="fa fa-times"></i> ' + (message || 'Gagal');
        } else {
            status.innerHTML = '';
        }
    }

    function getCleanFieldValue(input) {
        let value = input.value;

        if (input.dataset.money) {
            return parseNumber(value);
        }

        return value;
    }

    async function saveSingleField(input) {
        if (!input || !input.dataset.field || !input.dataset.id) {
            return;
        }

        // Jangan menyimpan ketika value belum berubah.
        const currentValue = getCleanFieldValue(input);
        const oldValue = input.dataset.originalValue ?? '';

        if (String(currentValue) === String(oldValue)) {
            return;
        }

        if (savingFields.get(input)) {
            return savingFields.get(input);
        }

        const row = input.closest('.ar-row');
        const isIpl = input.classList.contains('ar-field-ipl');
        const url = isIpl ?
            '/ar_buyer/ipl/' + input.dataset.id :
            '/ar_buyer/' + input.dataset.id;

        const request = (async () => {
            setFieldSaveStatus(row, 'saving');
            input.classList.add('is-saving');

            try {
                const response = await fetch(url, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        field: input.dataset.field,
                        value: currentValue
                    })
                });

                const result = await response.json().catch(() => ({}));

                if (!response.ok || result.success === false) {
                    throw new Error(
                        result.message ||
                        ('Gagal menyimpan ' + input.dataset.field)
                    );
                }

                input.dataset.originalValue = String(currentValue);
                input.classList.remove('is-saving');
                input.classList.add('is-saved');
                setFieldSaveStatus(row, 'success');

                setTimeout(() => {
                    input.classList.remove('is-saved');
                }, 1000);

                // Update perhitungan langsung tanpa reload.
                if (row && (
                        input.dataset.field === 'fob_peb_usd' ||
                        input.dataset.field === 'kurs_kemenkeu' ||
                        input.dataset.field === 'total_surcharge'
                    )) {
                    calculateJumlahRp(row);
                }

                // Update search/status row bila diperlukan.
                if (row && input.dataset.field === 'no_peb') {
                    row.dataset.search = row.dataset.search || '';
                }

            } catch (error) {
                console.error(error);
                input.classList.remove('is-saving');
                input.classList.add('is-save-error');
                setFieldSaveStatus(row, 'error', error.message);

                setTimeout(() => {
                    input.classList.remove('is-save-error');
                }, 1800);
            } finally {
                savingFields.delete(input);
            }
        })();

        savingFields.set(input, request);
        return request;
    }

    // Simpan nilai awal saat halaman selesai.
    document.querySelectorAll('.ar-editable-input').forEach(function(input) {
        input.dataset.originalValue = String(getCleanFieldValue(input));
    });

    // Enter = simpan dan tetap di field.
    document.addEventListener('keydown', function(e) {
        const input = e.target.closest('.ar-editable-input');
        if (!input) return;

        if (e.key === 'Enter') {
            e.preventDefault();
            input.blur();
        }
    });

    // Blur = otomatis simpan field tersebut.
    document.addEventListener('blur', function(e) {
        const input = e.target.closest('.ar-editable-input');
        if (!input) return;

        // Untuk input money, rapikan tampilan sebelum save.
        if (input.classList.contains('money-input')) {
            formatMoneyInput(input);
        }

        saveSingleField(input);
    }, true);

    // Tandai field sedang diedit.
    document.addEventListener('focus', function(e) {
        const input = e.target.closest('.ar-editable-input');
        if (!input) return;

        input.classList.add('is-editing');
    }, true);

    document.addEventListener('input', function(e) {
        const input = e.target.closest('.ar-editable-input');
        if (!input) return;

        const row = input.closest('.ar-row');
        if (row) row.classList.add('is-dirty');

        if (
            input.dataset.field === 'fob_peb_usd' ||
            input.dataset.field === 'kurs_kemenkeu'
        ) {
            calculateJumlahRp(row);
        }
    });

    document.addEventListener('change', function(e) {
        const input = e.target.closest('.ar-editable-input');
        if (!input) return;

        // Date/number select biasanya langsung tersimpan setelah change.
        if (input.type === 'date' || input.type === 'number') {
            saveSingleField(input);
        }
    });


    /* =========================================================
       FILTER
    ========================================================= */

    function filterAr() {

        const year =
            (
                document.getElementById(
                    'filterYear'
                )?.value ||
                ''
            ).toLowerCase();


        const status =
            (
                document.getElementById(
                    'filterStatus'
                )?.value ||
                ''
            ).toLowerCase();


        const customer =
            (
                document.getElementById(
                    'filterCustomer'
                )?.value ||
                ''
            ).toLowerCase()
            .trim();


        const search =
            (
                document.getElementById(
                    'filterSearch'
                )?.value ||
                ''
            ).toLowerCase()
            .trim();


        document
            .querySelectorAll(
                '.ar-row'
            )
            .forEach(function(row) {

                const rowYear =
                    (
                        row.dataset.year ||
                        ''
                    ).toLowerCase();


                const rowStatus =
                    (
                        row.dataset.status ||
                        ''
                    ).toLowerCase();


                const rowCustomer =
                    (
                        row.dataset.customer ||
                        ''
                    ).toLowerCase();


                const rowSearch =
                    (
                        row.dataset.search ||
                        ''
                    ).toLowerCase();


                let show =
                    true;


                if (
                    year &&
                    rowYear !== year
                ) {

                    show = false;

                }


                if (
                    status &&
                    rowStatus !== status
                ) {

                    show = false;

                }


                if (
                    customer &&
                    !rowCustomer.includes(
                        customer
                    )
                ) {

                    show = false;

                }


                if (
                    search &&
                    !rowSearch.includes(
                        search
                    )
                ) {

                    show = false;

                }


                row.style.display =
                    show ?
                    '' :
                    'none';

            });


        /*
        |--------------------------------------------------------------------------
        | GROUP HEADER & TOTAL
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '.month-group-row'
            )
            .forEach(function(header) {

                let next =
                    header.nextElementSibling;


                let visible =
                    false;


                let totalRow =
                    null;


                while (
                    next &&
                    !next.classList.contains(
                        'month-group-row'
                    )
                ) {

                    if (
                        next.classList.contains(
                            'month-total-row'
                        )
                    ) {

                        totalRow =
                            next;

                        break;

                    }


                    if (
                        next.classList.contains(
                            'ar-row'
                        ) &&
                        next.style.display !==
                        'none'
                    ) {

                        visible =
                            true;

                    }


                    next =
                        next.nextElementSibling;

                }


                header.style.display =
                    visible ?
                    '' :
                    'none';


                if (totalRow) {

                    totalRow.style.display =
                        visible ?
                        '' :
                        'none';

                }

            });

    }


    /* =========================================================
       FILTER ENTER
    ========================================================= */

    [
        'filterCustomer',
        'filterSearch'
    ].forEach(function(id) {

        const input =
            document.getElementById(
                id
            );


        if (input) {

            input.addEventListener(
                'keyup',
                function(e) {

                    if (
                        e.key === 'Enter'
                    ) {

                        filterAr();

                    }

                }
            );

        }

    });


    /* =========================================================
       PAYMENT
    ========================================================= */

    function addPayment(arId, type) {
        const title =
            type === 'deposit' ?
            'Tambah Deposit / Uang Muka' :
            'Tambah Pelunasan';


        Swal.fire({

            title: title,

            html: `

            <div style="text-align:left">

                <label style="
                    display:block;
                    font-size:12px;
                    margin-bottom:5px;
                    color:#6b7280;
                ">
                    Tanggal Pembayaran
                </label>

                <input
                    id="swal-payment-date"
                    type="date"
                    class="swal2-input"
                    style="margin:0 0 12px 0;width:100%;"
                >


                <label style="
                    display:block;
                    font-size:12px;
                    margin-bottom:5px;
                    color:#6b7280;
                ">
                    Jumlah Pembayaran
                </label>

                <input
                    id="swal-payment-amount"
                    type="text"
                    class="swal2-input"
                    style="margin:0 0 12px 0;width:100%;"
                    placeholder="Rp 0,00"
                >


                <label style="
                    display:block;
                    font-size:12px;
                    margin-bottom:5px;
                    color:#6b7280;
                ">
                    Reference
                </label>

                <input
                    id="swal-payment-reference"
                    type="text"
                    class="swal2-input"
                    style="margin:0;width:100%;"
                    placeholder="No. bukti / reference"
                >

            </div>

        `,

            showCancelButton: true,

            confirmButtonText: 'Simpan',

            cancelButtonText: 'Batal',

            reverseButtons: true,


            didOpen: function() {

                const amountInput =
                    document.getElementById(
                        'swal-payment-amount'
                    );


                amountInput.addEventListener(
                    'blur',
                    function() {

                        const value =
                            parseNumber(
                                this.value
                            );


                        this.value =
                            value.toLocaleString(
                                'id-ID', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }
                            );

                    }
                );

            },


            preConfirm: function() {

                const date =
                    document.getElementById(
                        'swal-payment-date'
                    ).value;


                const amount =
                    document.getElementById(
                        'swal-payment-amount'
                    ).value;


                const reference =
                    document.getElementById(
                        'swal-payment-reference'
                    ).value;


                if (!date) {

                    Swal.showValidationMessage(
                        'Tanggal pembayaran wajib diisi.'
                    );

                    return false;

                }


                const amountNumber =
                    parseNumber(
                        amount
                    );


                if (
                    !amountNumber ||
                    amountNumber <= 0
                ) {

                    Swal.showValidationMessage(
                        'Jumlah pembayaran harus lebih dari 0.'
                    );

                    return false;

                }


                return {

                    payment_date: date,

                    amount: amountNumber,

                    reference: reference

                };

            }

        }).then(
            async function(result) {

                if (!result.isConfirmed) {
                    return;
                }


                try {

                    /*
                    |--------------------------------------------------------------------------
                    | ROUTE ANDA MENERIMA PUT
                    |--------------------------------------------------------------------------
                    */

                    const response =
                        await fetch(
                            '/ar_buyer/' +
                            arId +
                            '/payment', {

                                method: 'PUT',

                                headers: {

                                    'Content-Type': 'application/json',

                                    'Accept': 'application/json',

                                    'X-CSRF-TOKEN': document
                                        .querySelector(
                                            'meta[name="csrf-token"]'
                                        )
                                        ?.getAttribute(
                                            'content'
                                        )

                                },

                                body: JSON.stringify({

                                    payment_type: type,

                                    payment_date: result.value
                                        .payment_date,

                                    amount: result.value
                                        .amount,

                                    reference: result.value
                                        .reference

                                })

                            }
                        );


                    const data =
                        await response
                        .json()
                        .catch(
                            () => ({})
                        );


                    if (!response.ok) {

                        throw new Error(
                            data.message ||
                            'Gagal menyimpan pembayaran.'
                        );

                    }


                    Swal.fire({

                        icon: 'success',

                        title: 'Berhasil',

                        text: type === 'deposit' ?
                            'Deposit berhasil ditambahkan.' :
                            'Pelunasan berhasil ditambahkan.',

                        timer: 1300,

                        showConfirmButton: false

                    }).then(function() {

                        location.reload();

                    });


                } catch (error) {

                    console.error(
                        'PAYMENT ERROR:',
                        error
                    );


                    Swal.fire({

                        icon: 'error',

                        title: 'Gagal',

                        text: error.message ||
                            'Terjadi kesalahan saat menyimpan pembayaran.'

                    });

                }

            }
        );
    }
    /* =========================================================
       INITIAL
    ========================================================= */

    document.addEventListener(
        'DOMContentLoaded',
        function() {

            /*
            |--------------------------------------------------------------------------
            | FORMAT MONEY
            |--------------------------------------------------------------------------
            */

            document
                .querySelectorAll(
                    '.money-input'
                )
                .forEach(function(input) {

                    formatMoneyInput(
                        input
                    );

                });


            /*
            |--------------------------------------------------------------------------
            | CALCULATE
            |--------------------------------------------------------------------------
            */

            document
                .querySelectorAll(
                    '.ar-row'
                )
                .forEach(function(row) {

                    calculateJumlahRp(
                        row
                    );

                });

        }
    );
</script>

@endsection
