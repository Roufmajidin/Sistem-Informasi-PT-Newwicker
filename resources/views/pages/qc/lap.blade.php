@extends('master.master')

@section('title','QC Inspection Report')

@section('content')
@php

$data = collect($response['data']);

$totalChecked = $data->sum('jumlah_inspect');
$totalPassed = $data->sum('passed');
$totalRejected = $data->sum('rejected');

// Batch pertama
$current = $data->first();

// Persentase
$passPercent = $totalChecked > 0
    ? round(($totalPassed / $totalChecked) * 100)
    : 0;

// Null Safe
$po = $current?->po;
$spk = $current?->spk;
$detail = $current?->detailPo?->detail ?? [];
$item = $current?->spk_item ?? [];
$photos = collect($current?->reportPhotos ?? []);

// Report Collection
$reports = collect($current?->qcReports ?? [])->keyBy('check_point_id');

$masterSample = optional($reports->get(16))->remark
    ? json_decode($reports->get(16)->remark, true)
    : [];

$cad = optional($reports->get(17))->remark
    ? json_decode($reports->get(17)->remark, true)
    : [];

$actual = optional($reports->get(18))->remark
    ? json_decode($reports->get(18)->remark, true)
    : [];

$bahan = optional($reports->get(19))->remark
    ? json_decode($reports->get(19)->remark, true)
    : [];

@endphp
@if($data->isEmpty())

<div class="alert alert-warning">

    <h4>Tidak ada data inspeksi.</h4>

    <p>User : {{ $response['filters']['user'] ?? '-' }}</p>

    <p>Tanggal : {{ $response['filters']['tanggal'] ?? '-' }}</p>

</div>

@else

<div class="wrapper mt-4">

    <!-- LEFT PANEL -->
    <aside class="sidebar">

        <div class="overview">
            <h4>INSPECTION OVERVIEW</h4>

            <div class="cards">
                <div class="mini-card">
                    <span>Total Checked</span>
                    {{ number_format($totalChecked) }}
                </div>

                <div class="mini-card green">
                    <span>Passed</span>
                  {{ number_format($totalPassed) }}
                </div>

                <div class="mini-card ">
                    <span>Rejected</span>
                  {{ number_format($totalRejected) }}
                </div>
            </div>
        </div>

      <h4 class="batch-title">
    Inspection Batches ({{ $data->count() }})
</h4>
<div id="batch-list">

@foreach($data as $index => $batch)

    @php
        $percent = $batch->jumlah_inspect > 0
            ? round(($batch->passed / $batch->jumlah_inspect) * 100)
            : 0;
    @endphp

    <div class="batch {{ $index == 0 ? 'active' : '' }}"
         data-index="{{ $index }}"
         data-id="{{ $batch->id }}">

        <div class="top">

            <strong>Batch #{{ $batch->batch }}</strong>

            <span class="badge">
                {{ $percent }}% Passed
            </span>

        </div>

        <p>
            {{ $batch->spk_item['nama'] ?? '-' }}
        </p>

        <div class="bottom">

            <small>Inspect Qty</small>

            <strong>
                {{ $batch->jumlah_inspect }}
                /
                {{ $batch->spk_item['qty'] ?? '-' }}
                {{ $batch->spk_item['satuan'] ?? '' }}
            </strong>

        </div>
@if($batch->batch_history->count() > 1)

<div class="batch-history">

    <table>

        <thead>

            <tr>

                <th>Batch</th>

                <th>P</th>

                <th>R</th>

            </tr>

        </thead>

        <tbody>

            @foreach($batch->batch_history as $history)

            <tr class="{{ $history->batch == $batch->batch ? 'active' : '' }}">

                <td>#{{ $history->batch }}</td>

                <td>{{ $history->passed }}</td>

                <td>{{ $history->rejected }}</td>

            </tr>

            @endforeach

        </tbody>

    </table>

</div>

@endif
    </div>

@endforeach

