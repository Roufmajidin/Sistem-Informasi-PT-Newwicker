@extends('master.master')
@section('title', 'SPK Editable')
@section('content')
@include('pages.spk.stylespk')
@php
$checkedTypes = $spk['checked_types'] ?? [];
@endphp
@php
$status = $spk['status'] ?? 'draft';
@endphp
<input type="hidden"
       id="view_only"
       value="{{ $viewOnly ? 1 : 0 }}">
<div class="box">
    <div class="box-header d-flex justify-content-between align-items-center">
        <h3>SPK PRODUKSI</h3>
        @if ($spk['mode'] === 'edit')
        <span class="warning">EDIT MODE</span>
        @else
        <span class="success">CREATE MODE</span>
        @endif
        <div style="min-width:180px">
            <label style="font-size:12px; margin-bottom:2px;"><b>Jenis SPK</b></label>
            <select name="spk_type" id="spk_type" class="form-control form-control-sm">
                <option value="">-- Pilih --</option>
                @foreach ($jenisSuppliers as $jenis)
                <option value="{{ strtolower($jenis->name) }}"
                    {{ strtolower($spk['type'] ?? '') == strtolower($jenis->name) ? 'selected' : '' }}>
                    {{ $jenis->name }}
                </option>
                @endforeach
            </select>
            <div style="margin-top:8px">
                <button type="button" class="btn btn-dark btn-sm w-100" id="btnRiwayatSpk">
                    🕘 Riwayat SPK
                </button>
            </div>
            <div style="margin-top:8px">
                <button type="button" class="btn btn-dark btn-sm w-100" id="previewBtn">
                    Preview
                </button>
            </div>
        </div>
    </div>
    <input type="hidden" id="spk_mode" value="{{ $spk['mode'] }}">
    <input type="hidden" id="spk_id" value="{{ $spk['id'] }}">
    <div class="box-body spk-wrapper" id="printArea">
        <table class="table table-bordered spk-table">
            {{-- HEADER --}}
            <!-- @include('pages.spk.header') -->
            <tr>
                <td colspan="6" style="border:none">
                    <img src="{{ asset('/assets/images/NEWWICKER WHITE.png') }}" height="80">
                </td>
                <td colspan="6" class="text-right" style="border:none; position:relative">
                    <div class="editable" id="itemSearch" contenteditable style="border:1px solid #ccc; padding:6px">
                        Ketik article / nama item
                    </div>
                    <div id="itemSuggest" class="suggest-box"></div>
                </td>
            </tr>
            <tr>
                <td colspan="12"></td>
            </tr>
            {{-- INFO --}}
            <tr>
                <td><b>NO SPK</b></td>
                <td colspan="1" class="editable no-spk" contenteditable>{{ $spk['no_spk'] }}</td>
                <td colspan="3"></td>
                <td><b>NO PO</b></td>
                <td colspan="3" class="editable no-po" contenteditable>{{ $spk['no_po'] }}</td>
                <td> <button id="btnSaveSpk" class="btn btn-success btn-sm">
                        💾 Save SPK
                    </button>
                    <div style="
    display:flex;
    gap:8px;
    align-items:center;
    margin-top:6px;
