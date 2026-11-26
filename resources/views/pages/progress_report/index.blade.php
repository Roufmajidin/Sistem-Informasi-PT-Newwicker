@php([$prLang, $prT] = require resource_path('views/pages/progress_report/translations.php'))
@extends('pages.progress_report.layout')
@section('progress_content')
@include('pages.progress_report.breadcrumb', ['current' => $tab === 'draft' ? $prT('Mijn concepten') : $prT('Gepubliceerd'), 'backTab' => $tab])
<style>
.pr-wrapper .pr-head{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}
.pr-wrapper .pr-head h1{text-align:left;margin:0 0 6px}
.pr-wrapper .muted{color:#64748b}
.pr-wrapper .pr-tabs{display:flex;gap:24px;border-bottom:1px solid #dbe3ec;margin-top:24px}
.pr-wrapper .pr-tabs a{padding:14px 4px;text-decoration:none;color:#64748b;font-weight:600;border-bottom:3px solid transparent}
.pr-wrapper .pr-tabs a.active{color:#174c82;border-color:#174c82}
.pr-wrapper .count{background:#eaf0f6;padding:3px 8px;border-radius:20px;font-size:12px}
.pr-wrapper .pr-list{min-width:760px;table-layout:auto}
.pr-wrapper .pr-list th,.pr-wrapper .pr-list td{border:0;border-bottom:1px solid #e2e8f0;padding:15px 12px;vertical-align:middle}
.pr-wrapper .pr-list th{background:#f1f5f9;font-weight:600}
.pr-wrapper .pr-list tr:hover td{background:#f8fafc}
.pr-wrapper .pr-actions{display:flex;gap:6px;flex-wrap:wrap}
.pr-wrapper .pill{display:inline-block;padding:5px 9px;border-radius:20px;font-size:12px;background:#e8f4ed;color:#166534}
.pr-wrapper .pill.draft{background:#fff4dd;color:#92400e}
.pr-wrapper .empty{text-align:center!important;padding:48px!important}
.pr-wrapper .pr-search{display:flex;gap:8px;flex-wrap:wrap}
.pr-wrapper .pr-search input{min-width:230px}
.pr-wrapper .pr-pagination{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:16px;flex-wrap:wrap}
</style>
<div class="pr-head"><div><h1>{{ $prT('Korte voortgangsupdate - productie') }}</h1><div class="muted">{{ $prT('Voortgangsrapportage productie · PT NewWicker Indonesia') }}</div></div>@unless($viewerOnly)<a class="btn primary" href="{{ route('progress-report.create') }}">+ {{ $prT('Nieuw rapport') }}</a>@endunless</div>
<nav class="pr-tabs" aria-label="{{ $prT('Rapportstatus') }}">
@unless($viewerOnly)<a class="{{ $tab==='draft'?'active':'' }}" href="{{ route('progress-report.index',['tab'=>'draft']) }}" @if($tab==='draft') aria-current="page" @endif>{{ $prT('Mijn concepten') }} <span class="count">{{ $draftCount }}</span></a>@endunless
<a class="{{ $tab==='published'?'active':'' }}" href="{{ route('progress-report.index',['tab'=>'published']) }}" @if($tab==='published') aria-current="page" @endif>{{ $prT('Gepubliceerd') }} <span class="count">{{ $publishedCount }}</span></a>
</nav>
<div class="toolbar" style="justify-content:space-between"><span class="muted">{{ $tab==='draft'?$prT('Uw nog niet gepubliceerde rapporten.'):$prT('Gepubliceerde rapporten.') }}</span><form class="pr-search" method="GET" action="{{ route('progress-report.index') }}"><input type="hidden" name="tab" value="{{ $tab }}"><input name="q" aria-label="{{ $prT('Rapport zoeken') }}" placeholder="{{ $prT('Zoek onderwerp of opsteller...') }}" value="{{ request('q') }}"><button>{{ $prT('Zoeken') }}</button>@if(request()->filled('q'))<a class="btn" href="{{ route('progress-report.index',['tab'=>$tab]) }}">{{ $prT('Reset') }}</a>@endif</form></div>
<div class="scroll"><table class="pr-list"><thead><tr><th>{{ $prT('Datum') }}</th><th>{{ $prT('Periode') }}</th><th>{{ $prT('Opgesteld door') }}</th><th>{{ $prT('Status') }}</th><th>{{ $prT('Onderwerpen') }}</th><th>{{ $prT('Acties') }}</th></tr></thead><tbody>
@forelse($reports as $report)
<tr><td>{{ $report->report_date->format('d/m/Y') }}</td><td>{{ $report->period_start->format('d/m/Y') }} – {{ $report->period_end->format('d/m/Y') }}</td><td>{{ $report->creator?->name ?? '—' }}</td><td><span class="pill {{ $report->status==='DRAFT'?'draft':'' }}">{{ $report->status === 'DRAFT' ? $prT('CONCEPT') : $prT('GEPUBLICEERD') }}</span></td><td>{{ $report->details_count }}</td><td><div class="pr-actions"><a class="btn" href="{{ route('progress-report.show',$report) }}">{{ $prT('Bekijken') }}</a>@if($tab==='draft' && !$viewerOnly)<a class="btn primary" href="{{ route('progress-report.edit',$report) }}">{{ $prT('Verder bewerken') }}</a>@else<a class="btn primary" href="{{ route('progress-report.pdf',$report) }}">{{ $prT('PDF downloaden') }}</a>@endif</div></td></tr>
@empty<tr><td colspan="6" class="empty"><strong>{{ request()->filled('q')?$prT('Geen zoekresultaten'):($tab==='draft'?$prT('Geen concepten'):$prT('Geen gepubliceerde rapporten')) }}</strong><p class="muted">{{ $tab==='draft'?$prT('Maak een nieuw rapport om te beginnen.'):$prT('Rapporten verschijnen na publicatie.') }}</p>@if($tab==='draft' && !$viewerOnly)<a class="btn primary" href="{{ route('progress-report.create') }}">+ {{ $prT('Nieuw rapport') }}</a>@endif</td></tr>@endforelse
</tbody></table></div>
<div class="pr-pagination"><span class="muted">{{ $reports->total() }} {{ $prT(' rapporten') }}</span><div>@if($reports->previousPageUrl())<a class="btn" href="{{ $reports->previousPageUrl() }}">{{ $prT('Vorige') }}</a>@endif <span>{{ $prT('Pagina') }} {{ $reports->currentPage() }} / {{ $reports->lastPage() }}</span> @if($reports->nextPageUrl())<a class="btn" href="{{ $reports->nextPageUrl() }}">{{ $prT('Volgende') }}</a>@endif</div></div>
@endsection
