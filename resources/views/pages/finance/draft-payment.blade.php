
<div>

    <h5 class="mb-1">Draft Payment</h5>

    <p class="text-muted mb-3">
        Daftar payment yang masih dalam status draft.
    </p>


    <div class="finance-table-wrapper">

        <table class="table table-hover finance-table">

            <thead>
                <tr>

                    <th class="text-center">
                        NO
                    </th>

                    <th>
                        REQUEST NO
                    </th>

                    <th>
                        TANGGAL
                    </th>

                    <th>
                        NO. PENGAJUAN
                    </th>

                    <th>
                        TYPE PEMBAYARAN
                    </th>

                    <th>
                        PEMBUAT
                    </th>

                    <th>
                        STATUS
                    </th>

                    <th class="text-center">
                        ACTION
                    </th>

                </tr>
            </thead>


            <tbody>

                @forelse($pengajuans as $pengajuan)

                    <tr>

                        {{-- NO --}}
                        <td class="text-center">
                            {{ $loop->iteration }}
                        </td>


                        {{-- REQUEST NO --}}
                        <td>

                            <strong>
                                {{ $pengajuan->meta->nomor ?? '-' }}
                            </strong>

                        </td>


                        {{-- TANGGAL --}}
                        <td>

                            @if($pengajuan->meta?->tanggal)

                                {{ \Carbon\Carbon::parse(
                                    $pengajuan->meta->tanggal
                                )->format('d/m/Y') }}

                            @else

                                -

                            @endif

                        </td>


                        {{-- NO PENGAJUAN --}}
                        <td>

                            #{{ $pengajuan->id }}

                        </td>


                        {{-- TYPE PEMBAYARAN --}}
                        <td>

                            {{ $pengajuan->meta->type_pembayaran ?? '-' }}

                        </td>


                        {{-- PEMBUAT --}}
                        <td>

                            {{ $pengajuan->user->name ?? '-' }}

                        </td>


                        {{-- STATUS --}}
                        <td>

                            @if($pengajuan->status === 'pending')

                                <span class="finance-status pending">
                                    Draft
                                </span>

                            @elseif($pengajuan->status === 'approved')

                                <span class="finance-status approved">
                                    Approved
                                </span>

                            @elseif($pengajuan->status === 'rejected')

                                <span class="finance-status rejected">
                                    Rejected
                                </span>

                            @else

                                <span class="finance-status">
                                    {{ ucfirst($pengajuan->status ?? '-') }}
                                </span>

                            @endif

                        </td>


                        {{-- ACTION --}}
                        <td class="text-center">

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary btn-detail-finance"
                                data-id="{{ $pengajuan->id }}"
                                title="See Details">

                                <i class="fas fa-eye"></i>

                                Detail

                            </button>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="8"
                            class="text-center text-muted py-4">

                            Belum ada Draft Payment

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>


<style>

/*
|--------------------------------------------------------------------------
| TABLE WRAPPER
|--------------------------------------------------------------------------
*/

.finance-table-wrapper {
    width: 100%;

    /*
    | Tidak ada overflow-x auto
    | supaya horizontal scrollbar tidak muncul
    */

    overflow-x: hidden;

    border: 1px solid #e1e5ea;

    border-radius: 6px;

    background: #fff;
}


/*
|--------------------------------------------------------------------------
| TABLE
|--------------------------------------------------------------------------
*/

.finance-table {
    width: 100%;

    margin-bottom: 0;

    font-size: 12px;

    /*
    | Jangan pakai min-width
    */

    table-layout: auto;
}


/*
|--------------------------------------------------------------------------
| TABLE HEADER
|--------------------------------------------------------------------------
*/

.finance-table thead th {

    background: #f5f7fa;

    color: #495057;

    font-size: 11px;

    font-weight: 600;

    white-space: nowrap;

    vertical-align: middle;

    border-bottom: 1px solid #dfe3e8;

    padding: 10px 12px;

}


/*
|--------------------------------------------------------------------------
| TABLE BODY
|--------------------------------------------------------------------------
*/

.finance-table tbody td {

    padding: 10px 12px;

    vertical-align: middle;

    /*
    | Tetap satu baris
    */

    white-space: nowrap;

    border-color: #edf0f2;

}


/*
|--------------------------------------------------------------------------
| HOVER
|--------------------------------------------------------------------------
*/

.finance-table tbody tr:hover {

    background: #f8fafc;

}


/*
|--------------------------------------------------------------------------
| ALIGNMENT
|--------------------------------------------------------------------------
*/

.finance-table .text-center {

    text-align: center;

}


/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

.finance-status {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding: 4px 9px;

    border-radius: 4px;

    background: #eef1f4;

    color: #495057;

    font-size: 11px;

    font-weight: 600;

    line-height: 1;

}


/*
|--------------------------------------------------------------------------
| STATUS - DRAFT
|--------------------------------------------------------------------------
*/

.finance-status.pending {

    background: #fff3cd;

    color: #856404;

}


/*
|--------------------------------------------------------------------------
| STATUS - APPROVED
|--------------------------------------------------------------------------
*/

.finance-status.approved {

    background: #d1e7dd;

    color: #0f5132;

}


/*
|--------------------------------------------------------------------------
| STATUS - REJECTED
|--------------------------------------------------------------------------
*/

.finance-status.rejected {

    background: #f8d7da;

    color: #842029;

}


/*
|--------------------------------------------------------------------------
| DETAIL BUTTON
|--------------------------------------------------------------------------
*/

.btn-detail-finance {

    font-size: 11px;

    padding: 4px 10px;

    white-space: nowrap;

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE
|--------------------------------------------------------------------------
|
| Pada layar kecil, kita kecilkan padding/font.
|
*/

@media (max-width: 768px) {

    .finance-table {

        font-size: 11px;

    }

    .finance-table thead th {

        font-size: 10px;

        padding: 8px 7px;

    }

    .finance-table tbody td {

        padding: 8px 7px;

    }

    .btn-detail-finance {

        font-size: 10px;

        padding: 3px 7px;

    }

}

</style>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | DETAIL BUTTON
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll('.btn-detail-finance')
            .forEach(function (button) {

                button.addEventListener(
                    'click',
                    function () {

                        const id =
                            this.dataset.id;

                        console.log(
                            'Finance Detail ID:',
                            id
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | NANTI DI SINI KITA BUKA DETAIL
                        |--------------------------------------------------------------------------
                        |
                        | Contoh nanti:
                        |
                        | /finance/{id}/detail
                        |
                        | atau modal AJAX.
                        |
                        */

                    }
                );

            });

    }
);

</script>

