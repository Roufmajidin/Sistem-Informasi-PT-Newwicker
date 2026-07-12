@extends('master.master')
@section('title', 'Pengajuan PR')

@section('content')

<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link active" href="#">All Divisi</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#">Finance</a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="#">Create PR</a>
    </li>
</ul>
  <div class="box-body spk-wrapper">
        <!-- navigasi -->
        <ul class="nav nav-tabs mb-3 mt-4">
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#draft-request-tab">
                    Payment Request
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#payment-request-tab">
                    Draft Request
                </button>
            </li>
        </ul>
        <div class="tab-content">
           <div class="tab-pane fade" id="payment-request-tab">
                <div style="
                    background:white;
                    padding:20px;
                    font-family:Arial;
                    font-size:11px;
                ">
                    {{-- HEADER --}}
                    <table width="100%" style="
                        margin-bottom:10px;
                    ">
                        <tr>
                            {{-- LOGO --}}
                            <td width="25%">
                            <img src="{{ asset('/assets/images/NEWWICKER WHITE.png') }}" height="80">


                            </td>
                            {{-- TITLE --}}
                            <td width="50%" align="center">
                                <h2 style="
                                    margin:0;
                                    font-size:28px;
                                ">
                                    Purchase Request
                                </h2>
                            </td>
                            {{-- NEED DATE --}}
                            <td width="25%">
                                <table width="100%" style="
                                        border-collapse:collapse;
                                    ">
                                    <tr>
                                        <td style="
                                border:1px solid black;
                                padding:4px;
                                font-size:11px;
                            ">
                                            Need by Date :
                                        </td>
                                        <td style="
                                border:1px solid black;
                                padding:4px;
                                font-size:11px;
                            ">
                                            <input type="date" id="need_date"
                                                value="{{ now()->format('Y-m-d') }}" style="
                                    width:100%;
                                    border:none;
                                    outline:none;
                                    background:transparent;
                                    font-size:11px;
                                ">
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    {{-- INFO --}}
                    <table width="100%" style="
                        border-collapse:collapse;
                        margin-bottom:10px;
                    ">
                        <tr>
                            <td style="
                            border:1px solid black;
                            padding:4px;
                            width:180px;
                            font-weight:bold;
                        ">
                                Requisition Date :
                            </td>
                            <td style="
                    border:1px solid black;
                    padding:4px;
                ">
                                <input type="date" id="request_date"
                                    value="{{ now()->format('Y-m-d') }}" style="
                        width:100%;
                        border:none;
                        outline:none;
                        background:transparent;
                        font-size:11px;
                    ">
                            </td>
                            <td style="
                    border:1px solid black;
                    padding:4px;
                    width:180px;
                    font-weight:bold;
                ">
                                Department :
                            </td>
                            <td style="
                    border:1px solid black;
                    padding:4px;
                ">
                                <input type="text" value="Purchasing" style="
                        width:100%;
                        border:none;
                        outline:none;
                        background:transparent;
                        font-size:11px;
                    ">
                            </td>
                        </tr>
                    </table>
                    {{-- SAVE BUTTON --}}
                    <div style="
            text-align:right;
            margin-bottom:10px;
        ">
                        <button id="btn-save-request" style="
                background:#111827;
                color:white;
                border:none;
                padding:8px 18px;
                border-radius:6px;
                font-size:12px;
                font-weight:bold;
                cursor:pointer;
            ">
                            💾 Save Draft Request
                        </button>
                    </div>
                    {{-- TABLE --}}
                    <table id="prTable" width="100%" style="border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f3f4f6;">
                                <th style="border:1px solid #000;width:50px">No</th>
                                <th style="border:1px solid #000;">Kebutuhan/Material</th>
                                <th style="border:1px solid #000;">Supplier/Vendor</th>
                                <th style="border:1px solid #000;">Payment</th>
                           <th style="border:1px solid #000;width:500px">
                            Description
                        </th>

                                <th style="border:1px solid #000;">Keterangan</th>
                                <th style="border:1px solid #000;width:80px">Qty</th>
                                <th style="border:1px solid #000;width:70px">Sat</th>
                                <th style="border:1px solid #000;width:120px">Unit Price</th>
                                <th style="border:1px solid #000;width:120px">Total</th>
                                <th style="border:1px solid #000;width:90px">Status</th>
                            </tr>
                        </thead>

                        <tbody id="prBody">
                        </tbody>

                        <tfoot>
                            <tr>
                                <td colspan="9"
                                    style="border:1px solid #000;text-align:right;font-weight:bold;padding-right:10px;">
                                    TOTAL
                                </td>

                                <td id="grandTotal"
                                    style="border:1px solid #000;font-weight:bold;padding:5px;">
                                    0
                                </td>

                                <td style="border:1px solid #000;"></td>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="mt-2">
                        <button class="btn btn-sm btn-success" id="addRow">
                            + Add Row
                        </button>
                    </div>

{{-- approval --}}
<div class="" id="approvalSignature"></div>

<datalist id="userList">
    @foreach($users as $user)
        <option
            value="{{ $user->name }}"
            data-id="{{ $user->id }}">
        </option>
    @endforeach
