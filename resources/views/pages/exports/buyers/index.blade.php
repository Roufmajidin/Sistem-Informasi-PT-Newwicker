@extends('master.master')

@section('content')
<div class="container-fluid py-3">

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-1">Database Loberon</h4>
            <small class="text-muted">Master database article Loberon</small>
        </div>

        <button type="button" class="btn btn-primary btn-sm" id="btnAddLoberon">
            <i class="fa fa-plus"></i> Add Data
        </button>
    </div>

    {{-- SEARCH --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('loberon.index') }}">
                <div class="d-flex align-items-center gap-2">
                    <div style="position:relative; width:300px;">
                        <input
                            type="text"
                            name="search"
                            id="searchLoberon"
                            class="form-control form-control-sm"
                            placeholder="Cari buyer, article, EAN..."
                            value="{{ request('search') }}"
                            style="padding-right:35px;"
                        >
                        @if(request('search'))
                            <a href="{{ route('loberon.index') }}"
                               style="position:absolute;right:10px;top:5px;text-decoration:none;color:#999;">
                                <i class="fa fa-times"></i>
                            </a>
                        @endif
                    </div>

                    <button class="btn btn-secondary btn-sm" type="submit">
                        <i class="fa fa-search"></i> Cari
                    </button>

                    @if(request('search'))
                        <a href="{{ route('loberon.index') }}" class="btn btn-light btn-sm">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive loberon-table-wrap">
                <table class="table table-bordered table-sm mb-0" id="loberonTable">
                    <thead>
                        <tr>
                            <th class="text-center sticky-col col-no">#</th>
                            <th class="sticky-head">BUYER</th>
                            <th class="sticky-head">ARTICLE CODE</th>
                            <th class="sticky-head">COLOR ID</th>
                            <th class="sticky-head">SIZE ID</th>
                            <th class="sticky-head">EAN CODE</th>
                            <th class="sticky-head">ARTICLE DESCRIPTION</th>
                            <th class="sticky-head">HTS CODE</th>
                            <th class="sticky-head">BULKY GOODS CLASS</th>
                            <th class="sticky-head text-end">NET WT / CRT</th>
                            <th class="sticky-head text-end">GROSS WT / CRT</th>
                            <th class="sticky-head text-end">TOTAL NUMBER CARTONS</th>
                            <th class="sticky-head text-end">CARTON L (CM)</th>
                            <th class="sticky-head text-end">CARTON W (CM)</th>
                            <th class="sticky-head text-end">CARTON H (CM)</th>
                            <th class="sticky-head text-end">VOLUME / CARTON</th>
                            <th class="sticky-head text-end">QTY</th>
                            <th class="sticky-head text-end">PRICE / ARTICLE (USD)</th>
                            <th class="sticky-head text-center action-col">ACTION</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($buyers as $index => $item)
                        <tr data-id="{{ $item->id }}">
                            <td class="text-center fw-semibold">
                                {{ $buyers->firstItem() + $index }}
                            </td>

                            @php
                                $fields = [
                                    'buyer',
                                    'article_code',
                                    'color_id',
                                    'size_id',
                                    'ean_code',
                                    'article_description',
                                    'hts_code',
                                    'bulky_goods_class',
                                    'net_wt_crt',
                                    'gross_wt_crt',
                                    'total_number_cartons',
                                    'carton_l',
                                    'carton_w',
                                    'carton_h',
                                    'volume_carton',
                                    'qty',
                                    'price_per_article',
                                ];

                                $numericFields = [
                                    'net_wt_crt',
                                    'gross_wt_crt',
                                    'total_number_cartons',
                                    'carton_l',
                                    'carton_w',
                                    'carton_h',
                                    'volume_carton',
                                    'qty',
                                    'price_per_article',
                                ];

                                // Format tampilan table supaya tidak muncul 4 angka desimal.
                                // Tetap menyimpan nilai asli di database.
                                $formatValue = function ($field, $value) {
                                    if ($value === null || $value === '') {
                                        return '';
                                    }

                                    $decimals = match ($field) {
                                        'net_wt_crt',
                                        'gross_wt_crt' => 2,
                                        'volume_carton' => 2,
                                        default => 0,
                                    };

                                    return number_format((float) $value, $decimals, '.', '');
                                };
                            @endphp

                            @foreach($fields as $field)
                                <td>
                                    <input
                                        type="{{ in_array($field, $numericFields) ? 'number' : 'text' }}"
                                        @if(in_array($field, $numericFields))
                                            step="any"
                                        @endif
                                        class="form-control form-control-sm loberon-edit {{ in_array($field, $numericFields) ? 'text-end' : '' }}"
                                        data-id="{{ $item->id }}"
                                        data-field="{{ $field }}"
                                        value="{{ in_array($field, $numericFields) ? $formatValue($field, $item->{$field}) : $item->{$field} }}"
                                    >
                                </td>
                            @endforeach

                            <td class="text-center action-col">
                                <button
                                    type="button"
                                    class="btn btn-outline-danger btn-sm btn-delete-loberon"
                                    data-id="{{ $item->id }}"
                                    title="Delete"
                                >
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="19" class="text-center py-5 text-muted">
                                Belum ada data Loberon.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($buyers->hasPages())
            <div class="card-footer">
                {{ $buyers->links() }}
            </div>
        @endif
    </div>
