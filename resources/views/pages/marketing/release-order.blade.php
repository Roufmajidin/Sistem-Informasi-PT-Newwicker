@extends('master.master')
@section('title', "Release Order Marketing")
@section('content')
@include('pages.spk.stylespk')
@include('pages.marketing.style')

<style>
/* =========================================================
   RELEASE ORDER - DETAIL TABLE UI
   ========================================================= */
#detail-view .box-body {
    padding-bottom: 12px;
}

#detail-view .table-info {
    margin-bottom: 12px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(31, 52, 95, .06);
}

#detail-view .table-info td {
    padding: 9px 14px;
    border-color: #e8ecf4;
}

#detail-view .table-info td:first-child {
    width: 190px;
    background: #f6f8fc;
    color: #52607a;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .45px;
}

#detail-view .table-info td:last-child {
    color: #17233d;
    font-weight: 600;
}

#btn-back {
    border: 0;
    border-radius: 8px;
    padding: 7px 12px;
    font-weight: 600;
}

#detail-action-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin: 0 0 12px 0;
}

#btn-add-item {
    border: 0;
    border-radius: 8px;
    padding: 8px 14px;
    box-shadow: 0 4px 10px rgba(13, 110, 253, .14);
}

#detail-table tbody tr.new-item-row td {
    background: #eef6ff !important;
    border-top: 2px solid #0d6efd !important;
    border-bottom: 2px solid #b9d8ff !important;
}

#detail-table tbody tr.new-item-row td:first-child {
    box-shadow: inset 5px 0 0 #0d6efd;
}

#detail-table tbody tr.new-item-row .new-item-input {
    min-width: 70px;
    height: 34px;
    border: 1px solid #7aa7e8;
    background: #fff;
    box-shadow: 0 0 0 2px rgba(13,110,253,.05);
}

#detail-table tbody tr.new-item-row .new-item-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 3px rgba(13,110,253,.12);
}

.new-item-field-wrap,
.new-item-photo-wrap {
    position: relative;
}

.new-item-badge {
    display: inline-block;
    margin-bottom: 4px;
    padding: 2px 6px;
    border-radius: 999px;
    background: #0d6efd;
    color: #fff;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .5px;
}

.new-image-editor {
    min-width: 92px;
    text-align: center;
    outline: none;
    cursor: pointer;
}

.new-image-editor.paste-target {
    box-shadow: 0 0 0 2px rgba(13,110,253,.18);
    border-radius: 8px;
}

.new-act-placeholder {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    color: #aab3c2;
    font-size: 16px;
    font-weight: 700;
}

.new-image-preview {
    width: 76px;
    height: 58px;
    margin: 0 auto 5px;
    border: 1px dashed #9db5d5;
    border-radius: 8px;
    background: #f8fbff;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    color: #7a8aa3;
    font-size: 9px;
}

.new-image-preview img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.new-image-actions {
    display: flex;
    justify-content: center;
    gap: 3px;
    flex-wrap: wrap;
}

.new-image-actions .btn {
    font-size: 9px;
    padding: 3px 6px;
}

.paste-success {
    position: absolute;
    right: 3px;
    bottom: 3px;
    padding: 2px 5px;
    border-radius: 5px;
    background: #198754;
    color: #fff;
    font-size: 9px;
    font-weight: 700;
}

#btn-save-all {
    margin: 0 0 12px 0;
    border: 0;
    border-radius: 8px;
    padding: 8px 14px;
    box-shadow: 0 4px 10px rgba(25, 135, 84, .14);
}

/* Horizontal + vertical scroll area.
   Sticky THEAD tetap bekerja saat body table discroll. */
.freeze-wrapper {
    position: relative;
    width: 100%;
    max-height: 68vh;
    overflow: auto;
    border: 1px solid #dfe5ef;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 5px 18px rgba(31, 52, 95, .07);
    scrollbar-width: thin;
    scrollbar-color: #aab5c9 #f1f4f8;
}

.freeze-wrapper::-webkit-scrollbar {
    width: 9px;
    height: 10px;
}

.freeze-wrapper::-webkit-scrollbar-track {
    background: #f1f4f8;
}

.freeze-wrapper::-webkit-scrollbar-thumb {
    background: #aab5c9;
    border-radius: 10px;
    border: 2px solid #f1f4f8;
}

#detail-table {
    margin: 0;
    width: max-content;
    min-width: 100%;
    table-layout: fixed;
    border-collapse: separate;
    border-spacing: 0;
    color: #25324b;
    font-size: 12px;
}

#detail-table th,
#detail-table td {
    border-right: 1px solid #e3e8f0;
    border-bottom: 1px solid #e8ecf2;
    vertical-align: middle;
}

#detail-table thead th {
    position: sticky;
    background: #2f437f;
    color: #fff;
    text-align: center;
    vertical-align: middle;
    font-weight: 700;
    white-space: nowrap;
    z-index: 20;
    box-shadow: 0 1px 0 rgba(255,255,255,.08);
}

#detail-table thead .header-top th {
    top: 0;
    height: 38px;
    padding: 7px 10px;
    font-size: 11px;
    letter-spacing: .35px;
}

#detail-table thead .header-bottom th {
    top: 38px;
    height: 30px;
    padding: 5px 8px;
    font-size: 11px;
    z-index: 21;
}

#detail-table thead .dimension-group {
    letter-spacing: .55px;
    border-left: 1px solid rgba(255,255,255,.22);
    border-right: 1px solid rgba(255,255,255,.22);
}

#detail-table thead .dimension-sub {
    width: 56px !important;
    min-width: 56px !important;
    max-width: 56px !important;
}

#detail-table thead .item-group {
    background: #344b8c;
}

#detail-table thead .packing-group {
    background: #344b8c;
}

#detail-table tbody td {
    height: 58px;
    padding: 8px 10px;
    background: #fff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#detail-table tbody tr:nth-child(even) td {
    background: #f9fbfd;
}

#detail-table tbody tr:hover td {
    background: #eef4ff;
}

#detail-table tbody tr.editing td {
    background: #fff8e1 !important;
}

#detail-table .dimension-cell {
    text-align: center;
    font-variant-numeric: tabular-nums;
    font-weight: 600;
    color: #33415f;
}

#detail-table .number-cell {
    text-align: right;
    font-variant-numeric: tabular-nums;
}

#detail-table .description-cell {
    text-align: left;
    font-weight: 600;
    color: #17233d;
}

#detail-table .photo-cell {
    text-align: center;
}

#detail-table .photo-cell img {
    display: block;
    width: 54px;
    height: 54px;
    object-fit: contain;
    margin: auto;
    border-radius: 8px;
    background: #f7f8fb;
    border: 1px solid #e5e9f0;
}

