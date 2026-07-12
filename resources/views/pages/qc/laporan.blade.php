@extends('master.master')

@section('title', 'QC Monitor')

@section('content')
@include('pages.spk.stylespk')
@include('pages.marketing.style')

<div class="padding">
    <div class="box">

        <div class="box-header">
            <h2>QC Monitor</h2>
        <div class="header-controls">

    <div class="filter-item">
        <label>Inspector</label>
       <select class="form-control" id="qc-user">
            <option value="">Pilih Inspector</option>

            @foreach($qcs as $qc)
                <option value="{{ $qc->id }}">
                    {{ $qc->karyawan->nama_lengkap }}
                    ({{ $qc->karyawan->divisi->nama }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="filter-item">
        <label>From</label>
        <input type="date"
               class="form-control date-filter"
               id="date_from">
    </div>

    <div class="filter-item">
        <label>To</label>
        <input type="date"
               class="form-control date-filter"
               id="date_to">
    </div>

    <div class="filter-buttons">
      <button class="btn btn-primary" id="btn-filter">

<i class="fa fa-search"></i>

Filter

</button>

        <button class="btn btn-outline-secondary">
            <i class="fas fa-rotate-left"></i>
            Reset
        </button>
    </div>

</div>
        </div>
                <div class="box-body">

            <div class="monitor-wrapper">
 {{-- ========================================= --}}
                {{-- DETAIL PO --}}
                {{-- ========================================= --}}
                <div class="panel">
                   <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <b>Inspection QC's</b>

    <button
        class="btn btn-light btn-sm shadow-sm"
        id="btn-detail"
        style="display:none;color:black">
        <i class="fas fa-eye me-1"></i>
        Detail Report
    </button>
</div>
                        <div class="card-body p-0">

                            <div class="panel-scroll">

                                <table class="table table-bordered mb-0">

                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Tanggal, Jam</th>
                                            <th>PO</th>
                                            <th>Buyer</th>
                                            <th>No. SPK</th>
                                            <th>person</th>
                                            <th>Total Inspected</th>
                                            <th>Pass</th>
                                            <th>Reject</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detail-list">
  {{-- <td colspan="9"
                                                class="text-center">
                                                No inspection data available. Please select an inspector and date to view the report.
                                            </td> --}}
                                             @foreach ($inspection as $inspected)

                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($inspected->tanggal_inspect)->format('d M Y') }}
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $inspected->created_at->diffForHumans() }}
                                                    </small>
                                                </td>
                                                <td>{{ optional($inspected->po)->order_no ?? '-' }}</td>

                                                <td>{{ optional($inspected->po)->company_name ?? '-' }}</td>
                                                <td>{{ optional($inspected->spk)->data['no_spk'] ?? '-' }}</td>



                                                <td>{{ optional($inspected->user)->name ?? '-' }}</td>
                                                <td>{{ $inspected->jumlah_inspect }}</td>
                                                <td>{{ $inspected->passed }}</td>
                                                <td>{{ $inspected->rejected }}</td>

                                            </tr>
                                            @endforeach


                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
const TOKEN = "{{ auth()->user()->api_token }}";

</script>
<script>
   $("#btn-filter").click(function(){

    loadInspection();

});

function loadInspection(){

    let inspector=$("#qc-user").val();

    let from=$("#date_from").val();

    let to=$("#date_to").val();

    $("#detail-list").html(`
        <tr>
            <td colspan="9" class="table-loading">

                <div class="spinner-border text-primary"></div>

                <div class="mt-2">
                    Loading inspection data...
                </div>

            </td>
        </tr>
    `);

    $.ajax({

        url:"{{ route('inspection.filter') }}",

        type:"GET",

        data:{
            inspector:inspector,
            from:from,
            to:to
        },

        success:function(res){
console.log({
    inspector: inspector,
    from: from,
    to: to
});
            $("#detail-list")
                .hide()
                .html(res.html)
                .fadeIn(250);

            $("#btn-detail").show();

        },

        error:function(){

            $("#detail-list").html(`
                <tr>

                    <td colspan="9"
                        class="text-center text-danger py-5">

                        Failed loading inspection data.

                    </td>

                </tr>
            `);

        }

    });

}
</script>
<script>