</div>
    </aside>

    <!-- CONTENT -->

    <main class="content">

        <header class="topbar">

            <div>
                <small>QC Inspection Report</small>
             <h2>
    Laporan Inspeksi :
    <span>
        {{ $current?->user?->karyawan?->nama_lengkap ?? '-' }}
    </span>

    <small>

        Periode :

        @if(!empty($response['filters']['from']) && !empty($response['filters']['to']))

            {{ \Carbon\Carbon::parse($response['filters']['from'])->translatedFormat('d F Y') }}
            -
            {{ \Carbon\Carbon::parse($response['filters']['to'])->translatedFormat('d F Y') }}

        @elseif(!empty($response['filters']['from']))

            {{ \Carbon\Carbon::parse($response['filters']['from'])->translatedFormat('d F Y') }}

        @else

            Semua Tanggal

        @endif

    </small>

</h2>
            </div>


        </header>


    <section class="hero">

    <section class="hero">

    <div class="hero-left">

       <div class="hero-product">

    <img
        id="hero-image"
        src="{{ $current->detailPo->detail['photo']
                ?? ($current->spk_item['images'][0] ?? asset('images/no-image.png')) }}"
        alt="Product">

    <div>

        <span class="label">
            Kategori :
            <span id="hero-kategori">
                {{ $current->kategori->kategori ?? '-' }}
            </span>
        </span>

        <h1 id="hero-product">
            {{ $current->spk_item['nama'] ?? '-' }}
        </h1>

        <small>
            Material :
            <span id="hero-material">
                {{ $current->spk_item['material'] ?? '-' }}
            </span>
        </small>

    </div>

        </div>

    </div>




</section>

    <div class="hero-right">

        {{-- <span>Status Kelulusan</span> --}}

        <h2 id="hero-status">

            {{ $current->passed }}

            Passed /

            {{ $current->jumlah_inspect }}

            Checked

        </h2>

        <small id="hero-percent">

            @php
                $heroPercent = $current->jumlah_inspect
                    ? round(($current->passed/$current->jumlah_inspect)*100)
                    : 0;
            @endphp

            {{ $heroPercent }}% Passed

        </small>

    </div>

</section>


<section class="info-grid">

    {{-- ORDER & PURCHASE --}}
    <div class="">

        <h4>ORDER & PURCHASE INFO</h4>

        <table>

            <tr>
                <td>Client</td>
                <td id="info-client">
                    {{ $po->company_name ?? '-' }}
                </td>
            </tr>

            <tr>
                <td>Country</td>
                <td id="info-country">
                    {{ $po->country ?? '-' }}
                </td>
            </tr>

            <tr>
                <td>PO Number</td>
                <td id="info-po">
                    {{ $po->order_no ?? '-' }}
                </td>
            </tr>

            <tr>
                <td>Shipment</td>
                <td id="info-shipment">
                    {{ $po->shipment_date ?? '-' }}
                </td>
            </tr>

            <tr>
                <td>Packing</td>
                <td id="info-packing">
                    {{ $po->packing ?? '-' }}
                </td>
            </tr>

        </table>

    </div>

    {{-- SPK --}}
    <div class="">

        <h4>SPK ORDER PABRIK</h4>

        <table>

            <tr>
                <td>Supplier</td>
                <td id="spk-supplier">
                    {{ $spk->data['sup'] ?? '-' }}
                </td>
            </tr>

            <tr>
                <td>SPK Number</td>
                <td id="spk-number">
                    {{ $spk->data['no_spk'] ?? '-' }}
                </td>
            </tr>

            <tr>
                <td>Tanggal Terima</td>
                <td id="spk-terima">
                    {{ $spk->data['tgl_terima'] ?? '-' }}
                </td>
            </tr>

            <tr>
                <td>Deadline</td>
                <td id="spk-deadline">
                    {{ $spk->data['tgl_selesai'] ?? '-' }}
                </td>
            </tr>

            <tr>
                <td>Status</td>
                <td id="spk-status">
                    {{ ucfirst($spk->status ?? '-') }}
                </td>
            </tr>

        </table>

    </div>

    {{-- DIMENSION --}}
    <div class="">

        <h4>DIMENSION SPEC</h4>

        <table>

            <tr>
                <td>Target Size</td>
                <td id="dim-target">
                    {{ $detail['item_w'] ?? '-' }}
                    ×
                    {{ $detail['item_d'] ?? '-' }}
                    ×
                    {{ $detail['item_h'] ?? '-' }}
                    cm
                </td>
            </tr>

            <tr>
                <td>Packing Size</td>
                <td id="dim-pack">
                    {{ $detail['pack_w'] ?? '-' }}
                    ×
                    {{ $detail['pack_d'] ?? '-' }}
                    ×
                    {{ $detail['pack_h'] ?? '-' }}
                    cm
                </td>
            </tr>

            <tr>
                <td>Volume</td>
                <td id="dim-volume">
                    {{ number_format((float)($detail['cbm'] ?? 0),6) }}
                    CBM
                </td>
            </tr>

            <tr>
                <td>Total Qty</td>
                <td id="dim-qty">
                    {{ $detail['qty'] ?? '-' }}
                    {{ $item['satuan'] ?? '' }}
                </td>
            </tr>

            <tr>
                <td>Finishing</td>
                <td id="dim-finishing">
                    {{ $detail['finishing'] ?? '-' }}
                </td>
            </tr>

        </table>

    </div>

