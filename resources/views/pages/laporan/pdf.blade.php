<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>

body{
    font-family: DejaVu Sans;
    font-size:11px;
}

table{
    width:100%;
    border-collapse:collapse;
}

table th,
table td{
    border:1px solid #000;
    padding:5px;
}

</style>

</head>
<body>

<h2 align="center">
    LAPORAN STOK BARANG
</h2>

<table style="border:none">

<tr>
    <td style="border:none">
        Kode Barang
    </td>

    <td style="border:none">
        :
        {{ $stok->kode_barang }}
    </td>
</tr>

<tr>
    <td style="border:none">
        Nama Barang
    </td>

    <td style="border:none">
        :
        {{ $stok->nama_barang }}
    </td>
</tr>

<tr>
    <td style="border:none">
        Jenis
    </td>

    <td style="border:none">
        :
        {{ $stok->jenis }}
    </td>
</tr>

<tr>
    <td style="border:none">
        Satuan
    </td>

    <td style="border:none">
        :
        {{ $stok->satuan }}
    </td>
</tr>

</table>

<br>

<table>

<thead>

<tr>

    <th>Tanggal</th>
    <th>IN</th>
    <th>OUT</th>
    <th>PO/SPK</th>
    <th>Keterangan</th>
    <th>Satuan</th>

</tr>

</thead>

<tbody>

@foreach($transaksi as $item)

<tr>

    <td>{{ $item->tanggal }}</td>

    <td>
        {{ $item->tipe == 'in'
            ? $item->qty
            : '' }}
    </td>

    <td>
        {{ $item->tipe == 'out'
            ? $item->qty
            : '' }}
    </td>

    <td>
        {{ $item->po }}
    </td>

    <td>
        {{ $item->keterangan }}
    </td>

    <td>
        {{ $stok->satuan }}
    </td>

</tr>

@endforeach

</tbody>

</table>

<br>

<b>Total IN :</b>
{{ $totalIn }}
{{ $stok->satuan }}

<br>

<b>Total OUT :</b>
{{ $totalOut }}
{{ $stok->satuan }}

</body>
</html>
