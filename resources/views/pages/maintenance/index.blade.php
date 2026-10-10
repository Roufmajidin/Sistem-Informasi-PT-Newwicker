@extends('master.master')

@section('content')

<style>
    /* =========================================================
       MAINTENANCE PAGE
       ========================================================= */

    .maintenance-page {
        min-height: calc(100vh - 80px);

        display: flex;
        align-items: center;
        justify-content: center;

        padding: 30px 20px;

        background: #f8fafc;
    }

    .maintenance-inner {
        width: min(720px, 100%);

        text-align: center;
    }


    /* =========================================================
       403
       ========================================================= */

    .maintenance-code {
        margin: 0;

        font-size: clamp(130px, 19vw, 230px);

        line-height: .8;

        font-weight: 850;

        letter-spacing: -.085em;

        color: #e5e7eb;

        user-select: none;
    }


    /* =========================================================
       TITLE
       ========================================================= */

    .maintenance-title {
        position: relative;

        margin: -8px 0 0;

        color: #172033;

        font-size: 26px;

        line-height: 1.25;

        font-weight: 750;

        letter-spacing: -.035em;
    }


    /* =========================================================
       DIVIDER
       ========================================================= */

    .maintenance-divider {
        width: 55px;

        height: 2px;

        margin: 20px auto;

        border-radius: 20px;

        background: #2563eb;
    }


    /* =========================================================
       DESCRIPTION
       ========================================================= */

    .maintenance-description {
        max-width: 540px;

        margin: 0 auto;

        color: #667085;

        font-size: 12px;

        line-height: 1.75;
    }


    /* =========================================================
       STATUS
       ========================================================= */

    .maintenance-status {
        display: inline-flex;

        align-items: center;

        gap: 7px;

        margin-top: 20px;

        padding: 7px 13px;

        border: 1px solid #bfdbfe;

        border-radius: 999px;

        background: #eff6ff;

        color: #2563eb;

        font-size: 10px;

        font-weight: 700;

        letter-spacing: .01em;
    }

    .maintenance-dot {
        width: 7px;

        height: 7px;

        flex-shrink: 0;

        border-radius: 50%;

        background: #2563eb;

        animation: maintenancePulse 1.8s ease-in-out infinite;
    }


    /* =========================================================
       FOOTER
       ========================================================= */

    .maintenance-footer {
        margin-top: 32px;

        color: #98a2b3;

        font-size: 9px;

        letter-spacing: .01em;
    }


    /* =========================================================
       ANIMATION
       ========================================================= */

    @keyframes maintenancePulse {

        0% {
            opacity: 1;
            transform: scale(1);
        }

        50% {
            opacity: .35;
            transform: scale(.8);
        }

        100% {
            opacity: 1;
            transform: scale(1);
        }

    }


    /* =========================================================
       RESPONSIVE
       ========================================================= */

    @media (max-width: 768px) {

        .maintenance-page {
            min-height: calc(100vh - 60px);

            padding: 25px 16px;
        }

        .maintenance-code {
            font-size: clamp(105px, 28vw, 170px);
        }

        .maintenance-title {
            font-size: 22px;
        }

        .maintenance-description {
            max-width: 470px;

            font-size: 11px;

            line-height: 1.7;
        }

        .maintenance-footer {
            margin-top: 25px;
        }

    }


    @media (max-width: 480px) {

        .maintenance-page {
            padding: 20px 15px;
        }

        .maintenance-code {
            font-size: 110px;
        }

        .maintenance-title {
            font-size: 20px;
        }

        .maintenance-description {
            font-size: 10.5px;
        }

        .maintenance-status {
            font-size: 9px;

            padding: 6px 11px;
        }

    }
</style>


<div class="maintenance-page">

    <div class="maintenance-inner">


        {{-- =====================================================
             ERROR CODE
             ===================================================== --}}

        <div class="maintenance-code">
            403
        </div>


        {{-- =====================================================
             TITLE
             ===================================================== --}}

        <h1 class="maintenance-title">
            Page Under Maintenance
        </h1>


        {{-- =====================================================
             DIVIDER
             ===================================================== --}}

        <div class="maintenance-divider"></div>


        {{-- =====================================================
             DESCRIPTION
             ===================================================== --}}

        <p class="maintenance-description">

            This page is currently unavailable while we prepare
            it for launch. We are working to ensure everything
            is properly configured and ready for your experience.

        </p>


        {{-- =====================================================
             STATUS
             ===================================================== --}}

        <div class="maintenance-status">

            <span class="maintenance-dot"></span>

            Coming Soon

        </div>


        {{-- =====================================================
             FOOTER
             ===================================================== --}}

        <div class="maintenance-footer">

            © {{ date('Y') }} NewWicker. All rights reserved.

        </div>


    </div>

</div>

@endsection