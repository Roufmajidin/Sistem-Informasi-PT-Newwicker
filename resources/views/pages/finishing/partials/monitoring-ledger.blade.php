@extends('master.master')

@section('title', 'Monitoring Finishing')

@section('content')

<div class="padding">

```
<div class="box">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div class="box-header d-flex justify-content-between align-items-center">

        <h2>
            Monitoring Finishing
        </h2>

        <a href="{{ url('/produksi') }}"
           class="btn btn-secondary">

            <i class="fa fa-arrow-left"></i>
            Kembali

        </a>

    </div>


    <div class="box-body">


        {{-- =========================================================
             FILTER
        ========================================================== --}}

        @php

            $filterBagian = strtolower(
                request('bagian', 'semua')
            );

            $allowedBagian = [
                'semua',
                'tomo',
                'darto',
                'produksi',
                'sampel',
            ];

            if (!in_array($filterBagian, $allowedBagian)) {
                $filterBagian = 'semua';
            }

        @endphp


        {{-- =========================================================
             TAB BAGIAN
        ========================================================== --}}

        <div class="card mb-3">

            <div class="card-header">

                <strong>
                    Filter Bagian
                </strong>

            </div>


            <div class="card-body">

                <ul class="nav nav-tabs monitoring-tabs">

                    {{-- SEMUA --}}
                    <li class="nav-item">

                        <a href="{{ request()->fullUrlWithQuery([
                            'bagian' => 'semua'
                        ]) }}"
                           class="nav-link
                           {{ $filterBagian == 'semua' ? 'active' : '' }}">

                            Semua

                        </a>

                    </li>


                    {{-- TOMO --}}
                    <li class="nav-item">

                        <a href="{{ request()->fullUrlWithQuery([
                            'bagian' => 'tomo'
                        ]) }}"
                           class="nav-link
                           {{ $filterBagian == 'tomo' ? 'active' : '' }}">

                            Tomo

                        </a>

                    </li>


                    {{-- DARTO --}}
                    <li class="nav-item">

                        <a href="{{ request()->fullUrlWithQuery([
                            'bagian' => 'darto'
                        ]) }}"
                           class="nav-link
                           {{ $filterBagian == 'darto' ? 'active' : '' }}">

                            Darto

                        </a>

                    </li>


                    {{-- PRODUKSI --}}
                    <li class="nav-item">

                        <a href="{{ request()->fullUrlWithQuery([
                            'bagian' => 'produksi'
                        ]) }}"
                           class="nav-link
                           {{ $filterBagian == 'produksi' ? 'active' : '' }}">

                            Produksi

                        </a>

                    </li>


                    {{-- SAMPEL --}}
                    <li class="nav-item">

                        <a href="{{ request()->fullUrlWithQuery([
                            'bagian' => 'sampel'
                        ]) }}"
                           class="nav-link
                           {{ $filterBagian == 'sampel' ? 'active' : '' }}">

                            Sampel

                        </a>

                    </li>

                </ul>

            </div>

        </div>


        {{-- =========================================================
             RINGKASAN
        ========================================================== --}}

        @php

            $totalDebet = 0;
            $totalKredit = 0;

            /*
             * Untuk ringkasan berdasarkan kategori.
             */
            $summary = [
                'tomo' => [
                    'debet' => 0,
                    'kredit' => 0,
                ],
                'darto' => [
                    'debet' => 0,
                    'kredit' => 0,
                ],
                'produksi' => [
                    'debet' => 0,
                    'kredit' => 0,
                ],
                'sampel' => [
                    'debet' => 0,
                    'kredit' => 0,
                ],
            ];


            /*
             * Loop seluruh ledger.
             */
            foreach ($ledger as $group) {

                /*
                 * Invoice.
                 *
                 * Jika invoice memiliki kategori,
                 * masukkan ke kategori tersebut.
                 */
                $invoiceKategori = strtolower(
                    trim($group['kategori'] ?? '')
                );


                if (
                    isset($summary[$invoiceKategori])
                ) {

                    foreach ($group['rows'] as $row) {

                        $kategoriRow = strtolower(
                            trim(
                                $row['kategori']
                                ?? $invoiceKategori
                            )
                        );

                        if (
                            isset(
                                $summary[$kategoriRow]
                            )
                        ) {

                            $summary[$kategoriRow]['debet']
                                += (float) (
                                    $row['debet'] ?? 0
                                );

                            $summary[$kategoriRow]['kredit']
                                += (float) (
                                    $row['kredit'] ?? 0
                                );

                        }

                    }

                }
                else {

                    /*
                     * Kalau invoice tidak punya kategori,
                     * tetap proses berdasarkan row.
                     */

                    foreach ($group['rows'] as $row) {

                        $kategoriRow = strtolower(
                            trim(
                                $row['kategori'] ?? ''
                            )
                        );

                        if (
                            isset(
                                $summary[$kategoriRow]
                            )
                        ) {

                            $summary[$kategoriRow]['debet']
                                += (float) (
                                    $row['debet'] ?? 0
                                );

                            $summary[$kategoriRow]['kredit']
                                += (float) (
                                    $row['kredit'] ?? 0
                                );

                        }

                    }

                }

            }


            /*
             * Ringkasan tab aktif.
             */
            if (
                $filterBagian !== 'semua'
                &&
                isset($summary[$filterBagian])
            ) {

                $totalDebet =
                    $summary[$filterBagian]['debet'];

                $totalKredit =
                    $summary[$filterBagian]['kredit'];

            }
            else {

                foreach ($summary as $item) {

                    $totalDebet +=
                        $item['debet'];

                    $totalKredit +=
                        $item['kredit'];

                }

            }


            $totalSaldo =
                $totalDebet - $totalKredit;

        @endphp


        <div class="row mb-3">

            {{-- TOTAL DEBET --}}
            <div class="col-md-4">

                <div class="card summary-card">

                    <div class="card-body">

                        <div class="summary-title">
                            Total Invoice
                        </div>

                        <div class="summary-value">

                            Rp
                            {{ number_format(
                                $totalDebet,
                                0,
                                ',',
                                '.'
                            ) }}

                        </div>

                    </div>

                </div>

            </div>


            {{-- TOTAL KREDIT --}}
            <div class="col-md-4">

                <div class="card summary-card">

                    <div class="card-body">

                        <div class="summary-title">
                            Total Pemotongan
                        </div>

                        <div class="summary-value">

                            Rp
                            {{ number_format(
                                $totalKredit,
                                0,
                                ',',
                                '.'
                            ) }}

                        </div>

                    </div>

                </div>

            </div>


            {{-- SALDO --}}
            <div class="col-md-4">

                <div class="card summary-card">

                    <div class="card-body">

                        <div class="summary-title">
                            Saldo
                        </div>

                        <div class="summary-value">

                            Rp
                            {{ number_format(
                                $totalSaldo,
                                0,
                                ',',
                                '.'
                            ) }}

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
             JUDUL BAGIAN AKTIF
        ========================================================== --}}

        <div class="alert alert-info">

            <strong>
                Bagian:
            </strong>

            @if($filterBagian == 'semua')

                Semua

            @elseif($filterBagian == 'tomo')

                Tomo

            @elseif($filterBagian == 'darto')

                Darto

            @elseif($filterBagian == 'produksi')

                Produksi

            @elseif($filterBagian == 'sampel')

                Sampel

            @endif

        </div>


        {{-- =========================================================
             LEDGER
        ========================================================== --}}

        @forelse($ledger as $group)

            @php

                /*
                 * Cek apakah group ini perlu ditampilkan.
                 */

                $groupKategori = strtolower(
                    trim(
                        $group['kategori'] ?? ''
                    )
                );


                /*
                 * Saldo sementara group.
                 */
                $groupSaldo = 0;


                /*
                 * Apakah group mempunyai row
                 * untuk kategori yang dipilih?
                 */
                $groupRows = [];


                foreach (
                    $group['rows']
                    as $row
                ) {

                    $rowKategori = strtolower(
                        trim(
                            $row['kategori']
                            ?? $groupKategori
                        )
                    );


                    /*
                     * SEMUA
                     */
                    if (
                        $filterBagian === 'semua'
                    ) {

                        $groupRows[] = $row;

                    }


                    /*
                     * FILTER BAGIAN
                     */
                    elseif (
                        $rowKategori ===
                        $filterBagian
                    ) {

                        $groupRows[] = $row;

                    }

                }


                /*
                 * Kalau filter bukan SEMUA dan
                 * tidak ada row yang sesuai,
                 * group tidak ditampilkan.
                 */

            @endphp


            @if(
                $filterBagian === 'semua'
                ||
                count($groupRows) > 0
                ||
                $groupKategori === $filterBagian
            )

                <div class="card mb-4 ledger-card">


                    {{-- =================================================
                         HEADER GROUP
                    ================================================== --}}

                    <div class="card-header ledger-header">

                        <div>

                            <strong>

                                Invoice:
                                {{ $group['invoice'] }}

                            </strong>

                            <br>

                            <small>

                                Tanggal:
                                @if(!empty($group['tanggal']))

                                    {{ \Carbon\Carbon::parse(
                                        $group['tanggal']
                                    )->format('d-m-Y') }}

                                @else

                                    -

                                @endif

                            </small>

                        </div>


                        <div>

                            @if(
                                !empty($group['kategori'])
                            )

                                <span class="badge badge-primary">

                                    {{ ucfirst(
                                        $group['kategori']
                                    ) }}

                                </span>

                            @endif

                        </div>

                    </div>


                    {{-- =================================================
                         TABLE
                    ================================================== --}}

                    <div class="card-body">

                        <div class="table-responsive">

                            <table class="table table-bordered table-striped ledger-table">

                                <thead>

                                    <tr>

                                        <th width="50">
                                            #
                                        </th>

                                        <th width="120">
                                            Tanggal
                                        </th>

                                        <th>
                                            Keterangan
                                        </th>

                                        <th>
                                            Bagian
                                        </th>

                                        <th>
                                            Supplier
                                        </th>

                                        <th>
                                            No SPK
                                        </th>

                                        <th>
                                            PO
                                        </th>

                                        <th class="text-right">
                                            Debet
                                        </th>

                                        <th class="text-right">
                                            Kredit
                                        </th>

                                        <th class="text-right">
                                            Saldo
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    @php

                                        $nomor = 1;

                                        /*
                                         * Untuk filter kategori,
                                         * saldo ditampilkan berdasarkan
                                         * row yang sedang tampil.
                                         */
                                        $saldoTampil = 0;

                                    @endphp


                                    @foreach(
                                        $groupRows
                                        as $row
                                    )

                                        @php

                                            $debet =
                                                (float) (
                                                    $row['debet']
                                                    ?? 0
                                                );


                                            $kredit =
                                                (float) (
                                                    $row['kredit']
                                                    ?? 0
                                                );


                                            $saldoTampil
                                                += $debet;

                                            $saldoTampil
                                                -= $kredit;


                                            $rowKategori =
                                                strtolower(
                                                    trim(
                                                        $row['kategori']
                                                        ?? ''
                                                    )
                                                );

                                        @endphp


                                        <tr>

                                            {{-- NO --}}
                                            <td>
                                                {{ $nomor++ }}
                                            </td>


                                            {{-- TANGGAL --}}
                                            <td>

                                                @if(
                                                    !empty(
                                                        $row['tanggal']
                                                    )
                                                )

                                                    {{ \Carbon\Carbon::parse(
                                                        $row['tanggal']
                                                    )->format('d-m-Y') }}

                                                @else

                                                    -

                                                @endif

                                            </td>


                                            {{-- DESCRIPTION --}}
                                            <td>

                                                <strong>

                                                    {{
                                                        $row['description']
                                                        ?? '-'
                                                    }}

                                                </strong>


                                                @if(
                                                    !empty(
                                                        $row['sub']
                                                    )
                                                )

                                                    <br>

                                                    <small
                                                        class="text-muted">

                                                        {{
                                                            $row['sub']
                                                        }}

                                                    </small>

                                                @endif


                                                @if(
                                                    !empty(
                                                        $row['note_tambahan']
                                                    )
                                                )

                                                    <br>

                                                    <small>

                                                        {{
                                                            $row[
                                                                'note_tambahan'
                                                            ]
                                                        }}

                                                    </small>

                                                @endif

                                            </td>


                                            {{-- BAGIAN --}}
                                            <td>

                                                @if(
                                                    $rowKategori !== ''
                                                )

                                                    <span
                                                        class="badge badge-secondary">

                                                        {{
                                                            ucfirst(
                                                                $rowKategori
                                                            )
                                                        }}

                                                    </span>

                                                @else

                                                    -

                                                @endif

                                            </td>


                                            {{-- SUPPLIER --}}
                                            <td>

                                                {{
                                                    $row['supplier']
                                                    ?? '-'
                                                }}

                                            </td>


                                            {{-- NO SPK --}}
                                            <td>

                                                {{
                                                    $row['no_spk']
                                                    ?? '-'
                                                }}

                                            </td>


                                            {{-- PO --}}
                                            <td>

                                                {{
                                                    $row['po']
                                                    ?? '-'
                                                }}

                                            </td>


                                            {{-- DEBET --}}
                                            <td class="text-right">

                                                @if(
                                                    $debet > 0
                                                )

                                                    Rp
                                                    {{
                                                        number_format(
                                                            $debet,
                                                            0,
                                                            ',',
                                                            '.'
                                                        )
                                                    }}

                                                @else

                                                    -

                                                @endif

                                            </td>


                                            {{-- KREDIT --}}
                                            <td class="text-right">

                                                @if(
                                                    $kredit > 0
                                                )

                                                    Rp
                                                    {{
                                                        number_format(
                                                            $kredit,
                                                            0,
                                                            ',',
                                                            '.'
                                                        )
                                                    }}

                                                @else

                                                    -

                                                @endif

                                            </td>


                                            {{-- SALDO --}}
                                            <td class="text-right">

                                                Rp
                                                {{
                                                    number_format(
                                                        $saldoTampil,
                                                        0,
                                                        ',',
                                                        '.'
                                                    )
                                                }}

                                            </td>

                                        </tr>


                                    @endforeach


                                    {{-- ===================================
                                         TIDAK ADA DATA
                                    ==================================== --}}

                                    @if(
                                        count($groupRows) === 0
                                    )

                                        <tr>

                                            <td
                                                colspan="10"
                                                class="text-center">

                                                Tidak ada transaksi
                                                untuk bagian
                                                <strong>

                                                    {{
                                                        ucfirst(
                                                            $filterBagian
                                                        )
                                                    }}

                                                </strong>

                                                pada invoice ini.

                                            </td>

                                        </tr>

                                    @endif

                                </tbody>


                                {{-- =================================================
                                     FOOTER TOTAL
                                ================================================== --}}

                                @if(
                                    count($groupRows) > 0
                                )

                                    @php

                                        $footerDebet = 0;
                                        $footerKredit = 0;

                                        foreach (
                                            $groupRows
                                            as $footerRow
                                        ) {

                                            $footerDebet
                                                += (float) (
                                                    $footerRow[
                                                        'debet'
                                                    ] ?? 0
                                                );

                                            $footerKredit
                                                += (float) (
                                                    $footerRow[
                                                        'kredit'
                                                    ] ?? 0
                                                );

                                        }


                                        $footerSaldo =
                                            $footerDebet
                                            -
                                            $footerKredit;

                                    @endphp


                                    <tfoot>

                                        <tr>

                                            <th
                                                colspan="7"
                                                class="text-right">

                                                TOTAL

                                            </th>


                                            <th
                                                class="text-right">

                                                Rp
                                                {{
                                                    number_format(
                                                        $footerDebet,
                                                        0,
                                                        ',',
                                                        '.'
                                                    )
                                                }}

                                            </th>


                                            <th
                                                class="text-right">

                                                Rp
                                                {{
                                                    number_format(
                                                        $footerKredit,
                                                        0,
                                                        ',',
                                                        '.'
                                                    )
                                                }}

                                            </th>


                                            <th
                                                class="text-right">

                                                Rp
                                                {{
                                                    number_format(
                                                        $footerSaldo,
                                                        0,
                                                        ',',
                                                        '.'
                                                    )
                                                }}

                                            </th>

                                        </tr>

                                    </tfoot>

                                @endif

                            </table>

                        </div>

                    </div>

                </div>

            @endif


        @empty

            <div class="card">

                <div class="card-body text-center">

                    <h4>
                        Tidak ada data monitoring.
                    </h4>

                </div>

            </div>

        @endforelse


    </div>

</div>
```

