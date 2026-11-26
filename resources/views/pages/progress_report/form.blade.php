@php([$prLang, $prT] = require resource_path('views/pages/progress_report/translations.php'))
@extends('pages.progress_report.layout')
@section('progress_content')
<style>
.pr-wrapper .pr-editor{--navy:#173c63;--muted:#64748b;--line:#dce4ee;max-width:none;margin:auto;font:14px/1.55 system-ui,-apple-system,"Segoe UI",sans-serif;color:#172b42}
.pr-wrapper .pr-editor *{box-sizing:border-box}
.pr-wrapper .pr-editor h1,.pr-wrapper .pr-editor h2{margin:0;text-align:left;line-height:1.3}
.pr-wrapper .pr-editor h1{font-size:28px;letter-spacing:-.7px}
.pr-wrapper .pr-editor h2{font-size:17px}
.pr-wrapper .pr-editor p{margin:5px 0;color:var(--muted)}
.pr-wrapper .pr-editor .crumb{font-size:12px;color:var(--muted);margin-bottom:20px}
.pr-wrapper .pr-editor .head,.pr-wrapper .pr-editor .section-head,.pr-wrapper .pr-editor .topic-head,.pr-wrapper .pr-editor .bottom{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.pr-wrapper .pr-editor .head{margin-bottom:24px}
.pr-wrapper .pr-editor .tag{background:#fff4dc;color:#92600d;padding:5px 12px;border-radius:20px;font-size:12px;font-weight:700}
.pr-wrapper .pr-editor .panel,.pr-wrapper .pr-editor .topic{border:1px solid var(--line);border-radius:14px;background:#fff;padding:22px;margin:16px 0;box-shadow:0 3px 12px #173c6308}
.pr-wrapper .pr-editor .grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
.pr-wrapper .pr-editor .meta-grid{grid-template-columns:repeat(4,minmax(0,1fr));margin-top:18px}
.pr-wrapper .pr-editor .field{display:flex;flex-direction:column;gap:6px;min-width:0}
.pr-wrapper .pr-editor .field>span{font-weight:600;font-size:13px}
.pr-wrapper .pr-editor .wide{grid-column:1/-1}
.pr-wrapper .pr-editor input:not([type=checkbox]),.pr-wrapper .pr-editor select,.pr-wrapper .pr-editor textarea{width:100%;max-width:none;border:1px solid #cbd5e1;border-radius:8px;padding:11px 12px;background:#fff;color:#172b42;font:inherit;outline:none}
.pr-wrapper .pr-editor input:focus,.pr-wrapper .pr-editor select:focus,.pr-wrapper .pr-editor textarea:focus{border-color:#3778b8;box-shadow:0 0 0 3px #3778b81c}
.pr-wrapper .pr-editor input[readonly]{background:#f1f5f9}
.pr-wrapper .pr-editor textarea{min-height:112px;resize:vertical}
.pr-wrapper .pr-editor .topic-head{padding-bottom:16px;margin-bottom:18px;border-bottom:1px solid var(--line)}
.pr-wrapper .pr-editor .number{display:inline-grid;place-items:center;width:32px;height:32px;background:#eaf1fa;color:var(--navy);border-radius:9px;margin-right:8px;font-weight:700}
.pr-wrapper .pr-editor .urgent{display:flex;align-items:center;gap:8px;color:#9a3412;font-weight:600;font-size:13px}
.pr-wrapper .pr-editor .topic[data-urgent="true"]{border-left:4px solid #ea580c}
.pr-wrapper .pr-editor .btn,.pr-wrapper .pr-editor button{border:1px solid #cbd5e1;border-radius:8px;padding:10px 16px;font:600 13px/1.5 system-ui,sans-serif;cursor:pointer;text-decoration:none;background:white;color:var(--navy)}
.pr-wrapper .pr-editor .primary{background:var(--navy);color:white;border-color:var(--navy)}
.pr-wrapper .pr-editor .remove{color:#b91c1c;border:0;background:#fff1f2}
.pr-wrapper .pr-editor .add{width:100%;border:1px dashed #91a9c4;background:#f5f9ff;padding:16px}
.pr-wrapper .pr-editor .bottom{position:sticky;bottom:0;z-index:5;background:#fffffff5;border:1px solid var(--line);border-radius:12px;padding:14px 18px;margin-top:24px;box-shadow:0 -4px 20px #173c6310}
.pr-wrapper .pr-editor .actions{display:flex;gap:8px;flex-wrap:wrap}
.pr-wrapper .pr-editor details{background:#f4f7fb;border-radius:10px;padding:12px 16px;margin:16px 0}
.pr-wrapper .pr-editor summary{cursor:pointer;font-weight:600;color:var(--navy)}
.pr-wrapper .pr-editor .hint{font-size:12px;color:var(--muted)}
.pr-wrapper .pr-editor .danger-zone{margin-top:16px;text-align:right}
.pr-wrapper .pr-editor button:disabled{opacity:.45;cursor:not-allowed}
@media (max-width:760px){.pr-wrapper .pr-editor .grid,.pr-wrapper .pr-editor .meta-grid{grid-template-columns:1fr}
.pr-wrapper .pr-editor .panel,.pr-wrapper .pr-editor .topic{padding:15px}
.pr-wrapper .pr-editor h1{font-size:23px}
.pr-wrapper .pr-editor .bottom{position:static}
.pr-wrapper .pr-editor .actions{width:100%}
.pr-wrapper .pr-editor .actions>*{flex:1;text-align:center}}
.pr-wrapper .pr-editor .pr-table-scroll{overflow:auto;max-height:60vh;min-height:180px;border:1px solid #cbd5e1;border-radius:10px;background:white;scroll-padding-top:65px}
.pr-wrapper .pr-editor .pr-edit-table{min-width:1498px;table-layout:fixed;border-collapse:separate;border-spacing:0}
.pr-wrapper .pr-editor .pr-edit-table th{position:sticky;top:0;z-index:3;background:#e7eef7;color:#173c63;font-weight:650;font-size:12px;padding:14px 10px;border:0;border-bottom:1px solid #bdccdc;text-align:left}
.pr-wrapper .pr-editor .pr-edit-table td{padding:10px 8px;border:0;border-bottom:1px solid #dce4ee;border-right:1px solid #edf1f5;vertical-align:top;background:#fff}
.pr-wrapper .pr-editor .pr-edit-table tr:nth-child(even) td{background:#f8fafc}
.pr-wrapper .pr-editor .pr-edit-table tr[data-urgent=true] td:first-child{box-shadow:inset 4px 0 #ea580c}
.pr-wrapper .pr-editor .pr-edit-table .topic{border:0;box-shadow:none}
.pr-wrapper .pr-editor .pr-edit-table input:not([type=checkbox]),.pr-wrapper .pr-editor .pr-edit-table select,.pr-wrapper .pr-editor .pr-edit-table textarea{padding:9px 8px;font-size:13px;margin-bottom:7px}
.pr-wrapper .pr-editor .pr-edit-table textarea{min-height:115px;overflow:hidden}
.pr-wrapper .pr-editor .pr-edit-table .number{width:28px;margin:0}
.pr-wrapper .pr-editor .pr-edit-table .remove{padding:5px 12px;font-size:20px}
.pr-wrapper .pr-editor .pr-edit-table select[data-field=status][data-status=OPEN]{background:#f1f5f9}
.pr-wrapper .pr-editor .pr-edit-table select[data-status=IN_PROGRESS]{background:#eaf3ff;color:#174c82}
.pr-wrapper .pr-editor .pr-edit-table select[data-status=BLOCKED]{background:#fff0ef;color:#b91c1c}
.pr-wrapper .pr-editor .pr-edit-table select[data-status=DONE]{background:#e8f5ec;color:#166534}
.pr-wrapper .pr-editor .add{margin-top:12px}
.pr-wrapper .pr-editor .panel{padding:16px}
.pr-wrapper .pr-editor .head{margin-bottom:12px}
.pr-wrapper .pr-editor .bottom{bottom:0;position:sticky}
.pr-wrapper .pr-editor .pr-edit-table td:last-child{text-align:center}

/* Keep subject and status inside their own table cell. */
.pr-wrapper .pr-editor .pr-edit-table { width:100%; min-width:1498px; }
.pr-wrapper .pr-editor .pr-edit-table .pr-subject-stack {
    display:flex !important; flex-direction:column !important;
    align-items:stretch !important; gap:8px; width:100%; min-width:0;
}
.pr-wrapper .pr-editor .pr-edit-table .pr-subject-stack > input,
.pr-wrapper .pr-editor .pr-edit-table .pr-subject-stack > select {
    display:block !important; width:100% !important; max-width:100% !important;
    min-width:0 !important; flex:none !important; margin:0 !important;
    box-sizing:border-box !important; float:none !important;
}
.pr-wrapper .pr-editor .pr-edit-table .pr-subject-stack > select {
    height:40px; padding:8px; text-overflow:ellipsis;
}
.pr-wrapper .pr-editor .pr-edit-table .pr-subject-stack .urgent {
    display:flex; align-items:center; gap:7px; margin:0; width:100%;
}
.pr-wrapper .pr-editor .pr-edit-table .pr-subject-stack .urgent input {
    width:14px !important; height:14px; margin:0 !important; flex:none;
}
.pr-wrapper .pr-editor .pr-table-scroll { max-width:100%; min-width:0; overflow:auto; }
</style>
<div class="pr-editor">
@include('pages.progress_report.breadcrumb', ['current' => $report->exists ? $prT('Concept bewerken') : $prT('Nieuw rapport'), 'backTab' => 'draft'])
<div class="head"><div><h1>{{ $report->exists ? $prT('Rapport bewerken') : $prT('Nieuw rapport') }}</h1><p>{{ $prT('Productie · PT NewWicker Indonesia') }}</p></div><span class="tag">{{ $prT('CONCEPT') }}</span></div>
<form id="pr-form" method="POST" action="{{ $report->exists ? route('progress-report.update',$report) : route('progress-report.store') }}">
@csrf @if($report->exists) @method('PUT') @endif
<section class="panel"><h2>01 · {{ $prT('Rapportgegevens') }}</h2><p>{{ $prT('Kies de datum en periode van het rapport.') }}</p><div class="grid meta-grid">
<label class="field"><span>{{ $prT('Datum') }}</span><input type="date" name="report_date" required value="{{ old('report_date',$report->report_date?->format('Y-m-d')??now()->toDateString()) }}"></label>
<label class="field"><span>{{ $prT('Opgesteld door') }}</span><input readonly value="{{ $report->exists ? $report->creator?->name : auth()->user()->name }}"></label>
<label class="field"><span>{{ $prT('Periode vanaf') }}</span><input type="date" name="period_start" required value="{{ old('period_start',$report->period_start?->format('Y-m-d')??now()->startOfWeek()->toDateString()) }}"></label>
<label class="field"><span>{{ $prT('Periode tot') }}</span><input type="date" name="period_end" required value="{{ old('period_end',$report->period_end?->format('Y-m-d')??now()->endOfWeek()->toDateString()) }}"></label>
</div></section>
<div class="section-head"><div><h2>02 · {{ $prT('Voortgangsdetails') }}</h2><p>{{ $prT('Gebruik één regel per onderwerp en eigenaar.') }}</p></div><span id="pr-count" class="hint"></span></div>
<details><summary>{{ $prT('Invulinstructies') }}</summary>@include('pages.progress_report.instructions') @include('pages.progress_report.footer')</details>
<div class="pr-table-scroll" tabindex="0" aria-label="{{ $prT('Tabel met voortgangsdetails') }}"><table class="pr-edit-table"><colgroup><col style="width:46px"><col style="width:250px"><col style="width:280px"><col style="width:240px"><col style="width:155px"><col style="width:175px"><col style="width:290px"><col style="width:62px"></colgroup><thead><tr><th>{{ $prT('No.') }}</th><th>{{ $prT('Onderwerp / status') }}</th><th>{{ $prT('Probleem en orderimpact') }}</th><th>{{ $prT('Actie') }}</th><th>{{ $prT('Eigenaar') }}</th><th>{{ $prT('Deadline') }}</th><th>{{ $prT('Resultaat / besluit nodig') }}</th><th>{{ $prT('Acties') }}</th></tr></thead><tbody id="pr-topics"></tbody></table></div><button class="add" type="button" id="pr-add">＋ {{ $prT('Regel toevoegen') }}</button>
<div class="bottom"><div><strong id="pr-summary">0 {{ $prT(' onderwerpen') }}</strong><div class="hint">{{ $prT('Sla het concept op voordat u publiceert.') }}</div></div><div class="actions"><a class="btn" href="{{ route('progress-report.index',['tab'=>'draft']) }}">{{ $prT('Terug') }}</a>@if($report->exists)<a class="btn" href="{{ route('progress-report.show',$report) }}">{{ $prT('Voorbeeld') }}</a>@endif<button class="primary" type="submit">{{ $prT('Concept opslaan') }}</button>@if($report->exists)<button type="submit" form="pr-publish" id="pr-publish-button">{{ $prT('Publiceren') }}</button>@endif</div></div>
</form>
@if($report->exists)
<form id="pr-publish" method="POST" action="{{ route('progress-report.publish',$report) }}" onsubmit="return confirm({{ \Illuminate\Support\Js::from($prT('Het laatst opgeslagen concept publiceren? Het rapport wordt vergrendeld.')) }})">@csrf</form>
<form class="danger-zone" method="POST" action="{{ route('progress-report.destroy',$report) }}" onsubmit="return confirm({{ \Illuminate\Support\Js::from($prT('Dit concept verwijderen?')) }})">@csrf @method('DELETE')<button class="remove">{{ $prT('Concept verwijderen') }}</button></form>
@endif
<template id="pr-template"><tr class="topic"><td><span class="number" data-number></span><input type="hidden" data-field="id"></td><td class="pr-subject-cell"><div class="pr-subject-stack"><input aria-label="{{ $prT('Onderwerp') }}" data-field="subject" required maxlength="255" placeholder="{{ $prT('Naam onderwerp') }}"><select aria-label="{{ $prT('Status') }}" data-field="status"><option value="OPEN">OPEN</option><option value="IN_PROGRESS">{{ $prT('LOOPT') }}</option><option value="BLOCKED">{{ $prT('GEBLOKKEERD') }}</option><option value="DONE">{{ $prT('GEREED') }}</option></select><label class="urgent"><input type="checkbox" data-field="is_urgent" value="1"> {{ $prT('Urgent') }}</label></div></td><td><textarea aria-label="{{ $prT('Probleem en orderimpact') }}" data-field="problem_order_impact" required maxlength="3000" placeholder="{{ $prT('PO, artikel, aantallen, effect op levering...') }}"></textarea></td><td><textarea aria-label="{{ $prT('Actie') }}" data-field="action" required maxlength="3000" placeholder="{{ $prT('Concrete actie...') }}"></textarea></td><td><select aria-label="{{ $prT('Eigenaar') }}" data-field="pic_id" required><option value="">{{ $prT('Kies eigenaar') }}</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></td><td><input aria-label="{{ $prT('Deadline') }}" type="date" data-field="deadline" required></td><td><textarea aria-label="{{ $prT('Resultaat of besluit') }}" data-field="result_decision" maxlength="3000" placeholder="{{ $prT('Resultaat, besluit, van wie en wanneer...') }}"></textarea><small class="hint" data-result-required hidden>{{ $prT('Verplicht vóór publicatie (GEREED)') }}</small></td><td><button type="button" class="remove" data-remove aria-label="{{ $prT('Regel verwijderen') }}" title="{{ $prT('Regel verwijderen') }}">×</button></td></tr></template>
</div>
@php($initial = old('details', $report->exists ? $report->details->map(fn($d)=>array_merge($d->toArray(),['deadline'=>$d->deadline->format('Y-m-d')]))->all() : []))
<script>
(()=>{const initial={{ Illuminate\Support\Js::from(array_values($initial ?? [])) }};const list=document.getElementById('pr-topics'),form=document.getElementById('pr-form'),pub=document.getElementById('pr-publish-button');let index=0,dirty=false;
function mark(){dirty=true;if(pub){pub.disabled=true;pub.title={{ \Illuminate\Support\Js::from($prT('Sla wijzigingen op vóór publicatie')) }};}}
function refresh(){const rows=[...list.children];rows.forEach((row,i)=>{row.querySelector('[data-number]').textContent=String(i+1).padStart(2,'0');row.querySelector('[data-field=status]').dataset.status=row.querySelector('[data-field=status]').value;row.dataset.urgent=row.querySelector('[data-field=is_urgent]').checked;row.querySelector('[data-result-required]').hidden=row.querySelector('[data-field=status]').value!=='DONE';});const urgent=rows.filter(r=>r.dataset.urgent==='true').length;document.getElementById('pr-count').textContent=rows.length+{{ \Illuminate\Support\Js::from($prT(' onderwerpen')) }};document.getElementById('pr-summary').textContent=rows.length+{{ \Illuminate\Support\Js::from($prT(' onderwerpen')) }}+' · '+urgent+{{ \Illuminate\Support\Js::from($prT(' urgent')) }};document.getElementById('pr-add').disabled=rows.length>=100;}
function add(data={},focus=false){if(list.children.length>=100)return;const row=document.getElementById('pr-template').content.firstElementChild.cloneNode(true),n=index++;row.querySelectorAll('[data-field]').forEach(el=>{const k=el.dataset.field;el.name=`details[${n}][${k}]`;if(el.type==='checkbox')el.checked=Boolean(Number(data[k]||0));else if(data[k]!=null)el.value=data[k];});row.querySelector('[data-remove]').onclick=()=>{if(confirm({{ \Illuminate\Support\Js::from($prT('Dit onderwerp uit het concept verwijderen?')) }})){row.remove();mark();refresh();}};list.append(row);row.querySelectorAll('textarea').forEach(el=>{const fit=()=>{el.style.height='auto';el.style.height=Math.max(115,el.scrollHeight+2)+'px';};el.addEventListener('input',fit);requestAnimationFrame(fit);});refresh();if(focus){mark();row.querySelector('[data-field=subject]').focus();row.scrollIntoView({block:'nearest',inline:'nearest'});}}
initial.forEach(d=>add(d));if(!initial.length)add();document.getElementById('pr-add').onclick=()=>add({},true);form.addEventListener('input',()=>{mark();refresh();});form.addEventListener('change',()=>{mark();refresh();});form.addEventListener('submit',()=>{dirty=false;});window.addEventListener('beforeunload',e=>{if(dirty){e.preventDefault();e.returnValue='';}});refresh();})();
</script>
@endsection