</section>
<section class="table-card">

    <h3>Checklist Parameter Hasil Inspeksi QC</h3>

    <table class="qc-table">

        <thead>
            <tr>
                <th width="25%">Parameter</th>
                <th width="25%">Parameter</th>
                <th width="35%">Actual</th>
                <th width="15%">Status</th>
            </tr>
        </thead>

        <tbody>

            {{-- MASTER SAMPLE --}}
            <tr>

                <td>Ukuran Master Sample</td>

                <td id="master-target">
                    {{ ($detail['item_w'] ?? '-') }}
                    ×
                    {{ ($detail['item_d'] ?? '-') }}
                    ×
                    {{ ($detail['item_h'] ?? '-') }}
                </td>

                <td id="master-actual">
                    {{ $masterSample['ukuran_master_sample'] ?? '-' }}
                </td>

                <td>
                    <span id="master-status" class="status green">
                        @if(($masterSample['ukuran_master_sample'] ?? '') ==
                            (($detail['item_w'] ?? '').' x '.($detail['item_d'] ?? '').' x '.($detail['item_h'] ?? '')))
                            {{-- MATCH --}}
                        @else
                            CHECK
                        @endif
                    </span>
                </td>

            </tr>

            {{-- CAD --}}
            <tr>

                <td>Ukuran Gambar CAD</td>

                <td>CAD</td>

                <td id="cad-actual">
                    {{ $cad['ukuran_cad'] ?? 'Tidak Ada' }}
                </td>

                <td>

                    <span id="cad-status"
                          class="status {{ empty($cad['ukuran_cad']) ? 'gray' : 'green' }}">

                        {{ empty($cad['ukuran_cad']) ? 'N/A' : 'OK' }}

                    </span>

                </td>

            </tr>

            {{-- UKURAN AKTUAL --}}
            <tr>

                <td>Ukuran Aktual</td>

                <td id="actual-target">
                    {{ ($detail['item_w'] ?? '-') }}
                    ×
                    {{ ($detail['item_d'] ?? '-') }}
                    ×
                    {{ ($detail['item_h'] ?? '-') }}
                </td>

                <td id="actual-size">

                    {{ $actual['aktual']['w'] ?? '-' }}

                    ×

                    {{ $actual['aktual']['d'] ?? '-' }}

                    ×

                    {{ $actual['aktual']['h'] ?? '-' }}

                </td>

                <td>

                    <span id="actual-status" class="status orange">

                      -

                    </span>

                </td>

            </tr>

            {{-- MATERIAL --}}
            <tr>

                <td>Bahan</td>

                <td id="material-target">

                    {{ $item['material'] ?? '-' }}

                </td>

                <td id="material-actual">

                    {{ $bahan['jenis_bahan_yang_digunakan'] ?? '-' }}

                </td>

                <td>

                    <span id="material-status" class="status green">

                        OK

                    </span>

                </td>

            </tr>

            {{-- PASSED --}}
            <tr>

                <td>Jumlah Passed</td>

                <td>Total Passed</td>

                <td id="passed-value">

                    {{ $current->passed }}

                </td>

                <td>

                    <span class="status green">

                        PASS

                    </span>

                </td>

            </tr>

            {{-- REJECTED --}}
            <tr>

                <td>Jumlah Rejected</td>

                <td>Total Reject</td>

                <td id="reject-value">

                    {{ $current->rejected }}

                </td>

                <td>

                    <span class="status red">

                        REJECT

                    </span>

                </td>

            </tr>

        </tbody>

    </table>

