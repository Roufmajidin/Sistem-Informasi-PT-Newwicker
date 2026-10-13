
@extends('master.master')

@section('content')

<style>
    .access-page .card {
        border: 1px solid #e9edf3;
        border-radius: 10px;
    }

    .access-page .card-header {
        background: #fff;
        border-bottom: 1px solid #edf0f5;
        padding: 18px 22px;
    }

    .access-page .table th {
        font-weight: 600;
        vertical-align: middle;
        white-space: nowrap;
    }

    .access-page .table td {
        vertical-align: middle;
    }

    .access-check-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .access-check-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 11px;
        border: 1px solid #e0e5ed;
        border-radius: 6px;
        background: #f8fafc;
        font-size: 12px;
        cursor: pointer;
        margin-bottom: 0;
        transition: all .2s ease;
    }

    .access-check-item input {
        margin: 0;
        cursor: pointer;
    }

    .access-check-item:has(input:checked) {
        background: #eaf4ff;
        border-color: #8cc5ff;
        color: #0866bd;
    }

    .access-check-item:has(input:disabled) {
        opacity: .65;
        cursor: wait;
    }

    .access-save-status {
        display: block;
        min-height: 18px;
        font-size: 11px;
        margin-top: 7px;
    }

    /* DRAWER */
    .access-drawer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .48);
        z-index: 1048;
        display: none;
    }

    .access-drawer {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        width: 550px;
        max-width: 95vw;
        background: #fff;
        z-index: 1049;
        transform: translateX(-105%);
        transition: transform .3s ease;
        display: flex;
        flex-direction: column;
        box-shadow: 5px 0 25px rgba(0,0,0,.12);
    }

    .access-drawer.open {
        transform: translateX(0);
    }

    .access-drawer-header {
        padding: 22px 25px;
        border-bottom: 1px solid #e9edf3;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .access-drawer-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
    }

    .access-drawer-footer {
        padding: 15px 24px;
        border-top: 1px solid #e9edf3;
        background: #fff;
    }

    .access-module-form {
        background: #f8fafc;
        border: 1px solid #e9edf3;
        border-radius: 8px;
        padding: 18px;
        margin-bottom: 22px;
    }

    .access-drawer .table th,
    .access-drawer .table td {
        vertical-align: middle;
    }

    body.access-drawer-active {
        overflow: hidden;
    }

    @media (max-width: 576px) {
        .access-drawer {
            width: 100%;
            max-width: 100%;
        }
    }
</style>

<div class="container-fluid access-page">

    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">

            <div>
                <h4 class="mb-1">Access Control</h4>
                <small class="text-muted">
                    Management user access application & website
                </small>
            </div>

            <button type="button"
                    class="btn btn-primary"
                    id="btnViewAccess">
                <i class="fa fa-list-alt mr-1"></i>
                View Access
            </button>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover"
                       id="tableUserAccess">

                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Name</th>
                            <th>User ID</th>
                            <th>Divisi</th>
                            <th>Akses</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($users as $index => $user)

                        <tr>

                            <td>{{ $index + 1 }}</td>

                            <td>
                                <strong>{{ $user->name }}</strong>
                            </td>

                            <td>{{ $user->id }}</td>

                            <td>
                                {{ $user->karyawan?->divisi?->name ?? '-' }}
                            </td>

                            <td>

                                <div class="access-check-list">

                                    @foreach($modules as $module)

                                    <label class="access-check-item">

                                        <input
                                            type="checkbox"
                                            class="auto-save-access"
                                            name="access[]"
                                            value="{{ $module->id }}"
                                            data-user-id="{{ $user->id }}"
                                            data-module-id="{{ $module->id }}"
                                            {{ $user->accessModules->contains('id', $module->id) ? 'checked' : '' }}
                                        >

                                        <span>{{ $module->name }}</span>

                                    </label>

                                    @endforeach

                                </div>

                                <small class="access-save-status text-muted"
                                       aria-live="polite"></small>

                            </td>

                        </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


{{-- DRAWER OVERLAY --}}
<div class="access-drawer-overlay"
     id="accessDrawerOverlay"></div>


