@php([$prLang, $prT] = require resource_path('views/pages/progress_report/translations.php'))
<div class="no-print" style="display:flex;justify-content:flex-end;align-items:center;gap:8px;margin-bottom:14px;flex-wrap:wrap" role="group" aria-label="Language / Taal / Bahasa">
<span style="font-size:12px;color:#64748b">🌐 Language / Taal / Bahasa</span>
@foreach(['nl'=>'NL · Nederlands','id'=>'ID · Indonesia','en'=>'EN · English'] as $code=>$label)
<a href="{{ request()->fullUrlWithQuery(['pr_lang'=>$code]) }}" class="btn {{ $prLang===$code?'primary':'' }}" @if($prLang===$code) aria-current="true" @endif>{{ $label }}</a>
@endforeach
</div>