</datalist>
<script>

let rowIndex = 0;

function renderApprovalSignature(grandTotal)
{
    const preparedName = "{{ auth()->user()->name }}";

    let html = '';

    if (grandTotal >= 5000000) {

        html = `
        <table width="100%" style="
            margin-top:50px;
            text-align:center;
            font-size:12px;
        ">
            <tr>
                <td><b>Prepared by,</b></td>
                <td><b>Checked by,</b></td>
                <td><b>Knowed by,</b></td>
                <td><b>Approved by,</b></td>
                <td><b>Approved by,</b></td>
            </tr>

            <tr>
                <td>${preparedName}</td>
                <td>Department</td>
                <td>Finance Accounting</td>
                <td>General Manager</td>
                <td>CEO</td>
            </tr>

            <tr>
                <td colspan="5" style="height:100px"></td>
            </tr>

            <tr>
                <td>
                    ________________________
                    <br>
                    ${preparedName}
                </td>

                <td>
                    ________________________
                    <br>
                    <input
                        type="text"
                        list="userList"
                        class="approval-input"
                        placeholder="Nama Department">
                </td>

                <td>
                    Ulfah Nabila Oktadiniah
                </td>

                <td>
                    Eka Wahyuni Lestari
                </td>

                <td>
                    Hermanus Bernandus Johanes Tans
                </td>
            </tr>
        </table>
        `;

    } else {

        html = `
        <table width="70%" style="
            margin-top:50px;
            text-align:center;
            font-size:12px;
        ">
            <tr>
                <td><b>Prepared by,</b></td>
                <td><b>Checked by,</b></td>
                <td><b>Approved by,</b></td>
            </tr>

            <tr>
                <td>${preparedName}</td>
                <td>Department</td>
                <td>Finance Accounting</td>
            </tr>

            <tr>
                <td colspan="3" style="height:100px"></td>
            </tr>

            <tr>
                <td>
                    ________________________
                    <br>
                    ${preparedName}
                </td>

                <td>
                    ________________________
                    <br>
                    <input
                        type="text"
                        list="userList"
                        class="approval-input"
                        placeholder="Nama Department">
                </td>

                <td>
                    Ulfah Nabila Oktadiniah
                </td>
            </tr>
        </table>
        `;
    }

    $('#approvalSignature').html(html);
}
$(document).ready(function () {
    renderApprovalSignature(0);
});
function createRow() {

    rowIndex++;

    return `
    <tr>
        <td style="border:1px solid #000;text-align:center;">
            ${rowIndex}
        </td>

        <td style="border:1px solid #000">
            <input name="items[${rowIndex}][material]"
                   class="sheet-input">
        </td>

        <td style="border:1px solid #000">
            <input name="items[${rowIndex}][vendor]"
                   class="sheet-input">
        </td>

        <td style="border:1px solid #000">
            <input name="items[${rowIndex}][payment]"
                   class="sheet-input">
        </td>

        <td style="border:1px solid #000">
            <input name="items[${rowIndex}][description]"
                   class="sheet-input">
        </td>

        <td style="border:1px solid #000">
            <input name="items[${rowIndex}][remark]"
                   class="sheet-input">
        </td>

        <td style="border:1px solid #000">
            <input type="number"
                   class="sheet-input qty">
        </td>

        <td style="border:1px solid #000">
            <input class="sheet-input">
        </td>

        <td style="border:1px solid #000">
            <input type="number"
                   class="sheet-input price">
        </td>

        <td style="border:1px solid #000">
            <input readonly
                   class="sheet-input total">
        </td>

        <td style="border:1px solid #000">
            <select class="sheet-input">
                <option>P1</option>
                <option>P2</option>
                <option>P3</option>
                <option>P4</option>
            </select>
        </td>
    </tr>`;
}

$(function(){

    for(let i=0;i<5;i++){
        $('#prBody').append(createRow());
    }

});

$('#addRow').on('click', function(){
    $('#prBody').append(createRow());
});

$(document).on('input','.qty,.price',function(){

    let row = $(this).closest('tr');

    let qty = parseFloat(row.find('.qty').val()) || 0;
    let price = parseFloat(row.find('.price').val()) || 0;

    let total = qty * price;

    row.find('.total').val(total);

    hitungGrandTotal();
});

function hitungGrandTotal(){
    let grand = 0;
    $('#prBody tr').each(function(){

        let qty = parseFloat($(this).find('.qty').val()) || 0;
        let price = parseFloat($(this).find('.price').val()) || 0;
        grand += qty * price;
    });
    $('#grandTotal').html(
        grand.toLocaleString('id-ID')
    );
    renderApprovalSignature(grand);

}
</script>
<style>
    .approval-input{
    width:100%;
    border:none;
    outline:none;
    background:transparent;
    text-align:center;
    font-size:12px;
}

.approval-input:focus{
    background:#fffbe6;
}
    .sheet-input{
    width:100%;
    border:none;
    outline:none;
    background:transparent;
    padding:4px;
    font-size:11px;
}

.sheet-input:focus{
    background:#fffbe6;
}
</style>
@endsection
