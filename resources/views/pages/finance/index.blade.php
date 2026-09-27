@extends('master.master')

@section('content')

<div class="container-fluid mt-4">

    {{-- TAB --}}
    <div class="finance-tabs">

        <button
            type="button"
            class="finance-tab active"
            data-target="draft-payment">
            <i class="fas fa-file-invoice"></i>
            Draft Payment
        </button>


        <button
            type="button"
            class="finance-tab"
            data-target="on-going">
            <i class="fas fa-spinner"></i>
            On Going
        </button>


        <button
            type="button"
            class="finance-tab"
            data-target="all-divisi">
            <i class="fas fa-building"></i>
            All Divisi
        </button>

    </div>


    {{-- TAB CONTENT --}}
    <div class="finance-tab-content">

        {{-- =========================================================
             DRAFT PAYMENT
             ========================================================= --}}
        <div
            id="tab-draft-payment"
            class="finance-tab-pane active">

            @include('pages.finance.draft-payment')

        </div>


        {{-- =========================================================
             ON GOING
             ========================================================= --}}
        <div
            id="tab-on-going"
            class="finance-tab-pane">

            @include('pages.finance.on-going')

        </div>


        {{-- =========================================================
             ALL DIVISI
             ========================================================= --}}
        <div
            id="tab-all-divisi"
            class="finance-tab-pane">

            @include('pages.finance.all-divisi', [
                'allDivisi' => $allDivisi
            ])

        </div>

    </div>

</div>


<style>

.finance-tabs {
    display: flex;
    align-items: flex-end;
    gap: 4px;
    border-bottom: 1px solid #d9dee5;
}

.finance-tab {
    border: 1px solid #d9dee5;
    border-bottom: none;

    background: #f7f8fa;
    color: #495057;

    padding: 9px 16px;

    font-size: 13px;
    font-weight: 500;

    border-radius: 6px 6px 0 0;

    cursor: pointer;

    transition: all .15s ease;
}

.finance-tab i {
    margin-right: 6px;
}

.finance-tab:hover {
    background: #eef1f4;
}

.finance-tab.active {
    background: #263b4d;
    color: #fff;
    border-color: #263b4d;
}

.finance-tab-content {
    background: #fff;

    border: 1px solid #d9dee5;
    border-top: none;

    min-height: 400px;
}

.finance-tab-pane {
    display: none;
    padding: 20px;
}

.finance-tab-pane.active {
    display: block;
}


/* =========================================================
   ALL DIVISI
   ========================================================= */

.all-divisi-table-wrapper {
    width: 100%;
    overflow-x: auto;
}

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

.all-divisi-table thead th {
    background: #263b4d;
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    padding: 10px 8px;
}

.all-divisi-table tbody td {
    font-size: 12px;
    padding: 9px 8px;
}

.all-divisi-table tbody tr:hover {
    background: #f8fafc;
}

.all-divisi-id {
    font-weight: 600;
    color: #263b4d;
}

.all-divisi-total {
    font-weight: 600;
}

.approver-list {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.approver-badge {
    display: block;

    width: 120px;
    max-width: 120px;

    padding: 3px 7px;

    border-radius: 4px;

    background: #eef2f6;
    color: #34495e;

    font-size: 11px;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;

    cursor: default;
}
</style>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const tabs = document.querySelectorAll('.finance-tab');
    const panes = document.querySelectorAll('.finance-tab-pane');

    tabs.forEach(tab => {

        tab.addEventListener('click', function () {

            const target = this.dataset.target;

            tabs.forEach(item => {
                item.classList.remove('active');
            });

            panes.forEach(pane => {
                pane.classList.remove('active');
            });

            this.classList.add('active');

            const targetPane = document.getElementById(
                'tab-' + target
            );

            if (targetPane) {
                targetPane.classList.add('active');
            }

        });

    });

});

</script>

@endsection