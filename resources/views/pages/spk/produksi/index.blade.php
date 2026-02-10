@extends('master.master')
@section('title', "Produksi")
@section('content')
@include('pages.spk.stylespk')

<div class="padding">
    <div class="box">
        <div class="box-header">
            <h2>Monitoring Barang Produksi</h2>
            @php
                $a = Auth::user();
            @endphp
            <!-- <small>you'r loggin as {{$a}}</small> -->
        </div>
             <div class="row" id="default-table">
                <div class="col-sm-12">
                    <div class="box">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr class="spk-header">
                                        <th>Tanggal</th>
                                        <th>Nama Barang</th>
                                        <th>Buyer</th>
                                        <th>No PO</th>
                                        <th>Qty PO</th>
                                        <th>Qty Masuk</th>
                                        <th>list sub</th>
                                        <th>Qty Out</th>
                                        <th>Jenis Barang</th>
                                        <th>Sub</th>
                                        <th>Waktu in/out barang</th>
                                        <th width="120">Action</th>
                                    </tr>
                                </thead>
                               <tbody>
    @foreach($result as $row)
    @php

    $dp = $row['detail_po'];
@endphp

   <tr>
    <td></td>

    {{-- NAMA BARANG --}}
    <td>
        <div style="display:flex; gap:10px;">
            <img src="{{ data_get($dp->detail,'photo') }}"
                 style="width:60px;height:60px;object-fit:cover"
                 onerror="this.style.display='none'">

            <div>
                <b>{{ data_get($dp->detail,'article_nr_') }}</b><br>
                <small>{{ data_get($dp->detail,'description') }}</small><br>
                <small class="text-muted">
                    Qty PO: {{ data_get($dp->detail,'qty') }}
                </small>
            </div>
        </div>
    </td>

    <td>{{ $dp->po->company_name }}</td>
    <td>{{ $dp->po->order_no }}</td>
    <td>{{ data_get($dp->detail,'qty') }}</td>

    {{-- LIST SUB --}}
    <td>
       <td>
@forelse($row['spk'] as $sub => $spks)
    <div>
        <span class="badge bg-info">{{ strtoupper($sub) }}</span>
        @foreach($spks as $spk)
            <div style="margin-left:8px">
                <a href="/spk/edit/{{ $spk['spk_id'] }}">
                    {{ $spk['no_spk'] }}
                </a>
                <small>({{ $spk['sup'] }} - {{ $spk['qty'] }})</small>
            </div>
        @endforeach
    </div>
@empty
    <span class="text-muted">Belum ada SPK</span>
@endforelse
</td>

    </td>
<td>
    <button type="button"
            class="btn btn-sm btn-secondary"
            onclick="toggleQtyOut('{{ $dp->id }}')">
        Input Qty Out
    </button>

    <div id="qty-form-{{ $dp->id }}" style="display:none; margin-top:8px;">
        <input type="number"
               class="form-control form-control-sm"
               placeholder="Qty Out">

        <textarea class="form-control form-control-sm mt-1"
                  rows="2"
                  placeholder="Keterangan"></textarea>

        <button class="btn btn-sm btn-primary mt-1">
            Simpan
        </button>
    </div>
</td>


</tr>


    @endforeach
</tbody>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    </div>
    </div>
    <script>
function toggleQtyOut(id) {
    const el = document.getElementById('qty-form-' + id);

    if (!el) {
        console.log('Form tidak ditemukan');
        return;
    }

    el.style.display = (el.style.display === 'none') ? 'block' : 'none';
}
</script>

@endsection