#detail-table .cell-text {
    display: inline-block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    vertical-align: middle;
}

#detail-table .inline-input {
    min-width: 70px;
    height: 32px;
    padding: 4px 7px;
    border-radius: 6px;
    border: 1px solid #b8c4d8;
    font-size: 12px;
}

#detail-table .inline-input:focus {
    border-color: #2f437f;
    box-shadow: 0 0 0 2px rgba(47,67,127,.12);
}

#detail-table .btn-xs {
    margin: 2px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 700;
}

#detail-table tfoot td {
    position: sticky;
    bottom: 0;
    z-index: 10;
    padding: 9px 10px;
    font-weight: 700;
    white-space: nowrap;
}

/* Keep first visible detail column readable on horizontal scroll.
   This does not alter the existing horizontal scrolling behavior. */
#detail-table .sticky-col {
    position: sticky;
    left: 0;
    z-index: 22;
    background: #fff !important;
    box-shadow: 5px 0 10px -10px rgba(20,35,65,.5);
}

#detail-table thead .sticky-col {
    z-index: 30;
    background: #2f437f !important;
}

#detail-table tbody tr:nth-child(even) .sticky-col {
    background: #f9fbfd !important;
}

#detail-table tbody tr:hover .sticky-col {
    background: #eef4ff !important;
}

@media (max-width: 768px) {
    .freeze-wrapper {
        max-height: 64vh;
        border-radius: 8px;
    }

    #detail-table {
        font-size: 11px;
    }

    #detail-table thead .header-top th {
        height: 34px;
        font-size: 10px;
    }

    #detail-table thead .header-bottom th {
        top: 34px;
        height: 28px;
    }
}
</style>
<div class="padding">
    <div class="box">
        <div class="box-header">
            <h2>Release PFI</h2>
            <small>___</small>
        </div>
        <input type="hidden" id="role" value="{{ auth()->user()->role }}">
        <input type="hidden" id="id" value="{{ auth()->user()->role }}">
        <div class="box-body">
            <div class="box-header d-flex justify-content-between align-items-center flex-wrap gap-3">

                {{-- LEFT : SEARCH --}}
                <div style="max-width:350px; width:100%;">

                    <input type="text"
                        id="search-qc"
                        class="form-control"
                        placeholder="Search PO / Item / Vendor">

                </div>

                {{-- RIGHT : FILTER --}}
                <div style="width:180px;">

                    <select id="filter-spk-type"
                        class="form-control">

                        <option value="">
                            Semua
                        </option>

                        <option value="NW">
                            NW
                        </option>

                        <option value="NWS">
                            NWS
                        </option>

                    </select>

                </div>

            </div>
            <!--<div class="col-12 d-flex justify-content-end">-->
            <!--    <a href="/semua-spk"-->
            <!--        class="btn btn-primary btn-sm">-->
            <!--        All SPK-->
            <!--    </a>-->
            <!--</div>-->
            <div class="row" id="default-table">
                <div class="col-sm-12">
                    <div class="box">
                      <div class="table-responsive po-wrapper">

                              <table id="po-table"  class="table-bordered">
                                <thead>
                                    <tr class="">
                                       <th
                                            id="sort-order-no"
                                            style="cursor:pointer;white-space:nowrap;user-select:none;">

                                            Order No

                                            <i
                                                id="sort-order-icon"
                                                class="fa fa-sort ml-1"
                                                style="color:white">
                                            </i>

                                        </th>
                                        <th>Company Name</th>
                                        <th>Country</th>

                                        <th>Release date</th>
                                        <th>Shipment Date</th>
                                           <th>Category</th>
                                        <th>Actual Ship</th>
                                     @if(in_array(strtolower(auth()->user()->role), ['marketing','sales','export','finance']))
    <th>Value</th>
@endif
                                        <th>Cont Numb</th>
                                        <th>DO Released</th>
                                        <th>Remark</th>

                                        <th width="120">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="po-table-body">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            Loading...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" id="detail-view" style="display:none;">
                <div class="col-sm-12">
                    <div class="box">
                        <div class="box-body">
                            <button class="btn btn-default btn-sm" id="btn-back">
                                ← Show All Order Release
                            </button>
                            <hr>
                            @php
                            $user = auth()->user();
                            $divisi = optional($user->karyawan->divisi)->nama;
                            @endphp
                            @if ($divisi == 'PURCHASING')
                            <div class="row">
                                <div class="col-12 d-flex justify-content-end">
                                    <a href="#"
                                        class="btn btn-primary btn-sm"
                                        id="btn-buat-spk">
                                        Buat SPK
                                    </a>
                                </div>
                            </div>
                            @endif
                            <table class="table table-bordered table-info">
                                <tr>
                                    <td width="200"><b>Order No</b></td>
                                    <td id="d-order"></td>
                                </tr>
                                <tr>
                                    <td><b>Company Name</b></td>
                                    <td id="d-company"></td>
                                </tr>
                                <tr>
                                    <td><b>Shipment Date</b></td>
                                    <td id="d-ship"></td>
                                </tr>
                                <tr>
                                    <td><b>Country</b></td>
                                    <td id="d-country"></td>
                                </tr>

                            </table>
                        </div>
                        <div id="detail-action-bar">
                            <button id="btn-save-all"
                                class="btn btn-success btn-sm">
                                💾 Save All Changes
                            </button>

                            @if (strtolower((string) auth()->user()->name) === 'rodiyah')
                                <button id="btn-add-item"
                                    type="button"
                                    class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus"></i> Add Items
                                </button>
                            @endif
                        </div>
                        <div class="freeze-wrapper">
                            <table class="table table-bordered table-striped" id="detail-table">
                                <thead id="detail-table-head"></thead>
                                <tbody id="detail-item-table"></tbody>
                                <tfoot id="detail-table-foot"></tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- CHAT MODAL -->
    <div class="modal fade" id="chatModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Chat Room</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="chat-box" style="height:400px; overflow:auto; background:#f5f5f5; padding:10px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="chat-room-id">
                    <div class="input-group">
                        <input type="text" id="chat-input" class="form-control" placeholder="Type message...">
                        <span class="input-group-btn">
                            <button class="btn btn-primary" id="btn-send-chat">Send</button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="save-status">
    ✔ All changes saved
    </div>
     <div id="loadingOverlay">
    <div class="loading-content">
        <div class="spinner-border text-primary"
             style="width:60px;height:60px"></div>

        <h5 class="mt-3">
            Sedang mengambil data...
        </h5>

        <small class="text-muted">
            Mohon tunggu sebentar
        </small>
    </div>