</div>


{{-- =========================================================
     ADD MODAL
========================================================= --}}
<div class="modal fade" id="modalAddLoberon" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width:96%;">
        <div class="modal-content">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0">Add Database Loberon</h5>
                    <small class="text-muted">
                        Bisa input manual atau copy-paste langsung dari Excel.
                    </small>
                </div>

                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <div class="alert alert-info py-2 small mb-3">
                    <i class="fa fa-info-circle"></i>
                    Copy beberapa baris dari Excel lalu klik salah satu cell di bawah dan
                    tekan <b>Ctrl + V</b>. Data akan otomatis masuk ke kolom dan baris.
                </div>

                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <button type="button" class="btn btn-success btn-sm" id="btnAddLoberonRow">
                            <i class="fa fa-plus"></i> Add Row
                        </button>

                        <button type="button" class="btn btn-outline-danger btn-sm" id="btnRemoveLoberonRows">
                            <i class="fa fa-trash"></i> Remove Empty Rows
                        </button>
                    </div>

                    <span class="small text-muted" id="loberonRowCount">
                        0 rows
                    </span>
                </div>

                <div class="table-responsive loberon-modal-wrap">
                    <table class="table table-bordered table-sm mb-0" id="loberonInputTable">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th>BUYER</th>
                                <th>ARTICLE CODE</th>
                                <th>COLOR ID</th>
                                <th>SIZE ID</th>
                                <th>EAN CODE</th>
                                <th>ARTICLE DESCRIPTION</th>
                                <th>HTS CODE</th>
                                <th>BULKY GOODS CLASS</th>
                                <th>NET WT / CRT</th>
                                <th>GROSS WT / CRT</th>
                                <th>TOTAL NUMBER CARTONS</th>
                                <th>CARTON L</th>
                                <th>CARTON W</th>
                                <th>CARTON H</th>
                                <th>VOLUME / CARTON</th>
                                <th>QTY</th>
                                <th>PRICE / ARTICLE</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    Cancel
                </button>

                <button type="button" class="btn btn-primary btn-sm" id="btnSaveLoberon">
                    <i class="fa fa-save"></i> Save Data
                </button>
            </div>

        </div>
    </div>
</div>


<style>
    .loberon-table-wrap {
        max-height: calc(100vh - 245px);
        overflow: auto;
    }

    .loberon-table-wrap table {
        min-width: 2300px;
    }

   /* =========================
   LOBERON TABLE HEADER
========================= */
#loberonTable > thead > tr > th {
    position: sticky !important;
    top: 0 !important;
    z-index: 20 !important;

    background-color: #1f2937 !important;
    color: #ffffff !important;

    border-color: #374151 !important;

    font-size: 11px !important;
    font-weight: 600 !important;
    text-align: center;
    vertical-align: middle !important;

    white-space: nowrap;
    padding: 8px 10px !important;

    opacity: 1 !important;
}

    .loberon-table-wrap td {
        padding: 3px;
        vertical-align: middle;
        white-space: nowrap;
    }

    .loberon-edit {
        min-width: 100px;
        border: 1px solid transparent;
        background: transparent;
        border-radius: 3px;
    }

    .loberon-edit:hover,
    .loberon-edit:focus {
        border-color: #86b7fe;
        background: #fff;
    }

    .loberon-table-wrap .col-no {
        width: 45px;
        min-width: 45px;
        position: sticky;
        left: 0;
        z-index: 12;
        background: #e9eef5;
    }

    .loberon-table-wrap tbody td:first-child {
        position: sticky;
        left: 0;
        z-index: 5;
        background: #fff;
    }

    .action-col {
        min-width: 70px;
        position: sticky;
        right: 0;
        z-index: 11;
        background: #fff;
    }

    .loberon-table-wrap thead .action-col {
        background: #e9eef5;
    }

    .loberon-modal-wrap {
        max-height: 60vh;
        overflow: auto;
    }

    #loberonInputTable {
        min-width: 2300px;
    }

    #loberonInputTable thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background: #e9eef5;
        font-size: 11px;
        white-space: nowrap;
        vertical-align: middle;
    }

    #loberonInputTable td {
        padding: 2px;
    }

    .loberon-paste-cell {
        min-width: 100px;
    }

    .loberon-input {
        min-width: 100px;
        font-size: 12px;
    }
