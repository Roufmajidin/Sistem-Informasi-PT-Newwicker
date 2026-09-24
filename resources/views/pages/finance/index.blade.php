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

    </div>


    {{-- TAB CONTENT --}}
    <div class="finance-tab-content">

        <div
            id="tab-draft-payment"
            class="finance-tab-pane active">

            @include('pages.finance.draft-payment')

        </div>


        <div
            id="tab-on-going"
            class="finance-tab-pane">

            @include('pages.finance.on-going')

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