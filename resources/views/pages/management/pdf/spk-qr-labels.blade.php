<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
@page { margin: 8mm 6mm; }
* { box-sizing: border-box; }
body { margin: 0; font-family: DejaVu Sans, sans-serif; color: #000; }
.page { page-break-after: always; }
.page:last-child { page-break-after: auto; }
table.sheet { border-collapse: separate; border-spacing: 1.5mm 1.5mm; table-layout: fixed; width: 100%; }
td.label { width: 33.333%; height: 59mm; border: 0.65pt solid #111; padding: 1.5mm; vertical-align: top; overflow: hidden; }
.spk { font-size: 6.6pt; line-height: 1.25; height: 8mm; overflow: hidden; overflow-wrap: break-word; }
.supplier { font-size: 8pt; font-weight: bold; height: 5.5mm; line-height: 1.2; overflow: hidden; overflow-wrap: break-word; }
/* Dompdf supports table-based left/right layout more reliably than flex. */
table.inner { width: 100%; border-collapse: collapse; table-layout: fixed; }
td.info { width: 48%; vertical-align: top; padding: 0 1mm 0 0; }
td.code { width: 52%; vertical-align: top; text-align: right; padding: 0; }
.name { font-size: 8pt; font-weight: bold; line-height: 1.25; height: 13mm; overflow: hidden; overflow-wrap: break-word; }
.po { font-size: 6.7pt; line-height: 1.3; height: 10mm; overflow: hidden; overflow-wrap: break-word; }
.unit { font-size: 11pt; font-weight: bold; margin-top: 2mm; }
.code img { display: block; width: 31mm; height: 31mm; margin-left: auto; }
</style>
</head>
<body>
@foreach($pages as $page)
<div class="page">
<table class="sheet">
@foreach(array_chunk($page, 3) as $row)
<tr>
@foreach($row as $label)
<td class="label">
  <div class="spk">{{ $label['no_spk'] }}</div>
  <div class="supplier">{{ $label['supplier'] ?: '-' }}</div>
  <table class="inner"><tr>
    <td class="info">
      <div class="name">{{ $label['name'] }}</div>
      <div class="po">PO: {{ $label['no_po'] }}</div>
      <div class="unit">{{ $label['number'] }}</div>
    </td>
    <td class="code"><img src="{{ $label['qr'] }}" alt="QR"></td>
  </tr></table>
</td>
@endforeach
@for($i=count($row); $i<3; $i++)
<td class="label"></td>
@endfor
</tr>
@endforeach
</table>
</div>
@endforeach
</body>
</html>