let poData = [];
$(document).on('click', '.detail-row', function(){

    $('.detail-row').removeClass('active');

    $(this).addClass('active');

    let detailId = $(this).data('id');

    let detail =
        window.currentDetails.find(
            x => x.id == detailId
        );

    if(!detail){
        return;
    }

 renderSchedules(detail);

});
$(document).on('click', '.po-row', function(){

    $('.po-row').removeClass('active');

    $(this).addClass('active');

    let poId = $(this).data('id');

    let po = poData.find(x => x.id == poId);

    if(!po){
        return;
    }

    renderDetailPo(po.details);
    // console.log(po.details);
    // renderReportPages(po.details)
});


</script>
<script>

const dateInput = document.getElementById("filter-date");
const userInput = document.getElementById("qc-user");

dateInput.addEventListener("change", openReport);
userInput.addEventListener("change", openReport);

function openReport(){

    // Ambil option yang dipilih
    const selectedOption = userInput.options[userInput.selectedIndex];

    // Ambil nama inspector (bukan ID)
    const user = selectedOption.dataset.name;

    const tanggal = dateInput.value;

    if(!user || !tanggal){
        return;
    }

    document.getElementById("loading-wrapper").style.display = "flex";
    document.getElementById("loading-text").innerHTML = "Memuat laporan...";

    setTimeout(function(){

        window.location.href =
            "/qc/laporan-qc?user=" +
            encodeURIComponent(user) +
            "&tanggal=" +
            encodeURIComponent(tanggal);

    },500);

}

</script>
<script>
window.addEventListener("pageshow", function (event) {

    // Halaman dibuka kembali dari tombol Back
    if (event.persisted || performance.getEntriesByType("navigation")[0]?.type === "back_forward") {

        // Reset tanggal
        document.getElementById("filter-date").value = "";

        // Reset inspector
        document.getElementById("qc-user").selectedIndex = 0;

        // Sembunyikan loading
        document.getElementById("loading-wrapper").style.display = "none";
    }

});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('#detail-list tr').forEach(row => {
        row.addEventListener('click', function () {

            // Hapus active dari semua baris
            document.querySelectorAll('#detail-list tr.active').forEach(r => {
                r.classList.remove('active');
            });

            // Tambahkan active ke baris yang diklik
            this.classList.add('active');
        });
    });

});
$("#btn-detail").click(function () {

    let user_id = $("#qc-user").val();
    let from = $("#date_from").val();
    let to = $("#date_to").val();

    window.location.href =
        "{{ route('qc.laporans') }}"
        + "?user_id=" + encodeURIComponent(user_id)
        + "&from=" + encodeURIComponent(from)
        + "&to=" + encodeURIComponent(to);

});
</script>
<style>




.report-panel .card-body{
    max-height:75vh;
    overflow:auto;
}

.po-row,
.detail-row,
.schedule-row{
    cursor:pointer;
}

.po-row:hover,
.detail-row:hover,
.schedule-row:hover{
    background:#f3f4f6;
}

.po-row.active,
.detail-row.active,
.schedule-row.active{
    background:#dbeafe !important;
}

.qc-sheet{
    border:1px solid #ddd;
    padding:20px;
    margin-bottom:20px;
    background:#fff;
}

.qc-title{
    text-align:center;
    font-weight:bold;
    margin-bottom:20px;
}
.report-page{
    background:#fff;
    width:900px;
    margin:0 auto 30px;
    padding:30px;
    border:1px solid #ddd;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
}

.report-header{
    text-align:center;
    margin-bottom:20px;
}

.report-header h3{
    margin:0;
    font-weight:700;
}

