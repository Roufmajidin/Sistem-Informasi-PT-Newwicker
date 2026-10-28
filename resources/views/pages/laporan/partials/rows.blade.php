    @forelse($stoks ?? [] as $key => $stok)

        <tr data-id="{{ $stok->id }}" data-is-opname="{{ $stok->is_opname ? 1 : 0 }}">



            {{-- NO --}}

            <td>

                {{ $stoks->firstItem() + $key }}

            </td>



            {{-- KODE BARANG --}}

            <td>

                <input type="hidden" class="id" value="{{ $stok->id }}">



                <input

                    type="text"

                    class="form-control kode_barang" readonly

                    value="{{ $stok->kode_barang }}"

                >
                @if($stok->is_opname)
                    <span class="opname-check" title="Sudah stock opname"><i class="fa fa-check-circle"></i> Opname</span>
                @endif

            </td>



            {{-- NAMA BARANG --}}

            <td>

                <input

                    type="text"

                    class="form-control nama_barang" readonly

                    value="{{ $stok->nama_barang }}"

                >

            </td>



            {{-- JENIS --}}

            <td>

                <select class="form-control jenis" disabled>



                    <option value="bahan baku"

                        {{ $stok->jenis == 'bahan baku' ? 'selected' : '' }}>

                        Bahan Baku

                    </option>



                    <option value="bahan penolong"

                        {{ $stok->jenis == 'bahan penolong' ? 'selected' : '' }}>

                        Bahan Penolong

                    </option>



                    <option value="bahan penolong alat"

                        {{ $stok->jenis == 'bahan penolong alat' ? 'selected' : '' }}>

                        Bahan Penolong Alat

                    </option>



                    <option value="bahan finishing"

                        {{ $stok->jenis == 'bahan finishing' ? 'selected' : '' }}>

                        Bahan Finishing

                    </option>



                </select>

            </td>



            {{-- SATUAN --}}

            <td>

                <input

                    type="text"

                    class="form-control satuan" readonly

                    value="{{ $stok->satuan }}"

                >

            </td>

 <td>

                <input

                    type="number"

                    step="0.001"

                    class="form-control stok_awal saldo-locked" readonly disabled title="Saldo dihitung otomatis dari transaksi dan hasil opname"

                    value="{{ rtrim(rtrim(number_format((float) ($stok->saldo ?? 0), 3, '.', ''), '0'), '.') }}"

                >

            </td>

            {{-- HARGA --}}

            <td>

                <input

                    type="text"

                    class="form-control harga" readonly

                    value="{{ !empty($stok->harga) ? number_format($stok->harga, 0, ',', '.') : '' }}"

                >

            </td>



            {{-- SALDO --}}

           



            {{-- STOK IN --}}

            {{-- <td>

                {{ rtrim(rtrim(number_format((float) ($stok->total_in ?? 0), 3, '.', ','), '0'), '.') }}

            </td> --}}



            {{-- STOK OUT --}}

            {{-- <td>

                {{ rtrim(rtrim(number_format((float) ($stok->total_out ?? 0), 3, '.', ','), '0'), '.') }}

            </td> --}}



            {{-- TANGGAL --}}

           



            {{-- AKSI --}}

            <td width="120">

                <button type="button" class="btn btn-warning btn-sm btn-edit-row" title="Edit baris" style="display:none"><i class="fa fa-pencil"></i> Edit</button>
                <button type="button" class="btn btn-primary btn-sm btn-opname" title="Stock Opname" style="display:none"><i class="fa fa-clipboard"></i> Opname</button>
                <button

                    type="button"

                    class="btn btn-success btn-sm btn-save"

                    title="Simpan"

                >

                    <i class="fa fa-save"></i>

                </button>



                <button

                    type="button"

                    class="btn btn-danger btn-sm remove-row"

                    title="Hapus"

                >

                    <i class="fa fa-trash"></i>

                </button>

            </td>



            {{-- DETAIL --}}

            <td>

                <a

                    href="{{ route('laporan.detail', $stok->id) }}"

                    class="btn btn-info btn-sm"

                >

                    Detail

                </a>

            </td>



        </tr>



    @empty

        <tr>

            <td colspan="10" class="text-center">

                Tidak ada data barang.

            </td>

        </tr>

    @endforelse