</section>
<section class="photo-gallery">

    <div class="gallery-header">

        <h3>📷 Foto Temuan QC</h3>

        <span id="photo-count">

            {{ $photos->count() }} Photos

        </span>

    </div>

    <div id="photo-container" class="gallery-grid">

        @forelse($photos as $photo)

            <div class="gallery-item">

                <img
                    src="{{ asset('storage/'.$photo->path) }}"
                    alt="QC Photo">

                @if(!empty($photo->keterangan))

                    <div class="remark">

                        {{ $photo->keterangan }}

                    </div>

                @endif

            </div>

        @empty

            <div class="empty-photo">

                <h4>Belum ada foto temuan</h4>

                <p>Pemeriksa belum mengunggah foto.</p>

            </div>

        @endforelse

    </div>

</section>
    </main>

</div>
<script>
    const qcData = @json($response['data']);
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const batches = document.querySelectorAll('.batch');

    // --- 1. PRELOAD/INITIAL RUN ---
    // Pastikan jika ada data, batch pertama langsung ter-render dengan benar
    if (qcData && qcData.length > 0) {
        renderBatchDetail(qcData[0]);
    }

    // --- 2. EVENT LISTENER FOR CLICK ---
    batches.forEach(function (batch) {
        batch.addEventListener('click', function () {
            // Hapus class active lama
            batches.forEach(b => b.classList.remove('active'));

            // Tambahkan class active ke batch yang diklik
            this.classList.add('active');

            // Ambil data berdasarkan indeks batch
            let index = this.dataset.index;
            let item = qcData[index];

            if (item) {
                renderBatchDetail(item);
            }
        });
    });

    // --- 3. SEQUENCE FUNCTION UNTUK RENDER ---
    function renderBatchDetail(item) {
        console.log("Rendering batch:", item);

        // --- URUTAN 1: HERO SECTION (Atas) ---
        document.getElementById('hero-kategori').textContent = item.kategori?.kategori ?? '-';
        document.getElementById('hero-product').textContent = item.spk_item?.nama ?? '-';
        document.getElementById('hero-material').textContent = item.spk_item?.material ?? '-';
        // image
        // Hero Image
const heroImage = document.getElementById('hero-image');

let image = '';

// Prioritas 1: Foto dari Detail PO
if (item.detail_po?.detail?.photo) {

    image = item.detail_po.detail.photo;

}
// Prioritas 2: Gambar dari SPK Item
else if (item.spk_item?.images?.length > 0) {

    image = item.spk_item.images[0];

}

if(image){

    heroImage.src = image;

}else{

    heroImage.src = "{{ asset('images/no-image.png') }}";

}
        let checkedQty = parseInt(item.jumlah_inspect) || 0;
        let passedQty = parseInt(item.passed) || 0;
        let rejectedQty = parseInt(item.rejected) || 0;
        let percent = checkedQty > 0 ? Math.round((passedQty / checkedQty) * 100) : 0;

        document.getElementById('hero-status').textContent = `${passedQty} Passed / ${checkedQty} Checked`;
        document.getElementById('hero-percent').textContent = `${percent}% Passed`;


        // --- URUTAN 2: INFO GRID (Tengah) ---
        // A. Order & Purchase Info
        let po = item.po ?? {};
        document.getElementById('info-client').textContent = po.company_name ?? '-';
        document.getElementById('info-country').textContent = po.country ?? '-';
        document.getElementById('info-po').textContent = po.order_no ?? '-';
        document.getElementById('info-shipment').textContent = po.shipment_date ?? '-';
        document.getElementById('info-packing').textContent = po.packing ?? '-';

        // B. SPK Order Pabrik
        let spk = item.spk ?? {};
        let spkData = spk.data ?? {};
        document.getElementById('spk-supplier').textContent = spkData.sup ?? '-';
        document.getElementById('spk-number').textContent = spkData.no_spk ?? '-';
        document.getElementById('spk-terima').textContent = spkData.tgl_terima ?? '-';
        document.getElementById('spk-deadline').textContent = spkData.tgl_selesai ?? '-';
        document.getElementById('spk-status').textContent = spk.status ? spk.status.charAt(0).toUpperCase() + spk.status.slice(1) : '-';

        // C. Dimension Spec
        let detailPo = item.detail_po?.detail ?? item.detail_po ?? {};
        let itemW = detailPo.item_w ?? '-';
        let itemD = detailPo.item_d ?? '-';
        let itemH = detailPo.item_h ?? '-';
        document.getElementById('dim-target').textContent = `${itemW} × ${itemD} × ${itemH} cm`;

        document.getElementById('dim-pack').textContent = `${detailPo.pack_w ?? '-'} × ${detailPo.pack_d ?? '-'} × ${detailPo.pack_h ?? '-'} cm`;

        let cbm = parseFloat(detailPo.cbm ?? 0).toFixed(6);
        document.getElementById('dim-volume').textContent = `${cbm} CBM`;
        document.getElementById('dim-qty').textContent = `${detailPo.qty ?? '-'} ${item.spk_item?.satuan ?? ''}`;
        document.getElementById('dim-finishing').textContent = detailPo.finishing ?? '-';


        // --- URUTAN 3: CHECKLIST PARAMETER TABLE (Bawah-Tengah) ---
        // Parsing laporan berdasarkan check_point_id (sesuai struktur PHP keyBy)
        let reports = {};
        if (item.qc_reports) {
            item.qc_reports.forEach(r => {
                reports[r.check_point_id] = r;
            });
        }

        // Helper untuk parse string JSON aman
        function parseRemark(reportObj) {
            if (!reportObj || !reportObj.remark) return {};
            try {
                return typeof reportObj.remark === 'string' ? JSON.parse(reportObj.remark) : reportObj.remark;
            } catch (e) {
                return {};
            }
        }

        let masterSample = parseRemark(reports[16]);
        let cad = parseRemark(reports[17]);
        let actual = parseRemark(reports[18]);
        let bahan = parseRemark(reports[19]);

        // Target vs Actual - Master Sample
        let targetSizeStr = `${itemW} x ${itemD} x ${itemH}`;
        let actualMasterStr = masterSample.ukuran_master_sample ?? '-';
        document.getElementById('master-target').textContent = `${itemW} × ${itemD} × ${itemH}`;
        document.getElementById('master-actual').textContent = actualMasterStr;

        let masterStatusNode = document.getElementById('master-status');
        if (actualMasterStr !== '-' && actualMasterStr === targetSizeStr) {
            masterStatusNode.textContent = 'MATCH';
            masterStatusNode.className = 'status green';
        } else {
            masterStatusNode.textContent = 'CHECK';
            masterStatusNode.className = 'status orange';
        }

        // CAD
        let cadActualStr = cad.ukuran_cad ?? '';
        document.getElementById('cad-actual').textContent = cadActualStr || 'Tidak Ada';
        let cadStatusNode = document.getElementById('cad-status');
        if (!cadActualStr) {
            cadStatusNode.textContent = 'N/A';
            cadStatusNode.className = 'status gray';
        } else {
            cadStatusNode.textContent = 'OK';
            cadStatusNode.className = 'status green';
        }

        // Ukuran Aktual
        document.getElementById('actual-target').textContent = `${itemW} × ${itemD} × ${itemH}`;
        let actW = actual.aktual?.w ?? '-';
        let actD = actual.aktual?.d ?? '-';
        let actH = actual.aktual?.h ?? '-';
    //    deviasi
    document.getElementById('actual-size').textContent =
`${actW} × ${actD} × ${actH}`;

const targetW = parseFloat(itemW) || 0;
const targetD = parseFloat(itemD) || 0;
const targetH = parseFloat(itemH) || 0;

const actualW = parseFloat(actW) || 0;
const actualD = parseFloat(actD) || 0;
const actualH = parseFloat(actH) || 0;

const diffW = Math.abs(targetW - actualW);
const diffD = Math.abs(targetD - actualD);
const diffH = Math.abs(targetH - actualH);

const maxDiff = Math.max(diffW, diffD, diffH);

const status = document.getElementById("actual-status");

if(maxDiff === 0){

    status.className = "status green";
    status.textContent = "Perfect Match";

}
else if(maxDiff <= 1){

    status.className = "status success";
    status.textContent = "Deviasi 1 cm";

}
else if(maxDiff <= 2){

    status.className = "status warning";
    status.textContent = "Deviasi " + maxDiff + " cm";

}
else{

    status.className = "status danger";
    status.textContent = "Deviasi " + maxDiff + " cm";

}
        // Bahan
        document.getElementById('material-target').textContent = item.spk_item?.material ?? '-';
        document.getElementById('material-actual').textContent = bahan.jenis_bahan_yang_digunakan ?? '-';

        // Summary Rows
        document.getElementById('passed-value').textContent = passedQty;
        document.getElementById('reject-value').textContent = rejectedQty;


        // --- URUTAN 4: PHOTO GALLERY (Paling Bawah) ---
        const photoContainer = document.getElementById('photo-container');
        const photoCount = document.getElementById('photo-count');
        const photos = item.report_photos ?? item.report_photos ?? [];

        photoCount.textContent = `${photos.length} Photos`;
        photoContainer.innerHTML = ''; // Clear gallery

        if (photos.length > 0) {
            photos.forEach(photo => {
                let itemDiv = document.createElement('div');
                itemDiv.className = 'gallery-item';

                // Base URL storage Laravel
                let imgUrl = `{{ asset('storage') }}/${photo.path}`;

                let img = document.createElement('img');
                img.src = imgUrl;
                img.alt = 'QC Photo';
                itemDiv.appendChild(img);

                if (photo.keterangan) {
                    let remarkDiv = document.createElement('div');
                    remarkDiv.className = 'remark';
                    remarkDiv.textContent = photo.keterangan;
                    itemDiv.appendChild(remarkDiv);
                }

                photoContainer.appendChild(itemDiv);
            });
        } else {
            photoContainer.innerHTML = `
                <div class="empty-photo" style="grid-column: 1 / -1;">
                    <h4>Belum ada foto temuan</h4>
                    <p>Pemeriksa belum mengunggah foto.</p>
                </div>
            `;
        }
    }
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const dateInput = document.getElementById("filter-date");

    if(dateInput){
        dateInput.value = "";
    }

});
document.addEventListener("DOMContentLoaded", function () {

    document.getElementById("filter-date").value = "";
    document.getElementById("qc-user").selectedIndex = 0;

});
window.addEventListener("pageshow", function () {

    document.getElementById("filter-date").value = "";
    document.getElementById("qc-user").selectedIndex = 0;

});
</script>
<style>
    *{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Segoe UI,sans-serif;
}

