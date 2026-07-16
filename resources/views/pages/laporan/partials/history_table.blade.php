
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
                    <td class="editable-po"
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).on('dblclick','.editable-po',function(){

    let td = $(this);

    if(td.find('input').length) return;

    let value = td.data('value') ?? '';

    td.html(
        '<input type="text" class="form-control form-control-sm po-input" value="'+value+'">'
    );

    td.find('input').focus().select();

});
    $(document).on('keypress','.po-input',function(e){

        if(e.which==13){

            $(this).blur();

        }

    });
    $(document).on('blur','.po-input',function(){

    let input = $(this);

    let td = input.closest('td');

    let id = td.data('id');

    let value = input.val();

    $.ajax({

        url:'/history/update-po/'+id,

        type:'POST',

        data:{
            _token:'{{ csrf_token() }}',
            po:value
        },

        success:function(){

            td.data('value',value);

            td.html(value);

        }

    });

});
</script>
