@extends('master.master')

@section('title', "Inventory Assets")

@section('content')

@php
    $isAdminInventory = auth()->check() && auth()->id() == 144;
@endphp

<div class="padding">

    <div class="box">

        <div class="p-a white lt box-shadow">

            <div class="row">

                <div class="col-sm-6">

                    <smal class="text-muted">
                        List Inventory
                    </small>

                    <br>

                    <small class="text-danger">
                        Double klik untuk melihat detail.
                    </small>

                    @if($isAdminInventory)

                    <div class="mb-2 mt-2">

                        <button class="btn btn-sm btn-primary"
                            id="btnTambahRow">

                            + Tambah Data

                        </button>

                    </div>

                    @endif

                </div>

            </div>

        </div>

        <!-- TABLE -->
        <div class="col-12">

            <div class="table-wrapper">

                <table class="table table-bordered"
                    id="inventoryTable">

                    <thead style="color:white">

                        <tr class="sticky-header"
                            style="font-size:12px;">

                            <th>No</th>
                            <th>Merk</th>
                            <th>Jenis</th>
                            <th>Description</th>
                            <th>Pemegang</th>
                            <th>Keterangan</th>
                            <th>Catatan</th>
                            <th>Foto</th>

                        </tr>

                    </thead>

                    <tbody>

                        @php $no = 1; @endphp

                        @forelse($data as $i)

                        <tr class="inventory-row"
                            data-url="{{ route('inventory.detail', $i->id) }}"
                            style="font-size:13px; cursor:pointer;">

                            <td>
                                {{ $no++ }}
                            </td>

                            <!-- MERK -->
                            <td>

                                @if($isAdminInventory)

                                <a href="#"
                                   class="editable-merk"
                                   data-name="merk"
                                   data-pk="{{ $i->id }}"
                                   data-type="text"
                                   data-url="/inventory-inline-update">

                                    {{ $i->merk }}

                                </a>

                                @else

                                <span>
                                    {{ $i->merk }}
                                </span>

                                @endif

                            </td>

                            <!-- JENIS -->
                            <td>

                                @if($isAdminInventory)

                                <a href="#"
                                   class="editable-jenis"
                                   data-name="jenis"
                                   data-pk="{{ $i->id }}"
                                   data-type="text"
                                   data-url="/inventory-inline-update">

                                    {{ $i->jenis }}

                                </a>

                                @else

                                <span>
                                    {{ $i->jenis }}
                                </span>

                                @endif

                            </td>

                            <!-- DESKRIPSI -->
                            <td>

                                @if($isAdminInventory)

                                <a href="#"
                                   class="editable-deskripsi"
                                   data-name="deskripsi"
                                   data-pk="{{ $i->id }}"
                                   data-type="text"
                                   data-url="/inventory-inline-update">

                                    {{ $i->deskripsi }}

                                </a>

                                @else

                                <span>
                                    {{ $i->deskripsi }}
                                </span>

                                @endif

                            </td>

                            <!-- PEMEGANG -->
                            <td>

                                @if($isAdminInventory)

                                <a href="#"
                                   class="editable-karyawan"
                                   data-name="karyawan"
                                   data-pk="{{ $i->id }}"
                                   data-type="text"
                                   data-url="/inventory-inline-update">

                                    {{ $i->karyawan->nama_lengkap ?? '-' }}

                                </a>

                                @else

                                <span>
                                    {{ $i->karyawan->nama_lengkap ?? '-' }}
                                </span>

                                @endif

                            </td>

                            <!-- KETERANGAN -->
                            <td>

                                @if($isAdminInventory)

                                <a href="#"
                                   class="editable-keterangan"
                                   data-name="keterangan"
                                   data-pk="{{ $i->id }}"
                                   data-type="text"
                                   data-url="/inventory-inline-update">

                                    {{ $i->keterangan }}

                                </a>

                                @else

                                <span>
                                    {{ $i->keterangan }}
                                </span>

                                @endif

                            </td>

                            <!-- CATATAN -->
                            <td>

                                @if($isAdminInventory)

                                <a href="#"
                                   class="editable-catatan"
                                   data-name="catatan"
                                   data-pk="{{ $i->id }}"
                                   data-type="text"
                                   data-url="/inventory-inline-update">

                                    {{ $i->catatan }}

                                </a>

                                @else

                                <span>
                                    {{ $i->catatan }}
                                </span>

                                @endif

                            </td>

                            <!-- FOTO -->
                            <td>

                                @if($isAdminInventory)

                                <a href="#"
                                   class="upload-foto"
                                   data-id="{{ $i->id }}">

                                @endif

                                    @if($i->foto)

                                        <img src="{{ asset('foto_inventory/' . $i->foto) }}"
                                             width="60">

                                    @else

                                        <span>No Image</span>

                                    @endif

                                @if($isAdminInventory)

                                </a>

                                <input type="file"
                                       accept="image/*"
                                       class="input-foto d-none"
                                       id="foto-input-{{ $i->id }}"
                                       data-id="{{ $i->id }}">

                                @endif

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="8"
                                class="text-center">

                                Tidak ada data

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>