">
                        @if ($status != 'finished')
                        <button class="btn btn-sm btn-dark btn-status-spk" data-status="closed">
                            🔒 Close SPK
                        </button>
                        <button
                            id="btnCleanUnchecked"
                            class="btn btn-danger btn-sm"
                            style="display:none">

                            🗑 Bersihkan Item Tidak Dipilih
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            <tr>
                <td><b>Nama</b></td>
                <td colspan="4" style="position:relative">
                    <div contenteditable="true" class="editable" id="supplierInput">
                        {{ $spk['nama'] }}
                    </div>
                    <div id="supplierSuggest">
                    </div>
                </td>
                <td colspan="7"></td>
            </tr>
            <tr>
                <td><b>Tgl Terima</b></td>
                <td colspan="4" class="editable tgl-terima" contenteditable>{{ $spk['tgl_terima'] }}</td>
                <td colspan="7"></td>
            </tr>
            <tr>
                <td><b>Tgl Selesai</b></td>
                <td colspan="4" class="editable tgl-selesai" contenteditable>{{ $spk['tgl_selesai'] }}</td>
                <td colspan="7"></td>
            </tr>
            @include('pages.spk.partial2')
            {{-- ITEMS --}}
            @foreach ($spk['items'] as $item)
            <tr class="spk-rowa" data-detail-id="{{ $item['detail_id'] }}">
                <td class="text-center select-item-cell">
                    <input
                        type="checkbox"
                        class="spk-item-check">
                </td>
                <!-- KODE -->
                <td class="editable text-center kode-item delete-row" contenteditable>
                    {{ $item['kode'] }}
                </td>
                <!-- GAMBAR -->
                <td class="gambar-cell">
                    <div class="image-box gambar-cell" contenteditable onpaste="handlePaste(event,this)">
                        @foreach ($item['images'] as $img)
                        <img src="{{ $img }}" class="preview-img">
                        @endforeach
                    </div>
                    <input type="file" accept="image/*" multiple capture="environment"
                        onchange="uploadPreview(this)">
                </td>
                <!-- NAMA -->
                <td class="editable nama" contenteditable>
                    {{ $item['nama'] }}
                </td>
                <!-- CUSTOM COLUMN -->
                <!-- CUSTOM COLUMN -->
                @php
                $mainCustom = $item['custom_columns'][0] ?? [];
                @endphp
                @foreach ($spk['custom_headers'] ?? [] as $header)
                <td class="editable custom-column" contenteditable data-custom="{{ $header['key'] }}">
                    {{ $mainCustom[$header['key']] ?? '' }}
                </td>
                @endforeach
                <!-- P -->
                <td class="editable text-center p" contenteditable>
                    {{ $item['p'] }}
                </td>
                <!-- L -->
                <td class="editable text-center l" contenteditable>
                    {{ $item['l'] }}
                </td>
                <!-- T -->
                <td class="editable text-center t" contenteditable>
                    {{ $item['t'] }}
                </td>
                <!-- MATERIAL -->
                <td class="editable material" contenteditable>
                    {{ $item['material'] }}
                </td>
                <!-- PCS -->
                <td class="editable text-center pcs" contenteditable>
                    {{ $item['pcs'] }}
                </td>
                <!-- SET -->
                <td class="editable text-center set" contenteditable>
                    {{ $item['set'] }}
                </td>
                <!-- HARGA -->
                <td class="editable text-right harga" contenteditable>
                    {{ $item['harga'] }}
                </td>
                <!-- TOTAL -->
                <td class="text-right total">
                    0
                </td>
                <!-- CATATAN -->
                <td>
                    <div class="editable note-box" contenteditable onpaste="handlePaste(event,this)">
                        {!! $item['catatan']['remark'] ?? '' !!}
                    </div>
                </td>
                <!-- ACTION -->
                <td class="text-center">
                    <button type="button" class="btn-add-extra">
                        ➕
                    </button>
                </td>
            </tr>
            <!-- extra -->
            @foreach (array_slice($item['custom_columns'] ?? [], 1) as $extra)
            <tr class="extra-row">
                <td class="hallo"></td>
                <td class="hallo"></td>
                <td class="hallo"></td>
                @foreach ($spk['custom_headers'] ?? [] as $header)
                <td class="editable custom-column" contenteditable data-custom="{{ $header['key'] }}">
                    {{ $extra[$header['key']] ?? '' }}
                </td>
                @endforeach
                <td class="editable p" contenteditable>
                    {{ $extra['p'] ?? '-' }}
                </td>
                <td class="editable l" contenteditable>
                    {{ $extra['l'] ?? '-' }}
                </td>
                <td class="editable t" contenteditable>
                    {{ $extra['t'] ?? '-' }}
                </td>
                <td class="editable material" contenteditable>
                    {{ $extra['material'] ?? '-' }}
                </td>
                <td class="editable pcs" contenteditable>
                    {{ $extra['pcs'] ?? 0 }}
                </td>
                <td class="editable set" contenteditable>
                    {{ $extra['set'] ?? 0 }}
                </td>
                <td class="editable harga" contenteditable>
                    {{ $extra['harga'] ?? 0 }}
                </td>
                <td class="total">
                    {{ number_format($extra['total'] ?? 0) }}
                </td>
                <td class="editable harga" contenteditable>
                    {{ $extra['catatan'] ?? '' }}
                </td>
                <td class="text-center">
                    <button type="button" class="btn-delete-extra"
                        style="
                border:none;
                background:red;
                color:white;
                cursor:pointer;
                padding:2px 6px;
            ">
                        ❌
                    </button>
                </td>
            </tr>
            @endforeach
            @endforeach
            <tr id="spkItemAnchor"></tr>
            @include('pages.spk.partial1')
            <td colspan="3" style="vertical-align: top;margin-left:12px">
                <table class="table table-bordered" style="font-size:12px; width:100%; min-width:520px;">
                    <tr class="text-center">
                        <th width="5%">
                            Req
                        </th>
                        <th width="20%">
                            Amount
                        </th>
                        <th width="20%">
                            Date
                        </th>
                        <th width="70%">
                            Note
                        </th>
                      <th width="25%">
                        Keterangan
                    </th>

                    <th width="20%">
                        Adjustment Finance
                    </th>
                    </tr>
                    <tbody id="paymentBody">
                        @php
                        $payments = $spk['payments'] ?? [];
                        @endphp
                        @if (count($payments))
                        @foreach ($payments as $pay)
                        @php
                        $paymentId = $pay['payment_id'] ?? 'pay_' . uniqid();
                        @endphp
                       <tr
    class="payment-row"
    data-adjustment="{{ $pay['adjustment'] ?? 0 }}"
    data-payment-id="{{ $paymentId }}"

                            data-pr-id="{{ $pay['pr_id'] ?? '' }}">
                            <!-- CHECKBOX -->
                            <td class="text-center">
                                <input type="checkbox" class="payment-request-check"
                                    {{ $pay['is_request'] ?? false ? 'checked' : '' }}>
                            </td>
                            <!-- AMOUNT -->
                            <td class="editable total-amount" contenteditable>
                                {{ $pay['amount'] ?? '' }}
                            </td>
                            <!-- DATE -->
                            <td class="editable date-isian" contenteditable>
                                {{ $pay['date'] ?? '' }}
                            </td>
                            <!-- TYPE -->
                            <td>
                                <select class="form-control form-control-sm payment-type" style="width:70px">
                                    <option value="">-- Pilih --</option>
                                    <option value="dp"
                                        {{ ($pay['note'] ?? '') == 'dp' ? 'selected' : '' }}>
                                        DP
                                    </option>
                                    <option value="pelunasan"
                                        {{ ($pay['note'] ?? '') == 'pelunasan' ? 'selected' : '' }}>
                                        Pelunasan
                                    </option>
                                    <option value="bahan"
                                        {{ ($pay['note'] ?? '') == 'bahan' ? 'selected' : '' }}>
                                        Bahan
                                    </option>
                                       <option value="return_bahan"
                                        {{ ($pay['note'] ?? '') == 'return_bahan' ? 'selected' : '' }}>
                                        Return Bahan
                                    </option>

                                    <option value="kasbon"
                                        {{ ($pay['note'] ?? '') == 'kasbon' ? 'selected' : '' }}>
                                        Kasbon
                                    </option>
                                    <option value="ppn"
                                        {{ ($pay['note'] ?? '') == 'ppn' ? 'selected' : '' }}>
                                        PPN
                                    </option>
                                </select>
                            </td>
                            <!-- KETERANGAN -->
                            <td class="editable note-tambahan" contenteditable>
                                {{ $pay['note_tambahan'] ?? '' }}
                            </td>
                            {{-- finance adjusment --}}
                            <td>

                          @if(($pay['adjustment'] ?? 0) > 0)

                            <div style="color:#16a34a;font-weight:bold">
                                Bayar :
                                Rp {{ number_format(
                                    $pay['adjustment'],
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </div>

                            <div style="color:#dc2626">
                                Sisa :
                                Rp {{ number_format(
                                    ($pay['amount'] ?? 0) - ($pay['adjustment'] ?? 0),
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </div>

                        @elseif(($pay['is_request'] ?? false))

                            <span class="text-success">
                                Full Payment
                            </span>

                        @else

                            <span class="text-secondary">
                                Belum Request
                            </span>

                        @endif

                            </td>
                        </tr>
                        @endforeach
                        @else
                      @php
                        $paymentId = $pay['payment_id'] ?? 'pay_' . uniqid();
                        @endphp

                        <tr
                            class="payment-row"
                            data-payment-id="{{ $paymentId }}"
                            data-adjustment="{{ $pay['adjustment'] ?? 0 }}"
                            data-pr-id="{{ $pay['pr_id'] ?? '' }}">
                            <!-- CHECK -->

                            <td class="text-center">
                                <input type="checkbox" class="payment-request-check">
                            </td>
                            <!-- AMOUNT -->
                            <td class="editable total-amount" contenteditable>
                            </td>
                            <!-- DATE -->
                            <td class="editable date-isian" contenteditable>
                            </td>
                            <!-- TYPE -->
                            <td>
                                <select class="form-control form-control-sm payment-type">
                                    <option value="">
                                        -- Pilih --
                                    </option>
                                    <option value="dp">DP</option>
                                    <option value="pelunasan">Pelunasan</option>
                                    <option value="bahan">Bahan</option>
                                    <option value="return_bahan">Return Bahan</option>
                                    <option value="kasbon">Kasbon</option>
                                    <option value="ppn">PPN</option>
                                </select>
                            </td>
                            <!-- NOTE -->
                            <td class="editable note-tambahan" contenteditable>
                            </td>
                            <td>
                                -
                            </td>
                        </tr>
                        @endif
                    </tbody>
                    <div class="mb-2 text-end">
                        <button type="button" id="btnAddPayment" class="btn btn-primary btn-sm">
                            ➕ Add Row
                        </button>
                    </div>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-center">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4">
                                <div id="paymentSummary"
                                    style="
                        padding:10px;
                        font-size:13px;
                        line-height:1.8;
                    ">
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>

            </td>

        </table>
        @if(isset($spk['signature']) && $spk['signature'])

@php
$sign = $spk['signature'];
@endphp

<div class="card mt-4">
    <div class="card-header bg-dark text-white">
        Approval SPK
    </div>

    <div class="card-body">

        <table class="table table-bordered text-center">
            <tr>
                <th>Made By</th>
                <th>Checked By</th>
                <th>Approved By</th>
                <th>Supplier</th>
            </tr>

         <tr style="height:120px">

    {{-- MADE --}}
    <td>
        @if($sign->made_at)
            <img
                src="{{ asset('assets/signature/'.$sign->made_by.'.png') }}"
                style="max-height:80px">
        @endif
    </td>

    {{-- CHECKED --}}
    <td>
        <div class="row">

            {{-- Checker 1 --}}
            <div class="col-6 text-center">

                @if($sign->checked_at)

                    <img
                        src="{{ asset('assets/signature/'.$sign->checked_by.'.png') }}"
                        style="max-height:70px">

                @else

                    <button
                        class="btn btn-warning btn-sm btn-sign"
                        data-id="{{ $sign->id }}"
                        data-type="checked">
                        TAP TO SIGN
                    </button>

                @endif

            </div>

            {{-- Checker 2 --}}
            <div class="col-6 text-center">

                @if($sign->checked_at_2)

                    <img
                        src="{{ asset('assets/signature/'.$sign->checked_by_2.'.png') }}"
                        style="max-height:70px">

                @else

                    <button
                        class="btn btn-warning btn-sm btn-sign"
                        data-id="{{ $sign->id }}"
                        data-type="checked_2">
                        TAP TO SIGN
                    </button>

                @endif

            </div>

        </div>
    </td>

    {{-- APPROVED --}}
    <td>

        @if($sign->approved_at)

            <img
                src="{{ asset('assets/signature/'.$sign->approved_by.'.png') }}"
                style="max-height:80px">

        @else

            <button
                class="btn btn-success btn-sm btn-sign"
                data-id="{{ $sign->id }}"
                data-type="approved">
                TAP TO SIGN
            </button>

        @endif

    </td>

    {{-- SUPPLIER --}}
    <td>
        {{ $sign->supplier->name ?? '-' }}
    </td>

</tr>

<tr>

    {{-- MADE --}}
    <td class="text-center">

        <b>{{ $sign->madeBy->name ?? '-' }}</b>
        <br>

        <b>
            {{ strtoupper(
                $sign->madeBy?->karyawan?->divisi?->nama ?? '-'
            ) }}
        </b>
        <br>

        @if($sign->made_at)
            {{ $sign->made_at->format('d/m/Y H:i') }}
        @else
            Pending
        @endif

    </td>

    {{-- CHECKED --}}
    <td>

        <div class="row">

            {{-- Checker 1 --}}
            <div class="col-6 text-center">

                <b>
                    {{ $sign->checkedBy->name ?? '-' }}
                </b>
                <br>

                <b>
                    {{ strtoupper(
                        $sign->checkedBy?->karyawan?->divisi?->nama ?? '-'
                    ) }}
                </b>
                <br>

                @if($sign->checked_at)
                    {{ $sign->checked_at->format('d/m/Y H:i') }}
                @else
                    Pending
                @endif

            </div>

            {{-- Checker 2 --}}
            <div class="col-6 text-center">

                <b>
                    {{ $sign->checkedBy2->name ?? '-' }}
                </b>
                <br>

                <b>
                    {{ strtoupper(
                        $sign->checkedBy2?->karyawan?->divisi?->nama ?? '-'
                    ) }}
                </b>
                <br>

                @if($sign->checked_at_2)
                    {{ $sign->checked_at_2->format('d/m/Y H:i') }}
                @else
                    Pending
                @endif

            </div>

        </div>

    </td>

    {{-- APPROVED --}}
    <td class="text-center">

        <b>{{ $sign->approvedBy->name ?? '-' }}</b>
        <br>

        <b>
            {{ strtoupper(
                $sign->approvedBy?->karyawan?->divisi?->nama ?? '-'
            ) }}
        </b>
        <br>

        @if($sign->approved_at)
            {{ $sign->approved_at->format('d/m/Y H:i') }}
        @else
            Pending
        @endif

    </td>

    {{-- SUPPLIER --}}
    <td class="text-center">
        {{ $sign->supplier->name ?? '-' }}
    </td>

</tr>

        </table>

    </div>
</div>

@endif
    </div>
</div>
<!-- MODAL RIWAYAT -->
<div class="modal fade" id="modalRiwayatSpk" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    Riwayat SPK
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                </button>
            </div>
            <div class="modal-body" id="timelineContainer"
                style="
                    background:#ece5dd;
                    min-height:500px;
                    padding:20px;
                    overflow-y:auto;
                ">
            </div>
        </div>
    </div>
</div>
<!-- search -->
<!-- heler -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    /* =========================================
    ADD HEADER BUTTON
    ========================================= */
    let customHeaderHtml = '';

    document.getElementById('btnAddHeader')
        .addEventListener('click', function() {
            const label = prompt(
                'Nama Header'
            );
            if (!label) return;
            const key = label
                .toLowerCase()
                .replace(/\s+/g, '_');
            addDynamicHeader({
                key,
                label
            });
        });
    /* =========================================
    ADD DYNAMIC HEADER
    ========================================= */
    document.getElementById('btnAddHeader')
        .addEventListener('click', function() {
            const label = prompt(
                'Nama Header'
            );
            if (!label) return;
            const key = label
                .toLowerCase()
                .replace(/\s+/g, '_');
            addDynamicHeader({
                key,
                label
            });
        });
        // check
        document.addEventListener('change', function (e) {

    if (e.target.classList.contains('spk-item-check')) {

        const ada = document.querySelector('.spk-item-check:checked');

        document.getElementById('btnCleanUnchecked').style.display =
            ada ? 'inline-block' : 'none';

    }

});

document.getElementById('btnCleanUnchecked').addEventListener('click', function () {

    document.querySelectorAll('.spk-rowa').forEach(function(row){

        const cb = row.querySelector('.spk-item-check');

        if(cb && !cb.checked){

            row.remove();

        }

    });

});
    function addDynamicHeader(header) {
        // HEADER
        const th =
            document.createElement('th');
        th.classList.add(
            'spk-dynamic-header'
        );
        th.dataset.custom =
            header.key;
        th.innerText =
            header.label;
        document
            .querySelector('.p-header')
            .before(th);
        // BODY
        document.querySelectorAll('.spk-rowa')
            .forEach(row => {
                const td =
                    document.createElement('td');
                td.classList.add(
                    'editable',
                    'custom-column'
                );
                td.contentEditable = true;
                td.dataset.custom =
                    header.key;
                row.querySelector('.p')
                    .before(td);
            });
    }
    /* =========================================
    ADD EXTRA ROW
    ========================================= */
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-add-extra')) {
            const parentRow = e.target.closest('.spk-rowa');
            const tr = document.createElement('tr');
            tr.classList.add('extra-row');
            let html = '';
            // dynamic header
            document.querySelectorAll('.spk-dynamic-header')
                .forEach(th => {
                    html += `
                    <td class="editable custom-column"
                        contenteditable
                        data-custom="${th.dataset.custom}">
                    </td>
                `;
                });
            html += `
    <td class="editable p" contenteditable></td>
    <td class="editable l" contenteditable></td>
    <td class="editable t" contenteditable></td>
    <td class="editable material" contenteditable></td>
    <td class="editable pcs" contenteditable>0</td>
    <td class="editable set" contenteditable>0</td>
    <td class="editable harga" contenteditable>0</td>
    <td class="total">0</td>
    <td class="editable catatan" contenteditable>''</td>
    <!-- JANGAN ADA TD CATATAN -->
    <td class="text-center">
        <button type="button"
            class="btn-delete-extra"
            style="
                border:none;
                background:red;
                color:white;
                cursor:pointer;
                padding:2px 6px;
            ">
            ❌
        </button>
    </td>
`;
            tr.innerHTML = html;
            let lastRow = parentRow;
            let next = parentRow.nextElementSibling;
            while (
                next &&
                next.classList.contains('extra-row')
            ) {
                lastRow = next;
                next = next.nextElementSibling;
            }
            lastRow.after(tr);
            updateRowspan(parentRow);
            document.querySelectorAll('.extra-row .hallo')
                .forEach(td => td.remove());
        }
    });
    /* =========================================
    DELETE EXTRA ROW
    ========================================= */
    document.addEventListener('click', function(e) {
        if (
            e.target.classList.contains(
                'btn-delete-extra'
            )
        ) {
            const row =
                e.target.closest('.extra-row');
            // cari parent utama
            let prev =
                row.previousElementSibling;
            while (
                prev &&
                !prev.classList.contains(
                    'spk-rowa'
                )
            ) {
                prev = prev.previousElementSibling;
            }
            // hapus row
            row.remove();
            // update rowspan parent
            if (prev) {
                updateRowspan(prev);
            }
        }
    });
</script>
<script>
    function extractNoteData(noteBox) {
        let remark = '';
        let images = [];
        noteBox.childNodes.forEach(node => {
            if (node.nodeType === Node.TEXT_NODE) {
                remark += node.textContent.trim();
            }
            if (node.nodeType === Node.ELEMENT_NODE && node.tagName === 'IMG') {
                images.push(node.src);
            }
        });
        return {
            remark: remark,
            images: images
        };
    }
</script>
<!-- delete row -->
<script>
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-row')) {
            const row = e.target.closest('tr');
            Swal.fire({
                title: 'Yakin?',
                text: 'Baris ini akan dihapus',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    row.remove();
                    renumberRows();
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus',
                        text: 'Baris berhasil dihapus',
                        timer: 1200,
                        showConfirmButton: false
                    });
                }
            });
        }
    });
    function renumberRows() {
        document.querySelectorAll('.spk-rowa').forEach((row, index) => {
            const noCell = row.querySelector('.row-no');
            if (noCell) {
                noCell.innerText = index + 1;
            }
        });
    }
