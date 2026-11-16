<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>PRINT QR SPK {{ $pages[0][0]['no_spk'] ?? '' }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            color: #111;
        }

        body {
            background: #e5e7eb;
        }

        /* TOOLBAR */

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            background: #fff;
            box-shadow: 0 2px 10px #0002;
        }

        .toolbar button {
            border: 0;
            border-radius: 6px;
            background: #1d4ed8;
            color: #fff;
            font-size: 14px;
            font-weight: bold;
            padding: 10px 18px;
            cursor: pointer;
        }

        .toolbar small {
            color: #475569;
        }

        /* KERTAS 165 x 200 MM */

        .sheet {
            width: 165mm;
            height: 200mm;
            margin: 8px auto;
            padding: 5.5mm;
            background: #fff;
            box-shadow: 0 2px 10px #0002;
            overflow: hidden;
            break-inside: avoid;
        }

        /* 6 KOLOM x 5 BARIS */

        .labels-grid {
            display: grid;
            width: 154mm;
            height: 189mm;

            grid-template-columns: repeat(6, 24mm);
            grid-template-rows: repeat(5, 37mm);

            column-gap: 2mm;
            row-gap: 1mm;
        }

        /* STIKER 24 x 37 MM */


        /* STIKER 24 x 37 MM - TANPA BORDER */

        .label {
            width: 24mm;
            height: 37mm;
            padding: 1mm .65mm .65mm;

            border: none;
            border-radius: 0;
            outline: none;
            box-shadow: none;
            background: #fff;

            display: flex;
            flex-direction: column;
            align-items: center;

            text-align: center;
            text-transform: uppercase;

            overflow: hidden;
            break-inside: avoid;
        }

        /* NOMOR SPK */

        .label .spk {
            width: 100%;
            font-size: 5.5pt;
            line-height: 1.1;
            font-weight: 700;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* QR CODE */

        .label .qr {
            width: 17mm;
            height: 17mm;
            flex: 0 0 17mm;
            object-fit: contain;
            margin: .25mm 0 .35mm;
        }

        /* NAMA ITEM */

        .label .item {
            width: 100%;
            font-size: 5.3pt;
            line-height: 1.1;
            font-weight: 700;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* FINISH COLOR */

        .label .finishing {
            width: 100%;
            margin-top: .4mm;
            font-size: 5pt;
            line-height: 1.1;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* NAMA SUB */

        .label .sub {
            width: 100%;
            margin-top: .6mm;
            font-size: 5pt;
            line-height: 1.1;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* NO PO DI BAWAH SUB */

        .label .po {
            width: 100%;
            margin-top: .3mm;
            font-size: 4.8pt;
            line-height: 1.1;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* NOMOR URUT */

        .label .serial {
            margin-top: auto;
            width: 100%;
            font-size: 9pt;
            line-height: 1;
            font-weight: 700;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* PRINT SETTING */

        @page {
            size: 165mm 200mm;
            margin: 0;
        }

        @media print {

            html,
            body {
                margin: 0;
                padding: 0;
                background: #fff;
            }

            .toolbar {
                display: none !important;
            }

            .sheet {
                width: 165mm;
                height: 200mm;
                margin: 0;
                padding: 5.5mm;
                box-shadow: none;

                break-after: page;
                page-break-after: always;
            }

            .sheet:last-child {
                break-after: auto;
                page-break-after: auto;
            }

            .label {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <div class="toolbar">

        <button type="button" onclick="window.print()">
            PRINT / SAVE AS PDF
        </button>

        <small>
            {{ count($pages) }} halaman ·
            {{ array_sum(array_map('count', $pages)) }} label ·
            Kertas 165 × 200 mm ·
            30 label/halaman ·
            Stiker 24 × 37 mm ·
            Skala 100%
        </small>

    </div>

    @foreach ($pages as $pageIndex => $page)
        <section class="sheet">

            <div class="labels-grid">

                @foreach ($page as $label)
                    <article class="label">

                        {{-- NOMOR SPK --}}
                        <div class="spk">
                            {{ mb_strtoupper($label['no_spk'] ?? '-', 'UTF-8') }}
                        </div>

                        {{-- QR CODE --}}
                        <img class="qr" src="{{ $label['qr'] }}" alt="QR {{ $label['number'] ?? '' }}">

                        {{-- NAMA ITEM --}}
                        <div class="item" title="{{ $label['name'] ?? '' }}">
                            {{ mb_strtoupper($label['name'] ?? '-', 'UTF-8') }}
                        </div>

                        {{-- FINISH COLOR --}}
                        <div class="finishing" title="{{ $label['finishing'] ?? '' }}">
                            {{ mb_strtoupper($label['finishing'] ?? '-', 'UTF-8') }}
                        </div>

                        {{-- NAMA SUB --}}
                        <div class="sub" title="{{ $label['supplier'] ?? '' }}">
                            {{ mb_strtoupper($label['supplier'] ?? '-', 'UTF-8') }}
                        </div>

                        {{-- NO PO DALAM KURUNG --}}
                        <div class="po" title="{{ $label['no_po'] ?? '' }}">
                            ({{ mb_strtoupper($label['no_po'] ?? '-', 'UTF-8') }})
                        </div>

                        {{-- NOMOR URUT --}}
                        <div class="serial">
                            {{ $label['number'] ?? '' }}
                        </div>

                    </article>
                @endforeach

            </div>

        </section>
    @endforeach

</body>

</html>