</style>


<script>
$(function () {

    const csrf = '{{ csrf_token() }}';

    const columns = [
        'buyer',
        'article_code',
        'color_id',
        'size_id',
        'ean_code',
        'article_description',
        'hts_code',
        'bulky_goods_class',
        'net_wt_crt',
        'gross_wt_crt',
        'total_number_cartons',
        'carton_l',
        'carton_w',
        'carton_h',
        'volume_carton',
        'qty',
        'price_per_article'
    ];

    const numericColumns = [
        'net_wt_crt',
        'gross_wt_crt',
        'total_number_cartons',
        'carton_l',
        'carton_w',
        'carton_h',
        'volume_carton',
        'qty',
        'price_per_article'
    ];

    function inputHtml(field, value = '') {
        const type = numericColumns.includes(field) ? 'number' : 'text';
        const step = numericColumns.includes(field) ? ' step="any"' : '';

        return `
            <input
                type="${type}"
                ${step}
                class="form-control form-control-sm loberon-input loberon-paste-cell"
                data-field="${field}"
                value="${escapeHtml(value)}"
            >
        `;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function addRow(values = []) {

        const $tbody = $('#loberonInputTable tbody');
        const rowNumber = $tbody.children('tr').length + 1;

        let html = `
            <tr>
                <td class="text-center row-number">${rowNumber}</td>
        `;

        columns.forEach(function (field, index) {
            html += `<td>${inputHtml(field, values[index] ?? '')}</td>`;
        });

        html += `
                <td class="text-center">
                    <button type="button"
                            class="btn btn-outline-danger btn-sm btn-remove-loberon-row">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        $tbody.append(html);

        updateRowNumbers();
    }

    function updateRowNumbers() {
        $('#loberonInputTable tbody tr').each(function (index) {
            $(this).find('.row-number').text(index + 1);
        });

        $('#loberonRowCount').text(
            $('#loberonInputTable tbody tr').length + ' rows'
        );
    }

    $('#btnAddLoberon').on('click', function () {

        $('#loberonInputTable tbody').empty();

        for (let i = 0; i < 5; i++) {
            addRow();
        }

        $('#modalAddLoberon').modal('show');

        setTimeout(function () {
            $('#loberonInputTable tbody tr:first .loberon-input').first().focus();
        }, 300);
    });

    $('#btnAddLoberonRow').on('click', function () {
        addRow();

        const $last = $('#loberonInputTable tbody tr:last .loberon-input').first();
        $last.focus();

        $('.loberon-modal-wrap').scrollTop(
            $('.loberon-modal-wrap')[0].scrollHeight
        );
    });

    $(document).on('click', '.btn-remove-loberon-row', function () {
        $(this).closest('tr').remove();
        updateRowNumbers();
    });

    $('#btnRemoveLoberonRows').on('click', function () {

        $('#loberonInputTable tbody tr').each(function () {

            let hasValue = false;

            $(this).find('.loberon-input').each(function () {
                if ($(this).val().trim() !== '') {
                    hasValue = true;
                }
            });

            if (!hasValue) {
                $(this).remove();
            }
        });

        updateRowNumbers();
    });


    /*
     * PASTE DARI EXCEL
     *
     * Excel:
     * TAB  = kolom
     * NEWLINE = baris
     */
    $(document).on('paste', '#loberonInputTable .loberon-input', function (e) {

        const clipboard = e.originalEvent.clipboardData ||
                          window.clipboardData;

        if (!clipboard) {
            return;
        }

        const text = clipboard.getData('text');

        if (!text || (!text.includes('\t') && !text.includes('\n'))) {
            return;
        }

        e.preventDefault();

        const $startInput = $(this);
        const $startRow = $startInput.closest('tr');
        const startRow = $startRow.index();
        const startCol = columns.indexOf($startInput.data('field'));

        if (startCol < 0) {
            return;
        }

        const rows = text
            .replace(/\r\n/g, '\n')
            .replace(/\r/g, '\n')
            .split('\n');

        while (rows.length && rows[rows.length - 1] === '') {
            rows.pop();
        }

        rows.forEach(function (line, rowOffset) {

            const values = line.split('\t');

            let $row = $('#loberonInputTable tbody tr').eq(
                startRow + rowOffset
            );

            if (!$row.length) {
                addRow();
                $row = $('#loberonInputTable tbody tr').last();
            }

            values.forEach(function (value, colOffset) {

                const colIndex = startCol + colOffset;

                if (colIndex >= columns.length) {
                    return;
                }

                $row.find('.loberon-input')
                    .eq(colIndex)
                    .val(value.trim());
            });
        });

        updateRowNumbers();
    });


    /*
     * SAVE
     */
    $('#btnSaveLoberon').on('click', function () {

        const $button = $(this);

        const rows = [];

        $('#loberonInputTable tbody tr').each(function () {

            const row = {};

            $(this).find('.loberon-input').each(function () {
                row[$(this).data('field')] = $(this).val();
            });

            let hasValue = false;

            Object.values(row).forEach(function (value) {
                if (String(value ?? '').trim() !== '') {
                    hasValue = true;
                }
            });

            if (hasValue) {
                rows.push(row);
            }
        });

        if (!rows.length) {
            alert('Belum ada data yang diisi.');
            return;
        }

        $button.prop('disabled', true)
               .html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: '{{ route('loberon.store') }}',
            method: 'POST',
            data: {
                _token: csrf,
                rows: rows
            },
            success: function (response) {

                if (response.success) {
                    $('#modalAddLoberon').modal('hide');
                    location.reload();
                } else {
                    alert(response.message || 'Gagal menyimpan data.');
                }
            },
            error: function (xhr) {

                let message = 'Gagal menyimpan data.';

                if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }

                alert(message);
            },
            complete: function () {
                $button.prop('disabled', false)
                       .html('<i class="fa fa-save"></i> Save Data');
            }
        });
    });


    /*
     * INLINE EDIT
     */
    let editTimer = null;

    $(document).on('change', '.loberon-edit', function () {

        const $input = $(this);

        const id = $input.data('id');
        const field = $input.data('field');
        const value = $input.val();

        clearTimeout(editTimer);

        editTimer = setTimeout(function () {

            $input.prop('disabled', true);

            $.ajax({
                url: '/loberon/' + id,
                method: 'PATCH',
                data: {
                    _token: csrf,
                    field: field,
                    value: value
                },
                success: function (response) {

                    if (!response.success) {
                        alert(response.message || 'Gagal menyimpan perubahan.');
                    }
                },
                error: function (xhr) {

                    alert(
                        xhr.responseJSON?.message ||
                        'Gagal menyimpan perubahan.'
                    );
                },
                complete: function () {
                    $input.prop('disabled', false);
                }
            });

        }, 150);
    });


    /*
     * DELETE
     */
    $(document).on('click', '.btn-delete-loberon', function () {

        const $button = $(this);
        const id = $button.data('id');

        if (!confirm('Hapus data Loberon ini?')) {
            return;
        }

        $button.prop('disabled', true);

        $.ajax({
            url: '/loberon/' + id,
            method: 'DELETE',
            data: {
                _token: csrf
            },
            success: function (response) {

                if (response.success) {
                    $button.closest('tr').remove();
                } else {
                    alert(response.message || 'Gagal menghapus data.');
                    $button.prop('disabled', false);
                }
            },
            error: function (xhr) {

                alert(
                    xhr.responseJSON?.message ||
                    'Gagal menghapus data.'
                );

                $button.prop('disabled', false);
            }
        });
    });

});
</script>
@endsection