</script>
<!-- helper wraping text -->
<script>
    document.addEventListener('keydown', function(e) {
        if (!e.target.isContentEditable) return;
        // BOLEH ENTER di material & catatan
        if (e.target.classList.contains('material') ||
            e.target.classList.contains('note-box')) {
            return;
        }
        // BLOK ENTER di field lain
        if (e.key === 'Enter') {
            e.preventDefault();
        }
    });
</script>
<!-- hitung helper -->
<script>
    function getSatuan(row) {
        const pcs = parseFloat(row.querySelector('.pcs')?.innerText) || 0;
        const set = parseFloat(row.querySelector('.set')?.innerText) || 0;
        if (pcs > 0) return 'pcs';
        if (set > 0) return 'set';
        return '';
    }
    function getNumber(el) {
        if (!el) return 0;
        const value = el.textContent
            .replace(/\./g, '')
            .replace(/,/g, '')
            .trim();
        return Number(value) || 0;
    }
    function format(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
    /* =====================
       HITUNG TOTAL PER ROW
       ===================== */
    function hitungTotal(row) {
        const pcs = getNumber(row.querySelector('.pcs'));
        const set = getNumber(row.querySelector('.set'));
        const harga = getNumber(row.querySelector('.harga'));
        const qty = pcs > 0 ? pcs : set;
        const total = qty * harga;
        const totalCell = row.querySelector('.total');
        if (totalCell) {
            totalCell.innerText = format(total);
        }
        hitungGrandTotal();
    }
    /* =====================
       HITUNG TOTAL AMOUNT
       ===================== */
    function hitungGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.spk-table .total').forEach(td => {
            grandTotal += getNumber(td);
        });
        const amountCell = document.querySelector('.grand-total-display');
        if (amountCell) {
            amountCell.innerText = format(grandTotal);
        }
    }
    /* =====================
       EVENT LISTENER
       ===================== */
    document.addEventListener('input', function(e) {
        if (
            e.target.classList.contains('pcs') ||
            e.target.classList.contains('set') ||
            e.target.classList.contains('harga')
        ) {
            const row = e.target.closest('tr');
            hitungTotal(row);
        }
    });
    document.addEventListener('paste', function(e) {
        if (e.target.closest('.pcs, .set, .harga')) {
            setTimeout(() => {
                const row = e.target.closest('tr');
                hitungTotal(row);
            }, 10);
        }
    });
    /* =====================
       HITUNG SAAT LOAD
       ===================== */
    document.querySelectorAll('.spk-table tr').forEach(row => {
        if (row.querySelector('.pcs') || row.querySelector('.set')) {
            hitungTotal(row);
        }
    });
</script>
<script>
    document.addEventListener('keydown', function(e) {
        if (e.target.isContentEditable && e.key === 'Enter') {
            e.preventDefault();
        }
    });
</script>
<script>
    const input = document.getElementById('supplierInput');
    const suggestBox = document.getElementById('supplierSuggest');
    let typingTimer;
    input.addEventListener('input', function() {
        const keyword = input.innerText.trim();
        clearTimeout(typingTimer);
        if (keyword.length < 2) {
            suggestBox.style.display = 'none';
            return;
        }
        typingTimer = setTimeout(() => {
            fetch(`/supplier/search?q=${encodeURIComponent(keyword)}`)
                .then(res => res.json())
                .then(data => {
                    suggestBox.innerHTML = '';
                    if (data.length === 0) {
                        suggestBox.style.display = 'none';
                        return;
                    }
                  data.forEach(item => {

                        const div = document.createElement('div');
                        div.className = 'suggest-item';
                        div.textContent = item.name;

                        div.onclick = () => {

                            input.innerText = item.name;

                            if (item.jenis) {
                                document.getElementById('spk_type').value = item.jenis;
                            }

                            suggestBox.style.display = 'none';
                        };

                        suggestBox.appendChild(div); // WAJIB
                    });

                    suggestBox.style.display = 'block';
                });
        }, 300);
    });
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !suggestBox.contains(e.target)) {
            suggestBox.style.display = 'none';
        }
    });