$.ajaxSetup({

    headers: {
        "X-CSRF-TOKEN":
        $('meta[name="csrf-token"]').attr("content"),
    },

});

@if($isAdminInventory)

// ========================
// TAMBAH ROW
// ========================
$("#btnTambahRow").on("click", function(){

    if($("#rowInput").length > 0) return;

    let newRow = `

        <tr id="rowInput">

            <td>New</td>

            <td>
                <input type="text"
                    class="form-control form-control-sm"
                    name="merk">
            </td>

            <td>
                <input type="text"
                    class="form-control form-control-sm"
                    name="jenis">
            </td>

            <td>
                <input type="text"
                    class="form-control form-control-sm"
                    name="deskripsi">
            </td>

            <td>
                <input type="text"
                    class="form-control form-control-sm"
                    name="keterangan">
            </td>

            <td>
                <input type="text"
                    class="form-control form-control-sm"
                    name="catatan">
            </td>

            <td>
                <input type="file"
                    class="form-control-file"
                    name="foto">
            </td>

            <td>

                <button class="btn btn-success btn-sm"
                    id="btnSaveRow">

                    Simpan

                </button>

            </td>

        </tr>

    `;

    $("#inventoryTable tbody").prepend(newRow);

});

// ========================
// SAVE
// ========================
$(document).on("click", "#btnSaveRow", function(){

    let formData = new FormData();

    $("#rowInput").find("input").each(function(){

        let name = $(this).attr("name");

        if($(this).attr("type") == "file"){

            if(this.files[0]){

                formData.append(name, this.files[0]);

            }

        }else{

            formData.append(name, $(this).val());

        }

    });

    $.ajax({

        url:"/inventory",

        method:"POST",

        data:formData,

        processData:false,

        contentType:false,

        success:function(){

            location.reload();

        }

    });

});

// ========================
// UPLOAD FOTO
// ========================
$(document).on("click", ".upload-foto", function(e){

    e.preventDefault();

    let id = $(this).data("id");

    $("#foto-input-" + id).trigger("click");

});

$(document).on("change", ".input-foto", function(){

    let id = $(this).data("id");

    let file = this.files[0];

    let formData = new FormData();

    formData.append("foto", file);

    $.ajax({

        url:`/inventory/${id}/upload-foto`,

        type:"POST",

        data:formData,

        processData:false,

        contentType:false,

        success:function(){

            location.reload();

        }

    });

});

@endif

// ========================
// DETAIL PAGE
// ========================
$(document).on("dblclick", ".inventory-row td", function(){

    if($(this).find("input").length > 0){

        return;

    }

    let url = $(this).closest("tr").data("url");

    if(url){

        window.location.href = url;

    }

});

</script>

@endsection
