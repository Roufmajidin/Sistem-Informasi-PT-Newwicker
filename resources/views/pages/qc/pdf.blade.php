<style>
body { font-family: sans-serif; font-size: 12px; }

.box { border:1px solid #ddd; padding:10px; margin-bottom:15px; }

.table { width:100%; border-collapse: collapse; margin-top:10px; }
.table th, .table td { border:1px solid #ccc; padding:5px; }

.page-break { page-break-after: always; }
.table th {
    background-color: #2e3e4e; /* 🔥 warna header */
    color: #ffffff;            /* 🔥 text putih */
    padding:6px;
    text-align:center;
}
.title { font-weight:bold; margin-bottom:5px; }

img {
    display:block;
    margin:auto;
}
</style>

<h2>QC - {{ strtoupper($kategori) }}</h2>

@foreach($items as $itemId => $item)

<div class="box">

    {{-- HEADER --}}
    <div class="title">
        ITEM: {{ $itemId }}
    </div>

    {{-- TABLE ITEM --}}
    <table class="table">
        <tr>
            <th>Article</th>
            <th>Item Name</th>
            <th>Qty</th>
        </tr>
        <tr>
            <td>{{ $item['article'] ?? '-' }}</td>
            <td>{{ $item['name'] ?? '-' }}</td>
            <td>{{ $item['qty'] ?? '-' }}</td>
        </tr>
    </table>

    {{-- LOOP BATCH --}}
    @foreach($item['batches'] as $batchNo => $b)

        <h4 style="margin-top:15px;">
            Batch {{ $batchNo }} - {{ $b['tanggal'] }}
        </h4>

        {{-- SIZE CONTROL --}}
        <table class="table">
            <thead>
                <tr>
                    @foreach($b['checkpoints'] as $cpName => $cp)
                        <th>{{ $cpName }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    @foreach($b['checkpoints'] as $cp)
                        <td>{{ $cp['size'] ?? '-' }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>

        {{-- RESULT --}}
        <p>
            Inspect: {{ $b['inspect'] }} |
            ✔ {{ $b['passed'] }} |
            ✖ {{ $b['rejected'] }}
        </p>

        {{-- FOTO + REMARK --}}
        @foreach($b['checkpoints'] as $cpName => $cp)

            <div style="margin-bottom:15px;">

                <b>{{ $cpName }}</b> :
                {{ $cp['remark'] ?? '-' }}

              @if(!empty($cp['photos']))

<table width="100%" style="margin-top:8px;">
    
    @foreach(collect($cp['photos'])->chunk(2) as $row)
        <tr>

            @foreach($row as $photo)

                @php
                    $path = storage_path('app/public/'.$photo->path);
                @endphp

                <td style="padding:10px; text-align:center; width:50%;">

                    @if(file_exists($path))
                        <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents($path)) }}"
                             style="width:220px; height:auto;">
                    @endif

                    <div style="font-size:11px; margin-top:5px;">
                        {{ $photo->keterangan ?? '' }}
                    </div>

                </td>

            @endforeach

            {{-- 🔥 kalau ganjil biar tetap rapi --}}
            @if(count($row) == 1)
                <td style="width:50%;"></td>
            @endif

        </tr>
    @endforeach

</table>

@endif

            </div>

        @endforeach

    @endforeach

</div>

{{-- PAGE BREAK PER ITEM --}}
<div class="page-break"></div>

@endforeach