</script>
<script>
    function handlePaste(e, container) {
        let items = (e.clipboardData || window.clipboardData).items;
        for (let i = 0; i < items.length; i++) {
            let item = items[i];
            if (item.type.indexOf("image") !== -1) {
                e.preventDefault();
                let blob = item.getAsFile();
                let reader = new FileReader();
                reader.onload = function(event) {
                    let img = document.createElement('img');
                    img.src = event.target.result;
                    img.className = 'preview-img';
                    container.appendChild(img);
                };
                reader.readAsDataURL(blob);
            }
        }
    }
    function uploadPreview(input) {
        let container = input.previousElementSibling;
        Array.from(input.files).forEach(file => {
            let reader = new FileReader();
            reader.onload = e => {
                let img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'preview-img';
                container.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
        input.value = '';
    }
</script>
<!-- save data -->
<script>
    document.getElementById('btnSaveSpk').addEventListener('click', function() {
        let items = [];
        let payments = [];
   const validation = validatePaymentLimit();

if (!validation.valid) {

    Swal.fire({
        icon: 'error',
        title: 'Total Payment Melebihi SPK',
        text:
            'Total payment Rp ' +
            formatRupiah(validation.totalPayment) +
            ' melebihi total SPK Rp ' +
            formatRupiah(validation.grandTotal)
    });

    return;
}
        let totalDp = 0;
        let totalBahan = 0;
        let totalPelunasan = 0;
        let totalKasbon = 0;
      document.querySelectorAll('.payment-row').forEach(row => {

            let originalAmount = parseNumber(
                row.querySelector('.total-amount')?.innerText
            );

            let adjustment = parseFloat(
                row.dataset.adjustment || 0
            );

            let amount =
                adjustment > 0
                    ? adjustment
                    : originalAmount;

            let type = row.querySelector('.payment-type')?.value;

            if (type === 'dp') {
                totalDp += amount;
            }

            if (type === 'bahan') {
                totalBahan += amount;
            }

            if (type === 'pelunasan') {
                totalPelunasan += amount;
            }

            if (type === 'kasbon') {
                totalKasbon += amount;
            }
     payments.push({
                amount: originalAmount,

                adjustment:
                    parseFloat(
                        row.dataset.adjustment || 0
                    ),

                payment_request_amount:
                    parseFloat(
                        row.dataset.adjustment || 0
                    ),

                remaining_amount:
                    originalAmount -
                    parseFloat(
                        row.dataset.adjustment || 0
                    ),

                date:
                    row.querySelector('.date-isian')
                    ?.innerText.trim(),

                note:
                    row.querySelector('.payment-type')
                    ?.value,

                note_tambahan:
                    row.querySelector('.note-tambahan')
                    ?.innerText.trim(),

                payment_id:
                    row.dataset.paymentId || null,

                pr_id:
                    row.dataset.prId || null,

                is_request:
                    row.querySelector('.payment-request-check')
                    ?.checked || false
            });

        });
        document.querySelectorAll('.spk-rowa')
            .forEach(row => {
                const detailId =
                    row.dataset.detailId;
                if (!detailId) return;
                let images = [];
                row.querySelectorAll('.image-box img')
                    .forEach(img => {
                        images.push(img.src);
                    });
                const noteBox =
                    row.querySelector('.note-box');
                // =====================================
                // MAIN CUSTOM COLUMN
                // =====================================
                let customColumns = [];
                // parent row
                let parentCustom = {};
                row.querySelectorAll('.custom-column')
                    .forEach(col => {
                        const key =
                            col.dataset.custom
                            .trim()
                            .toLowerCase();
                        parentCustom[key] =
                            col.innerText.trim();
                    });
                // simpan parent
                if (
                    Object.keys(parentCustom).length
                ) {
                    customColumns.push(parentCustom);
                }
                // =====================================
                // EXTRA ROW
                // =====================================
                let next =
                    row.nextElementSibling;
                while (
                    next &&
                    next.classList.contains(
                        'extra-row'
                    )
                ) {
                    let extraData = {};
                    // dynamic column
                    next.querySelectorAll(
                            '.custom-column'
                        )
                        .forEach(col => {
                            const key =
                                col.dataset.custom
                                .trim()
                                .toLowerCase();
                            extraData[key] =
                                col.innerText.trim();
                        });
                    // ukuran
                    extraData.p =
                        next.querySelector('.p')
                        ?.innerText.trim() || '';
                    extraData.l =
                        next.querySelector('.l')
                        ?.innerText.trim() || '';
                    extraData.t =
                        next.querySelector('.t')
                        ?.innerText.trim() || '';
                    extraData.material =
                        next.querySelector('.material')
                        ?.innerText.trim() || '';
                    extraData.pcs =
                        next.querySelector('.pcs')
                        ?.innerText.trim() || '';
                    extraData.set =
                        next.querySelector('.set')
                        ?.innerText.trim() || '';
                    extraData.harga =
                        next.querySelector('.harga')
                        ?.innerText.trim() || '';
                    extraData.total =
                        getNumber(
                            next.querySelector('.total')
                        );
                    customColumns.push(extraData);
                    next =
                        next.nextElementSibling;
                }
                // =====================================
                // PUSH ITEM
                // =====================================
                items.push({
                    detail_id: detailId,
                    kode: row.querySelector('.kode-item')
                        ?.innerText.trim() || '',
                    nama: row.querySelector('.nama')
                        ?.innerText.trim() || '',
                    p: row.querySelector('.p')
                        ?.innerText.trim() || '',
                    l: row.querySelector('.l')
                        ?.innerText.trim() || '',
                    t: row.querySelector('.t')
                        ?.innerText.trim() || '',
                    material: row.querySelector('.material')
                        ?.innerText.trim() || '',
                    pcs: row.querySelector('.pcs')
                        ?.innerText.trim() || '',
                    set: row.querySelector('.set')
                        ?.innerText.trim() || '',
                    satuan: getSatuan(row),
                    harga: row.querySelector('.harga')
                        ?.innerText.trim() || '',
                    total: getNumber(
                        row.querySelector('.total')
                    ),
                    images: images,
                    catatan: noteBox ?
                        extractNoteData(noteBox) : {
                            remark: '',
                            images: []
                        },
                    custom_columns: customColumns
                });
            });
        const mode = document.getElementById('spk_mode')?.value;
        const spkId = document.getElementById('spk_id')?.value;
        const noSpkEl = document.querySelector('.no-spk');
        const noPoEl = document.querySelector('.no-po');
        let customHeaders = [];
        document.querySelectorAll(
                '.spk-dynamic-header'
            )
            .forEach(th => {
                const label =
                    th.innerText
                    .replace('➕', '')
                    .trim();
                const key = label
                    .toLowerCase()
                    .replace(/\s+/g, '_');
                customHeaders.push({
                    key: key,
                    label: label
                });
            });
        const payload = {
            spk_id: mode === 'edit' ? spkId : null, // 🔥 KUNCI
            spk_type: document.getElementById('spk_type').value,
            custom_headers: customHeaders,
            no_spk: noSpkEl ? noSpkEl.innerText.trim() : '',
            no_po: noPoEl ? noPoEl.innerText.trim() : '',
            nama: document.getElementById('supplierInput')?.innerText || '',
            tgl_terima: document.querySelector('.tgl-terima')?.innerText || '',
            tgl_selesai: document.querySelector('.tgl-selesai')?.innerText || '',
            items: items,
            payments: payments
        };
        //     console.log('NO SPK:', payload.no_spk);
        // console.log('NO PO:', payload.no_po);
        // 🔥 URL DINAMIS
        let url = '';
        if (mode === 'edit') {
            url = "{{ url('/spk/update') }}/" + spkId;
        } else {
            url = "{{ url('/spk/create') }}/" + spkId; // spkId = PO ID
        }
        fetch(url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'SPK Berhasil',
                        html: `
                <div style="font-size:14px">
                    ${res.message}<br><br>
                    <b>No SPK:</b><br>
                    <span style="font-size:18px;color:#198754">
                        ${res.no_spk}
                    </span>
                </div>
            `,
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.message || 'Gagal menyimpan SPK'
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error Server',
                    text: 'Terjadi kesalahan pada server'
                });
            });
    });
</script>
<!-- search and add row -->
<script>
    const itemInput = document.getElementById('itemSearch');
    const itemSuggest = document.getElementById('itemSuggest');
    let itemTimer;
    itemInput.addEventListener('input', function() {
        const keyword = itemInput.innerText.trim();
        clearTimeout(itemTimer);
        if (keyword.length < 2) {
            itemSuggest.style.display = 'none';
            return;
        }
        itemTimer = setTimeout(() => {
            fetch("{{ route('detailpo.search') }}?q=" + encodeURIComponent(keyword))
                .then(res => res.json())
                .then(data => {
                    itemSuggest.innerHTML = '';
                    if (!data.length) {
                        itemSuggest.style.display = 'none';
                        return;
                    }
                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'suggest-item';
                        div.innerHTML = `
                    <b>${item.kode}</b><br>
                    <small>${item.nama}</small>
                `;
                        div.onclick = () => {
                            addItemRow(item);
                            itemInput.innerText = '';
                            itemSuggest.style.display = 'none';
                        };
                        itemSuggest.appendChild(div);
                    });
                    itemSuggest.style.display = 'block';
                });
        }, 300);
    });
</script>
<!-- add rows -->
<script>
    function addItemRow(item) {
        const tr =
            document.createElement('tr');
        tr.classList.add('spk-rowa');
        tr.dataset.detailId =
            item.detail_id;
        let dynamicCols = '';
        document.querySelectorAll(
                '.spk-dynamic-header'
            )
            .forEach(th => {
                dynamicCols += `
            <td class="
                editable
                custom-column
            "
            contenteditable
            data-custom="
                ${th.dataset.custom}
            ">
            </td>
        `;
            });
        tr.innerHTML = `
        <td class="text-center select-item-cell">
            <input type="checkbox" class="spk-item-check">
        </td>
        <td class="
            editable
            text-center
            kode-item
            delete-row
        "
        contenteditable>
            ${item.kode ?? ''}
        </td>
        <td >
            <div class="image-box">
            </div>
            <input type="file"
                   accept="image/*"
                   multiple
                   capture="environment"
                   onchange="uploadPreview(this)">
        </td>
        <td class="editable nama"
            contenteditable>
            ${item.nama ?? ''}
        </td>
        ${dynamicCols}
        <td class="editable text-center p"
            contenteditable>
            ${item.p ?? ''}
        </td>
        <td class="editable text-center l"
            contenteditable>
            ${item.l ?? ''}
        </td>
        <td class="editable text-center t"
            contenteditable>
            ${item.t ?? ''}
        </td>
        <td class="editable material"
            contenteditable>
            ${item.material ?? ''}
        </td>
        <td class="editable text-center pcs"
            contenteditable>
            ${item.qty ?? 0}
        </td>
        <td class="editable text-center set"
            contenteditable>
            0
        </td>
        <td class="editable text-right harga"
            contenteditable>
            0
        </td>
        <td class="text-right total">
            0
        </td>
        <td class="catatan-cell">
            <div class="editable note-box"
                 contenteditable>
            </div>
        </td>
        <td class="text-center action-cell">
            <button type="button"
                    class="btn-add-extra">
                ➕
            </button>
        </td>
    `;
        document
            .getElementById('spkItemAnchor')
            .before(tr);
        const anchor =
            document.getElementById('spkItemAnchor');
        anchor.before(tr);
        hitungTotal(tr);
    }
    function updateRowspan(parentRow) {
        let rowspan = 1;
        let next =
            parentRow.nextElementSibling;
        while (
            next &&
            next.classList.contains(
                'extra-row'
            )
        ) {
            rowspan++;
            next =
                next.nextElementSibling;
        }
        const rowspanCells = [
            '.kode-item',
            '.gambar-cell',
            '.nama',
            '.catatan-cell',
            '.action-cell'
        ];
        rowspanCells.forEach(selector => {
            const cell =
                parentRow.querySelector(selector);
            if (cell) {
                cell.rowSpan = rowspan;
                cell.style.verticalAlign = 'middle';
            }
        });
    }
</script>
<script>
    document.getElementById('btnAddPayment')
        .addEventListener('click', function() {
            let tr = document.createElement('tr');
            let paymentId =
                'pay_' + Date.now();
            tr.classList.add('payment-row');
            tr.setAttribute(
                'data-payment-id',
                paymentId
            );
            tr.innerHTML = `
        <td class="text-center">
            <input type="checkbox"
                class="payment-request-check">
        </td>
        <td class="editable total-amount"
            contenteditable>
        </td>
        <td class="editable date-isian"
            contenteditable>
        </td>
        <td>
            <select class="form-control
                form-control-sm payment-type">
                <option value="">
                    -- Pilih --
                </option>
                <option value="dp">
                    DP
                </option>
    <option value="bahan">
                    Bahan
                </option>
                <option value="return_bahan">
                    Return Bahan
                </option>
                <option value="kasbon">
                    Kasbon
                </option>
                <option value="pelunasan">
                    Pelunasan
                </option>
                <option value="ppn">
                    PPN
                </option>
            </select>
        </td>
        <td class="editable note-tambahan"
            contenteditable>
        </td>
        <td>
            -
        </td>
    `;
            document
                .getElementById('paymentBody')
                .appendChild(tr);
        });
