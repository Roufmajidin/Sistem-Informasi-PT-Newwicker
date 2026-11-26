@php([$prLang, $prT] = require resource_path('views/pages/progress_report/translations.php'))
@extends('master.master')
@section('title', $prT('Korte voortgangsupdate - productie'))
@section('content')
<style>
    /* FIX TABLE HEADER */
.pr-wrapper table thead th {
    background-color: #e5edf5 !important;
    color: #1e3a5f !important;
    font-size: 12px;
    font-weight: 600 !important;
    padding: 12px 14px;
    vertical-align: middle;
    border-bottom: 2px solid #cbd5e1;
}

.pr-wrapper table tbody td {
    background-color: #ffffff;
    color: #334155;
    padding: 14px;
    vertical-align: middle;
    border-bottom: 1px solid #e2e8f0;
}

.pr-wrapper table tbody tr:hover td {
    background-color: #f8fafc;
}

/* STICKY HEADER */
.pr-wrapper .scroll thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background-color: #e5edf5 !important;
    color: #1e3a5f !important;
}
.pr-wrapper{font:14px Arial,sans-serif;background:#f1f5f9;color:#17212b;margin:0}
.pr-wrapper{max-width:none;margin:24px auto;padding:20px;background:white}
.pr-wrapper a{color:#174c82}
.pr-wrapper button,.pr-wrapper .btn{display:inline-block;padding:8px 12px;border:1px solid #9aabb9;border-radius:4px;background:white;color:#17212b;text-decoration:none;cursor:pointer}
.pr-wrapper .primary{background:#174c82;color:white}
.pr-wrapper table{border-collapse:collapse;width:100%;table-layout:fixed}
.pr-wrapper th,.pr-wrapper td{border:1px solid #a0aeb9;padding:8px;vertical-align:top;text-align:left;overflow-wrap:anywhere}
.pr-wrapper th{background:#e5edf2;font-weight:normal}
.pr-wrapper input,.pr-wrapper select,.pr-wrapper textarea{box-sizing:border-box;max-width:100%;padding:6px;border:1px solid #a0aeb9;border-radius:3px;font:inherit}
.pr-wrapper td input:not([type=checkbox]),.pr-wrapper td select,.pr-wrapper td textarea{width:100%;margin-bottom:5px}
.pr-wrapper textarea{min-height:95px;resize:vertical}
.pr-wrapper .toolbar{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin:12px 0}
.pr-wrapper .scroll{overflow-x:auto}
.pr-wrapper .error{color:#a11}
.pr-wrapper .success{color:#176b38}
.pr-wrapper h1{text-align:center;font-size:24px}
.pr-wrapper .sheet{min-width:950px}
.pr-wrapper .sheet td{height:85px;white-space:pre-wrap}
.pr-wrapper .sheet th{height:28px}
.pr-wrapper .sheet p{margin:10px 0;line-height:1.35}
.pr-wrapper .sheet .meta{white-space:normal}
.pr-wrapper form.inline{display:inline}
@media  print{.pr-wrapper .toolbar,.pr-wrapper .no-print{display:none}
.pr-wrapper,.pr-wrapper{background:white;margin:0;padding:0}}
.pr-wrapper{font-family:system-ui,-apple-system,"Segoe UI",sans-serif;background:#f1f5f9}
.pr-wrapper{max-width:none;margin:20px auto;padding:24px;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 4px 20px #173c6308}
.pr-wrapper .pr-breadcrumb{display:flex;align-items:center;gap:8px;font-size:12px;color:#64748b;margin-bottom:20px}
.pr-wrapper .pr-breadcrumb a{color:#174c82;text-decoration:underline;text-underline-offset:2px}
.pr-wrapper .btn,.pr-wrapper button{border-radius:7px}
.pr-wrapper .error,.pr-wrapper .success{padding:12px;background:#f8fafc;border-radius:8px}
@media (max-width:760px){.pr-wrapper{margin:8px;padding:12px}}
@media  print{.pr-wrapper .pr-breadcrumb{display:none}
.pr-wrapper{border:0;box-shadow:none}}
.pr-wrapper{width:100%;min-width:0;box-sizing:border-box}.pr-wrapper .scroll{max-height:65vh;overflow:auto}.pr-wrapper .scroll thead th{position:sticky;top:0;z-index:3}
</style>
<div class="pr-wrapper" lang="{{ $prLang }}">
@if(session('success'))<p class="success">{{ session('success') }}</p>@endif
@if($errors->any())<div class="error"><strong>{{ $prT('Controleer de volgende gegevens:') }}</strong>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
@include('pages.progress_report.language_selector')
@yield('progress_content')
</div>
@endsection
