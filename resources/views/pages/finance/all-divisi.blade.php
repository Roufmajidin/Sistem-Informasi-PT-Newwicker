<div>

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h5 class="mb-1">
                All Divisi
            </h5>

            <div class="text-muted small">
                Daftar pengajuan divisi yang masuk ke sistem.
            </div>
        </div>

        <div>
            <span class="badge bg-light text-dark border">
                {{ $allDivisi->count() }} Pengajuan
            </span>
        </div>

    </div>


    {{-- TABLE --}}
    <div class="all-divisi-table-wrapper">

        <table class="table table-bordered all-divisi-table">

            <thead>

                <tr>
                    <th style="width: 45px;">No</th>
                    <th>ID</th>
                    <th>Tanggal</th>
                    <th>Divisi</th>
                    <th>Pembuat</th>
                    <th>Jumlah Item</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th class="approver-header">Approver</th>
                    <th>Aksi</th>
                </tr>

            </thead>

            <tbody>

                @forelse($allDivisi as $pengajuan)

                    @php

                        /*
                        |--------------------------------------------------------------------------
                        | ITEMS
                        |--------------------------------------------------------------------------
                        */
                        $items = $pengajuan->divisiItems ?? collect();


                        /*
                        |--------------------------------------------------------------------------
                        | TOTAL
                        |--------------------------------------------------------------------------
                        */
                        $total = $items->sum(function ($item) {

                            return
                                (float) ($item->qty ?? 0)
                                *
                                (float) ($item->price ?? 0);

                        });


                        /*
                        |--------------------------------------------------------------------------
                        | APPROVERS
                        |--------------------------------------------------------------------------
                        */
                        $approvers = ($pengajuan->approvalSteps ?? collect())
                            ->where('step_order', '>=', 2)
                            ->filter(function ($step) {

                                return
                                    !empty($step->user_name)
                                    ||
                                    !empty($step->user_id);

                            });

                        /*
                        |--------------------------------------------------------------------------
                        | STATUS PENGAJUAN
                        |--------------------------------------------------------------------------
                        */
                        $status = strtolower(
                            trim((string) ($pengajuan->status ?? ''))
                        );

                    @endphp


                    <tr>

                        {{-- NO --}}
                        <td class="text-center">
                            {{ $loop->iteration }}
                        </td>


                        {{-- ID --}}
                        <td class="all-divisi-id">
                            #{{ $pengajuan->id }}
                        </td>


                        {{-- TANGGAL --}}
                        <td>
                            {{ optional($pengajuan->created_at)->format('d/m/Y') }}
                        </td>


                        {{-- DIVISI --}}
                        <td>
                            {{ $pengajuan->divisi?->nama ?? '-' }}
                        </td>


                        {{-- PEMBUAT --}}
                        <td>
                            {{ $pengajuan->user?->name ?? '-' }}
                        </td>


                        {{-- JUMLAH ITEM --}}
                        <td class="text-center">
                            {{ $items->count() }}
                        </td>


                        {{-- TOTAL --}}
                        <td class="text-end all-divisi-total">
                            Rp {{ number_format($total, 0, ',', '.') }}
                        </td>


                        {{-- STATUS --}}
                        <td>

                            @if($status === 'approved')

                                <span class="badge bg-success">
                                    APPROVED
                                </span>

                            @elseif($status === 'pending')

                                <span class="badge bg-warning text-dark">
                                    PENDING
                                </span>

                            @elseif($status === 'rejected')

                                <span class="badge bg-danger">
                                    REJECTED
                                </span>

                            @else

                                <span class="badge bg-secondary">
                                    {{ strtoupper($pengajuan->status ?? '-') }}
                                </span>

                            @endif

                        </td>


                        {{-- APPROVER --}}
                        <td class="approver-column">

                            @if($approvers->isNotEmpty())

                                <div class="approver-list">

                                    @foreach($approvers as $step)

                                        @php

                                            /*
                                            |--------------------------------------------------------------------------
                                            | STATUS APPROVAL PER ORANG
                                            |--------------------------------------------------------------------------
                                            |
                                            | Jika approvalSteps memiliki field status:
                                            | approved = hijau
                                            | selain approved = abu-abu
                                            |
                                            */
                                            $approvalStatus = strtolower(
                                                trim((string) ($step->status ?? ''))
                                            );

                                            $isApproved =
                                                $approvalStatus === 'approved';

                                        @endphp


                                        <span
                                            class="approver-badge {{ $isApproved ? 'approved' : '' }}"
                                            title="{{ $step->user_name ?? '-' }}"
                                        >

                                            @if($isApproved)

                                                <i class="fas fa-check-circle"></i>

                                            @endif

                                            {{ $step->user_name ?? '-' }}

                                        </span>

                                    @endforeach

                                </div>

                            @else

                                <span class="text-muted">
                                    Belum ada approver
                                </span>

                            @endif

                        </td>


                        {{-- AKSI --}}
                        <td>

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary"
                                onclick="viewAllDivisi({{ $pengajuan->id }})"
                            >

                                <i class="fas fa-eye"></i>
                                View

                            </button>

                        </td>

                    </tr>


                @empty

                    <tr>

                        <td
                            colspan="10"
                            class="divisi-empty"
                        >

                            <i class="fas fa-folder-open"></i>

                            Belum ada pengajuan divisi.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>


