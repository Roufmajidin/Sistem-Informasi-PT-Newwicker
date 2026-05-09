@extends('master.master')
@section('title', "bank data - master data")

@section('content')

<div class="container-fluid mt-4">

    {{-- CARD --}}
    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Upload Excel Bank Data</h5>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6">

                    <label class="form-label fw-bold">
                        Upload Excel
                    </label>

                    <input
                        type="file"
                        id="excelFile"
                        class="form-control"
                        accept=".xls,.xlsx"
                    >

                </div>

                <div class="col-md-3 d-flex align-items-end">

                    <button
                        class="btn btn-success w-100"
                        id="btnGenerate"
                    >
                        Generate Excel
                    </button>

                </div>

            </div>

        </div>

    </div>

    {{-- LOADING --}}
    <div
        id="loadingArea"
        class="text-center mt-4 d-none"
    >

        <div class="spinner-border text-primary"></div>

        <div class="mt-2 fw-bold">
            Loading Excel...
        </div>

    </div>

    {{-- RESULT --}}
    <div
        class="card mt-4 shadow-sm d-none"
        id="resultCard"
    >

        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Preview Table</h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table
                    class="table table-bordered table-hover align-middle"
                    id="previewTable"
                >
                    <thead></thead>
                    <tbody></tbody>
                </table>

            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')

<script>

$(document).ready(function(){

    // =====================================
    // GENERATE
    // =====================================

   $('#btnGenerate').click(function(){

    let file = $('#excelFile')[0].files[0];

    if(!file){

        Swal.fire({
            icon:'warning',
            title:'Oops...',
            text:'Upload file excel terlebih dahulu'
        });

        return;
    }

    let formData = new FormData();

    formData.append('excel_file', file);

    formData.append('_token', '{{ csrf_token() }}');

    // =====================================
    // LOADING SWAL
    // =====================================

    Swal.fire({

        title: 'Loading Excel...',

        html: 'Sedang generate data excel',

        allowOutsideClick: false,

        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({

        url: '{{ route("bank-data.upload") }}',

        type: 'POST',

        data: formData,

        processData: false,

        contentType: false,

        success: function(res){

    Swal.close();

    renderTable(res.rows, res.images);

    // =====================================
    // HITUNG ITEMS
    // =====================================

    let totalItems = 0;

    for(let i = 1; i < res.rows.length; i++){

        let row = res.rows[i];

        let isEmpty = row.every(cell => {

            return (
                cell === null ||
                cell.toString().trim() === ''
            );
        });

        if(!isEmpty){
            totalItems++;
        }
    }

    // =====================================
    // NAMA FILE
    // =====================================

    let fileName = file.name;

    // hapus extension
    fileName = fileName.replace(/\.[^/.]+$/, "");

    // =====================================
    // SUCCESS SWAL
    // =====================================

    Swal.fire({

        icon:'success',

        title:'Berhasil',

        html: `
            <div style="font-size:15px">

                Berhasil generate dari excel buyer

                <br><br>

                <b>${fileName}</b>

                <br><br>

                Jumlah Items :

                <b>${totalItems}</b>

            </div>
        `
    });
},

        error: function(err){

            Swal.close();

            Swal.fire({
                icon:'error',
                title:'Gagal',
                text:'Gagal upload excel'
            });
        }
    });
});

    // =====================================
    // RENDER TABLE
    // =====================================

    function renderTable(rows, imagesMap){

        if(rows.length == 0){
            return;
        }

        // =====================================
        // HEADER DINAMIS
        // =====================================

        let headers = rows[0];

        let thead = '<tr>';

        headers.forEach(function(header){

            header = header ?? '';

            let bg = '#00B0F0';

            // yellow
            if(
                header.toString().toLowerCase().includes('remark') ||
                header.toString().toLowerCase().includes('comment') ||
                header.toString().toLowerCase().includes('finishing steps')
            ){
                bg = '#FFFF00';
            }

            // orange
            if(
                header.toString().toLowerCase().includes('uom box') ||
                header.toString().toLowerCase() == 'nw' ||
                header.toString().toLowerCase() == 'gw' ||
                header.toString().toLowerCase() == 'cbm'
            ){
                bg = '#F4B183';
            }

            thead += `
                <th style="
                    background:${bg};
                    color:black;
                    text-align:center;
                    vertical-align:middle;
                    white-space:nowrap;
                    font-size:12px;
                ">
                    ${header}
                </th>
            `;
        });

        thead += '</tr>';

        $('#previewTable thead').html(thead);

        // =====================================
        // BODY
        // =====================================

        let tbody = '';

        for(let r = 1; r < rows.length; r++){

            let row = rows[r];

            // skip kosong
            let isEmpty = row.every(cell => {

                return (
                    cell === null ||
                    cell.toString().trim() === ''
                );
            });

            if(isEmpty){
                continue;
            }

            tbody += '<tr>';

            headers.forEach(function(header, cIndex){

                let value = row[cIndex] ?? '';

                value = value.toString()
                             .replace(/\n/g,'<br>');

                // =====================================
                // IMAGE
                // =====================================

                if(
                    header &&
                    header.toString().toLowerCase().includes('photo')
                ){

                    let key = `${r}-${cIndex}`;

                    if(imagesMap[key]){

                        value = `
                            <img
                                src="${imagesMap[key]}"
                                style="
                                    width:100px;
                                    height:100px;
                                    object-fit:cover;
                                    border:1px solid #ddd;
                                    border-radius:6px;
                                    background:#fff;
                                "
                            >
                        `;

                    } else {

                        value = `
                            <div style="
                                width:100px;
                                height:100px;
                                background:#eee;
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                color:#999;
                                font-size:12px;
                            ">
                                No Image
                            </div>
                        `;
                    }
                }

                tbody += `
                    <td style="
                        min-width:120px;
                        vertical-align:top;
                        white-space:pre-line;
                        font-size:12px;
                    ">
                        ${value}
                    </td>
                `;
            });

            tbody += '</tr>';
        }

        $('#previewTable tbody').html(tbody);

        $('#resultCard').removeClass('d-none');
    }

});

</script>

@endpush
