@extends('master.master')

@section('content')
    <style>
        <style>.live-activity-item {
            padding: 14px 18px;
            border-top: 1px solid #eee;
            transition: all .3s ease;
        }

        .live-activity-item.new-activity {
            animation: liveActivityFlash 1.5s ease;
        }

        .live-activity-user {
            font-weight: 700;
            color: #212529;
        }

        .live-activity-url {
            font-family: monospace;
            font-size: 13px;
            color: #495057;
            word-break: break-all;
        }

        .live-activity-time {
            font-size: 12px;
            color: #6c757d;
        }

        .live-method {
            font-size: 10px;
            font-weight: 700;
            padding: 3px 7px;
            border-radius: 5px;
        }

        .live-method-get {
            background: #d1e7dd;
            color: #0f5132;
        }

        .live-method-post {
            background: #cfe2ff;
            color: #084298;
        }

        .live-method-put {
            background: #fff3cd;
            color: #664d03;
        }

        .live-method-delete {
            background: #f8d7da;
            color: #842029;
        }

        @keyframes liveActivityFlash {

            0% {
                background: #fff3cd;
            }

            100% {
                background: transparent;
            }

        }
    </style>
    </style>
    <div class="container-fluid px-4 py-3">

        ```
        {{-- ========================================================= --}}
        {{-- TAB NAVIGATION --}}
        {{-- ========================================================= --}}

        <div class="it-tabbar">

            <div class="card border-0 shadow-sm mb-4" style="margin-top:20px">

                <div class="card-body pb-0">

                    <ul class="nav nav-tabs border-0">

                        <li class="nav-item">

                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#web-monitoring"
                                type="button">

                                <i class="bi bi-globe me-1"></i>
                                Web Monitoring

                            </button>

                        </li>

                        <li class="nav-item">

                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#apps-monitoring" type="button">

                                <i class="bi bi-phone me-1"></i>
                                Apps Monitoring

                            </button>

                        </li>

                    </ul>

                </div>

            </div>

        </div>


        <div class="tab-content">


            {{-- ========================================================= --}}
            {{-- WEB MONITORING --}}
            {{-- ========================================================= --}}

            <div class="tab-pane fade show active" id="web-monitoring">


                {{-- HEADER --}}

            @section('btn')
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">

                    <div>

                        <h5 class="fw-bold mb-1 text-dark">
                            IT Dashboard
                        </h5>

                        <p class="text-muted mb-0 small">
                            Web user activity monitoring
                        </p>

                    </div>

                </div>
                <div id="liveActivityContainer" class="card border-0 shadow-sm rounded-3 mb-4 d-none">

                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">

                            <div>
                                <h6 class="fw-bold mb-0">
                                    <span class="text-danger">●</span>
                                    Live Activity
                                </h6>

                                <small class="text-muted">
                                    Aktivitas user secara realtime
                                </small>
                            </div>

                            <div class="d-flex gap-2">

                                <button type="button" id="voiceToggle" class="btn btn-sm btn-outline-primary">
                                    🔊 Voice ON
                                </button>

                                <button type="button" id="soundToggle" class="btn btn-sm btn-outline-secondary">
                                    🔔 Sound ON
                                </button>

                            </div>

                        </div>
                    </div>

                    <div class="card-body p-0">

                        {{-- <div id="liveActivityList"></div> --}}

                    </div>

                </div>
            @endsection


            {{-- ===================================================== --}}
            {{-- WEB SUMMARY CARDS --}}
            {{-- ===================================================== --}}

            <div class="row g-3 mb-4">


                {{-- TOTAL ACTIVITY --}}

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100 rounded-3">

                        <div class="card-body p-3 d-flex align-items-center gap-3">

                            <div class="metric-icon bg-primary bg-opacity-10 text-primary">

                                <i class="bi bi-activity fs-4"></i>

                            </div>

                            <div>

                                <span class="text-muted small fw-semibold d-block">
                                    Total Activity
                                </span>

                                <h4 id="totalActivity" class="fw-bold mb-0 text-dark">

                                    {{ number_format($totalActivity) }}

                                </h4>

                                <span class="text-muted small">
                                    All web activity
                                </span>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- ACTIVE USERS --}}

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100 rounded-3">

                        <div class="card-body p-3 d-flex align-items-center gap-3">

                            <div class="metric-icon bg-success bg-opacity-10 text-success">

                                <i class="bi bi-people fs-4"></i>

                            </div>

                            <div>

                                <span class="text-muted small fw-semibold d-block">
                                    Active Users
                                </span>

                                <h4 id="activeUsers" class="fw-bold mb-0 text-dark">

                                    {{ number_format($activeUsers) }}

                                </h4>

                                <span class="text-muted small">
                                    Active today
                                </span>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- ACTIVITY TODAY --}}

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100 rounded-3">

                        <div class="card-body p-3 d-flex align-items-center gap-3">

                            <div class="metric-icon bg-warning bg-opacity-10 text-warning">

                                <i class="bi bi-calendar-check fs-4"></i>

                            </div>

                            <div>

                                <span class="text-muted small fw-semibold d-block">
                                    Activity Today
                                </span>

                                <h4 id="todayActivity" class="fw-bold mb-0 text-dark">

                                    {{ number_format($todayActivity) }}

                                </h4>

                                <span class="text-muted small">
                                    Today's activity
                                </span>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- MOST ACTIVE USER --}}

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100 rounded-3">

                        <div class="card-body p-3 d-flex align-items-center gap-3">

                            <div class="metric-icon bg-danger bg-opacity-10 text-danger">

                                <i class="bi bi-person-check fs-4"></i>

                            </div>

                            <div id="mostActiveUserContainer" class="overflow-hidden">

                                <span class="text-muted small fw-semibold d-block">
                                    Most Active User
                                </span>

                                @if ($mostActiveUser)
                                    <h5 class="fw-bold mb-0 text-dark text-truncate">

                                        {{ $mostActiveUser->user->name ?? '-' }}

                                    </h5>

                                    <span class="text-muted small">

                                        {{ number_format($mostActiveUser->total_activity) }}
                                        activities / 7 days

                                    </span>
                                @else
                                    <h5 class="fw-bold mb-0">
                                        -
                                    </h5>
                                @endif

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ===================================================== --}}
            {{-- WEB CHARTS --}}
            {{-- ===================================================== --}}

            <div class="row g-3 mb-4">


                {{-- ACTIVITY PER DAY --}}

                <div class="col-12 col-lg-8">

                    <div class="card border-0 shadow-sm rounded-3 h-100">

                        <div class="card-header bg-white py-3 border-0">

                            <strong>
                                User Activity
                            </strong>

                            <div class="text-muted small">
                                Activity 7 hari terakhir
                            </div>

                        </div>

                        <div class="card-body">

                            <div id="activityByDayChart" style="height:360px;">
                            </div>

                        </div>

                    </div>

                </div>


                {{-- ACTIVITY MODULE --}}

                <div class="col-12 col-lg-4">

                    <div class="card border-0 shadow-sm rounded-3 h-100">

                        <div class="card-header bg-white py-3 border-0">

                            <strong>
                                Activity by Module
                            </strong>

                            <div class="text-muted small">
                                30 hari terakhir
                            </div>

                        </div>

                        <div class="card-body">

                            <div id="activityByModuleChart" style="height:260px;">
                            </div>

                            <div id="activityByModuleList" class="mt-2 small" style="padding:20px;">

                                @foreach ($activityByModule as $module)
                                    <div class="d-flex justify-content-between mb-1">

                                        <span>
                                            {{ $module['name'] }}
                                        </span>

                                        <span class="fw-bold">
                                            {{ number_format($module['total']) }}
                                        </span>

                                    </div>
                                @endforeach

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ===================================================== --}}
            {{-- WEB ACTIVITY TABLE --}}
            {{-- ===================================================== --}}

            <div class="card border-0 shadow-sm rounded-3 mb-4">

                <div class="card-header bg-white py-3 border-0">

                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">

                        <div>

                            <h6 class="fw-bold mb-0">
                                User Activity
                            </h6>

                            <small class="text-muted">
                                Latest 500 activity
                            </small>

                        </div>


                        <div style="width:280px;">

                            <div class="input-group input-group-sm">

                                <span class="input-group-text bg-white">

                                    <i class="bi bi-search"></i>

                                </span>

                                <input type="text" id="searchUser" class="form-control"
                                    placeholder="Search user..." autocomplete="off">

                            </div>

                        </div>

                    </div>

                </div>


                <div class="card-body p-0">

                    <div class="it-table-wrapper">

                        <table class="table table-hover table-bordered align-middle mb-0" id="activityTable">

                            <thead>

                                <tr>

                                    <th style="width:60px;">
                                        No
                                    </th>

                                    <th>
                                        User
                                    </th>

                                    <th>
                                        Route
                                    </th>

                                    <th>
                                        URL
                                    </th>

                                    <th class="text-center">
                                        Method
                                    </th>

                                    <th>
                                        IP Address
                                    </th>

                                    <th>
                                        Time
                                    </th>

                                </tr>

                            </thead>


                            <tbody id="activityTableBody">

                                @forelse($activities as $index => $activity)
                                    <tr class="activity-row">

                                        <td class="text-center">
                                            {{ $index + 1 }}
                                        </td>

                                        <td>

                                            <strong class="user-name">

                                                {{ $activity->user->name ?? '-' }}

                                            </strong>

                                        </td>

                                        <td>
                                            {{ $activity->route_name ?? '-' }}
                                        </td>

                                        <td>

                                            <code>
                                                {{ $activity->url }}
                                            </code>

                                        </td>

                                        <td class="text-center">

                                            @if ($activity->method === 'GET')
                                                <span class="badge bg-success">
                                                    GET
                                                </span>
                                            @elseif($activity->method === 'POST')
                                                <span class="badge bg-primary">
                                                    POST
                                                </span>
                                            @elseif($activity->method === 'PUT' || $activity->method === 'PATCH')
                                                <span class="badge bg-warning text-dark">
                                                    {{ $activity->method }}
                                                </span>
                                            @elseif($activity->method === 'DELETE')
                                                <span class="badge bg-danger">
                                                    DELETE
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    {{ $activity->method }}
                                                </span>
                                            @endif

                                        </td>

                                        <td>
                                            {{ $activity->ip_address ?? '-' }}
                                        </td>

                                        <td>

                                            <div>
                                                {{ $activity->created_at?->format('d M Y') }}
                                            </div>

                                            <small class="text-muted">
                                                {{ $activity->created_at?->format('H:i:s') }}
                                            </small>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td colspan="7" class="text-center py-5 text-muted">

                                            Belum ada activity.

                                        </td>

                                    </tr>
                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- APPS MONITORING --}}
        {{-- ========================================================= --}}

        <div class="tab-pane fade" id="apps-monitoring">


            {{-- HEADER --}}

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">

                <div>

                    <h5 class="fw-bold mb-1 text-dark">
                        Apps Monitoring
                    </h5>

                    <p class="text-muted mb-0 small">
                        QC Application API activity monitoring
                    </p>

                </div>

            </div>


            {{-- ===================================================== --}}
            {{-- API SUMMARY CARDS --}}
            {{-- ===================================================== --}}

            <div class="row g-3 mb-4">


                {{-- API ACTIVITY --}}

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100 rounded-3">

                        <div class="card-body p-3 d-flex align-items-center gap-3">

                            <div class="metric-icon bg-primary bg-opacity-10 text-primary">

                                <i class="bi bi-phone fs-4"></i>

                            </div>

                            <div>

                                <span class="text-muted small fw-semibold d-block">
                                    API Activity
                                </span>

                                <h4 id="totalApiActivity" class="fw-bold mb-0 text-dark">

                                    {{ number_format($totalApiActivity) }}

                                </h4>

                                <span class="text-muted small">
                                    All API requests
                                </span>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- ACTIVE API USERS --}}

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100 rounded-3">

                        <div class="card-body p-3 d-flex align-items-center gap-3">

                            <div class="metric-icon bg-success bg-opacity-10 text-success">

                                <i class="bi bi-people fs-4"></i>

                            </div>

                            <div>

                                <span class="text-muted small fw-semibold d-block">
                                    Active Users
                                </span>

                                <h4 id="activeApiUsers" class="fw-bold mb-0 text-dark">

                                    {{ number_format($activeApiUsers) }}

                                </h4>

                                <span class="text-muted small">
                                    API users today
                                </span>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- API TODAY --}}

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100 rounded-3">

                        <div class="card-body p-3 d-flex align-items-center gap-3">

                            <div class="metric-icon bg-warning bg-opacity-10 text-warning">

                                <i class="bi bi-calendar-check fs-4"></i>

                            </div>

                            <div>

                                <span class="text-muted small fw-semibold d-block">
                                    API Today
                                </span>

                                <h4 id="todayApiActivity" class="fw-bold mb-0 text-dark">

                                    {{ number_format($todayApiActivity) }}

                                </h4>

                                <span class="text-muted small">
                                    Requests today
                                </span>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- MOST USED API --}}

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100 rounded-3">

                        <div class="card-body p-3 d-flex align-items-center gap-3">

                            <div class="metric-icon bg-danger bg-opacity-10 text-danger">

                                <i class="bi bi-bar-chart fs-4"></i>

                            </div>

                            <div id="mostUsedApiContainer" class="overflow-hidden">

                                <span class="text-muted small fw-semibold d-block">
                                    Most Used API
                                </span>

                                @if ($mostUsedApi)
                                    <h6 class="fw-bold mb-0 text-dark text-truncate" title="{{ $mostUsedApi->url }}">

                                        {{ $mostUsedApi->url }}

                                    </h6>

                                    <small class="text-muted">

                                        {{ number_format($mostUsedApi->total) }}
                                        requests

                                    </small>
                                @else
                                    <h6 class="fw-bold mb-0">
                                        -
                                    </h6>
                                @endif

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ===================================================== --}}
            {{-- API CHARTS --}}
            {{-- ===================================================== --}}

            <div class="row g-3 mb-4">


                {{-- API ACTIVITY 7 DAYS --}}

                <div class="col-12 col-lg-8">

                    <div class="card border-0 shadow-sm rounded-3 h-100">

                        <div class="card-header bg-white py-3 border-0">

                            <strong>
                                API Activity
                            </strong>

                            <div class="text-muted small">
                                API request 7 hari terakhir
                            </div>

                        </div>

                        <div class="card-body">

                            <div id="apiActivityByDayChart" style="height:360px;">
                            </div>

                        </div>

                    </div>

                </div>


                {{-- API ENDPOINT --}}

                <div class="col-12 col-lg-4">

                    <div class="card border-0 shadow-sm rounded-3 h-100">

                        <div class="card-header bg-white py-3 border-0">

                            <strong>
                                API Endpoint Usage
                            </strong>

                            <div class="text-muted small">
                                Most frequently called
                            </div>

                        </div>

                        <div class="card-body">

                            <div id="apiEndpointChart" style="height:360px;">
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ===================================================== --}}
            {{-- API ACTIVITY TABLE --}}
            {{-- ===================================================== --}}

            <div class="card border-0 shadow-sm rounded-3 mb-4">

                <div class="card-header bg-white py-3 border-0">

                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">

                        <div>

                            <h6 class="fw-bold mb-0">
                                Apps Activity
                            </h6>

                            <small class="text-muted">
                                Latest 500 API activity
                            </small>

                        </div>


                        <div style="width:280px;">

                            <div class="input-group input-group-sm">

                                <span class="input-group-text bg-white">

                                    <i class="bi bi-search"></i>

                                </span>

                                <input type="text" id="searchAppUser" class="form-control"
                                    placeholder="Search user..." autocomplete="off">

                            </div>

                        </div>

                    </div>

                </div>


                <div class="card-body p-0">

                    <div class="it-table-wrapper">

                        <table class="table table-hover table-bordered align-middle mb-0" id="appActivityTable">

                            <thead>

                                <tr>

                                    <th style="width:60px;">
                                        No
                                    </th>

                                    <th>
                                        User
                                    </th>

                                    <th>
                                        Endpoint
                                    </th>

                                    <th class="text-center">
                                        Method
                                    </th>

                                    <th>
                                        IP Address
                                    </th>

                                    <th>
                                        User Agent
                                    </th>

                                    <th>
                                        Time
                                    </th>

                                </tr>

                            </thead>


                            <tbody id="appActivityTableBody">

                                @forelse($appActivities as $index => $activity)
                                    <tr class="app-activity-row">

                                        <td class="text-center">
                                            {{ $index + 1 }}
                                        </td>

                                        <td>

                                            <strong class="app-user-name">

                                                {{ $activity->user->name ?? '-' }}

                                            </strong>

                                        </td>

                                        <td>

                                            <code>
                                                {{ $activity->url }}
                                            </code>

                                        </td>

                                        <td class="text-center">

                                            @if ($activity->method === 'GET')
                                                <span class="badge bg-success">
                                                    GET
                                                </span>
                                            @elseif($activity->method === 'POST')
                                                <span class="badge bg-primary">
                                                    POST
                                                </span>
                                            @elseif($activity->method === 'PUT' || $activity->method === 'PATCH')
                                                <span class="badge bg-warning text-dark">
                                                    {{ $activity->method }}
                                                </span>
                                            @elseif($activity->method === 'DELETE')
                                                <span class="badge bg-danger">
                                                    DELETE
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    {{ $activity->method }}
                                                </span>
                                            @endif

                                        </td>

                                        <td>
                                            {{ $activity->ip_address ?? '-' }}
                                        </td>

                                        <td style="max-width:250px;" class="text-truncate"
                                            title="{{ $activity->user_agent }}">

                                            {{ $activity->user_agent ?? '-' }}

                                        </td>

                                        <td>

                                            <div>
                                                {{ $activity->created_at?->format('d M Y') }}
                                            </div>

                                            <small class="text-muted">
                                                {{ $activity->created_at?->format('H:i:s') }}
                                            </small>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td colspan="7" class="text-center py-5 text-muted">

                                            <i class="bi bi-phone fs-3 d-block mb-2"></i>

                                            Belum ada API activity.

                                        </td>

                                    </tr>
                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>
    ```

