<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
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

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            padding: 12px 20px;
            background: #fff;
            display: flex;
            gap: 12px;
            align-items: center;
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

        .sheet {
            width: 210mm;
            height: 330mm;
            margin: 8px auto;
            padding: 2mm 5mm;
            background: #fff;
            display: flex;
            flex-direction: column;
            gap: 1.5mm;
            box-shadow: 0 2px 10px #0002;
            overflow: hidden;
        }

        .sheet-header {
            flex: 0 0 10mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: .25mm solid #aaa;
            padding: 0 1mm 1mm;
            min-width: 0;
        }

        .sheet-header .heading {
            min-width: 0;
            max-width: 155mm;
        }

        .sheet-header strong {
            display: block;
            font-size: 10pt;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .sheet-header .page-no {
            font-weight: 600;
            white-space: nowrap;
            font-size: 9pt;
        }

        /* 5 kolom x 6 baris = 30 label */
        .labels-grid {
            flex: 0 0 auto;
            display: grid;
            width: 200mm;
            height: 288mm;
            grid-template-columns: repeat(5, 40mm);
            grid-template-rows: repeat(6, 48mm);
            gap: 0;
        }

        .label {
            position: relative;
            width: 40mm;
            height: 48mm;
            border: .16mm solid #888;
            padding: 1.8mm 1mm 3.5mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            gap: .35mm;
            overflow: hidden;
            break-inside: avoid;
            text-align: center;
            text-transform: uppercase;
        }

        /* Nomor SPK diperbesar */
        .label .spk {
            font-size: 8.5pt;
            font-weight: 700;
            line-height: 1.05;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* QR Code diperbesar */
        .label .qr {
            width: 19mm;
            height: 19mm;
            flex: 0 0 19mm;
            object-fit: contain;
            margin: .2mm 0 .4mm;
        }

        .label .item {
            font-size: 6.7pt;
            font-weight: 700;
            line-height: 1.05;
            overflow-wrap: anywhere;
            max-height: 6mm;
            overflow: hidden;
            width: 100%;
        }

        .label .code {
            font-size: 6.5pt;
            font-weight: 700;
            line-height: 1.05;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: .4mm;
        }

        .label .material {
            font-size: 5.4pt;
            font-weight: 400;
            line-height: 1.05;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .label .finishing {
            font-size: 5.5pt;
            font-weight: 400;
            line-height: 1.05;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .label .po {
            font-size: 5.6pt;
            font-weight: 400;
            line-height: 1.05;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .label .sub {
            font-size: 5.4pt;
            font-weight: 400;
            line-height: 1.05;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: .4mm;
        }

        /* Nomor label jauh lebih besar */
        .label .serial {
            position: absolute;
            right: 1mm;
            bottom: .7mm;
            font-size: 11pt;
            font-weight: 700;
            line-height: 1;
            background: #fff;
            padding-left: .7mm;
            font-variant-numeric: tabular-nums;
        }

        @page {
            size: 210mm 330mm;
            margin: 0;
        }

        @media print {

            html,
            body {
                background: #fff;
                margin: 0;
                padding: 0;
            }

            .toolbar {
                display: none !important;
            }

            .sheet {
                width: 210mm;
                height: 330mm;
                margin: 0;
                padding: 2mm 5mm;
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
            F4 210 × 330 mm ·
            30 label/halaman ·
            Potong 4 × 4,8 cm ·
            Skala cetak 100%
        </small>
    </div>

    @foreach ($pages as $pageIndex => $page)
        <section class="sheet">

            <header class="sheet-header">
                <div class="heading">
                    <strong>
                        SPK: {{ mb_strtoupper($page[0]['no_spk'] ?? '-', 'UTF-8') }}
                    </strong>

                    <strong>
                        SUB: {{ mb_strtoupper($page[0]['supplier'] ?? '-', 'UTF-8') }}
                    </strong>
                </div>

                <span class="page-no">
                    PAGE {{ $pageIndex + 1 }} OF {{ count($pages) }}
                </span>
            </header>

            <div class="labels-grid">
                @foreach ($page as $label)
                    <article class="label">

                        <div class="spk">
                            {{ mb_strtoupper($label['no_spk'] ?? '', 'UTF-8') }}
                        </div>

                        <img class="qr" src="{{ $label['qr'] }}" alt="QR {{ $label['number'] }}">

                        <div class="item">
                            {{ mb_strtoupper($label['name'] ?? '', 'UTF-8') }}
                        </div>

                        <div class="code">
                            {{ mb_strtoupper($label['kode'] ?? '', 'UTF-8') }}
                        </div>

                        <div class="material">
                            {{ mb_strtoupper($label['material'] ?? '', 'UTF-8') }}
                        </div>

                        <div class="finishing">
                            FINISHING:
                            {{ mb_strtoupper($label['finishing'] ?? '-', 'UTF-8') }}
                        </div>

                        <div class="po">
                            PO:
                            {{ mb_strtoupper($label['no_po'] ?? '', 'UTF-8') }}
                        </div>

                        <div class="sub">
                            SUB:
                            {{ mb_strtoupper($label['supplier'] ?? '', 'UTF-8') }}
                        </div>

                        <div class="serial">
                            {{ $label['number'] }}
                        </div>

                    </article>
                @endforeach
            </div>

        </section>
    @endforeach

</body>

</html>
