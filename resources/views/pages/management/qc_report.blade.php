@extends('master.master')
@section('title','QC Report')
@section('content')
               @php
use Illuminate\Support\Facades\Storage;
@endphp
<style>
    body{
        background:#f5f7fb;
    }
    .report-card{
        border:none;
        /* border-radius:24px; */
        overflow:hidden;
        box-shadow:0 4px 18px rgba(0,0,0,.05);
    }
    .report-header{
        background:#111827;
        color:white;
        padding:18px 24px;
    }
    .report-header h4{
        margin:0;
        font-weight:700;
    }
    .date-header{
        background:#1f2937;
        color:white;
        padding:14px 22px;
        /* border-radius:18px; */
        margin-bottom:20px;
    }
    .remark-table th{
        background:#f3f4f6;
        font-size:13px;
        text-transform:uppercase;
        letter-spacing:.5px;
    }
    .remark-table td{
        vertical-align:middle;
    }
    .photo-box{
        border:none;
        border-radius:18px;
        overflow:hidden;
        box-shadow:0 2px 10px rgba(0,0,0,.05);
    }
    .photo-box img{
        width:100%;
        height:230px;
        object-fit:cover;
    }
    .mini-card{
        border-radius:18px;
        padding:18px;
        border:1px solid #e5e7eb;
        background:white;
    }
    .mini-title{
        font-size:13px;
        color:#6b7280;
        margin-bottom:6px;
    }
    .mini-value{
        font-size:30px;
        font-weight:700;
    }
</style>
<div class="container py-4">
    {{-- HEADER --}}
    <div class="report-card mb-4">
        <div class="report-header">
            <h4>
                QC REPORT
            </h4>
            <div class="mt-2 text-light">
                Tanggal Inspect :
                {{ \Carbon\Carbon::parse($inspect->tanggal_inspect)->format('d M Y') }}
            </div>
        </div>
    </div>
    {{-- LOOP DATE --}}
    @forelse($grouped as $date => $items)
        <div class="date-header">
            <h5 class="mb-0">
                {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
            </h5>
        </div>
        @foreach($items as $item)
            <div class="card report-card mb-4">
                <div class="card-body p-4">
                    {{-- CHECK POINT --}}
                    <div class="mb-4">
                        <span class="badge bg-primary px-3 py-2">
                            CHECK POINT :
                            {{ $item['check_point_id'] }}
                        </span>
                    </div>
                    {{-- REMARK --}}
                    @if(is_array($item['remark']))
                        @php
                            $remark = $item['remark'];
                            $passed =
                                $remark['passed']
                                ?? null;
                            $rejected =
                                $remark['rejected']
                                ?? null;
                            unset($remark['passed']);
                            unset($remark['rejected']);
                        @endphp
                        {{-- PASS / REJECT --}}
                        <div class="row mb-4">
                            @if($passed !== null)
                                <div class="col-md-3 mb-3">
                                    <div class="mini-card">
                                        <div class="mini-title">
                                            Passed
                                        </div>
                                        <div class="mini-value text-success">
                                            {{ $passed }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($rejected !== null)
                                <div class="col-md-3 mb-3">
                                    <div class="mini-card">
                                        <div class="mini-title">
                                            Rejected
                                        </div>
                                        <div class="mini-value text-danger">
                                            {{ $rejected }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        {{-- TABLE --}}
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered remark-table align-middle">
                                <thead>
                                    <tr>
                                        <th width="40%">
                                            SPK PFI
                                        </th>
                                        <th>
                                            AKTUAL
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($remark as $key => $value)
                                        <tr>
                                            {{-- LEFT --}}
                                            <td class="fw-bold">
                                                {{ $key }}
                                            </td>
                                            {{-- RIGHT --}}
                                            <td>
                                                @if(is_array($value))
                                                    <table class="table table-sm mb-0">
                                                        @foreach($value as $vKey => $vVal)
                                                            <tr>
                                                                <td width="40%"
                                                                    class="fw-bold bg-light">
                                                                    {{ $vKey }}
                                                                </td>
                                                                <td>
                                                                    {{ $vVal }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </table>
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-light border">
                            {{ $item['remark'] }}
                        </div>
                    @endif
                    {{-- PHOTO --}}
                    @if(count($item['photos']))
                        <div class="mt-4">
                            <h5 class="fw-bold mb-4">
                                FOTO QC
                            </h5>
                            <div class="row">
                                @foreach($item['photos'] as $photo)
                                    <div class="col-md-3 mb-4">
                                        <div class="photo-box bg-white">
                                          <img src="{{ Storage::url($photo->path) }}">
                                            <div class="p-3">
                                                <div class="small text-muted">
                                                    {{ $photo->keterangan ?? '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @empty
        <div class="alert alert-warning">
            Tidak ada laporan QC
        </div>
    @endforelse
</div>
@endsection
