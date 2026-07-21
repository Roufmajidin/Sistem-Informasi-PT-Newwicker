
<div class="table-wrapper">

    <table class="table table-bordered">

        <thead>
            <tr style="">
                <th>#</th>
                <th>Description</th>
                <th>Kode Barang</th>
                <th>jenis</th>
                <th>Tanggal</th>
                <th>Qty</th>
                <th>In/Out</th>
                <th>Satuan</th>
                <th>SPK / INV</th>
                <th>Po. Numb</th>
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
                    <td>{{ $item->stok->jenis ?? '-' }}</td>

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
                   @php
                        $a = App\Models\Spk::find($item->spk_id);

                        $spk = '-';

                        if ($a) {
                            $spk = data_get($a->data, 'no_spk', '-');
                        }
                    @endphp

                    <td>{{ $spk }}</td>
                    <td class="js-inline-po"
    data-id="{{ $item->id }}"
    data-value="{{ $item->po }}">

                        @php
                            $po = $item->po ?? '-';

                            if (!empty($po) && substr_count($po, '/') >= 2) {
                                $parts = explode('/', $po);
                                if(count($parts)>=3){
                                    $po = trim($parts[1]);
                                }
                            }
                        @endphp

                        {{ $po }}

                    </td>

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