{{-- ====================================================================== --}}
{{-- STYLE --}}
{{-- ====================================================================== --}}

<style>

    /*
    |--------------------------------------------------------------------------
    | TABLE WRAPPER
    |--------------------------------------------------------------------------
    */

    .all-divisi-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }


    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    .all-divisi-table {
        width: 100%;
        min-width: 1200px;
        border-collapse: collapse;
    }


    .all-divisi-table th,
    .all-divisi-table td {
        white-space: nowrap;
        vertical-align: middle;
    }


    /*
    |--------------------------------------------------------------------------
    | HEADER
    |--------------------------------------------------------------------------
    */

    .all-divisi-table thead th {
        background: #263b4d;
        color: #fff;

        font-size: 12px;
        font-weight: 600;

        padding: 10px 8px;
    }


    /*
    |--------------------------------------------------------------------------
    | BODY
    |--------------------------------------------------------------------------
    */

    .all-divisi-table tbody td {
        font-size: 12px;
        padding: 9px 8px;
    }


    .all-divisi-table tbody tr:hover {
        background: #f8fafc;
    }


    /*
    |--------------------------------------------------------------------------
    | ID
    |--------------------------------------------------------------------------
    */

    .all-divisi-id {
        font-weight: 600;
        color: #263b4d;
    }


    /*
    |--------------------------------------------------------------------------
    | TOTAL
    |--------------------------------------------------------------------------
    */

    .all-divisi-total {
        font-weight: 600;
    }


    /*
    |--------------------------------------------------------------------------
    | APPROVER COLUMN
    |--------------------------------------------------------------------------
    */

    .approver-header {
        width: 140px;
        min-width: 140px;
        max-width: 140px;
    }


    .approver-column {
        width: 140px;
        min-width: 140px;
        max-width: 140px;
    }


    /*
    |--------------------------------------------------------------------------
    | APPROVER LIST
    |--------------------------------------------------------------------------
    */

    .approver-list {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }


    /*
    |--------------------------------------------------------------------------
    | APPROVER BADGE
    |--------------------------------------------------------------------------
    */

    .approver-badge {

        display: block;

        width: 125px;
        max-width: 125px;

        box-sizing: border-box;

        padding: 3px 7px;

        border-radius: 4px;

        background: #eef2f6;
        color: #34495e;

        border: 1px solid transparent;

        font-size: 11px;
        line-height: 16px;

        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;

        cursor: default;

    }


    /*
    |--------------------------------------------------------------------------
    | APPROVER SUDAH APPROVE
    |--------------------------------------------------------------------------
    */

    .approver-badge.approved {

        background: #d1e7dd;

        color: #198754;

        border-color: #a3cfbb;

        font-weight: 600;

    }


    /*
    |--------------------------------------------------------------------------
    | CHECK ICON
    |--------------------------------------------------------------------------
    */

    .approver-badge.approved i {

        margin-right: 3px;

        font-size: 10px;

    }


    /*
    |--------------------------------------------------------------------------
    | EMPTY
    |--------------------------------------------------------------------------
    */

    .divisi-empty {

        padding: 50px 20px;

        text-align: center;

        color: #9aa5b1;

    }


    .divisi-empty i {

        display: block;

        font-size: 30px;

        margin-bottom: 10px;

    }

</style>


{{-- ====================================================================== --}}
{{-- JAVASCRIPT --}}
{{-- ====================================================================== --}}

<script>

    function viewAllDivisi(id)
    {
        const url =
            "{{ url('/pengajuan_purchasing') }}/" + id;

        window.location.href = url;
    }

</script>