</div>
    <pre id="result"></pre>
    @push('scripts')
    <script>
        let orderSort = 'asc';
const currentUsername = @json(auth()->user()->name);
window.currentUsername = currentUsername;
</script>
    <script>
        const CURRENT_USER_ID = {{    auth() -> id()   }};
    </script>
    @push('scripts')
    <script>
        /* =====================================================
   INIT PAGE (WAJIB UNTUK PJAX)
===================================================== */
 /* ===== ROLE ===== */
            const role = ($('#role').val() || '').toLowerCase();
            const canEdit = ['marketing','sales','export', 'finance'].includes(role);
            const canSeeValue = ['marketing','sales','export', 'finance'].includes(role);
        function initPage() {
            console.log('INIT PAGE JALAN');



            if (role !== 'marketing') {
                $('#btn-save-all')
                    .prop('disabled', true)
                    .addClass('disabled');
            }
            /* ===== LOAD TABLE ===== */
            loadPoTable();
            $('#search-qc')
                .off('keyup')
                .on('keyup', function() {

                    loadPoTable(
                        this.value,
                        $('#filter-spk-type').val()
                    );

                });
            /* ===== BACK BUTTON ===== */
            $('#btn-back')
                .off('click')
                .on('click', function() {
                    $('#detail-view').hide();
                    $('#default-table').show();
                });
            $('#filter-spk-type')
                .off('change')
                .on('change', function() {

                    loadPoTable(
                        $('#search-qc').val(),
                        $(this).val()
                    );

                });
        }
        /* =====================================================
           LOAD TABLE
        ===================================================== */
        // const role = ($('#role').val() || '').toLowerCase();

        function canEditField(field){

            switch(role){

                case 'marketing':
                    return true;

                case 'export':
                    return [
                        'act_ship',
                        'cont_numb',
                        'do_released'
                    ].includes(field);

                default:
                    return false;
            }

        }
            function editableTd(field, value, id, type='text'){

            let editable = canEditField(field);

            return `
                <td>
                    <input
                        type="${type}"
                        class="po-edit ${editable ? '' : 'readonly-input'}"
                        data-id="${id}"
                        data-field="${field}"
                        value="${value ?? ''}"
                        ${editable ? '' : 'readonly tabindex="-1"'}
                    >
                </td>
            `;
        }
        // dedaline
        function deadlineProgress(releaseDate, shipmentDate){

    if(!releaseDate || !shipmentDate){
        return '<span class="text-muted">-</span>';
    }

    let start = new Date(releaseDate);
    let end   = new Date(shipmentDate);
    let now   = new Date();

    let totalDays = Math.ceil((end-start)/(1000*60*60*24));

    if(totalDays <= 0){
        return '-';
    }

    let elapsedDays = Math.ceil((now-start)/(1000*60*60*24));

    let percent = (elapsedDays/totalDays)*100;

    percent = Math.max(0,Math.min(100,percent));

    let barColor = '#28a745';
    let textColor = '#2F437F';

    if(percent >= 60){
        barColor='#f0ad4e';
        textColor='#2F437F';
    }

    if(percent >= 85){
        barColor='#dc3545';
        textColor='#dc3545';
    }

    // ===== OVERDUE =====

    if(now > end){

        let late = Math.ceil((now-end)/(1000*60*60*24));

        return `
        <div style="min-width:180px">

            <div class="progress" style="height:18px;border-radius:20px">

                <div class="progress-bar"
                    style="
                        width:100%;
                        background:#dc3545;
                        font-size:11px;
                    ">
                    100%
                </div>

            </div>

            <div style="
                font-size:11px;
                margin-top:3px;
                color:#dc3545;
                font-weight:bold;
            ">
                ${totalDays} / ${totalDays} Hari
                <br>
                🔴 Late ${late} Hari
            </div>

        </div>
        `;
    }

    let sisa = Math.ceil((end-now)/(1000*60*60*24));

    return `
    <div style="min-width:180px">

        <div class="progress" style="height:18px;border-radius:20px">

            <div class="progress-bar"
                style="
                    width:${percent}%;
                    background:${barColor};
                    font-size:11px;
                ">
                ${Math.round(percent)}%
            </div>

        </div>

        <div style="
            font-size:11px;
            margin-top:3px;
            color:${textColor};
            font-weight:bold;
        ">

            ${Math.max(0,elapsedDays)} / ${totalDays} Hari
            <br>

            🟢 Sisa ${sisa} Hari

        </div>

    </div>
    `;
}
        function loadPoTable(keyword = '', type = '') {
           fetch(
`{{ route('marketing.ajax.po') }}
?q=${encodeURIComponent(keyword)}
&type=${encodeURIComponent(type)}
&sort=${orderSort}`
)
                .then(res => res.json())
                .then(data => {
                   data.forEach(po => {
    console.log(po.shipment_date);
});
                    const tbody = document.getElementById('po-table-body');

                    if (!tbody) return;
                    tbody.innerHTML = '';
                    if (!data.length) {
                        tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            Belum ada data
                        </td>
                    </tr>`;
                        return;
                    }
                    data.forEach(po => {
                        let deleteButton = '';

    if (currentUsername.toLowerCase() === 'rodiyah') {
        deleteButton = `
            <button
                class="btn btn-danger btn-xs btn-delete-po"
                data-id="${po.id}">
                Delete
            </button>
        `;
    }
                     tbody.innerHTML += `
                       <tr>

                            ${editableTd('order_no',po.order_no,po.id)}

                            ${editableTd('company_name',po.company_name,po.id)}

                            ${editableTd('country',po.country,po.id)}

                            ${editableTd('release_date',po.release_date,po.id,'date')}

                            ${editableTd('shipment_date',normalizeDate(po.shipment_date),po.id,'date')}

                            <td>
                                ${deadlineProgress(
                                    normalizeDate(po.release_date),
                                    normalizeDate(po.shipment_date)
                                )}
                            </td>

                            ${editableTd('act_ship',po.act_ship,po.id,'date')}

                          ${canSeeValue
    ? editableTd('value', po.value, po.id, 'number')
    : ''
}

                            ${editableTd('cont_numb',po.cont_numb,po.id)}

                            ${editableTd('do_released',po.do_released,po.id)}

                            ${editableTd('remark',po.remark,po.id)}

                            <td>
                                <button class="btn btn-success btn-xs btn-view"
                                    data-id="${po.id}">
                                    View
                                </button>

                                ${deleteButton}
                            </td>

                        </tr>
                        `;
                    });
                })
                .catch(err => console.error(err));
        }
        /* =====================================================
           VIEW DETAIL
        ===================================================== */
        // normalize
      function normalizeDate(date){

    if(!date || date == '-') return '';

    date = date.trim();

    // Kalau sudah format YYYY-MM-DD
    if(/^\d{4}-\d{2}-\d{2}$/.test(date)){
        return date;
    }

    // Buang tulisan dalam kurung
    date = date.replace(/\(.*?\)/g,'');

    const months = {
        january:'01',
        february:'02',
        march:'03',
        april:'04',
        may:'05',
        june:'06',
        july:'07',
        august:'08',
        september:'09',
        october:'10',
        november:'11',
        december:'12'
    };

    let m = date.match(/^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})$/i);

    if(!m) return '';

    let day = m[1].padStart(2,'0');
    let month = months[m[2].toLowerCase()];

    return `${m[3]}-${month}-${day}`;
}
        // update
        $(document).on('change','.po-edit',function(){
         if($(this).prop('readonly')){
        return;
    }
        let input=$(this);

        $.ajax({
            beforeSend:function(){

                showSaving();

            },
            url:'/marketing/po/update-field',

            type:'POST',

            headers:{
                'X-CSRF-TOKEN':
                $('meta[name="csrf-token"]').attr('content')
            },

            data:{
                id:input.data('id'),
                field:input.data('field'),
                value:input.val()
            },

            success:function(){
                 showSaved();
                input.addClass('is-valid');

                setTimeout(function(){

                    input.removeClass('is-valid');

                },800);

            }

        });

    });
        // delete
        $(document).off('click', '.btn-delete-po')
.on('click', '.btn-delete-po', function () {

    let id = $(this).data('id');

    Swal.fire({
        title: 'Yakin hapus?',
        text: 'Data PO dan detail akan dihapus',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33'
    })
    .then((result) => {

        if (!result.isConfirmed) return;

        $.ajax({

            url: '/marketing/po-delete/' + id,
            type: 'DELETE',

            headers: {
                'X-CSRF-TOKEN':
                    $('meta[name="csrf-token"]').attr('content')
            },

            success: function (res) {

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message
                });

                loadPoTable();

            },

            error: function (xhr) {

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: xhr.responseJSON?.message || 'Delete gagal'
                });

            }

        });

    });

});
        $(document).off('click', '.btn-view').on('click', '.btn-view', function() {
            let id = $(this).data('id');
            $('#btn-buat-spk').attr('href', '/spk/' + id);
            fetch(`/marketing/po-detail/${id}`)
                .then(res => res.json())
                .then(res => {
                    $('#default-table').hide();
                    $('#detail-view').show();
                    $('#d-order').text(res.po.order_no);
                    $('#d-company').text(res.po.company_name);
                    $('#d-ship').text(res.po.shipment_date ?? '-');
                    $('#d-country').text(res.po.country ?? '-');
                    let thead = $('#detail-table-head');
                    let tbody = $('#detail-item-table');
                    let foot = $('#detail-table-foot');
                    thead.html('');
                    tbody.html('');
                    foot.html('');
                    if (!res.items.length) return;
                    // =====================================================
                    // COLLECT ALL KEYS FROM ALL ITEMS
                    // =====================================================
                    let allKeysSet = new Set();

                    res.items.forEach(item => {
                        const detail = item.detail || {};
                        Object.keys(detail).forEach(key => allKeysSet.add(key));
                    });

                    let allKeys = [...allKeysSet];

                    // =====================================================
                    // HEADER PRIORITY
                    // =====================================================
                    const priority = [
                        'no_', 'photo', 'description', 'article_nr_', 'article_nr_nw',
                        'nw_code', 'sub_category', 'qty', 'remark', 'cushion', 'glass',
                        'item_w', 'item_d', 'item_h',
                        'pack_w', 'pack_d', 'pack_h',
                        'composition', 'finishing', 'cbm', 'total_cbm',
                        'value_in_usd', 'fob_jakarta_in_usd'
                    ];

                    const role = $('#role').val();

                    // =====================================================
                    // BUILD KEYS
                    // =====================================================
                    let keys = [
                        ...priority.filter(k => allKeys.includes(k)),
                        ...allKeys.filter(k => !priority.includes(k))
                    ];

                    // =====================================================
                    // ROLE FILTER
                    // =====================================================
                    if (role !== 'marketing') {
                        keys = keys.filter(k =>
                            k !== 'value_in_usd' &&
                            k !== 'fob_jakarta_in_usd'
                        );
                    }

                    // =====================================================
                    // INSERT ACT COLUMN BEFORE QTY
                    // =====================================================
                    let qtyIndex = keys.indexOf('qty');
                    if (qtyIndex !== -1) {
                        keys.splice(qtyIndex, 0, 'act');
                    }

                    // =====================================================
                    // INDEX
                    // =====================================================
                    let descIndex = keys.indexOf('description');
                    let cbmIndex = keys.includes('total_cbm')
                        ? keys.indexOf('total_cbm')
                        : keys.indexOf('cbm');
                    let priceIndex = keys.includes('value_in_usd')
                        ? keys.indexOf('value_in_usd')
                        : keys.indexOf('fob_jakarta_in_usd');

                    // =====================================================
                    // NORMALIZE PACKING ALIAS UNTUK DISPLAY
                    // packing_w/d/h tetap didukung jika data lama memakainya.
                    // =====================================================
                    const packingAlias = {
                        packing_w: 'pack_w',
                        packing_d: 'pack_d',
                        packing_h: 'pack_h'
                    };

                    keys = keys.map(k => packingAlias[k] || k);

                    // Hilangkan duplikat akibat pack_* + packing_* muncul bersamaan.
                    keys = [...new Set(keys)];

                    // Dipakai oleh modal Add Items. ACT bukan field detail_po.
                    window.currentMarketingPoId = id;
                    window.currentDetailKeys = keys;
                    window.currentDetailItems = res.items || [];

                    // =====================================================
                    // COLUMN WIDTHS
                    // Ini yang menghilangkan jarak besar antar W/D/H.
                    // =====================================================
                    const columnWidth = key => {
                        const widths = {
                            no_: 58,
                            photo: 82,
                            description: 220,
                            article_nr_: 120,
                            article_nr_nw: 120,
                            nw_code: 110,
                            sub_category: 130,
                            act: 115,
                            qty: 70,
                            remark: 180,
                            cushion: 90,
                            glass: 80,
                            item_w: 56,
                            item_d: 56,
                            item_h: 56,
                            pack_w: 56,
                            pack_d: 56,
                            pack_h: 56,
                            composition: 180,
                            finishing: 180,
                            cbm: 78,
                            total_cbm: 92,
                            value_in_usd: 125,
                            fob_jakarta_in_usd: 145
                        };

                        return widths[key] || 120;
                    };

                    // =====================================================
                    // COLGROUP - WIDTH TETAP
                    // =====================================================
                    $('#detail-table colgroup').remove();
                    let colgroup = $('<colgroup></colgroup>');
                    keys.forEach(k => {
                        colgroup.append(`<col style="width:${columnWidth(k)}px;min-width:${columnWidth(k)}px;">`);
                    });
                    $('#detail-table').prepend(colgroup);

                    // =====================================================
                    // HEADER 2 LEVEL
                    // =====================================================
                    let headerTop = $('<tr class="header-top"></tr>');
                    let headerBottom = $('<tr class="header-bottom"></tr>');

                    const itemDimensionKeys = ['item_w', 'item_d', 'item_h'];
                    const packingDimensionKeys = ['pack_w', 'pack_d', 'pack_h'];

                    const itemDimensionCount = itemDimensionKeys.filter(k => keys.includes(k)).length;
                    const packingDimensionCount = packingDimensionKeys.filter(k => keys.includes(k)).length;

                    const addSubHeader = (k, label) => {
                        headerBottom.append(`
                            <th class="dimension-sub" data-header-key="${k}">${label}</th>
                        `);
                    };

                    keys.forEach((k, i) => {
                        let cls = (i === descIndex) ? 'sticky-col' : '';

                        // ITEM DIMENSION GROUP
                        if (k === 'item_w') {
                            if (itemDimensionCount > 0) {
                                headerTop.append(`
                                    <th colspan="${itemDimensionCount}" class="dimension-group item-group">
                                        ITEM DIMENSION
                                    </th>
                                `);
                            }

                            if (keys.includes('item_w')) addSubHeader('item_w', 'W');
                            if (keys.includes('item_d')) addSubHeader('item_d', 'D');
                            if (keys.includes('item_h')) addSubHeader('item_h', 'H');
                            return;
                        }

                        if (k === 'item_d' || k === 'item_h') return;

                        // PACKING DIMENSION GROUP
                        if (k === 'pack_w') {
                            if (packingDimensionCount > 0) {
                                headerTop.append(`
                                    <th colspan="${packingDimensionCount}" class="dimension-group packing-group">
                                        PACKING DIMENSION
                                    </th>
                                `);
                            }

                            if (keys.includes('pack_w')) addSubHeader('pack_w', 'W');
                            if (keys.includes('pack_d')) addSubHeader('pack_d', 'D');
                            if (keys.includes('pack_h')) addSubHeader('pack_h', 'H');
                            return;
                        }

                        if (k === 'pack_d' || k === 'pack_h') return;

                        let label = k === 'act'
                            ? 'ACT'
                            : k.replaceAll('_', ' ').toUpperCase();

                        headerTop.append(`
                            <th rowspan="2" class="${cls}">${label}</th>
                        `);
                    });

                    thead.append(headerTop);
                    thead.append(headerBottom);
                    // =========================
                    // TOTAL
                    // =========================
                    let totalCbm = 0;
                    let totalPrice = 0;
                    // =========================
                    // BODY
                    // =========================
                    res.items.forEach(item => {
                        let detail = item.detail || {};
                        let row = $(`<tr class="editable-row" data-id="${item.id}"></tr>`);
                        let rawCode =
                            detail.article_code ||
                            detail.article_nr_ ||
                            detail.nw_code ||
                            '';
                        let articleCode = encodeURIComponent(rawCode);
                        keys.forEach((key, i) => {
                            let cls = (i === descIndex) ? 'sticky-col' : '';
                            // 🔥 ACT COLUMN
                            if (key === 'act') {
                                let rawCode =
                                    // detail.article_code ||
                                    detail.article_nr_ ||
                                    detail.nw_code ||
                                    '';
                                let articleCode = encodeURIComponent(rawCode);
                                row.append(`
        <td class="${cls}">
            <a href="/cad/${articleCode}" class="btn btn-xs btn-primary">CAD</a>
          <button class="btn btn-xs btn-warning btn-chat"
    data-id="${item.id}">
    CHAT
</button>
        </td>
    `);
                                return;
                            }
                            // =====================================================
                            // VALUE + CELL CLASS
                            // =====================================================
                            let value = detail[key] ?? '';

                            // Backward compatibility untuk data lama.
                            if (value === '' && key === 'item_w') {
                                value = detail['dimention_(cm)'] ?? detail['dimension_(cm)'] ?? detail['dimension'] ?? '';
                            }
                            if (value === '' && key === 'item_d') {
                                value = detail['d'] ?? '';
                            }
                            if (value === '' && key === 'item_h') {
                                value = detail['h'] ?? '';
                            }
                            if (value === '' && key === 'pack_w') {
                                value = detail['packing_w'] ?? '';
                            }
                            if (value === '' && key === 'pack_d') {
                                value = detail['packing_d'] ?? '';
                            }
                            if (value === '' && key === 'pack_h') {
                                value = detail['packing_h'] ?? '';
                            }

                            let cellClasses = [cls];

                            if (itemDimensionKeys.includes(key) || packingDimensionKeys.includes(key)) {
                                cellClasses.push('dimension-cell');
                            }

                            if (key === 'description') {
                                cellClasses.push('description-cell');
                            }

                            if (key.includes('photo')) {
                                cellClasses.push('photo-cell');
                            }

                            if (['qty', 'cbm', 'total_cbm', 'value_in_usd', 'fob_jakarta_in_usd'].includes(key)) {
                                cellClasses.push('number-cell');
                            }

                            let td = $(`<td class="${cellClasses.filter(Boolean).join(' ')}" data-key="${key}"></td>`);
                            // FORMAT CBM
                            if ((key === 'cbm' || key === 'total_cbm') && value !== '') {
                                value = isNaN(value) ? '0.00' : parseFloat(value).toFixed(2);
                                totalCbm += parseFloat(value);
                            }
                            // FORMAT PRICE
                            if (key === 'value_in_usd' || key === 'fob_jakarta_in_usd') {
                                totalPrice += parseFloat(value || 0);
                            }
                            // IMAGE
                            if (key.includes('photo') && typeof value === 'string' && value.startsWith('http')) {
                                td.html(`<img src="${value}" alt="Photo">`);
                            } else {
                                td.html(`<span class="cell-text">${value}</span>`);
                            }
                            row.append(td);
                        });
                        tbody.append(row);
                    });
                    // =========================
                    // FOOTER
                    // =========================
                    foot.html(`
                <tr style="font-weight:bold;background:#f4f6f9">
                    ${emptyTds(cbmIndex)}
                    <td>TOTAL CBM</td>
                    <td>${isNaN(totalCbm) ? '0.00' : totalCbm.toFixed(2)}</td>
                    ${emptyTds(keys.length - cbmIndex - 2)}
                </tr>
                <tr style="font-weight:bold;background:#e8f5e9">
                    ${emptyTds(priceIndex)}
                    <td>TOTAL FOB PRICE</td>
                    <td>${totalPrice.toLocaleString('id-ID')}</td>
                    ${emptyTds(keys.length - priceIndex - 2)}
                </tr>
            `);
                });
        });
        /* =====================================================
           EDIT ROW (AMAN PJAX)
        ===================================================== */
        $(document).off('click', '.editable-row').on('click', '.editable-row', function() {
            const role = $('#role').val();
            if (role !== 'marketing') return;
            let row = $(this);
            $('.editable-row.editing').not(row).each(function() {
                exitEdit($(this));
            });
            if (row.hasClass('editing')) return;
            row.addClass('editing').css('background', '#fff8e1');
            row.find('td[data-key]').each(function() {
                let td = $(this);
                let key = td.data('key');
                if (key && key.toLowerCase().includes('photo')) return;
                let text = td.find('.cell-text').text().trim();
                td.attr('data-original', text);
                td.html(`
            <input type="text"
                class="form-control form-control-sm inline-input"
                value="${text}">
        `);
            });
        });
        /* =====================================================
           EXIT EDIT
        ===================================================== */
        function exitEdit(row) {
            row.removeClass('editing').css('background', '');
            row.find('td[data-key]').each(function() {
                let td = $(this);
                let input = td.find('input');
                if (!input.length) return;
                let val = input.val();
                td.html(`<span class="cell-text">${val}</span>`);
            });
        }
        /* =====================================================
           ADD ITEM INLINE - KHUSUS RODIYAH
           Add langsung menjadi BARIS PALING ATAS.
           Baris diberi marker NEW + mode editing.
        ===================================================== */
        function addItemLabel(key) {
            const labels = {
                no_: 'NO',
                photo: 'PHOTO',
                description: 'DESCRIPTION',
                article_nr_: 'ARTICLE NR',
                article_nr_nw: 'ARTICLE NR NW',
                nw_code: 'NW CODE',
                sub_category: 'SUB CATEGORY',
                qty: 'QTY',
                remark: 'REMARK',
                cushion: 'CUSHION',
                glass: 'GLASS',
                item_w: 'ITEM W',
                item_d: 'ITEM D',
                item_h: 'ITEM H',
                pack_w: 'PACK W',
                pack_d: 'PACK D',
                pack_h: 'PACK H',
                composition: 'COMPOSITION',
                finishing: 'FINISHING',
                cbm: 'CBM',
                total_cbm: 'TOTAL CBM',
                value_in_usd: 'VALUE IN USD',
                fob_jakarta_in_usd: 'FOB JAKARTA IN USD'
            };
            return labels[key] || key.replaceAll('_', ' ').toUpperCase();
        }

        function addItemInputType(key) {
            if (['qty','item_w','item_d','item_h','pack_w','pack_d','pack_h','cbm','total_cbm','value_in_usd','fob_jakarta_in_usd'].includes(key)) {
                return 'number';
            }
            return 'text';
        }

        function nextItemNo() {
            let maxNo = 0;
            (window.currentDetailItems || []).forEach(item => {
                const raw = item.detail?.no_;
                const n = parseInt(raw, 10);
                if (!isNaN(n)) maxNo = Math.max(maxNo, n);
            });
            return maxNo + 1;
        }

        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        function buildNewItemInput(key) {
            const value = key === 'no_' ? nextItemNo() : '';
            const type = addItemInputType(key);
            const isLong = ['description','remark','cushion','glass','composition','finishing','packaging'].includes(key);

            if (key.includes('photo')) {
                return `
                    <div class="new-image-editor" data-image-key="${key}" tabindex="0" title="Klik area foto lalu Ctrl+V untuk paste gambar dari Excel">
                        <input type="file"
                            class="new-photo-file"
                            accept="image/*"
                            style="display:none">
                        <input type="hidden"
                            class="new-photo-value"
                            data-key="${key}"
                            value="">
                        <div class="new-image-preview">
                            <span class="new-image-empty">
                                <i class="fa fa-image"></i>
                                <br>Browse / Paste
                            </span>
                        </div>
                        <div class="new-image-actions">
                            <button type="button" class="btn btn-xs btn-primary btn-browse-new-image">
                                <i class="fa fa-folder-open"></i> Browse
                            </button>
                            <button type="button" class="btn btn-xs btn-default btn-paste-help">
                                <i class="fa fa-paste"></i> Paste
                            </button>
                        </div>
                    </div>
                `;
            }

            const input = isLong
                ? `<textarea class="form-control form-control-sm new-item-input" data-key="${key}" placeholder="${addItemLabel(key)}"></textarea>`
                : `<input type="${type}" class="form-control form-control-sm new-item-input" data-key="${key}" value="${escapeHtml(value)}" placeholder="${addItemLabel(key)}" ${type === 'number' ? 'step="any"' : ''}>`;

            return input;
        }

        function createNewItemRow() {
            if (!window.currentMarketingPoId) {
                alert('PO belum dipilih');
                return;
            }

            const tbody = $('#detail-item-table');
            if (!tbody.length) return;

            // Jangan membuat 2 baris baru sekaligus.
            const existingNew = tbody.find('tr.new-item-row');
            if (existingNew.length) {
                existingNew.find('.new-item-input').first().focus();
                return;
            }

            const keys = (window.currentDetailKeys || []);
            if (!keys.length) {
                alert('Header item belum tersedia');
                return;
            }

            const row = $('<tr class="editable-row editing new-item-row" data-id="new"></tr>');

            keys.forEach((key, index) => {
                const cls = (key === 'description') ? 'sticky-col' : '';
                let html = buildNewItemInput(key);

                if (key === 'no_') {
                    html = `
                        <div class="new-item-field-wrap">
                            <span class="new-item-badge">NEW</span>
                            ${html}
                        </div>
                    `;
                }

                if (key === 'photo') {
                    html = `
                        <div class="new-item-photo-wrap">
                            <span class="new-item-badge">NEW</span>
                            ${html}
                        </div>
                    `;
                }

                if (key === 'act') {
                    // ACT adalah kolom CAD/CHAT pada item lama.
                    // Pada row NEW kolom ini sengaja dikosongkan agar
                    // posisi kolom setelahnya (terutama QTY) tidak bergeser.
                    html = '<span class="new-act-placeholder">—</span>';
                }

                row.append(`<td class="${cls}" data-key="${key}">${html}</td>`);
            });

            tbody.prepend(row);

            // Scroll kembali ke bagian paling atas agar baris baru terlihat.
            $('.freeze-wrapper').scrollTop(0);

            const firstInput = row.find('.new-item-input').first();
            if (firstInput.length) {
                firstInput.focus();
            }
        }

        $(document).off('click', '#btn-add-item').on('click', '#btn-add-item', function() {
            const username = (window.currentUsername || '').toLowerCase();
            if (username !== 'rodiyah') {
                alert('❌ Tidak ada akses');
                return;
            }
            createNewItemRow();
        });

        // Browse gambar dari gallery/file picker.
        $(document).off('click', '.btn-browse-new-image').on('click', '.btn-browse-new-image', function(e) {
            e.preventDefault();
            $(this).closest('.new-image-editor').find('.new-photo-file').trigger('click');
        });

        $(document).off('change', '.new-photo-file').on('change', '.new-photo-file', function() {
            const file = this.files?.[0];
            if (!file) return;
            uploadNewItemImage(file, $(this).closest('.new-image-editor'));
        });

        // Upload gambar ke storage Laravel.
        function uploadNewItemImage(file, editor) {
            if (!file || !editor?.length) return Promise.reject(new Error('File gambar tidak valid'));

            const formData = new FormData();
            formData.append('image', file);

            editor.attr('data-uploading', '1');
            editor.find('.new-image-empty').html('<i class="fa fa-spinner fa-spin"></i><br>Uploading...');

            return fetch('/marketing/po-detail/upload-image', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: formData
            })
            .then(async res => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    throw new Error(data.message || 'Upload gambar gagal');
                }
                return data;
            })
            .then(data => {
                const url = data.url || data.path;
                if (!url) throw new Error('URL gambar tidak diterima server');

                editor.find('.new-photo-value').val(url);
                editor.find('.new-image-preview').html(`<img src="${escapeHtml(url)}" alt="Preview">`);
                editor.attr('data-uploading', '0');
                return url;
            })
            .catch(err => {
                editor.attr('data-uploading', '0');
                editor.find('.new-image-preview').html(`
                    <span class="new-image-empty text-danger">
                        <i class="fa fa-exclamation-triangle"></i><br>${escapeHtml(err.message)}
                    </span>
                `);
                throw err;
            });
        }

        // =====================================================
        // PASTE IMAGE DARI EXCEL / CLIPBOARD
        // =====================================================
        let newItemPasteBusy = false;

        function getActiveNewImageEditor() {
            const focused = $(document.activeElement).closest('.new-image-editor');
            if (focused.length) return focused;

            const row = $('#detail-item-table tr.new-item-row');
            return row.find('.new-image-editor.paste-target').first();
        }

        async function uploadClipboardImageFromBrowser(editor) {
            if (!editor?.length || newItemPasteBusy) return false;

            // Chrome/Edge HTTPS: Clipboard API biasanya bisa membaca
            // image/png dari gambar yang di-copy dari Excel.
            if (!navigator.clipboard || !navigator.clipboard.read) {
                return false;
            }

            try {
                const clipboardItems = await navigator.clipboard.read();

                for (const clipboardItem of clipboardItems) {
                    const imageType = clipboardItem.types.find(type =>
                        type === 'image/png' ||
                        type === 'image/jpeg' ||
                        type === 'image/webp'
                    );

                    if (!imageType) continue;

                    const blob = await clipboardItem.getType(imageType);
                    const extension = imageType.split('/')[1] || 'png';
                    const file = new File(
                        [blob],
                        `excel-paste-${Date.now()}.${extension}`,
                        { type: imageType }
                    );

                    newItemPasteBusy = true;
                    try {
                        await uploadNewItemImage(file, editor);
                        editor.find('.paste-success').remove();
                        editor.find('.new-image-actions').append(
                            '<span class="paste-success">✓ Pasted</span>'
                        );
                    } finally {
                        newItemPasteBusy = false;
                    }

                    return true;
                }
            } catch (err) {
                console.debug('[NEW ITEM] Clipboard API:', err);
            }

            return false;
        }

        async function handleNewItemImagePaste(event) {
            const row = $('#detail-item-table tr.new-item-row');
            if (!row.length) return;

            const editor = getActiveNewImageEditor();
            if (!editor.length) return;

            const clipboard = event.clipboardData;
            if (!clipboard) return;

            let file = null;

            // Cara pertama: ClipboardEvent langsung menyediakan image/png.
            for (const item of clipboard.items) {
                if (item.type && item.type.startsWith('image/')) {
                    file = item.getAsFile();
                    if (file) break;
                }
            }

            // Cara kedua: Excel/browser memasukkan image sebagai HTML data URI.
            if (!file) {
                const html = clipboard.getData('text/html');
                const match = html && html.match(
                    /<img[^>]+src=["']([^"']+)["']/i
                );

                if (match && match[1] && match[1].startsWith('data:image/')) {
                    try {
                        const response = await fetch(match[1]);
                        const blob = await response.blob();
                        file = new File(
                            [blob],
                            `excel-paste-${Date.now()}.png`,
                            { type: blob.type || 'image/png' }
                        );
                    } catch (err) {
                        console.debug('[NEW ITEM] HTML image:', err);
                    }
                }
            }

            if (!file) {
                // Jangan preventDefault kalau clipboard event tidak berisi image,
                // supaya Ctrl+V tetap normal untuk input teks.
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            if (newItemPasteBusy) return;

            newItemPasteBusy = true;
            try {
                await uploadNewItemImage(file, editor);
                editor.find('.paste-success').remove();
                editor.find('.new-image-actions').append(
                    '<span class="paste-success">✓ Pasted</span>'
                );
            } catch (err) {
                alert('❌ ' + (err.message || 'Gagal paste gambar'));
            } finally {
                newItemPasteBusy = false;
            }
        }

        // Klik area foto = jadikan target paste aktif.
        $(document).off('click.newItemImageFocus', '.new-image-editor')
            .on('click.newItemImageFocus', '.new-image-editor', function(e) {
                if ($(e.target).closest('button,input').length) return;

                $('.new-image-editor.paste-target').removeClass('paste-target');
                $(this).addClass('paste-target').trigger('focus');
            });

        // Tombol Paste juga langsung mengaktifkan area foto dan mencoba
        // Clipboard API dengan user gesture.
        $(document).off('click.newItemPaste', '.btn-paste-help')
            .on('click.newItemPaste', '.btn-paste-help', async function(e) {
                e.preventDefault();
                e.stopPropagation();

                const editor = $(this).closest('.new-image-editor');
                $('.new-image-editor.paste-target').removeClass('paste-target');
                editor.addClass('paste-target').trigger('focus');

                const pasted = await uploadClipboardImageFromBrowser(editor);

                if (!pasted) {
                    alert(
                        'Gambar belum terbaca dari clipboard. ' +
                        'Klik area PHOTO, lalu tekan Ctrl+V setelah menyalin gambar dari Excel.'
                    );
                }
            });

        // Paste langsung dari Ctrl+V.
        $(document).off('paste.newItemImage')
            .on('paste.newItemImage', handleNewItemImagePaste);

        // Fallback khusus Ctrl+V menggunakan Clipboard API.
        // Ini penting pada Chrome/Edge ketika Excel tidak mengirim image
        // sebagai ClipboardEvent image/* tetapi Clipboard API tetap bisa membacanya.
        $(document).off('keydown.newItemPaste')
            .on('keydown.newItemPaste', async function(e) {
                if (!(e.ctrlKey || e.metaKey) || e.key.toLowerCase() !== 'v') return;

                const editor = getActiveNewImageEditor();
                if (!editor.length) return;

                // Kalau sedang mengetik di field biasa, jangan ganggu paste teks.
                const active = document.activeElement;
                const isTextField = active &&
                    ['INPUT', 'TEXTAREA'].includes(active.tagName) &&
                    !$(active).closest('.new-image-editor').length;

                if (isTextField) return;

                const pasted = await uploadClipboardImageFromBrowser(editor);
                if (pasted) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });

        /* =====================================================
           SAVE - EXISTING + NEW ROW
        ===================================================== */
        $(document).off('click', '#btn-save-all').on('click', '#btn-save-all', function() {
            const role = $('#role').val();
            if (role !== 'marketing') {
                alert('❌ Tidak ada akses');
                return;
            }

            const button = $(this);
            const newRow = $('#detail-item-table tr.new-item-row');

            if (newRow.find('.new-image-editor[data-uploading="1"]').length) {
                alert('Mohon tunggu upload gambar selesai.');
                return;
            }

            let payload = [];
            let newItems = [];

            $('#detail-item-table tr.editable-row').each(function() {
                let row = $(this);

                // ==============================================
                // BARIS BARU
                // ==============================================
                if (row.hasClass('new-item-row')) {
                    let detail = {};

                    row.find('td[data-key]').each(function() {
                        const td = $(this);
                        const key = td.data('key');
                        if (!key || key === 'act') return;

                        if (key.toLowerCase().includes('photo')) {
                            detail[key] = td.find('.new-photo-value').val() || null;
                            return;
                        }

                        const input = td.find('.new-item-input');
                        let value = input.length ? input.val() : '';
                        value = value === null ? '' : String(value).trim();
                        detail[key] = value === '' ? null : value;
                    });

                    // Tidak dianggap kosong hanya karena sebagian field belum diisi.
                    // Yang penting ada minimal satu value nyata.
                    const hasValue = Object.values(detail).some(v => v !== null && String(v).trim() !== '');
                    if (hasValue) {
                        newItems.push(detail);
                    }
                    return;
                }

                // ==============================================
                // BARIS LAMA - LOGIC EXISTING TETAP
                // ==============================================
                let itemId = row.data('id');
                let changedData = {};

                row.find('td[data-key]').each(function() {
                    let td = $(this);
                    let key = td.data('key');
                    if (!key || key.toLowerCase().includes('photo')) return;
                    let newVal = td.find('input').length ?
                        td.find('input').val().trim() :
                        td.find('.cell-text').text().trim();
                    let oldVal = td.attr('data-original') ?? '';
                    if (newVal !== oldVal) {
                        changedData[key] = newVal;
                    }
                });

                if (Object.keys(changedData).length > 0) {
                    payload.push({
                        id: itemId,
                        detail: changedData
                    });
                }
            });

            if (!payload.length && !newItems.length) {
                alert('Tidak ada perubahan');
                return;
            }

            button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
            showSaving();

            const requests = [];

            if (payload.length) {
                requests.push(
                    fetch('/marketing/po-item-update-bulk', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        body: JSON.stringify({ items: payload })
                    }).then(async res => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok || !data.success) {
                            throw new Error(data.message || 'Gagal menyimpan perubahan item lama');
                        }
                        return data;
                    })
                );
            }

            newItems.forEach(detail => {
                requests.push(
                    fetch('/marketing/po-detail/add-item', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        body: JSON.stringify({
                            po_id: window.currentMarketingPoId,
                            detail: detail
                        })
                    }).then(async res => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok || !data.success) {
                            throw new Error(data.message || 'Gagal menambah item baru');
                        }
                        return data;
                    })
                );
            });

            Promise.all(requests)
                .then(() => {
                    showSaved();
                    $(`.btn-view[data-id="${window.currentMarketingPoId}"]`).trigger('click');
                })
                .catch(err => {
                    alert('❌ ' + (err.message || 'Gagal menyimpan'));
                    $('#save-status').hide();
                })
                .finally(() => {
                    button.prop('disabled', false).html('💾 Save All Changes');
                });
        });

        /* =====================================================
           ENTER = EXIT EDIT
        ===================================================== */
        $(document).off('keydown', '.inline-input').on('keydown', '.inline-input', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                exitEdit($(this).closest('tr'));
            }
        });

        /* =====================================================
           SAVE
        ===================================================== */
                /* =====================================================
           ENTER = EXIT EDIT
        ===================================================== */
        $(document).off('keydown', '.inline-input').on('keydown', '.inline-input', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                exitEdit($(this).closest('tr'));
            }
        });
        /* =====================================================
           HELPER
        ===================================================== */
        function emptyTds(count) {
            return '<td></td>'.repeat(count);
        }
        /* =====================================================
           TRIGGER
        ===================================================== */
        $(document).ready(initPage);
        $(document).on('pjax:end', initPage);
        function showSaving(){

                $('#save-status')
                    .stop(true, true)
                    .css('background', '#0d6efd')
                    .text('💾 Saving...')
                    .fadeIn(150);

            }

            function showSaved(){

                $('#save-status')
                    .css('background', '#198754')
                    .text('✔ All changes saved');

                setTimeout(function(){

                    $('#save-status').fadeOut();

                }, 1200);

            }
            $(document).on('click','#sort-order-no',function(){

    orderSort = orderSort === 'asc'
        ? 'desc'
        : 'asc';

    loadPoTable(
        $('#search-qc').val(),
        $('#filter-spk-type').val()
    );

});
    </script>

    @endpush
    @endpush
    @endsection
