@php([$prLang, $prT] = require resource_path('views/pages/progress_report/translations.php'))
<nav class="pr-breadcrumb" aria-label="{{ $prT('Broodkruimelpad') }}"><a href="{{ route('progress-report.index', ['tab' => $backTab ?? 'published']) }}">{{ $prT('Voortgangsrapportage') }}</a>@if(!empty($current))<span aria-hidden="true">/</span><span aria-current="page">{{ $current }}</span>@endif</nav>
