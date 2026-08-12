<div id="tableContainer" style="position:relative;">

    <div id="tableLoading" class="table-loading d-none">
        <div class="spinner-border text-primary" role="status"></div>
        <div class="mt-2">Loading data...</div>
    </div>

   <div class="table-wrapper">

    <style>
        .ellipsis-150 {
            width: 150px;
            max-width: 150px;
        }

        .ellipsis-100 {
            width: 100px;
            max-width: 100px;
        }

        .ellipsis-150 span,
        .ellipsis-100 span {
            display: block;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
    </style>

    <table class="table table-bordered">

        <thead>
            <tr>
                <th width="40">#</th>
                <th class="ellipsis-150">Description</th>
                <th width="120">Kode Barang</th>
                <th class="ellipsis-100">Jenis</th>
                <th width="100">Tanggal</th>
                <th width="80">Qty</th>
                <th width="70">In/Out</th>
                <th width="70">Satuan</th>
                <th width="130">SPK / INV</th>
                <th width="120">Po. Numb</th>
                <th>Remark</th>
                <th width="120">Created at</th>
            </tr>
        </thead>

        <tbody>
            @forelse($histories as $item)
                <tr>

                    <td>{{ $histories->firstItem() + $loop->index }}</td>

                    {{-- Description --}}
                    <td class="ellipsis-150">
                        <span
                            title="{{ $item->stok->nama_barang ?? '-' }}">
                            {{ $item->stok->nama_barang ?? '-' }}
                        </span>
                    </td>

                    {{-- Kode Barang --}}
                    <td>{{ $item->stok->kode_barang ?? '-' }}</td>

                    {{-- Jenis --}}
                    <td class="ellipsis-100">
                        <span
                            title="{{ $item->stok->jenis ?? '-' }}">
                            {{ $item->stok->jenis ?? '-' }}
                        </span>
                    </td>

                    {{-- Tanggal --}}
                    <td>
                        {{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}
                    </td>

                    {{-- Qty --}}
                    <td>
                        {{ rtrim(rtrim(number_format($item->qty, 2, '.', ''), '0'), '.') }}
                    </td>

                    {{-- IN / OUT --}}
                    <td>
                        @if ($item->tipe == 'in')
                            <span class="badge bg-success">IN</span>
                        @else
                            <span class="badge bg-danger">OUT</span>
                        @endif
                    </td>

                    {{-- Satuan --}}
                    <td>{{ $item->stok->satuan ?? '-' }}</td>

                    {{-- SPK --}}
                    @php
                        $a = App\Models\Spk::find($item->spk_id);

                        $spk = '-';

                        if ($a) {
                            $spk = data_get($a->data, 'no_spk', '-');
                        }
                    @endphp

                    <td>{{ $spk }}</td>

                    {{-- PO --}}
                    <td class="js-inline-po"
                        data-id="{{ $item->id }}"
                        data-value="{{ $item->po }}">

                        @php
                            $po = $item->po ?? '-';

                            if (!empty($po) && substr_count($po, '/') >= 2) {
                                $parts = explode('/', $po);

                                if (count($parts) >= 3) {
                                    $po = trim($parts[1]);
                                }
                            }
                        @endphp

                        <span title="{{ $po }}">
                            {{ $po }}
                        </span>

                    </td>

                    {{-- Remark --}}
                    <td>
                        <span title="{{ $item->keterangan ?? '-' }}">
                            {{ $item->keterangan ?? '-' }}
                        </span>
                    </td>

                    {{-- Created At --}}
                    <td>
                        {{ optional($item->created_at)->format('d/m/Y H:i') }}
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center">
                        Tidak ada data.
                    </td>
                </tr>
            @endforelse
        </tbody>

    </table>

</div>

</div>
@php
    $showSummary =
        request()->filled('search') ||
        request()->filled('date_from') ||
        request()->filled('date_to') ||
        request()->filled('type');
@endphp

@if($showSummary)

<div class="d-flex justify-content-between align-items-center mt-3 mb-2">

    <div>

        <strong>Record Data :</strong>
        {{ number_format($summary->total_transaksi) }}

        &nbsp; | &nbsp;

        <strong>Total Qty :</strong>
        {{ number_format($summary->total_qty,2) }}

        &nbsp; | &nbsp;

        <strong>Total Nilai :</strong>

        <span class="text-success font-weight-bold">

            Rp {{ number_format($summary->total_value,0,',','.') }}

        </span>

    </div>

</div>

@endif
<div class="d-flex justify-content-start mt-3">
    {{ $histories->links() }}
</div>



<style>

.table-loading{
    position:absolute;
    top:0;
    left:0;
    right:0;
    bottom:0;
    background:rgba(255,255,255,.85);
    display:flex;
    align-items:center;
    justify-content:center;
    flex-direction:column;
    backdrop-filter: blur(1px);
    z-index:9999;
}

.spinner-border{
    width:3rem;
    height:3rem;
}
</style>
<script>
$(document).on('click','.pagination a',function(e){

    $('#tableLoading').removeClass('d-none');

    // nonaktifkan klik ganda
    $('.pagination a').css({
        'pointer-events':'none',
        'opacity':0.6
    });

});
</script>