body{
background:#f4f7fb;
}
.photo-gallery{

    margin-top:25px;

    background:#fff;

    border-radius:12px;

    border:1px solid #e6e9ef;

    padding:20px;

}

.gallery-header{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-bottom:20px;

}

.gallery-header h3{

    margin:0;

    font-size:18px;

}

.gallery-header span{

    color:#666;

    font-size:13px;

}

.gallery-grid{

    display:grid;

    grid-template-columns:repeat(auto-fill,minmax(280px,1fr));

    gap:20px;

}

.gallery-item{

    border:1px solid #ececec;

    border-radius:12px;

    overflow:hidden;

    background:#fff;

    transition:.3s;

}

.gallery-item:hover{

    box-shadow:0 10px 20px rgba(0,0,0,.08);

    transform:translateY(-3px);

}

.gallery-item img{

    width:100%;

    height:250px;

    object-fit:cover;

    display:block;

}

.remark{

    padding:15px;

    font-size:14px;

    color:#444;

    background:#fafafa;

    border-top:1px solid #eee;

}

.empty-photo{

    text-align:center;

    padding:60px;

    border:2px dashed #ddd;

    border-radius:10px;

    color:#888;

}
.wrapper{
display:flex;
height:100vh;
}

.sidebar{

width:320px;
background:#fff;
border-right:1px solid #e4e7ef;
padding:20px;
overflow:auto;

}