</div>

{{-- =============================================================
STYLE
============================================================= --}}

<style>

    /*
    |--------------------------------------------------------------------------
    | TAB
    |--------------------------------------------------------------------------
    */

    .monitoring-tabs {

        margin-bottom: 0;

    }


    .monitoring-tabs .nav-link {

        cursor: pointer;

        font-weight: 500;

        padding: 10px 20px;

    }


    .monitoring-tabs .nav-link.active {

        font-weight: bold;

    }


    /*
    |--------------------------------------------------------------------------
    | SUMMARY CARD
    |--------------------------------------------------------------------------
    */

    .summary-card {

        border: 1px solid #ddd;

        box-shadow:
            0 2px 5px
            rgba(0,0,0,.05);

    }


    .summary-title {

        font-size: 14px;

        color: #777;

        margin-bottom: 5px;

    }


    .summary-value {

        font-size: 20px;

        font-weight: bold;

    }


    /*
    |--------------------------------------------------------------------------
    | LEDGER
    |--------------------------------------------------------------------------
    */

    .ledger-card {

        box-shadow:
            0 2px 8px
            rgba(0,0,0,.06);

    }


    .ledger-header {

        display: flex;

        justify-content: space-between;

        align-items: center;

    }


    .ledger-table {

        font-size: 13px;

        margin-bottom: 0;

    }


    .ledger-table th {

        white-space: nowrap;

        vertical-align: middle;

    }


    .ledger-table td {

        vertical-align: middle;

    }


    .ledger-table .text-right {

        white-space: nowrap;

    }


    /*
    |--------------------------------------------------------------------------
    | BADGE
    |--------------------------------------------------------------------------
    */

    .badge {

        padding: 5px 8px;

    }


    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 768px) {

        .monitoring-tabs {

            display: flex;

            flex-wrap: wrap;

        }


        .monitoring-tabs .nav-item {

            margin-bottom: 5px;

        }


        .ledger-header {

            display: block;

        }


        .ledger-header .badge {

            display: inline-block;

            margin-top: 8px;

        }

    }

</style>

@endsection
