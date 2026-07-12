<tr id="dynamicHeaderRow">

    <th>Article Nr</th>

    <th>Gambar</th>

    <th id="namaHeader">

        Nama Barang

        <button type="button"
                id="btnAddHeader"
                style="
                    border:none;
                    background:none;
                    color:white;
                    margin-left:6px;
                    cursor:pointer;
                ">
            ➕
        </button>

    </th>

    <!-- dynamic header -->
@foreach($spk['custom_headers'] ?? [] as $header)

    <th class="spk-dynamic-header"
        data-custom="{{ $header['key'] }}">

        {{ $header['label'] }}

    </th>

@endforeach

    <th class="p-header">P</th>

    <th>L</th>

    <th>T</th>

    <th>Material</th>

    <th>PCS</th>

    <th>SET</th>

    <th>Harga</th>

    <th>Total</th>

    <th>Catatan</th>

    <th>#</th>

</tr>
