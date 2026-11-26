@php([$prLang, $prT] = require resource_path('views/pages/progress_report/translations.php'))
<!doctype html><html lang="{{ $prLang }}"><head><meta charset="utf-8"><style>
@page{margin:30pt 35pt}body{font-family:Helvetica,sans-serif;font-size:9pt;color:#000}h1{font-size:18pt;text-align:center;margin:0 0 6pt}p{margin:0 0 7pt;line-height:1.3}p.meta{margin-top:12pt}table{border-collapse:collapse;width:99%;margin:0 auto 9pt;table-layout:fixed}th,td{border:.55pt solid #9aaab6;padding:6pt;vertical-align:top;text-align:left;word-wrap:break-word}th{background:#e5edf2;font-weight:normal;height:20pt}td{height:48pt;white-space:pre-wrap}thead{display:table-header-group}tr{page-break-inside:avoid}
</style></head><body>@include('pages.progress_report.sheet')</body></html>
