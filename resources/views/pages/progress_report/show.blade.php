@php([$prLang, $prT] = require resource_path('views/pages/progress_report/translations.php'))
@extends('pages.progress_report.layout')
@section('progress_content')
@include('pages.progress_report.breadcrumb', ['current' => $report->status === 'DRAFT' ? $prT('Conceptvoorbeeld') : $prT('Rapportdetails'), 'backTab' => $report->status === 'DRAFT' ? 'draft' : 'published'])
<div class="toolbar"><a class="btn" href="{{ route('progress-report.index',['tab'=>$report->status==='DRAFT'?'draft':'published']) }}">{{ $prT('Terug') }}</a><strong>{{ $report->status === 'DRAFT' ? $prT('CONCEPT') : $prT('GEPUBLICEERD') }}</strong>@if($report->status==='DRAFT')<a class="btn" href="{{ route('progress-report.edit',$report) }}">{{ $prT('Bewerken') }}</a><form method="POST" action="{{ route('progress-report.publish',$report) }}" onsubmit="return confirm({{ \Illuminate\Support\Js::from($prT('Rapport publiceren en vergrendelen?')) }})">@csrf<button class="primary">{{ $prT('Publiceren') }}</button></form>@else<a class="btn primary" href="{{ route('progress-report.pdf',$report) }}">{{ $prT('PDF downloaden') }}</a>@endif</div><div class="scroll">@include('pages.progress_report.sheet')</div>
@endsection
