@php([$prLang, $prT] = require resource_path('views/pages/progress_report/translations.php'))
<div class="sheet">
<h1>{{ $prT('Korte voortgangsupdate - productie') }}</h1>
<p>PT NewWicker Indonesia | {{ $report->creator->name ?? '-' }}</p>
<p class="meta">{{ $prT('Datum') }}: {{ $report->report_date->format('d/m/Y') }} &nbsp; {{ $prT('Opgesteld door') }}: {{ $report->creator?->name }} &nbsp; {{ $prT('Periode') }}: {{ $report->period_start->format('d/m/Y') }} - {{ $report->period_end->format('d/m/Y') }}</p>
@include('pages.progress_report.instructions')
<table>@include('pages.progress_report.columns')<tbody>
@foreach($report->details as $detail)<tr><td>{{ $detail->subject }}<br>{{ ['OPEN' => 'OPEN', 'IN_PROGRESS' => $prT('LOOPT'), 'BLOCKED' => $prT('GEBLOKKEERD'), 'DONE' => $prT('GEREED')][$detail->status] ?? $detail->status }}@if($detail->is_urgent)<br><strong>{{ $prT('URGENT') }}</strong>@endif</td><td>{{ $detail->problem_order_impact }}</td><td>{{ $detail->action }}</td><td>{{ $detail->pic?->name }}</td><td>{{ $detail->deadline->format('d/m/Y') }}</td><td>{{ $detail->result_decision }}</td></tr>@endforeach
@for($i = $report->details->count(); $i < 5; $i++)<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>@endfor
</tbody></table>
@include('pages.progress_report.footer')
</div>