</script>
<script>
    async function savePaymentRequestRow(row) {
        try {
            // =========================
            // VALIDASI ROW
            // =========================
            if (!row) {
                return;
            }
            // =========================
            // AMBIL DATA
            // =========================
            const amount =
                (
                    row.querySelector('.total-amount')
                    ?.innerText || ''
                )
                .replace(/\./g, '')
                .trim();
            const date =
                row.querySelector('.date-isian')
                ?.innerText
                .trim() || '';
            const note =
                row.querySelector('.payment-type')
                ?.value || '';
            const noteTambahan =
                row.querySelector('.note-tambahan')
                ?.innerText
                .trim() || '';
            const isRequest =
                row.querySelector('.payment-request-check')
                ?.checked || false;
            // =========================
            // VALIDASI
            // =========================
            const val = validatePaymentLimit();
if(!val.valid){
    row.querySelector('.payment-request-check').checked = false;
    Swal.fire({
        icon:'error',
        title:'Total Payment Melebihi SPK',
        text:
            'Total payment Rp ' +
            formatRupiah(val.totalPayment) +
            ' melebihi total SPK Rp ' +
            formatRupiah(val.grandTotal)
    });
    return;
}
            if (!note) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Jenis payment kosong',
                    text: 'Pilih jenis payment terlebih dahulu'
                });
                row.querySelector('.payment-request-check').checked = false;
                return;
            }
            if (!amount || parseInt(amount) <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nominal kosong',
                    text: 'Isi nominal payment terlebih dahulu'
                });
                row.querySelector('.payment-request-check').checked = false;
                return;
            }
            // =========================
            // FORMAT TANGGAL
            // 12/05/26 => 12/05/2026
            // =========================
            let finalDate = date;
            if (date) {
                const split = date.split('/');
                if (
                    split.length === 3 &&
                    split[2].length === 2
                ) {
                    finalDate =
                        split[0] + '/' +
                        split[1] + '/20' +
                        split[2];
                }
            }
            // =========================
            // PAYLOAD
            // =========================
            const payload = {
                spk_id: document.getElementById('spk_id')
                    ?.value,
                no_spk: document.querySelector('.no-spk')
                    ?.innerText
                    .trim(),
                payment: {
                    payment_id: row.dataset.paymentId,
                    amount: amount,
                    date: finalDate,
                    note: note,
                    note_tambahan: noteTambahan,
                    is_request: isRequest
                }
            };
            console.log('PAYLOAD:', payload);
            // =========================
            // LOADING
            // =========================
            row.style.opacity = '.6';
            row.style.pointerEvents = 'none';
            // =========================
            // FETCH
            // =========================
            const response = await fetch(
                '/payment-request/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                }
            );
            // =========================
            // RESPONSE JSON
            // =========================
            let result = {};
            try {
                result = await response.json();
            } catch (e) {
                throw new Error(
                    'Response server tidak valid'
                );
            }
            console.log('RESULT:', result);
            // =========================
            // HANDLE ERROR HTTP
            // =========================
            if (!response.ok) {
                throw new Error(
                    result.message ||
                    'Terjadi kesalahan server'
                );
            }
            // =========================
            // HANDLE FAILED
            // =========================
            if (!result.success) {
                throw new Error(
                    result.message ||
                    'Gagal membuat request'
                );
            }
            // =========================
            // SUCCESS
            // =========================
            Toast.fire({
                icon: 'success',
                title: isRequest ?
                    'Payment request dibuat' : 'Payment request dibatalkan'
            });
            // =========================
            // AUTO SAVE SPK
            // supaya JSON sync
            // =========================
            const saveBtn =
                document.getElementById('btnSaveSpk');
            if (saveBtn) {
                saveBtn.click();
            }
        } catch (err) {
            console.error(err);
            // rollback checkbox
            const checkbox =
                row.querySelector(
                    '.payment-request-check'
                );
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
            }
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: err.message ||
                    'Terjadi kesalahan'
            });
        } finally {
            // =========================
            // ENABLE ROW
            // =========================
            row.style.opacity = '1';
            row.style.pointerEvents = 'auto';
        }
    }
    function parseNumber(value) {
        return parseInt(
            (value || '').toString().replace(/\./g, '')
        ) || 0;
    }
    function formatRupiah(value) {
        value = parseInt(
            value.toString().replace(/[^\d]/g, '')
        ) || 0;
        return new Intl.NumberFormat('id-ID').format(value);
    }
    function updatePaymentSummary() {
        // =========================
        // GRAND TOTAL SPK
        // =========================
        let grandTotal = 0;
        document.querySelectorAll('.total').forEach(td => {
            grandTotal += parseNumber(td.innerText);
        });
        let totalPpn = 0;
        // =========================
        // PAYMENT TOTAL
        // =========================
       let totalDp = 0;
        let totalBahan = 0;
        let totalReturnBahan = 0;
        let totalPelunasan = 0;
        let totalKasbon = 0;
        document.querySelectorAll('.payment-row').forEach(row => {
        let originalAmount = parseNumber(
                row.querySelector('.total-amount')?.innerText
            );

            let adjustment = parseFloat(
                row.dataset.adjustment || 0
            );

            let amount =
                adjustment > 0
                    ? adjustment
                    : originalAmount;

            console.log({
                originalAmount,
                adjustment,
                finalAmount: amount
            });
            let type = row.querySelector('.payment-type')?.value;
            if(type === 'ppn'){
                totalPpn += amount;
            }
            if (type === 'dp') {
                totalDp += amount;
            }
            if (type === 'bahan') {
                totalBahan += amount;
            }
            if (type === 'return_bahan') {
                totalReturnBahan += amount;
            }
            if (type === 'pelunasan') {
                totalPelunasan += amount;
            }
            if (type === 'kasbon') {
                totalKasbon += amount;
            }
        });
        let bahanBersih = totalBahan - totalReturnBahan;

        if (bahanBersih < 0) {
            bahanBersih = 0;
        }
        // =========================
        // SISA
        // =========================
    let grandTotalSetelahPpn =
    grandTotal + totalPpn;

    let sisaPelunasan =
        grandTotalSetelahPpn -
        totalDp -
        bahanBersih -
        totalKasbon -
        totalPelunasan;
        // =========================
        // RENDER
        // =========================
      let summary = `
<div>
    <b>Grand Total :</b>
    Rp ${formatRupiah(grandTotal)}
</div>
`;

if (totalDp > 0) {
    summary += `
    <div>
        <b>Total DP :</b>
        Rp ${formatRupiah(totalDp)}
    </div>`;
}

if (totalBahan > 0) {
    summary += `
    <div style="color:#16a34a">
        <b>Total Bahan :</b>
        Rp ${formatRupiah(totalBahan)}
    </div>`;
}

if (totalReturnBahan > 0) {
    summary += `
    <div style="color:red">
        <b>Return Bahan :</b>
        Rp ${formatRupiah(totalReturnBahan)}
    </div>`;
}

if (bahanBersih > 0) {
    summary += `
    <div style="color:green;font-weight:bold">
        <b>Total Bahan Bersih :</b>
        Rp ${formatRupiah(bahanBersih)}
    </div>`;
}

if (totalKasbon > 0) {
    summary += `
    <div>
        <b>Total Kasbon :</b>
        <span style="color:red">
            Rp ${formatRupiah(totalKasbon)}
        </span>
    </div>`;
}

if (totalPelunasan > 0) {
    summary += `
    <div>
        <b>Total Pelunasan :</b>
        Rp ${formatRupiah(totalPelunasan)}
    </div>`;
}

if (totalPpn > 0) {
    summary += `
    <hr>

    <div>
        <b>Total PPN :</b>
        <span style="color:#2563eb">
            Rp ${formatRupiah(totalPpn)}
        </span>
    </div>

    <div style="
        background:#ecfeff;
        padding:8px;
        border-radius:8px;
        margin-top:8px;
        font-weight:bold;
    ">
        Grand Total + PPN :
        Rp ${formatRupiah(grandTotalSetelahPpn)}
    </div>`;
}

summary += `
<div style="
    margin-top:12px;
    padding:12px;
    border-radius:12px;
    background:#fff5f5;
    border:1px solid #fecaca;
">
    <div style="
        font-size:16px;
        color:#dc2626;
        font-weight:bold;
    ">
        💰 Sisa Pelunasan :
        Rp ${formatRupiah(sisaPelunasan)}
    </div>
</div>
`;

document.getElementById('paymentSummary').innerHTML = summary;
    }
    document.addEventListener('change', function(e) {
        if (
            e.target.classList.contains(
                'payment-request-check'
            )
        ) {
            let row =
                e.target.closest('.payment-row');
            savePaymentRequestRow(row);
        }
    });
    // realtime update
    document.addEventListener('input', function(e) {
        if (
            e.target.classList.contains('total-amount')
        ) {
            updatePaymentSummary();
        }
        if (
            e.target.classList.contains('harga')
        ) {
            updatePaymentSummary();
        }
        if (
            e.target.classList.contains('pcs')
        ) {
            updatePaymentSummary();
        }
        if (
            e.target.classList.contains('set')
        ) {
            updatePaymentSummary();
        }
    });
    document.addEventListener('change', function(e) {
        if (
            e.target.classList.contains('payment-type')
        ) {
            updatePaymentSummary();
        }
    });
    // load pertama
    setTimeout(updatePaymentSummary, 300);
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('total-amount')) {
            let value = e.target.innerText || '';
            e.target.innerText = formatRupiah(value);
            placeCaretAtEnd(e.target);
        }
    });
    function placeCaretAtEnd(el) {
        el.focus();
        if (typeof window.getSelection != "undefined" &&
            typeof document.createRange != "undefined") {
            let range = document.createRange();
            range.selectNodeContents(el);
            range.collapse(false);
            let sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        }
    }
</script>
<!-- alert -->
<script>
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 1500
    });
    // ==============================
    // AUTO REQUEST CHECKBOX
    // ==============================
    document.addEventListener('change', function(e) {
        if (!e.target.classList.contains('request-check'))
            return;
        // ==========================
        // GET CHECKED TYPE
        // ==========================
        let checkedTypes = [];
        document.querySelectorAll('.request-check:checked')
            .forEach(el => {
                checkedTypes.push(el.value);
            });
        // ==========================
        // GET PAYMENT SELECTED
        // ==========================
        let selectedPayments = [];
        document.querySelectorAll('.payment-row')
            .forEach(row => {
                let type =
                    row.querySelector('.payment-type')?.value;
                // hanya yg dicentang
                if (checkedTypes.includes(type)) {
                    selectedPayments.push({
                        date: row.querySelector('.date-isian')
                            ?.innerText.trim() || '',
                        note: type,
                        amount: (
                            row.querySelector('.total-amount')
                            ?.innerText || ''
                        ).replace(/\./g, ''),
                        note_tambahan: row.querySelector('.note-tambahan')
                            ?.innerText.trim() || '',
                        is_request: true
                    });
                }
            });
        // ==========================
        // PAYLOAD
        // ==========================
        const payload = {
            spk_id: document.getElementById('spk_id')?.value,
            no_spk: document.querySelector('.no-spk')
                ?.innerText.trim(),
            checked_types: checkedTypes,
            payments: selectedPayments
        };
        console.log(payload);
        // ==========================
        // SAVE REQUEST
        // ==========================
        fetch('/payment-request/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    Toast.fire({
                        icon: 'success',
                        title: 'Request updated'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.message || 'Gagal update request'
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Terjadi kesalahan server'
                });
            });
    });