{{-- DRAWER LEFT --}}
<div class="access-drawer"
     id="accessDrawer"
     role="dialog"
     aria-modal="true"
     aria-labelledby="accessDrawerTitle">

    <div class="access-drawer-header">

        <div>
            <h4 class="mb-1" id="accessDrawerTitle">
                Access Modules
            </h4>

            <small class="text-muted">
                Manage application access modules
            </small>
        </div>

        <button type="button"
                class="btn btn-light"
                id="btnCloseDrawer"
                aria-label="Close drawer">
            <i class="fa fa-times"></i>
        </button>

    </div>

    <div class="access-drawer-body">

        {{-- ADD / EDIT MODULE --}}
        <div class="access-module-form">

            <h5 id="moduleFormTitle" class="mb-3">
                <i class="fa fa-plus-circle"></i>
                Add Access Module
            </h5>

            <form id="formAccessModule">

                @csrf

                <input type="hidden"
                       id="moduleId"
                       name="id">

                <div class="form-group">

                    <label for="moduleName">
                        Module Name
                    </label>

                    <input type="text"
                           class="form-control"
                           id="moduleName"
                           name="name"
                           placeholder="Enter module name..."
                           maxlength="150"
                           required>

                </div>

                <div class="d-flex justify-content-end">

                    <button type="button"
                            class="btn btn-secondary mr-2"
                            id="btnCancelEdit"
                            style="display:none">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-primary"
                            id="btnSaveModule">

                        <i class="fa fa-save"></i>
                        Save Module

                    </button>

                </div>

            </form>

        </div>


        {{-- MODULE TABLE --}}
        <div class="d-flex justify-content-between align-items-center mb-3">

            <h5 class="mb-0">Module List</h5>

            <span class="badge badge-primary" id="moduleCount">
                {{ $modules->count() }} Modules
            </span>

        </div>

        <div class="table-responsive">

            <table class="table table-bordered table-hover"
                   id="tableAccessModule">

                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Module Name</th>
                        <th width="110">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($modules as $index => $module)

                    <tr data-id="{{ $module->id }}">

                        <td>{{ $index + 1 }}</td>

                        <td class="module-name">
                            {{ $module->name }}
                        </td>

                        <td>

                            <button type="button"
                                    class="btn btn-warning btn-sm btnEditModule"
                                    data-id="{{ $module->id }}"
                                    data-name="{{ $module->name }}"
                                    title="Edit">
                                <i class="fa fa-pencil"></i>
                            </button>

                            <button type="button"
                                    class="btn btn-danger btn-sm btnDeleteModule"
                                    data-id="{{ $module->id }}"
                                    title="Delete">
                                <i class="fa fa-trash"></i>
                            </button>

                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

    <div class="access-drawer-footer text-right">

        <button type="button"
                class="btn btn-secondary"
                id="btnCloseDrawerFooter">
            Close
        </button>

    </div>

</div>

@endsection


@push('scripts')

