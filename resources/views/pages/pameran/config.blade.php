@extends('master.master')
@section('title', "Pameran list")
@section('content')
<div class="padding">
    <div class="box">
        <div class="p-a white lt box-shadow">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="mb-0 _300">Pameran View</h4>
                    <small class="text-muted">PT. Newwicker Indonesia</small>
                </div>

                <div class="col-sm-6 text-sm-right">
                    <div class="m-y-sm">
                        <!-- Dropdown Tahun -->
                        <select name="exhibition_id" id="exhibition_year" class="form-control">
                            <option value="">-- Semua Tahun --</option>
                            @foreach($e as $exhibition)
                                <option value="{{ $exhibition->id }}" {{ request('exhibition_id') == $exhibition->id ? 'selected' : '' }}>
                                    {{ $exhibition->name }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Form Import -->
                        <form id="importForm" enctype="multipart/form-data" class="mt-2">
                            @csrf
                            <input type="hidden" name="exhibition_id" id="exhibition_id_input" value="{{ request('exhibition_id') }}">
                            <div class="mb-3">
                                <label>Pilih File Excel</label>
                                <input type="file" name="file" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Import</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>



<style>
thead th {
    background-color: #f2f2f2;
    color: #000;
    font-weight: bold;
    text-align: center;
    vertical-align: middle;
    border: 1px solid #000;
}
</style>
@endsection
