
<div class="table-wrapper">

    <table class="table table-bordered">

        <thead>
            <tr style="">
                <th>#</th>
                <th>Description</th>
                <th>Kode Barang</th>
                <th>Tanggal</th>
                <th>Qty</th>
                <th>In/Out</th>
                <th>Satuan</th>
                <th>SPK / PO / INV</th>
                <th>Remark</th>
                <th width="80">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($histories as $item)
                <tr>
                    <td>{{ $histories->firstItem() + $loop->index }}</td>

                    <td>{{ $item->stok->nama_barang ?? '-' }}</td>

                    <td>{{ $item->stok->kode_barang ?? '-' }}</td>

                    <td>
                        {{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}
                    </td>

                    <td>
                        {{ rtrim(rtrim(number_format($item->qty, 2, '.', ''), '0'), '.') }}
                    </td>

                    <td>
                        @if($item->tipe == 'in')
                            <span class="badge bg-success">IN</span>
                        @else
                            <span class="badge bg-danger">OUT</span>
                        @endif
                    </td>

                    <td>{{ $item->stok->satuan ?? '-' }}</td>

                    <td>{{ $item->spk->no_spk ?? ($item->po ?? '-') }}</td>

                    <td>{{ $item->keterangan ?? '-' }}</td>

                    <td>
                        {{-- Tombol aksi --}}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">
                        Tidak ada data.
                    </td>
                </tr>
            @endforelse
        </tbody>

    </table>

</div>

<div class="d-flex justify-content-start mt-3">
    {{ $histories->links() }}
</div>