.overview h4{
font-size:13px;
color:#777;
margin-bottom:15px;
}

.cards{
display:grid;
grid-template-columns:repeat(3,1fr);
gap:10px;
}

.mini-card{

background:white;
border:1px solid #e4e7ef;
border-radius:10px;
padding:15px;
text-align:center;

}

.mini-card.green h2{
color:#22b573;
}

.mini-card.red h2{
color:#ff3c5a;
}

.batch-title{
margin:25px 0 10px;
font-size:13px;
color:#888;
}

.batch{

background:white;
border:1px solid #ddd;
border-radius:12px;
padding:15px;
margin-bottom:15px;

}

.batch.active{
border:2px solid #5664ff;
}

.top{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:10px;
}

.badge{

background:#dff8e9;
color:#13a455;
padding:5px 10px;
font-size:12px;
border-radius:30px;

}

.bottom{
margin-top:15px;
}

.content{
flex:1;
padding:20px;
overflow:auto;
}

.topbar{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:20px;
}

.topbar button{

background:#5b5cf6;
border:none;
color:white;
padding:10px 18px;
border-radius:8px;
cursor:pointer;

}

.hero{

background:linear-gradient(90deg,#161d52,#22275f);
color:white;
padding:25px;
border-radius:12px;
display:flex;
justify-content:space-between;
align-items:center;

}

.label{
background:#6f74ff;
padding:5px 12px;
border-radius:20px;
font-size:12px;
display:inline-block;
margin-bottom:10px;
}

.hero h1{
margin:10px 0;
}

.hero-right{

background:rgba(255,255,255,.08);
padding:20px;
border-radius:10px;
text-align:center;

}

.info-grid{

display:grid;
grid-template-columns:repeat(3,1fr);
gap:20px;
margin-top:20px;

}

.info{

/* background:white; */
border-radius:10px;
padding:20px;
border:1px solid #e8ebf2;

}

.info h4{

font-size:14px;
margin-bottom:15px;
color:#555;

}

.info table{

width:100%;
font-size:14px;

}

.info td{

padding:8px 0;
border-bottom:1px solid #eee;

}

.table-card{

background:white;
margin-top:25px;
border-radius:12px;
padding:20px;
border:1px solid #e8ebf2;

}

.qc-table{
color:black;
width:100%;
border-collapse:collapse;
margin-top:20px;

}

.qc-table th{

/* background:#f5f7fb; */
padding:15px;
text-align:left;
font-size:14px;

}

.qc-table td{

padding:15px;
border-top:1px solid #eee;

}

.status{

padding:6px 12px;
border-radius:20px;
font-size:12px;
display:inline-block;

}

.green{
background:#dff8e9;
color:#1f9d5d;
}

.orange{
background:#ffeccf;
color:#c57a00;
}

.gray{
background:#ececec;
color:#666;
}
.hero-product{

    display:flex;

    align-items:center;

    gap:20px;

}

.hero-product img{

    width:110px;

    height:110px;

    border-radius:12px;

    object-fit:cover;

    background:#fff;

    padding:6px;

    box-shadow:0 5px 15px rgba(0,0,0,.2);

    flex-shrink:0;

}

.hero-product h1{

    margin:10px 0;

}
.batch-history{

    margin-top:12px;

    border-top:1px dashed #ddd;

    padding-top:10px;

}

.batch-history table{

    width:100%;

    border-collapse:collapse;

    font-size:11px;

}

.batch-history th{

    font-weight:600;

    color:#777;

    text-align:center;

    padding-bottom:5px;

}

.batch-history td{

    text-align:center;

    padding:3px;

}

.batch-history tr.active{

    background:#eef4ff;

    border-radius:4px;

    font-weight:bold;

    color:#3B82F6;

}
.status.success{

    background:#E8F8F0;

    color:#16A34A;

}

.status.warning{

    background:#FFF4D6;

    color:#D97706;

}

.status.danger{

    background:#FEE2E2;
MATCH
    color:#DC2626;

}
</style>
@endsection


@endif
