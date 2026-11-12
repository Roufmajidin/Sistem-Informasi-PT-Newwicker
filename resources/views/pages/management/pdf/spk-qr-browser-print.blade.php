<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Print QR SPK {{ $pages[0][0]['no_spk'] ?? '' }}</title>
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; font-family: Arial, sans-serif; color: #111; }
        body { background: #e5e7eb; }
        .toolbar { position: sticky; top: 0; z-index: 10; padding: 12px 20px; background: #fff;
            display: flex; gap: 12px; align-items: center; box-shadow: 0 2px 10px #0002; }
        .toolbar button { border: 0; border-radius: 6px; background: #1d4ed8; color: white;
            font-size: 14px; font-weight: bold; padding: 10px 18px; cursor: pointer; }
        .toolbar small { color: #475569; }
        .sheet { width: 210mm; height: 330mm; margin: 14px auto; padding: 6mm;
            background: white; display: flex; flex-direction: column; gap: 2mm;
            box-shadow: 0 2px 10px #0002; overflow: hidden; }
        .sheet-header { flex: 0 0 13mm; display: flex; justify-content: space-between; align-items: center;
            border-bottom: .25mm solid #aaa; padding: 0 1mm 1mm; font-size: 10pt; }
        .sheet-header strong { display: block; font-size: 11pt; line-height: 1.3; }
        .sheet-header .page-no { font-weight: 700; white-space: nowrap; font-size: 9pt; }
        .labels-grid { flex: 1 1 auto; min-height: 0; display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            grid-template-rows: repeat(5, minmax(0, 1fr)); gap: 1.5mm; }
        .label { border: .25mm solid #777; padding: 2mm; min-width: 0; min-height: 0;
            display: flex; flex-direction: column; align-items: center; justify-content: flex-start;
            overflow: hidden; break-inside: avoid; }
        .label .spk { font-size: 9pt; font-weight: 700; text-align: center; line-height: 1.1;
            overflow-wrap: anywhere; }
        .label .qr { width: 29mm; height: 29mm; object-fit: contain; margin: 1mm 0; }
        .label .item { font-size: 8pt; font-weight: bold; text-align: center; line-height: 1.1;
            overflow-wrap: anywhere; max-height: 10mm; overflow: hidden; }
        .label .meta { font-size: 7pt; text-align: center; line-height: 1.15;
            overflow-wrap: anywhere; max-width: 100%; }
        .label .serial { font-size: 9pt; font-weight: 700; margin-top: auto; }
        @page { size: 210mm 330mm; margin: 0; }
        @media print {
            html, body { background: white; margin: 0; padding: 0; }
            .toolbar { display: none !important; }
            .sheet { width: 210mm; height: 330mm; margin: 0; padding: 6mm;
                box-shadow: none; break-after: page; page-break-after: always; }
            .sheet:last-child { break-after: auto; page-break-after: auto; }
            .label { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print / Save as PDF</button>
        <small>{{ count($pages) }} halaman · {{ array_sum(array_map('count', $pages)) }} label · F4 210 × 330 mm · 15 label/halaman</small>
    </div>
    @foreach($pages as $pageIndex => $page)
        <section class="sheet">
            <header class="sheet-header">
                <div>
                    <h5>SPK: {{ $page[0]['no_spk'] ?? '-' }}</h5>
                    <h5>SUB: {{ $page[0]['supplier'] ?? '-' }}</h5>
                </div>
                <span class="page-no">Page {{ $pageIndex + 1 }} of {{ count($pages) }}</span>
            </header>
            <div class="labels-grid">
            @foreach($page as $label)
                <article class="label">
                    <div class="spk">{{ $label['no_spk'] }}</div>
                    <img class="qr" src="{{ $label['qr'] }}" alt="QR {{ $label['number'] }}">
                    <div class="item">{{ $label['name'] }}</div>
                    <div class="meta">{{ $label['kode'] }}</div>
                    <div class="meta">{{ $label['material'] }}</div>
                    <div class="meta">PO: {{ $label['no_po'] }}</div>
                    <div class="meta">{{ $label['supplier'] }}</div>
                    <div class="serial">{{ $label['number'] }}</div>
                </article>
            @endforeach
            </div>
        </section>
    @endforeach
</body>
</html>