.section-title{
    background:#f3f4f6;
    padding:10px;
    font-weight:bold;
    margin-top:20px;
    margin-bottom:10px;
}

.finding-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:15px;
}

.finding-item{
    border:1px solid #ddd;
    padding:8px;
    background:#fff;
}

.finding-image{
    width:100%;
    height:180px;
    object-fit:cover;
    border:1px solid #ddd;
}

.finding-checkpoint{
    margin-top:8px;
    font-weight:600;
    font-size:12px;
}

.finding-remark{
    margin-top:6px;
    font-size:12px;
    min-height:40px;
}
/* Kontainer utama untuk Search dan Date */
.header-controls {
    display: flex;
    align-items: center;
    gap: 12px; /* Jarak antara kolom search dan date */
}

/* Penyesuaian wrapper search agar tetap konsisten */
.search-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    width: 240px;
}

.search-icon {
    position: absolute;
    left: 12px;
    font-size: 14px;
    color: #888;
    pointer-events: none;
}

.search-wrapper input {
    width: 100%;
    padding: 8px 12px 8px 35px;
    font-size: 14px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    outline: none;
    transition: all 0.2s ease;
}

/* Style untuk Input Date */
.date-wrapper input[type="date"] {
    padding: 7px 12px;
    font-size: 14px;
    color: #495057;
    border: 1px solid #ced4da;
    border-radius: 6px;
    outline: none;
    background-color: #fff;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.2s ease;
}

/* Efek Fokus untuk semua input di header */
.search-wrapper input:focus,
.date-wrapper input[type="date"]:focus {
    border-color: #5664ff;
    box-shadow: 0 0 0 3px rgba(86, 100, 255, 0.1);
}
.loading-wrapper{

    display:flex;

    align-items:center;

    gap:10px;

    margin-left:15px;

    color:#4F46E5;

    font-weight:600;

}

.spinner{

    width:18px;

    height:18px;

    border:3px solid #ddd;

    border-top:3px solid #4F46E5;

    border-radius:50%;

    animation:spin .8s linear infinite;

}

@keyframes spin{

    to{

        transform:rotate(360deg);

    }

}
#detail-list tr {
    cursor: pointer;
    transition: background-color .15s ease;
}

#detail-list tr:hover {
    background: #f5f9ff;
}

#detail-list tr.active {
    background: #699deb !important;
    color: #fff;
}

#detail-list tr.active small,
#detail-list tr.active td {
    color: #fff !important;
}
.inspection-loading{
    height:250px;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    animation:fade .3s;
}

@keyframes fade{

from{
opacity:0;
transform:translateY(10px);
}

to{
opacity:1;
transform:none;
}

}
.panel{
    position: relative;
}

.card-header{
    position: sticky;
    top: 0;
    z-index: 100;
    background: #0d6efd !important;
    color: #fff;
    padding: 12px 16px;
    border-bottom: 1px solid rgba(255,255,255,.2);
}

.panel-scroll{
    max-height: calc(100vh - 230px);
    overflow-y: auto;
    overflow-x: auto;
    position: relative;
}

.panel-scroll table{
    border-collapse: separate;
    border-spacing: 0;
    margin: 0;
}

/* Header tabel */
.panel-scroll thead th{
    position: sticky;
    top: 0px;              /* tinggi card-header */
    z-index: 90;
    background: #fff;
    /* color: #212529; */
    border-bottom: 2px solid #dee2e6;
    white-space: nowrap;
    box-shadow: 0 2px 4px rgba(0,0,0,.05);
}

/* Supaya border tidak hilang */
.panel-scroll th,
.panel-scroll td{
    vertical-align: middle;
}

/* Efek zebra */
.panel-scroll tbody tr:nth-child(even){
    background:#fafafa;
}

/* Hover */
.panel-scroll tbody tr:hover{
    background:#eef6ff;
}
</style>


@endsection