</div>

{{-- ============================================================= --}}
{{-- VITE --}}
{{-- ============================================================= --}}

@vite(['resources/css/app.css', 'resources/js/app.js'])

{{-- ============================================================= --}}
{{-- APEXCHARTS --}}
{{-- ============================================================= --}}

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    let voiceEnabled = true;
    let soundEnabled = true;
    let activityByDayChart = null;
    let activityByModuleChart = null;
    let apiActivityByDayChart = null;
    let apiEndpointChart = null;

    let dashboardRefreshing = false;

  function speakActivity(activity) {

    if (!voiceEnabled) {
        return;
    }

    if (!('speechSynthesis' in window)) {
        console.warn('Speech Synthesis tidak tersedia.');
        return;
    }

    const user =
        activity.user_name ??
        activity.user?.name ??
        'User';

    const url =
        activity.url ??
        '';

    const route =
        activity.route_name ??
        '';

    const method =
        String(activity.method ?? 'GET').toUpperCase();


    /*
    |--------------------------------------------------------------------------
    | JANGAN GUNAKAN ROUTE "generated:..."
    |--------------------------------------------------------------------------
    */

    const isGeneratedRoute =
        route &&
        route.toLowerCase().startsWith('generated:');


    /*
    |--------------------------------------------------------------------------
    | TENTUKAN IDENTITAS HALAMAN
    |--------------------------------------------------------------------------
    */

    let page = '';

    if (
        url &&
        url !== '/' &&
        url !== '#'
    ) {

        page = url;

    } else if (
        route &&
        !isGeneratedRoute
    ) {

        page = route;

    } else {

        page = 'halaman tersebut';

    }


    /*
    |--------------------------------------------------------------------------
    | KALIMAT VOICE
    |--------------------------------------------------------------------------
    */

    let text = '';


    if (method === 'POST') {

        text =
            `${user} menyimpan data pada ${page}`;

    } else if (
        method === 'PUT' ||
        method === 'PATCH'
    ) {

        text =
            `${user} memperbarui data pada ${page}`;

    } else if (method === 'DELETE') {

        text =
            `${user} menghapus data pada ${page}`;

    } else {

        text =
            `${user} mengakses ${page}`;

    }


    /*
    |--------------------------------------------------------------------------
    | SPEAK
    |--------------------------------------------------------------------------
    */

    window.speechSynthesis.cancel();

    const utterance =
        new SpeechSynthesisUtterance(text);

    utterance.lang = 'id-ID';
    utterance.rate = 0.95;
    utterance.pitch = 1;
    utterance.volume = 1;

    window.speechSynthesis.speak(
        utterance
    );
}
    function showLiveActivity(activity) {
        const container =
            document.getElementById(
                'liveActivityContainer'
            );

        const list =
            document.getElementById(
                'liveActivityList'
            );

        if (!container || !list) {
            return;
        }

        container.classList.remove(
            'd-none'
        );

        const user =
            activity.user_name ??
            activity.user?.name ??
            'User';

        const route =
            activity.route_name ??
            '-';

        const url =
            activity.url ??
            '-';

        const method =
            activity.method ??
            'GET';

        const time =
            new Date().toLocaleTimeString(
                'id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                }
            );

        let methodClass =
            'live-method-get';

        if (method === 'POST') {
            methodClass =
                'live-method-post';
        }

        if (
            method === 'PUT' ||
            method === 'PATCH'
        ) {
            methodClass =
                'live-method-put';
        }

        if (method === 'DELETE') {
            methodClass =
                'live-method-delete';
        }

        const item =
            document.createElement('div');

        item.className =
            'live-activity-item new-activity';

        item.innerHTML = `
        <div class="d-flex justify-content-between gap-3">

            <div>

                <div class="live-activity-user">

                    👤 ${escapeHtml(user)}

                    <span class="live-method ${methodClass}">
                        ${escapeHtml(method)}
                    </span>

                </div>

                <div class="mt-1">
                    Mengakses:
                    <strong>
                        ${escapeHtml(route)}
                    </strong>
                </div>

                <div class="live-activity-url mt-1">
                    ${escapeHtml(url)}
                </div>

            </div>

            <div class="live-activity-time">
                ${time}
            </div>

        </div>
    `;

        list.prepend(item);

        while (list.children.length > 10) {
            list.removeChild(
                list.lastElementChild
            );
        }

        playActivitySound();
    }

    function playActivitySound() {
        if (!soundEnabled) {
            return;
        }

        try {

            const AudioContext =
                window.AudioContext ||
                window.webkitAudioContext;

            if (!AudioContext) {
                return;
            }

            const context =
                new AudioContext();

            const oscillator =
                context.createOscillator();

            const gain =
                context.createGain();

            oscillator.type = 'sine';

            oscillator.frequency.value =
                880;

            gain.gain.setValueAtTime(
                0.0001,
                context.currentTime
            );

            gain.gain.exponentialRampToValueAtTime(
                0.15,
                context.currentTime + 0.02
            );

            gain.gain.exponentialRampToValueAtTime(
                0.0001,
                context.currentTime + 0.25
            );

            oscillator.connect(gain);
            gain.connect(context.destination);

            oscillator.start();

            oscillator.stop(
                context.currentTime + 0.25
            );

        } catch (error) {

            console.error(
                'Sound error:',
                error
            );

        }
    }

    /*
    |--------------------------------------------------------------------------
    | DOM READY
    |--------------------------------------------------------------------------
    */

    document.addEventListener('DOMContentLoaded', function() {

        const voiceToggle =
            document.getElementById(
                'voiceToggle'
            );

        const soundToggle =
            document.getElementById(
                'soundToggle'
            );


        if (voiceToggle) {

            voiceToggle.addEventListener(
                'click',
                function() {

                    voiceEnabled = !voiceEnabled;

                    this.textContent =
                        voiceEnabled ?
                        '🔊 Voice ON' :
                        '🔇 Voice OFF';

                }
            );

        }


     if (soundToggle) {

    soundToggle.addEventListener(
        'click',
        function () {

            soundEnabled =
                !soundEnabled;

            this.textContent =
                soundEnabled
                    ? '🔔 Sound ON'
                    : '🔕 Sound OFF';

        }
    );

}



    
    /*
    |--------------------------------------------------------------------------
    | INITIAL CHART
    |--------------------------------------------------------------------------
    */

    initializeCharts();


    /*
    |--------------------------------------------------------------------------
    | SEARCH WEB USER
    |--------------------------------------------------------------------------
    */

    const searchUser = document.getElementById('searchUser');

    if (searchUser) {

        searchUser.addEventListener('input', function() {

            const keyword = this.value.toLowerCase();

            document
                .querySelectorAll('#activityTableBody .activity-row')
                .forEach(row => {

                    const user =
                        row.querySelector('.user-name')
                        ?.textContent
                        .toLowerCase() ?? '';

                    row.style.display =
                        user.includes(keyword) ?
                        '' :
                        'none';

                });

        });

    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH API USER
    |--------------------------------------------------------------------------
    */

    const searchAppUser =
        document.getElementById('searchAppUser');

    if (searchAppUser) {

        searchAppUser.addEventListener('input', function() {

            const keyword =
                this.value.toLowerCase();

            document
                .querySelectorAll('#appActivityTableBody .app-activity-row')
                .forEach(row => {

                    const user =
                        row.querySelector('.app-user-name')
                        ?.textContent
                        .toLowerCase() ?? '';

                    row.style.display =
                        user.includes(keyword) ?
                        '' :
                        'none';

                });

        });

    }


    /*
    |--------------------------------------------------------------------------
    | ECHO / PUSHER
    |--------------------------------------------------------------------------
    */

    if (typeof window.Echo === 'undefined') {

        console.error(
            'Laravel Echo belum tersedia.'
        );

        return;

    }


    console.log(
        'Connecting to Pusher...'
    );


    window.Echo
    .channel('it-dashboard')
    .listen('.activity.logged', function(activity) {

        console.log(
            '🔥 Activity baru:',
            activity
        );


        /*
        |--------------------------------------------------------------------------
        | JANGAN RENDER DARI DATA EVENT
        |--------------------------------------------------------------------------
        |
        | Event hanya menjadi trigger.
        | Setelah ada activity baru, kita request ulang
        | seluruh data dashboard.
        |
        */

        // tampilkan aktivitas
        showLiveActivity(activity);

        // voice
        speakActivity(activity);

        // refresh tabel/dashboard yang lama
        refreshITDashboard();

    });

    });


    /*
    |--------------------------------------------------------------------------
    | REFRESH DASHBOARD
    |--------------------------------------------------------------------------
    */

    function refreshITDashboard() {

        /*
        |--------------------------------------------------------------------------
        | Hindari request bersamaan
        |--------------------------------------------------------------------------
        */

        if (dashboardRefreshing) {
            return;
        }


        dashboardRefreshing = true;


        console.log(
            '🔄 Refreshing IT Dashboard...'
        );


        fetch('{{ route('it.data') }}', {

                method: 'GET',

                headers: {

                    'X-Requested-With': 'XMLHttpRequest',

                    'Accept': 'application/json'

                }

            })

            .then(response => {

                if (!response.ok) {

                    throw new Error(
                        'HTTP Error: ' +
                        response.status
                    );

                }

                return response.json();

            })

            .then(data => {

                renderITDashboard(data);

                console.log(
                    '✅ IT Dashboard updated'
                );

            })

            .catch(error => {

                console.error(
                    '❌ Dashboard refresh error:',
                    error
                );

            })

            .finally(() => {

                dashboardRefreshing = false;

            });

    }


    /*
    |--------------------------------------------------------------------------
    | RENDER ALL DASHBOARD
    |--------------------------------------------------------------------------
    */

    function renderITDashboard(data) {

        /*
        |--------------------------------------------------------------------------
        | WEB SUMMARY
        |--------------------------------------------------------------------------
        */

        setText(
            'totalActivity',
            numberFormat(data.totalActivity)
        );


        setText(
            'activeUsers',
            numberFormat(data.activeUsers)
        );


        setText(
            'todayActivity',
            numberFormat(data.todayActivity)
        );


        /*
        |--------------------------------------------------------------------------
        | MOST ACTIVE USER
        |--------------------------------------------------------------------------
        */

        renderMostActiveUser(
            data.mostActiveUser
        );


        /*
        |--------------------------------------------------------------------------
        | WEB TABLE
        |--------------------------------------------------------------------------
        */

        renderWebActivityTable(
            data.activities
        );


        /*
        |--------------------------------------------------------------------------
        | WEB CHARTS
        |--------------------------------------------------------------------------
        */

        updateActivityByDayChart(
            data.activityByDay
        );


        updateActivityByModuleChart(
            data.activityByModule
        );


        /*
        |--------------------------------------------------------------------------
        | API SUMMARY
        |--------------------------------------------------------------------------
        */

        setText(
            'totalApiActivity',
            numberFormat(data.totalApiActivity)
        );


        setText(
            'activeApiUsers',
            numberFormat(data.activeApiUsers)
        );


        setText(
            'todayApiActivity',
            numberFormat(data.todayApiActivity)
        );


        /*
        |--------------------------------------------------------------------------
        | MOST USED API
        |--------------------------------------------------------------------------
        */

        renderMostUsedApi(
            data.mostUsedApi
        );


        /*
        |--------------------------------------------------------------------------
        | API TABLE
        |--------------------------------------------------------------------------
        */

        renderApiActivityTable(
            data.appActivities
        );


        /*
        |--------------------------------------------------------------------------
        | API CHARTS
        |--------------------------------------------------------------------------
        */

        updateApiActivityByDayChart(
            data.apiActivityByDay
        );


        updateApiEndpointChart(
            data.apiEndpointUsage
        );

    }


    /*
    |--------------------------------------------------------------------------
    | FORMAT NUMBER
    |--------------------------------------------------------------------------
    */

    function numberFormat(value) {

        return Number(
            value ?? 0
        ).toLocaleString(
            'en-US'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | SET TEXT
    |--------------------------------------------------------------------------
    */

    function setText(id, value) {

        const element =
            document.getElementById(id);


        if (element) {

            element.textContent =
                value;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | MOST ACTIVE USER
    |--------------------------------------------------------------------------
    */

    function renderMostActiveUser(user) {

        const container =
            document.getElementById(
                'mostActiveUserContainer'
            );


        if (!container) {
            return;
        }


        if (!user) {

            container.innerHTML = `

            <span class="text-muted small fw-semibold d-block">
                Most Active User
            </span>

            <h5 class="fw-bold mb-0">
                -
            </h5>

        `;

            return;

        }


        const name =
            user.user?.name ?? '-';


        const total =
            numberFormat(
                user.total_activity
            );


        container.innerHTML = `

        <span class="text-muted small fw-semibold d-block">
            Most Active User
        </span>

        <h5 class="fw-bold mb-0 text-dark text-truncate"
            title="${escapeHtml(name)}">

            ${escapeHtml(name)}

        </h5>

        <span class="text-muted small">

            ${total}
            activities / 7 days

        </span>

    `;

    }


    /*
    |--------------------------------------------------------------------------
    | MOST USED API
    |--------------------------------------------------------------------------
    */

    function renderMostUsedApi(api) {

        const container =
            document.getElementById(
                'mostUsedApiContainer'
            );


        if (!container) {
            return;
        }


        if (!api) {

            container.innerHTML = `

            <span class="text-muted small fw-semibold d-block">
                Most Used API
            </span>

            <h6 class="fw-bold mb-0">
                -
            </h6>

        `;

            return;

        }


        container.innerHTML = `

        <span class="text-muted small fw-semibold d-block">
            Most Used API
        </span>

        <h6 class="fw-bold mb-0 text-dark text-truncate"
            title="${escapeHtml(api.url ?? '-')}">

            ${escapeHtml(api.url ?? '-')}

        </h6>

        <small class="text-muted">

            ${numberFormat(api.total)}
            requests

        </small>

    `;

    }


    /*
    |--------------------------------------------------------------------------
    | WEB TABLE
    |--------------------------------------------------------------------------
    */

    function renderWebActivityTable(activities) {

        const tbody =
            document.getElementById(
                'activityTableBody'
            );


        if (!tbody) {
            return;
        }


        if (!activities || !activities.length) {

            tbody.innerHTML = `

            <tr>

                <td colspan="7"
                    class="text-center py-5 text-muted">

                    Belum ada activity.

                </td>

            </tr>

        `;

            return;

        }


        let html = '';


        activities.forEach((activity, index) => {

            html += `

            <tr class="activity-row">

                <td class="text-center">
                    ${index + 1}
                </td>

                <td>

                    <strong class="user-name">

                        ${escapeHtml(
                            activity.user_name  ?? '-'
                        )}

                    </strong>

                </td>

                <td>

                    ${escapeHtml(
                        activity.route_name ?? '-'
                    )}

                </td>

                <td>

                    <code>

                        ${escapeHtml(
                            activity.url ?? '-'
                        )}

                    </code>

                </td>

                <td class="text-center">

                    ${methodBadge(
                        activity.method
                    )}

                </td>

                <td>

                    ${escapeHtml(
                        activity.ip_address ?? '-'
                    )}

                </td>

                <td>

                    <div>
                        ${formatDate(
                            activity.created_at
                        )}
                    </div>

                    <small class="text-muted">
                        ${formatTime(
                            activity.created_at
                        )}
                    </small>

                </td>

            </tr>

        `;

        });


        tbody.innerHTML = html;


        /*
        |--------------------------------------------------------------------------
        | Terapkan search yang sedang aktif
        |--------------------------------------------------------------------------
        */

        const search =
            document.getElementById(
                'searchUser'
            );


        if (search && search.value) {

            filterWebTable(
                search.value
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | API TABLE
    |--------------------------------------------------------------------------
    */

    function renderApiActivityTable(activities) {

        const tbody =
            document.getElementById(
                'appActivityTableBody'
            );


        if (!tbody) {
            return;
        }


        if (!activities || !activities.length) {

            tbody.innerHTML = `

            <tr>

                <td colspan="7"
                    class="text-center py-5 text-muted">

                    <i class="bi bi-phone fs-3 d-block mb-2"></i>

                    Belum ada API activity.

                </td>

            </tr>

        `;

            return;

        }


        let html = '';


        activities.forEach((activity, index) => {

            html += `

            <tr class="app-activity-row">

                <td class="text-center">
                    ${index + 1}
                </td>

                <td>

                    <strong class="app-user-name">

                        ${escapeHtml(
                            activity.user?.name ?? '-'
                        )}

                    </strong>

                </td>

                <td>

                    <code>

                        ${escapeHtml(
                            activity.url ?? '-'
                        )}

                    </code>

                </td>

                <td class="text-center">

                    ${methodBadge(
                        activity.method
                    )}

                </td>

                <td>

                    ${escapeHtml(
                        activity.ip_address ?? '-'
                    )}

                </td>

                <td style="max-width:250px;"
                    class="text-truncate"
                    title="${escapeHtml(
                        activity.user_agent ?? '-'
                    )}">

                    ${escapeHtml(
                        activity.user_agent ?? '-'
                    )}

                </td>

                <td>

                    <div>
                        ${formatDate(
                            activity.created_at
                        )}
                    </div>

                    <small class="text-muted">

                        ${formatTime(
                            activity.created_at
                        )}

                    </small>

                </td>

            </tr>

        `;

        });


        tbody.innerHTML = html;


        const search =
            document.getElementById(
                'searchAppUser'
            );


        if (search && search.value) {

            filterAppTable(
                search.value
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | METHOD BADGE
    |--------------------------------------------------------------------------
    */

    function methodBadge(method) {

        method =
            method ?? '-';


        let badge =
            'bg-secondary';


        if (method === 'GET') {

            badge =
                'bg-success';

        } else if (method === 'POST') {

            badge =
                'bg-primary';

        } else if (
            method === 'PUT' ||
            method === 'PATCH'
        ) {

            return `

            <span class="badge bg-warning text-dark">

                ${escapeHtml(method)}

            </span>

        `;

        } else if (method === 'DELETE') {

            badge =
                'bg-danger';

        }


        return `

        <span class="badge ${badge}">

            ${escapeHtml(method)}

        </span>

    `;

    }


    /*
    |--------------------------------------------------------------------------
    | DATE
    |--------------------------------------------------------------------------
    */

    function formatDate(value) {

        if (!value) {
            return '-';
        }


        const date =
            new Date(value);


        if (isNaN(date.getTime())) {
            return '-';
        }


        return date.toLocaleDateString(
            'en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | TIME
    |--------------------------------------------------------------------------
    */

    function formatTime(value) {

        if (!value) {
            return '-';
        }


        const date =
            new Date(value);


        if (isNaN(date.getTime())) {
            return '-';
        }


        return date.toLocaleTimeString(
            'en-GB', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | ESCAPE HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {

        const div =
            document.createElement('div');


        div.textContent =
            value ?? '';


        return div.innerHTML;

    }


    /*
    |--------------------------------------------------------------------------
    | FILTER WEB
    |--------------------------------------------------------------------------
    */

    function filterWebTable(keyword) {

        keyword =
            keyword.toLowerCase();


        document
            .querySelectorAll(
                '#activityTableBody .activity-row'
            )
            .forEach(row => {

                const user =
                    row.querySelector(
                        '.user-name'
                    )?.textContent
                    .toLowerCase() ?? '';


                row.style.display =
                    user.includes(keyword) ?
                    '' :
                    'none';

            });

    }


    /*
    |--------------------------------------------------------------------------
    | FILTER API
    |--------------------------------------------------------------------------
    */

    function filterAppTable(keyword) {

        keyword =
            keyword.toLowerCase();


        document
            .querySelectorAll(
                '#appActivityTableBody .app-activity-row'
            )
            .forEach(row => {

                const user =
                    row.querySelector(
                        '.app-user-name'
                    )?.textContent
                    .toLowerCase() ?? '';


                row.style.display =
                    user.includes(keyword) ?
                    '' :
                    'none';

            });

    }


    /*
    |--------------------------------------------------------------------------
    | INITIALIZE CHARTS
    |--------------------------------------------------------------------------
    */

    function initializeCharts() {

        /*
        |--------------------------------------------------------------------------
        | WEB ACTIVITY BY DAY
        |--------------------------------------------------------------------------
        */

        const activityDayEl =
            document.getElementById(
                'activityByDayChart'
            );


        if (activityDayEl) {

            activityByDayChart =
                new ApexCharts(
                    activityDayEl, {
                        chart: {
                            type: 'area',
                            height: 360,
                            toolbar: {
                                show: false
                            }
                        },

                        series: [{
                            name: 'Activity',
                            data: @json($activityByDay->pluck('total')->values())
                        }],

                        xaxis: {
                            categories: @json($activityByDay->pluck('label')->values())
                        },

                        stroke: {
                            curve: 'smooth',
                            width: 3
                        },

                        dataLabels: {
                            enabled: false
                        },

                        tooltip: {
                            y: {
                                formatter: function(value) {
                                    return numberFormat(value);
                                }
                            }
                        }
                    }
                );

            activityByDayChart.render();

        }


        /*
        |--------------------------------------------------------------------------
        | WEB MODULE
        |--------------------------------------------------------------------------
        */

        const moduleEl =
            document.getElementById(
                'activityByModuleChart'
            );


        if (moduleEl) {

            activityByModuleChart =
                new ApexCharts(
                    moduleEl, {
                        chart: {
                            type: 'donut',
                            height: 260
                        },

                        series: @json($activityByModule->pluck('total')->values()),

                        labels: @json($activityByModule->pluck('name')->values()),

                        legend: {
                            show: false
                        }
                    }
                );

            activityByModuleChart.render();

        }


        /*
        |--------------------------------------------------------------------------
        | API ACTIVITY BY DAY
        |--------------------------------------------------------------------------
        */

        const apiDayEl =
            document.getElementById(
                'apiActivityByDayChart'
            );


        if (apiDayEl) {

            apiActivityByDayChart =
                new ApexCharts(
                    apiDayEl, {
                        chart: {
                            type: 'area',
                            height: 360,
                            toolbar: {
                                show: false
                            }
                        },

                        series: [{
                            name: 'API Request',
                            data: @json($apiActivityByDay->pluck('total')->values())
                        }],

                        xaxis: {
                            categories: @json($apiActivityByDay->pluck('label')->values())
                        },

                        stroke: {
                            curve: 'smooth',
                            width: 3
                        },

                        dataLabels: {
                            enabled: false
                        },

                        tooltip: {
                            y: {
                                formatter: function(value) {
                                    return numberFormat(value);
                                }
                            }
                        }
                    }
                );

            apiActivityByDayChart.render();

        }


        /*
        |--------------------------------------------------------------------------
        | API ENDPOINT
        |--------------------------------------------------------------------------
        */

        const apiEndpointEl =
            document.getElementById(
                'apiEndpointChart'
            );


        if (apiEndpointEl) {

            apiEndpointChart =
                new ApexCharts(
                    apiEndpointEl, {
                        chart: {
                            type: 'donut',
                            height: 360
                        },

                        series: @json($apiEndpointUsage->pluck('total')->values()),

                        labels: @json($apiEndpointUsage->pluck('url')->values()),

                        legend: {
                            position: 'bottom'
                        }
                    }
                );

            apiEndpointChart.render();

        }

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE WEB ACTIVITY CHART
    |--------------------------------------------------------------------------
    */

    function updateActivityByDayChart(data) {

        if (!activityByDayChart) {
            return;
        }


        activityByDayChart.updateOptions({

            xaxis: {

                categories: (data ?? [])
                    .map(item => item.label)

            }

        });


        activityByDayChart.updateSeries([

            {

                name: 'Activity',

                data: (data ?? [])
                    .map(item => Number(item.total))

            }

        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE WEB MODULE CHART
    |--------------------------------------------------------------------------
    */

    function updateActivityByModuleChart(data) {

        if (!activityByModuleChart) {
            return;
        }


        activityByModuleChart.updateOptions({

            labels: (data ?? [])
                .map(item => item.name)

        });


        activityByModuleChart.updateSeries(

            (data ?? [])
            .map(item => Number(item.total))

        );


        /*
        |--------------------------------------------------------------------------
        | Update module list
        |--------------------------------------------------------------------------
        */

        const container =
            document.getElementById(
                'activityByModuleList'
            );


        if (!container) {
            return;
        }


        if (!data || !data.length) {

            container.innerHTML = '';

            return;

        }


        container.innerHTML =
            data.map(module => `

            <div class="d-flex justify-content-between mb-1">

                <span>
                    ${escapeHtml(module.name)}
                </span>

                <span class="fw-bold">
                    ${numberFormat(module.total)}
                </span>

            </div>

        `).join('');

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE API ACTIVITY CHART
    |--------------------------------------------------------------------------
    */

    function updateApiActivityByDayChart(data) {

        if (!apiActivityByDayChart) {
            return;
        }


        apiActivityByDayChart.updateOptions({

            xaxis: {

                categories: (data ?? [])
                    .map(item => item.label)

            }

        });


        apiActivityByDayChart.updateSeries([

            {

                name: 'API Request',

                data: (data ?? [])
                    .map(item => Number(item.total))

            }

        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE API ENDPOINT CHART
    |--------------------------------------------------------------------------
    */

    function updateApiEndpointChart(data) {

        if (!apiEndpointChart) {
            return;
        }


        apiEndpointChart.updateOptions({

            labels: (data ?? [])
                .map(item => item.url)

        });


        apiEndpointChart.updateSeries(

            (data ?? [])
            .map(item => Number(item.total))

        );

    }
</script>

@endsection