<script>
$(document).ready(function() {

    const csrfToken = $('meta[name="csrf-token"]').attr('content')
        || '{{ csrf_token() }}';

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    });

    // ========================================
    // AUTO SAVE USER ACCESS
    // ========================================

    $(document).on('change', '.auto-save-access', function() {

        let checkbox = $(this);
        let userId = checkbox.data('user-id');
        let moduleId = checkbox.data('module-id');
        let isChecked = checkbox.is(':checked');

        let status = checkbox.closest('td')
            .find('.access-save-status');

        checkbox.prop('disabled', true);

        status.stop(true, true)
            .removeClass('text-success text-danger')
            .addClass('text-muted')
            .html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: "{{ route('it.access-update') }}",
            type: "POST",

            data: {
                user_id: userId,
                module_id: moduleId,
                is_checked: isChecked ? 1 : 0
            },

            success: function(response) {

                status.stop(true, true)
                    .removeClass('text-muted text-danger')
                    .addClass('text-success')
                    .html('<i class="fa fa-check-circle"></i> Saved');

                setTimeout(function() {
                    if (status.text().trim() === 'Saved') {
                        status.fadeOut(300, function() {
                            $(this).html('').show();
                        });
                    }
                }, 1500);

            },

            error: function(xhr) {

                // Kembalikan checkbox jika gagal
                checkbox.prop('checked', !isChecked);

                status.stop(true, true)
                    .removeClass('text-muted text-success')
                    .addClass('text-danger')
                    .html(
                        '<i class="fa fa-exclamation-circle"></i> Failed'
                    );

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal menyimpan',
                    text: xhr.responseJSON?.message
                        || 'Terjadi kesalahan saat menyimpan akses.'
                });

            },

            complete: function() {
                checkbox.prop('disabled', false);
            }

        });

    });


    // ========================================
    // DRAWER
    // ========================================

    function openDrawer() {
        $('#accessDrawerOverlay').fadeIn(200);
        $('#accessDrawer').addClass('open');
        $('body').addClass('access-drawer-active');
        $('#btnCloseDrawer').trigger('focus');
    }

    function closeDrawer() {
        $('#accessDrawer').removeClass('open');
        $('#accessDrawerOverlay').fadeOut(200);
        $('body').removeClass('access-drawer-active');
        $('#btnViewAccess').trigger('focus');
    }

    $('#btnViewAccess').on('click', openDrawer);

    $('#btnCloseDrawer, #btnCloseDrawerFooter, #accessDrawerOverlay')
        .on('click', closeDrawer);

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#accessDrawer').hasClass('open')) {
            closeDrawer();
        }
    });


    // ========================================
    // RESET MODULE FORM
    // ========================================

    function resetModuleForm() {

        $('#formAccessModule')[0].reset();
        $('#moduleId').val('');

        $('#moduleFormTitle').html(
            '<i class="fa fa-plus-circle"></i> Add Access Module'
        );

        $('#btnSaveModule').html(
            '<i class="fa fa-save"></i> Save Module'
        );

        $('#btnCancelEdit').hide();
    }

    $('#btnCancelEdit').on('click', resetModuleForm);


    // ========================================
    // EDIT MODULE
    // ========================================

    $(document).on('click', '.btnEditModule', function() {

        let id = $(this).attr('data-id');
        let name = $(this).attr('data-name');

        $('#moduleId').val(id);
        $('#moduleName').val(name);

        $('#moduleFormTitle').html(
            '<i class="fa fa-pencil"></i> Edit Access Module'
        );

        $('#btnSaveModule').html(
            '<i class="fa fa-save"></i> Update Module'
        );

        $('#btnCancelEdit').show();
        $('#moduleName').focus();

    });


    // ========================================
    // SAVE / UPDATE MODULE
    // ========================================

    $('#formAccessModule').on('submit', function(e) {

        e.preventDefault();

        let id = $('#moduleId').val();

        let url = id
            ? "{{ route('it.module.update', ':id') }}".replace(':id', id)
            : "{{ route('it.module.store') }}";

        let method = id ? 'PUT' : 'POST';

        let btn = $('#btnSaveModule');

        btn.prop('disabled', true)
           .html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: url,
            type: method,

            data: {
                name: $('#moduleName').val()
            },

            success: function(response) {

                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    timer: 1200,
                    showConfirmButton: false
                }).then(function() {
                    location.reload();
                });

            },

            error: function(xhr) {

                let message = xhr.responseJSON?.message
                    || 'Failed to save module';

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message
                });

            },

            complete: function() {
                btn.prop('disabled', false);

                btn.html(
                    '<i class="fa fa-save"></i> ' +
                    ($('#moduleId').val() ? 'Update Module' : 'Save Module')
                );
            }
        });

    });


    // ========================================
    // DELETE MODULE
    // ========================================

    $(document).on('click', '.btnDeleteModule', function() {

        let id = $(this).attr('data-id');

        Swal.fire({
            title: 'Delete Module?',
            text: 'User access related to this module will also be removed.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then(function(result) {

            if (!result.isConfirmed) return;

            $.ajax({
                url: "{{ route('it.module.destroy', ':id') }}"
                    .replace(':id', id),

                type: 'DELETE',

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted',
                        text: response.message,
                        timer: 1200,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload();
                    });

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message
                            || 'Failed to delete module'
                    });

                }
            });

        });

    });

});
</script>

@endpush