</script>
<script>
    document.addEventListener('click', function(e) {
        if (
            !e.target.classList.contains(
                'btn-status-spk'
            )
        ) return;
        let status =
            e.target.dataset.status;
        let spkId =
            document.getElementById('spk_id').value;
        Swal.fire({
            title: 'Yakin?',
            text: 'SPK akan diubah menjadi ' +
                status,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (!result.isConfirmed)
                return;
            fetch(
                    `/spk/change-status/${spkId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            status: status
                        })
                    })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1200,
                            showConfirmButton: false
                        });
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error'
                    });
                });
        });
    });
</script>
<!-- riwayat -->
<script>
    // Select All
document.addEventListener('change', function(e){

    if(e.target.id === 'checkAllItems'){

        document.querySelectorAll('.spk-item-check').forEach(cb=>{
            cb.checked = e.target.checked;
        });

        toggleCleanButton();
    }

});

// Checkbox per item
document.addEventListener('change', function(e){

    if(e.target.classList.contains('spk-item-check')){
        toggleCleanButton();
    }

});

function toggleCleanButton(){

    const checked =
        document.querySelectorAll('.spk-item-check:checked').length;

    document.getElementById('btnCleanUnchecked').style.display =
        checked ? 'inline-block' : 'none';

}
    document.getElementById('btnRiwayatSpk')
        .addEventListener('click', function() {
            const spkId = document.getElementById('spk_id').value;
            if (!spkId) {
                Swal.fire({
                    icon: 'warning',
                    text: 'SPK belum disimpan'
                });
                return;
            }
            fetch(`/spk/timeline/${spkId}`)
                .then(res => res.json())
                .then(data => {
    // console.log(data);

                    let html = '';
                    if (!data.length) {
                        html = `
                <div class="text-center text-muted">
                    Belum ada riwayat
                </div>
            `;
                    } else {
                        data.forEach(item => {
                            // const row = item.data;
                            let row = item.data;

                            if (typeof row === 'string') {
                                try {
                                    row = JSON.parse(row);
                                } catch (e) {
                                    console.error(e);
                                }
                            }
                            let changesHtml = '';
                                // approver
                    let actionText = '';
                    let badgeColor = '#3498db';

                    switch(row.type){

                        case 'create':
                            actionText = '🆕 Membuat SPK';
                            badgeColor = '#27ae60';
                            break;

                        case 'update':
                            actionText = '✏️ Mengupdate SPK';
                            badgeColor = '#3498db';
                            break;

                        case 'checked':
                            actionText = '✅ Checked SPK';
                            badgeColor = '#f39c12';
                            break;

                        case 'approved':
                            actionText = '🎯 Approved SPK';
                            badgeColor = '#2ecc71';
                            break;

                        default:
                            actionText = row.type;
                            badgeColor = '#95a5a6';
                    }
                            // UPDATE
                            if (row.type === 'update') {
                                if (row.changes) {
                                    Object.entries(row.changes).forEach(([key, val]) => {
                                        let label = key;
                                        // =========================
                                        // PAYMENT LABEL
                                        // =========================
                                        if (key.includes('payments')) {
                                            if (key.includes('amount')) {
                                                label = '💰 Nominal Pembayaran';
                                            } else if (key.includes('date')) {
                                                label = '📅 Tanggal Pembayaran';
                                            } else if (key.includes('note')) {
                                                label = '📝 Jenis Pembayaran';
                                            } else if (key.includes('note_tambahan')) {
                                                label = '📌 Keterangan Pembayaran';
                                            } else {
                                                label = '💳 Payment';
                                            }
                                        }
                                        // =========================
                                        // ITEMS LABEL
                                        // =========================
                                        if (key.includes('items')) {
                                            if (key.includes('qty')) {
                                                label = '📦 Qty Item';
                                            } else if (key.includes('harga')) {
                                                label = '💰 Harga Item';
                                            } else if (key.includes('material')) {
                                                label = '🪵 Material';
                                            } else {
                                                label = '📦 Item SPK';
                                            }
                                        }
                                        // =========================
                                        // NORMAL FIELD
                                        // =========================
                                        if (key === 'tgl_selesai') {
                                            label = '📅 Tanggal Selesai';
                                        }
                                        if (key === 'tgl_terima') {
                                            label = '📅 Tanggal Terima';
                                        }
                                        if (key === 'sup') {
                                            label = '👤 Supplier';
                                        }
                                        changesHtml += `
        <div style="
            margin-top:6px;
            padding:8px;
            background:#fff;
            border-radius:10px;
            font-size:12px;
        ">
            <div style="
                font-weight:bold;
                margin-bottom:4px;
            ">
                ${label}
            </div>
            <div>
                <span style="color:red">
                    ${formatTimelineValue(val.before)}
                </span>
                <span style="
                    margin:0 6px;
                    color:#999;
                ">
                    →
                </span>
                <span style="color:green">
                    ${formatTimelineValue(val.after)}
                </span>
            </div>
        </div>
    `;
                                    });
                                }
                            }
                       html += `
                        <div style="
                            display:flex;
                            justify-content:flex-end;
                            margin-bottom:14px;
                        ">
                            <div style="
                                background:#dcf8c6;
                                max-width:75%;
                                padding:12px;
                                border-radius:12px;
                                box-shadow:0 1px 3px rgba(0,0,0,.15);
                            ">

                                <div style="
                                    font-size:13px;
                                    font-weight:bold;
                                    margin-bottom:4px;
                                ">
                                    ${row.user ?? '-'}
                                </div>

                                <div style="
                                    display:inline-block;
                                    background:${badgeColor};
                                    color:white;
                                    padding:3px 8px;
                                    border-radius:20px;
                                    font-size:11px;
                                    margin-bottom:8px;
                                ">
                                    ${actionText}
                                </div>

                                ${
                                    row.remark
                                    ? `
                                        <div style="
                                            margin-top:8px;
                                            padding:8px;
                                            background:white;
                                            border-radius:8px;
                                        ">
                                            📝 ${row.remark}
                                        </div>
                                    `
                                    : ''
                                }

                                ${changesHtml}

                                <div style="
                                    text-align:right;
                                    font-size:11px;
                                    color:#666;
                                    margin-top:8px;
                                ">
                                 ${row.user ?? 'System'}
                                        ${row.time ?? '-'}
                                </div>

                            </div>
                        </div>
                        `;
                        });
                    }
                    document.getElementById('timelineContainer')
                        .innerHTML = html;
                    $('#modalRiwayatSpk').modal('show');
                });
            function formatTimelineValue(value) {
                if (value === null || value === undefined) {
                    return '-';
                }
                // ARRAY
                if (Array.isArray(value)) {
                    return value.map(item => {
                        // payment object
                        if (typeof item === 'object') {
                            return Object.entries(item)
                                .map(([k, v]) => `${k}: ${v}`)
                                .join(', ');
                        }
                        return item;
                    }).join('<br>');
                }
                // OBJECT
                if (typeof value === 'object') {
                    return Object.entries(value)
                        .map(([k, v]) => {
                            // nested object
                            if (typeof v === 'object') {
                                return `
                        <div style="margin-top:4px">
                            <b>${k}</b> :
                            ${JSON.stringify(v)}
                        </div>
                    `;
                            }
                            return `<b>${k}</b> : ${v}`;
                        })
                        .join('<br>');
                }
                return value;
            }
        });
document.getElementById('previewBtn').addEventListener('click', function () {
    const spkJson = {
        supplier: {},
        headers: [],
        items: [],
        payments: []
    };
    /* ==========================
       HEADER SPK
    ========================== */
    spkJson.supplier = {
        no_spk: document.querySelector('.no-spk')?.innerText.trim() || '',
        no_po: document.querySelector('.no-po')?.innerText.trim() || '',
        nama_supplier: document.getElementById('supplierInput')?.innerText.trim() || '',
        tgl_terima: document.querySelector('.tgl-terima')?.innerText.trim() || '',
        tgl_selesai: document.querySelector('.tgl-selesai')?.innerText.trim() || ''
    };
    /* ==========================
       DYNAMIC HEADER
    ========================== */
    document.querySelectorAll('.spk-dynamic-header')
        .forEach(th => {
            spkJson.headers.push({
                key: th.dataset.custom,
                label: th.innerText.trim()
            });
        });
    /* ==========================
       ITEMS + EXTRA ROW
    ========================== */
    document.querySelectorAll('.spk-rowa')
        .forEach(row => {
            let item = {
                kode: row.querySelector('.kode-item')?.innerText.trim() || '',
                nama: row.querySelector('.nama')?.innerText.trim() || '',
                images: [],
                catatan:
                    row.querySelector('.note-box')
                    ?.innerText.trim() || '',
                rows: []
            };
            row.querySelectorAll('.image-box img')
                .forEach(img => {
                    item.images.push(img.src);
                });
            /* ---------- parent row ---------- */
            let parentCustom = {};
            row.querySelectorAll('.custom-column')
                .forEach(col => {
                    parentCustom[col.dataset.custom] =
                        col.innerText.trim();
                });
            item.rows.push({
                custom: parentCustom,
                p: row.querySelector('.p')?.innerText.trim() || '',
                l: row.querySelector('.l')?.innerText.trim() || '',
                t: row.querySelector('.t')?.innerText.trim() || '',
                material:
                    row.querySelector('.material')
                    ?.innerText.trim() || '',
                pcs:
                    row.querySelector('.pcs')
                    ?.innerText.trim() || '',
                set:
                    row.querySelector('.set')
                    ?.innerText.trim() || '',
                harga:
                    row.querySelector('.harga')
                    ?.innerText.trim() || '',
                total:
                    row.querySelector('.total')
                    ?.innerText.trim() || ''
            });
            /* ---------- extra row ---------- */
            let next = row.nextElementSibling;
            while (
                next &&
                next.classList.contains('extra-row')
            ) {
                let extraCustom = {};
                next.querySelectorAll('.custom-column')
                    .forEach(col => {
                        extraCustom[col.dataset.custom] =
                            col.innerText.trim();
                    });
                item.rows.push({
                    custom: extraCustom,
                    p:
                        next.querySelector('.p')
                        ?.innerText.trim() || '',
                    l:
                        next.querySelector('.l')
                        ?.innerText.trim() || '',
                    t:
                        next.querySelector('.t')
                        ?.innerText.trim() || '',
                    material:
                        next.querySelector('.material')
                        ?.innerText.trim() || '',
                    pcs:
                        next.querySelector('.pcs')
                        ?.innerText.trim() || '',
                    set:
                        next.querySelector('.set')
                        ?.innerText.trim() || '',
                    harga:
                        next.querySelector('.harga')
                        ?.innerText.trim() || '',
                    total:
                        next.querySelector('.total')
                        ?.innerText.trim() || ''
                });
                next = next.nextElementSibling;
            }
            spkJson.items.push(item);
        });
    /* ==========================
       PAYMENT
    ========================== */
    document.querySelectorAll('.payment-row')
        .forEach(row => {
            spkJson.payments.push({
                 payment_id:
        row.dataset.paymentId,
                amount:
                    row.querySelector('.total-amount')
                    ?.innerText.trim() || '',
                date:
                    row.querySelector('.date-isian')
                    ?.innerText.trim() || '',
                type:
                    row.querySelector('.payment-type')
                    ?.value || '',
                note:
                    row.querySelector('.note-tambahan')
                    ?.innerText.trim() || ''
            });
        });
    renderPrevieww(spkJson);
});

function renderPrevieww(data) {
    let dynamicHeader = '';
    data.headers.forEach(h => {
        dynamicHeader += `
            <th>${h.label}</th>
        `;
    });
    let rows = '';
let customHeaderHtml = '';
data.headers.forEach(h => {
    customHeaderHtml += `
        <th rowspan="2">
            ${h.label}
        </th>
    `;
});
    data.items.forEach(item => {
        item.rows.forEach((detail, index) => {
            let customCols = '';
            data.headers.forEach(h => {
                customCols += `
                    <td>
                        ${detail.custom[h.key] ?? ''}
                    </td>
                `;
            });
            rows += `
            <tr>
                ${
                    index === 0
                    ? `
                    <td rowspan="${item.rows.length}">
                        ${item.kode}
                    </td>
                    `
                    : ''
                }
                ${
                    index === 0
                    ? `
                    <td rowspan="${item.rows.length}">
                        ${
                            item.images.length
                            ? `<img src="${item.images[0]}" style="max-width:90px">`
                            : ''
                        }
                    </td>
                    `
                    : ''
                }
                ${
                    index === 0
                    ? `
                    <td rowspan="${item.rows.length}">
                        ${item.nama}
                    </td>
                    `
                    : ''
                }
                ${customCols}
                <td>${detail.p}</td>
                <td>${detail.l}</td>
                <td>${detail.t}</td>
                <td style="white-space:pre-line">
                    ${detail.material}
                </td>
                <td>${detail.pcs}</td>
                <td>${detail.set}</td>
                <td>${detail.harga}</td>
                <td>${detail.total}</td>
                ${
                    index === 0
                    ? `
                    <td rowspan="${item.rows.length}">
                        ${item.catatan}
                    </td>
                    `
                    : ''
                }
            </tr>
            `;
        });
    });
    let paymentRows = '';
    data.payments.forEach(pay => {
        paymentRows += `
        <tr>
            <td>${pay.amount}</td>
            <td>${pay.date}</td>
            <td>${pay.type}</td>
            <td>${pay.note}</td>
        </tr>
        `;
    });
    const html = `
    <html>
    <head>
        <title>Preview SPK</title>
        <style>
            body{
                font-family:Arial;
                padding:20px;
            }
            table{
                width:100%;
                border-collapse:collapse;
                margin-bottom:20px;
            }
            th,td{
                border:1px solid #000;
                padding:5px;
                font-size:12px;
                vertical-align:middle;
            }
            th{
                background:#2f437f;
                color:#fff;
            }
            img{
                display:block;
                margin:auto;
            }
            .header{
                margin-bottom:20px;
            }
            .header div{
                margin-bottom:4px;
            }
        </style>
    </head>
    <body>
       ${getKopHtml(data)}
        <table>
    <thead>
<tr>
    <th width="40" class="text-center">
        <input type="checkbox" id="checkAllItems">
    </th>
    <th rowspan="2">Article Nr</th>
    <th rowspan="2">Gambar</th>
    <th rowspan="2">Nama Barang</th>
    ${customHeaderHtml}
    <th colspan="3">
        Ukuran
    </th>
    <th rowspan="2">
        Material
    </th>
    <th colspan="2">
        Qty
    </th>
    <th rowspan="2">
        Harga
    </th>
    <th rowspan="2">
        Total
    </th>
    <th rowspan="2">
        Catatan
    </th>
</tr>
<tr>
    <th>P</th>
    <th>L</th>
    <th>T</th>
    <th>PCS</th>
    <th>SET</th>
</tr>
</thead>
            <tbody>
                ${rows}
            </tbody>
        </table>
 <div style="
    width:100%;
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    margin-top:20px;
">
    <!-- kiri -->
 <div style="
    flex:1;
    padding-right:20px;
    font-size:13px;
    line-height:1.8;
">
    ${renderAgreement()}
</div>
    <!-- kanan -->
    <div style="
        width:500px;
        flex-shrink:0;
    ">
        ${renderPaymentSection(data)}
    </div>
</div>
 ${renderSignaturePreview(data)}
        <script>
            window.onload = function(){
                window.print();
            }
        <\/script>
    </body>
    </html>
    `;
    const win = window.open('', '_blank');
    win.document.open();
    win.document.write(html);
    win.document.close();
}
function renderAgreement() {
    return `
        <div>
            <ol style="padding-left:18px;margin:0;">
                <li>Spesifikasi barang harus sesuai dengan sample.</li>
                <li>Harga belum termasuk transportasi sampai gudang NewWicker.</li>
                <li>Supplier bertanggung jawab atas ketidaksesuaian spesifikasi barang.</li>
                <li>Final Quality Controlling akan dilaksanakan di gudang NewWicker.</li>
                <li>Supplier dikenakan penalty 1% setiap harinya atas keterlambatan produksi.</li>
                <li>Supplier wajib melaporkan perkembangan produksi dan permasalahan yang dapat menghambat kelancaran produksi.</li>
                <li>Penyelesaian pembayaran dilakukan setelah supplier memenuhi semua kewajibannya.</li>
                <li>Supplier dilarang memberikan hadiah atau komisi dalam bentuk uang kepada karyawan dan staff PT. NewWicker.</li>
            </ol>

            <div style="margin-top:15px">
                Dengan Anda.
            </div>
        </div>
    `;
}
function renderSignatureSection(data)
{
    return `
    <div style="
        width:100%;
    ">
        <table style="
            width:100%;
            border-collapse:collapse;
            border:none;
        ">
            <tr>
                <td style="
                    border:none;
                    width:20%;
                    text-align:center;
                    font-weight:bold;
                ">
                    Made By :
                </td>
                <td style="
                    border:none;
                    width:20%;
                    text-align:center;
                    font-weight:bold;
                ">
                    Checked By :
                </td>
                <td style="
                    border:none;
                    width:20%;
                    text-align:center;
                    font-weight:bold;
                ">
                    Approved By :
                </td>
                <td style="
                    border:none;
                    width:20%;
                    text-align:center;
                    font-weight:bold;
                ">
                    Know By :
                </td>
                <td style="
                    border:none;
                    width:20%;
                    text-align:center;
                    font-weight:bold;
                ">
                    Supplier
                </td>
            </tr>
            <tr>
                <td colspan="5"
                    style="
                        border:none;
                        height:80px;
                    ">
                </td>
            </tr>
            <tr>
                <td style="
                    border:none;
                    text-align:center;
                    font-weight:bold;
                ">
                  "Nur"
                </td>
                <td style="
                    border:none;
                    text-align:center;
                    font-weight:bold;
                ">
                    VIVI
                </td>
                <td style="
                    border:none;
                    text-align:center;
                    font-weight:bold;
                ">
                    Mr. Stanley
                </td>
                <td style="
                    border:none;
                    text-align:center;
                    font-weight:bold;
                ">
                </td>
                <td style="
                    border:none;
                    text-align:center;
                    font-weight:bold;
                ">
                    ${data.supplier.nama_supplier}
                </td>
            </tr>
            <tr>
                <td style="border:none"></td>
                <td style="
                    border:none;
                    text-align:center;
                    font-weight:bold;
                ">
                    Purchasing
                </td>
                <td style="
                    border:none;
                    text-align:center;
                    font-weight:bold;
                ">
                    General Manager
                </td>
                <td style="border:none"></td>
                <td style="border:none"></td>
            </tr>
        </table>
    </div>
    `;
}
function renderPaymentSection(data)
{
    let grandTotal = 0;
    data.items.forEach(item => {
        item.rows.forEach(detail => {
            grandTotal += parseFloat(
                String(detail.total || 0)
                .replace(/\./g,'')
                .replace(/,/g,'')
            ) || 0;
        });
    });
    const totalFormat = new Intl.NumberFormat('id-ID')
        .format(grandTotal);
    let paymentRows = '';
    data.payments.forEach(pay => {
        const note =
            pay.type ||
            pay.note ||
            '';
        const keterangan =
            pay.keterangan ||
            pay.note_tambahan ||
            note;
        paymentRows += `
            <tr>
                <td style="text-align:center">
                    ${pay.is_request ? '✓' : ''}
                </td>
                <td>
                    ${pay.amount || ''}
                </td>
                <td>
                    ${pay.date || ''}
                </td>
                <td>
                    ${note}
                </td>
                <td>
                    ${keterangan}
                </td>
            </tr>
        `;
    });
    const emptyRows = Math.max(
        0,
        6 - data.payments.length
    );
    for(let i = 0; i < emptyRows; i++) {
        paymentRows += `
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        `;
    }
    return `
    <div style="
        margin-top:-20px;
        width:100%;
        display:flex;
        justify-content:flex-end;
    ">
        <div style="
            width:500px;
        ">
            <!-- TOTAL -->
            <table style="
                width:100%;
                border-collapse:collapse;
                margin-bottom:6px;
            ">
                <tr>
                    <td style="
                        border:1px solid #000;
                        text-align:right;
                        padding-right:10px;
                        font-weight:bold;
                    ">
                        ${totalFormat}
                    </td>
                </tr>
            </table>
            <!-- PAYMENT -->
            <table style="
                width:100%;
                border-collapse:collapse;
            ">
                <thead>
                    <tr>
                        <th width="40">
                            Req
                        </th>
                        <th width="120">
                            Amount
                        </th>
                        <th width="80">
                            Date
                        </th>
                        <th width="100">
                            Note
                        </th>
                        <th>
                            Keterangan
                        </th>
                    </tr>
                </thead>
                <tbody>
                    ${paymentRows}
                </tbody>
            </table>
        </div>
    </div>
    `;
}
function getKopHtml(data) {
    return `
    <div style="
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        margin-bottom:20px;
    ">
        <!-- kiri -->
        <div>
            <img
                src="${window.location.origin}/assets/images/NEWWICKER WHITE.png"
                style="
                    width:220px;
                    height:auto;
                ">
        </div>
        <!-- kanan -->
        <div style="
            text-align:right;
            line-height:1.6;
        ">
            <div style="
                font-size:28px;
                font-weight:bold;
            ">
                PT. NewWicker Indonesia
            </div>
            Jalan Kisaba Lanang RT 019 RW 002,
            Bode Lor
            <br>
            Plumbon, Cirebon 45155
            <br>
            Indonesia
            <br><br>
            <span style="
                color:#0d6efd;
                text-decoration:underline;
            ">
                factory@newwicker.com
            </span>
        </div>
    </div>
<div style="
    border-top:2px solid #000;
    width:100%;
    margin-bottom:10px;
"></div>
    <div style="
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        margin-bottom:20px;
    ">
        <!-- kiri -->
        <div>
            <div style="margin-bottom:6px">
                <span style="
                    display:inline-block;
                    width:110px;
                ">
                    No Spk
                </span>
                :
                <span>
                    ${data.supplier.no_spk}
                </span>
            </div>
            <div style="margin-bottom:6px">
                <span style="
                    display:inline-block;
                    width:110px;
                ">
                    Nama
                </span>
                :
                <span style="
                    background:yellow;
                    padding:2px 6px;
                ">
                    ${data.supplier.nama_supplier}
                </span>
            </div>
            <div style="margin-bottom:6px">
                <span style="
                    display:inline-block;
                    width:110px;
                ">
                    Tgl Terima
                </span>
                :
                ${data.supplier.tgl_terima}
            </div>
            <div>
                <span style="
                    display:inline-block;
                    width:110px;
                ">
                    Tgl Selesai
                </span>
                :
                <span style="
                    background:yellow;
                    font-weight:bold;
                    padding:2px 6px;
                ">
                    ${data.supplier.tgl_selesai}
                </span>
            </div>
        </div>
        <!-- kanan -->
        <div style="
            background:yellow;
            padding:4px 12px;
            font-weight:bold;
            min-width:140px;
            text-align:center;
        ">
            ${data.supplier.no_po}
        </div>
    </div>
    `;
}
function validatePaymentLimit() {

    let grandTotal = 0;

    document.querySelectorAll('.total').forEach(td => {
        grandTotal += parseNumber(td.innerText);
    });

    let totalPayment = 0;

    document.querySelectorAll('.payment-row').forEach(row => {

        let originalAmount = parseNumber(
            row.querySelector('.total-amount')?.innerText
        );

        let adjustment = parseFloat(
            row.dataset.adjustment || 0
        );

        let amount =
            adjustment > 0
                ? adjustment
                : originalAmount;

        totalPayment += amount;

    });

    return {
        grandTotal,
        totalPayment,
        valid: totalPayment <= grandTotal
    };
}
document.addEventListener('input', function(e){
    const result = validatePaymentLimit();
    if(!e.target.classList.contains('total-amount')){
        return;
    }
    // if(!result.valid){
    //     Swal.fire({
    //         icon: 'warning',
    //         title: 'Nominal Melebihi Total SPK',
    //         text:
    //             'Total payment (' +
    //             formatRupiah(result.totalPayment) +
    //             ') melebihi total SPK (' +
    //             formatRupiah(result.grandTotal) +
    //             ')'
    //     });
    //     e.target.innerText = '';
    //     placeCaretAtEnd(e.target);
    // }
    updatePaymentSummary();
});
</script>
<script>
    let checkedTypesFromServer =
        @json($spk['checked_types'] ?? []);
    console.log(checkedTypesFromServer);
</script>
{{-- spk approver --}}
<script>
      $(document).on('click','.btn-sign',function(){

    let id = $(this).data('id');
    let type = $(this).data('type');

    switch (type) {
        case 'checked':
            title = 'Approve by Checker 1?';
            break;

        case 'checked_2':
            title = 'Approve by Checker 2?';
            break;

        case 'approved':
            title = 'Approve by Mr. Stanley?';
            break;

        default:
            title = 'Approve?';
    }

    Swal.fire({
        title: title,
        input: 'textarea',
        inputLabel: 'Remark',
        inputPlaceholder: 'Masukkan remark...',
        inputAttributes: {
            rows: 4
        },
        showCancelButton: true,
        confirmButtonText: 'Approve',
        cancelButtonText: 'Batal'
    }).then((result)=>{

        if(!result.isConfirmed){
            return;
        }

        $.ajax({
            url: `/spk/signature/${id}`,
            type: 'POST',
            data: {
                _token : $('meta[name="csrf-token"]').attr('content'),
                type   : type,
                remark : result.value
            },

            success: function(res){

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message
                }).then(()=>{
                    location.reload();
                });

            },

            error: function(xhr){

                let message = 'Terjadi kesalahan';

                if(xhr.responseJSON?.message){
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: message
                });

            }
        });

    });

});
$(function(){

    if($('#view_only').val() == 1){

        $('[contenteditable]')
            .attr('contenteditable', false);

        $('input').prop('disabled', true);

        $('textarea').prop('disabled', true);

        $('select').prop('disabled', true);

        $('#btnSaveSpk').hide();
        $('#btnAddPayment').hide();

        $('.btn-add-extra').hide();
        $('.btn-delete-extra').hide();

    }

});

function renderSignaturePreview(data) {

    let html = '';

    @if(isset($spk['signature']) && $spk['signature'])

    html = `
    <div style="
        margin-top:40px;
        width:100%;
    ">

        <table width="100%"
               border="1"
               style="
                    border-collapse:collapse;
                    text-align:center;
                    font-size:12px;
               ">

            <tr>
                <td><b>Made By</b></td>
                <td><b>Checked By</b></td>
                <td><b>Approved By</b></td>
                <td><b>Supplier</b></td>
            </tr>

            <!-- AREA TTD -->
            <tr>

                <!-- MADE -->
                <td style="height:90px;">
                    @if($spk['signature']->made_at)
                        <img
                            src="{{ asset('assets/signature/'.$spk['signature']->made_by.'.png') }}"
                            style="max-height:70px;max-width:120px;">
                    @endif
                </td>

                <!-- CHECKED -->
                <td style="height:90px;">
                    <div style="
                        display:flex;
                        justify-content:space-around;
                        align-items:center;
                        height:90px;
                    ">

                        <div style="flex:1;">
                            @if($spk['signature']->checked_at)
                                <img
                                    src="{{ asset('assets/signature/'.$spk['signature']->checked_by.'.png') }}"
                                    style="max-height:70px;max-width:120px;">
                            @endif
                        </div>

                        <div style="flex:1;">
                            @if($spk['signature']->checked_by_2)
                                <img
                                    src="{{ asset('assets/signature/'.$spk['signature']->checked_by_2.'.png') }}"
                                    style="max-height:70px;max-width:120px;">
                            @endif
                        </div>

                    </div>
                </td>

                <!-- APPROVED -->
                <td style="height:90px;">
                    @if($spk['signature']->approved_at)
                        <img
                            src="{{ asset('assets/signature/'.$spk['signature']->approved_by.'.png') }}"
                            style="max-height:70px;max-width:120px;">
                    @endif
                </td>

                <!-- SUPPLIER -->
                <td style="height:90px;"></td>

            </tr>

            <!-- NAMA -->
            <tr>

                <!-- MADE -->
                <td>
                    <b>{{ $spk['signature']->madeBy->name ?? '-' }}</b>
                    <br>

                    @if($spk['signature']->made_at)
                        Approved On<br>
                        {{ $spk['signature']->made_at->format('d/m/Y H:i') }}
                    @else
                        Pending
                    @endif
                </td>

                <!-- CHECKED -->
                <td>
                    <div style="
                        display:flex;
                        justify-content:space-around;
                        gap:20px;
                    ">

                        <div style="flex:1;">
                            <b>
                                {{ $spk['signature']->checkedBy->name ?? '-' }}
                            </b>
                            <br>

                            @if($spk['signature']->checked_at)
                                Approved On<br>
                                {{ $spk['signature']->checked_at->format('d/m/Y H:i') }}
                            @else
                                Pending
                            @endif
                        </div>

                        <div style="flex:1;">
                            <b>
                                {{ $spk['signature']->checkedBy2->name ?? '-' }}
                            </b>
                            <br>

                            @if($spk['signature']->checked_by_2)
                                Approved
                            @else
                                Pending
                            @endif
                        </div>

                    </div>
                </td>

                <!-- APPROVED -->
                <td>
                    <b>{{ $spk['signature']->approvedBy->name ?? '-' }}</b>
                    <br>

                    @if($spk['signature']->approved_at)
                        Approved On<br>
                        {{ $spk['signature']->approved_at->format('d/m/Y H:i') }}
                    @else
                        Pending
                    @endif
                </td>

                <!-- SUPPLIER -->
                <td>
                    <b>${data.supplier.nama_supplier}</b>
                </td>

            </tr>

        </table>

    </div>
    `;

    @endif

    return html;
}
// PPN
document.addEventListener('blur', function(e){

    if(!e.target.classList.contains('total-amount')) return;

    const row = e.target.closest('.payment-row');
    if(!row) return;

    const type = row.querySelector('.payment-type').value;

    if(type !== 'ppn') return;

    let persen = parseFloat(
        e.target.innerText
            .replace(/[^\d.,]/g,'')
            .replace(',','.')
    ) || 0;

    let grandTotal = 0;

    document.querySelectorAll('.total').forEach(td=>{
        grandTotal += parseNumber(td.innerText);
    });

    let nilaiPpn = grandTotal * persen / 100;

    e.target.innerText = formatRupiah(Math.round(nilaiPpn));

    updatePaymentSummary();

}, true);
</script>
<style>
    .spk-dynamic-header {
        background: #2f437f !important;
        color: white;
        font-weight: bold;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }
</style>
@endsection
