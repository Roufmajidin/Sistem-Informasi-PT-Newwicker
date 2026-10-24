    @extends('master.master')
    @section('title', 'monitorings SPK')
    @section('content')

        {{-- Critical mobile CSS: loaded before desktop table markup to prevent FOUC. --}}
        <style>
            @media screen and (max-width: 767.98px) {
                .inventor-card .inventor-wrapper {
                    display: none !important;
                }

                .inventor-card .spk-mobile-list {
                    display: grid !important;
                }

                #spkMobilePageLoader {
                    position: fixed;
                    inset: 0;
                    z-index: 2147483000;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: #f5f8fc;
                    padding: 24px;
                }

                #spkMobilePageLoader .spk-loading-box {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 12px;
                    color: #243b60;
                    font: 600 13px/1.5 system-ui, sans-serif;
                }

                #spkMobilePageLoader .spk-loading-spinner {
                    width: 32px;
                    height: 32px;
                    border: 3px solid #dce7f7;
                    border-top-color: #2563eb;
                    border-radius: 50%;
                    animation: spkLoadingSpin .7s linear infinite;
                }

                @keyframes spkLoadingSpin {
                    to {
                        transform: rotate(360deg);
                    }
                }

                #spkMobilePageLoader.spk-loader-hidden {
                    display: none !important;
                }
            }

            @media screen and (min-width: 768px) {
                #spkMobilePageLoader {
                    display: none !important;
                }
            }
        </style>
        <div id="spkMobilePageLoader" role="status" aria-live="polite">
            <div class="spk-loading-box">
                <span class="spk-loading-spinner" aria-hidden="true"></span>
                <span>Memuat Monitoring SPK...</span>
            </div>
        </div>
        <script>
            (function() {
                var loaderId = 'spkMobilePageLoader';

                function loader() {
                    return document.getElementById(loaderId);
                }

                function hide() {
                    var el = loader();
                    if (el) el.classList.add('spk-loader-hidden');
                }

                function show() {
                    var el = loader();
                    if (el) el.classList.remove('spk-loader-hidden');
                }
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', hide, {
                        once: true
                    });
                } else {
                    hide();
                }
                window.addEventListener('pageshow', hide);
                // Prevent a stuck overlay if another asset fails to load.
                window.setTimeout(hide, 4500);
                document.addEventListener('click', function(event) {
                    var a = event.target.closest('a[href]');
                    if (!a || event.defaultPrevented || event.ctrlKey || event.metaKey || event.shiftKey || event
                        .altKey || a.target === '_blank') return;
                    var path;
                    try {
                        path = new URL(a.href, window.location.href).pathname;
                    } catch (_) {
                        return;
                    }
                    if (window.matchMedia('(max-width: 767.98px)').matches &&
                        /^\/produksi\/inventor(?:\/|$)/i.test(path) &&
                        path !== window.location.pathname) show();
                });
            })();
        </script>

        @include('pages.management.style')

        @php
            $hideHarga = in_array(auth()->user()->email, ['john@gmail.com', 'aji@gmail.com']);
        @endphp
        <div class="container-fluid py-4">
            <div class="card inventor-card">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="mb-0 fw-bold mr-4">
                                Monitoring SPK
                            </h4>

                            <a href="{{ route('inventor.arsip') }}" class="btn btn-outline-secondary btn-sm ms-2"
                                title="Arsip SPK">
                                Arsip
                            </a>
                        </div>

                        {{-- RIGHT --}}

                        <div class="d-flex gap-2">
                            @php
                                $suppliers = array_unique(array_column($spks, 'supplier'));
                            @endphp

                            <input type="text" id="searchSub" list="supplierList" class="form-control"
                                placeholder="Cari Supplier...">

                            <datalist id="supplierList">
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier }}">
                                @endforeach
                            </datalist>
                            <select id="filterStatus" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="draft">Belum Diajukan</option>
                                <option value="diajukan">Sudah Diajukan</option>
                            </select>
                            <input type="text" id="searchSpk" class="form-control" placeholder="Cari No PO / No SPK..."
                                style="width:300px">
                            <select id="filterSpkType" class="form-control">
                                <option value="">Semua SPK</option>
                                <option value="NW">SPK Produksi</option>
                                <option value="NWS">SPK Sampel</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive inventor-wrapper">
                        <table class="table inventor-table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th style="background:#1F3B7A" width="60"> No </th>
                                    <th style="background:#1F3B7A"> No SPK </th>
                                    <th style="width:150px"> Supplier </th>
                                    <th> Kategori</th>
                                    <th> PO </th>
                                    <th> Tgl Terima </th>
                                    <th style="width:20px"> Tenggat </th>
                                    <th> Deadline </th>
                                    {{-- <th>Status</th> --}}
                                    @if (!$hideHarga)
                                        <th>Vivi </th>
                                        <th>Didin </th>
                                        <th>Mr. Stanley </th>
                                    @endif

                                    <th width="120">Total Item
                                    </th>
                                    <th width="120">Action </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($spks as $spk)
                                    <tr class="spk-row" data-id="{{ $spk['id'] }}" data-status="{{ $spk['status'] }}">
                                        <td>
                                            {{ $loop->iteration }}
                                        </td>
                                        {{-- CLICK FOR INPUT --}}
                                        <td class="{{ $hideHarga ? '' : 'inventor-row' }} spk-col"
                                            data-id="{{ $spk['id'] }}">
                                            <div class="fw-bold text-primary">
                                                {{ $spk['no_spk'] }}
                                            </div>
                                        </td>
                                        <td style="font-size:12px" class="inventor-row" data-id="{{ $spk['id'] }}">

                                            <div class="fw-bold">
                                                {{ $spk['supplier'] }}
                                            </div>

                                            @foreach ($spk['items'] as $item)
                                                <div class="item-spk"
                                                    title="{{ $item['nama'] }} ({{ $item['qty'] }} {{ $item['satuan'] ?? 'pcs' }})">
                                                    • {{ $item['nama'] }}
                                                    ({{ $item['qty'] }} {{ $item['satuan'] ?? 'pcs' }})
                                                </div>
                                            @endforeach

                                        </td>
                                        <td class="inventor-row" data-id="{{ $spk['id'] }}">
                                            <span class="badge bg-info">
                                                {{ strtoupper($spk['kategori']) }}
                                            </span>
                                        </td>
                                        <td class="inventor-row po-col" data-id="{{ $spk['id'] }}">
                                            {{ $spk['no_po'] }}
                                        </td>
                                        <td class="inventor-row" data-id="{{ $spk['id'] }}">
                                            {{ $spk['tgl_terima'] }}
                                        </td>
                                        <td class="inventor-row" data-id="{{ $spk['id'] }}">
                                            {{ $spk['tgl_selesai'] }}
                                        </td>
                                        <td style="width:20px">
                                            <div class="deadline-card">

                                                <div class="deadline-bar">

                                                    <div class="deadline-fill deadline-{{ $spk['deadline_color'] }}"
                                                        style="width: {{ $spk['deadline_percent'] }}%">
                                                    </div>

                                                </div>

                                                <div class="deadline-footer">
                                                    <span>
                                                        {{ $spk['deadline_text'] }}
                                                    </span>

                                                    <span>
                                                        <!-- {{ $spk['deadline_percent'] }}% -->
                                                    </span>
                                                </div>

                                            </div>
                                        </td>
                                        {{-- <td>"{{ $spk['status'] }}"</td> --}}
                                        @if (!$hideHarga)
                                            {{-- VIVI --}}
                                            <td class="text-center">

                                                @if (!empty($spk['signature']['checked_at']))
                                                    <a href="{{ url('/spk/edit/' . $spk['id']) }}">
                                                        <span class="badge bg-success">
                                                            <i class="fa fa-check"></i>
                                                            Signed
                                                        </span>
                                                    </a>

                                                    <br>

                                                    <small class="text-muted">
                                                        {{ $spk['signature']['checked_by'] }}
                                                    </small>
                                                @elseif($spk['status'] == 'diajukan')
                                                    <a href="{{ url('/spk/edit/' . $spk['id']) }}">
                                                        <span class="badge bg-warning">
                                                            Pending
                                                        </span>
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif

                                            </td>

                                            {{-- DIDIN --}}
                                            <td class="text-center">

                                                @if (!empty($spk['signature']['checked_at_2']))
                                                    <a href="{{ url('/spk/edit/' . $spk['id']) }}">
                                                        <span class="badge bg-success">
                                                            <i class="fa fa-check"></i>
                                                            Signed
                                                        </span>
                                                    </a>

                                                    <br>

                                                    <small class="text-muted">
                                                        {{ $spk['signature']['checked_by_2'] }}
                                                    </small>
                                                @elseif($spk['status'] == 'diajukan')
                                                    <a href="{{ url('/spk/edit/' . $spk['id']) }}">
                                                        <span class="badge bg-warning">
                                                            Pending
                                                        </span>
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif

                                            </td>
                                            <td class="text-center">
                                                @if (!empty($spk['signature']['approved_at']))
                                                    <a href="{{ url('/spk/edit/' . $spk['id']) }}">
                                                        <span class="badge bg-success">
                                                            <i class="fa fa-check"></i>
                                                            Signed
                                                        </span>
                                                    </a>

                                                    <br>

                                                    <small class="text-muted approved-by"
                                                        title="{{ $spk['signature']['approved_by'] }}">
                                                        {{ $spk['signature']['approved_by'] }}
                                                    </small>
                                                @elseif($spk['status'] == 'diajukan' && auth()->id() == 191)
                                                    <button type="button" class="btn btn-warning btn-sm btn-approve-spk"
                                                        data-id="{{ $spk['id'] }}">
                                                        Pending Approval
                                                    </button>
                                                @elseif($spk['status'] == 'diajukan')
                                                    <a href="{{ url('/spk/edit/' . $spk['id']) }}">
                                                        <span class="badge bg-warning">
                                                            Pending
                                                        </span>
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endif
                                        <td class="text-center inventor-row" data-id="{{ $spk['id'] }}">
                                            {{ count($spk['items']) }}
                                        </td>
                                        {{-- DETAIL BUTTON --}}
                                        <td>

                                            {{-- Selain Admin Produksi dan bukan John/Aji --}}
                                            @php
                                                $userEmail = strtolower(trim((string) auth()->user()->email));
                                                $userRole = strtolower(trim((string) auth()->user()->role));
                                                $spkStatus = strtolower(trim((string) ($spk['status'] ?? '')));
                                                $isNur = $userEmail === 'nurk@gmail.com';

                                                // Nur boleh mengajukan SPK draft.
                                                // User lain tetap mengikuti aturan lama.
                                                $canAjukan =
                                                    $isNur ||
                                                    ($userRole !== 'admin produksi' &&
                                                        !in_array(
                                                            $userEmail,
                                                            ['john@gmail.com', 'aji@gmail.com'],
                                                            true,
                                                        ));
                                            @endphp

                                            @if ($canAjukan && $spkStatus === 'draft')
                                                <button type="button" class="btn btn-success btn-sm btn-ajukan"
                                                    data-id="{{ $spk['id'] }}" data-spk="{{ $spk['no_spk'] }}">
                                                    Ajukan
                                                </button>
                                            @endif

                                            {{-- Detail boleh untuk semua selain Admin Produksi, termasuk John & Aji --}}
                                            @if (auth()->user()->role != 'admin produksi' || in_array(auth()->user()->email, ['john@gmail.com', 'aji@gmail.com']))
                                                <button type="button" class="btn btn-primary btn-sm btn-detail"
                                                    data-id="{{ $spk['id'] }}">
                                                    Detail
                                                </button>
                                            @endif

                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-5">
                                            Tidak ada data SPK
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- MOBILE ONLY: SPK cards. Desktop table above remains unchanged. --}}
                    <div class="spk-mobile-list" id="spkMobileList">
                        @forelse($spks as $spk)
                            <article class="spk-mobile-card" data-id="{{ $spk['id'] }}"
                                data-status="{{ $spk['status'] }}"
                                data-supplier="{{ strtolower($spk['supplier'] ?? '') }}"
                                data-search="{{ strtolower(($spk['no_spk'] ?? '') . ' ' . ($spk['no_po'] ?? '') . ' ' . ($spk['supplier'] ?? '')) }}"
                                data-spktype="{{ str_contains(strtoupper($spk['no_spk'] ?? ''), 'NWS') ? 'NWS' : 'NW' }}">
                                <div class="spk-mobile-card-top">
                                    <div class="spk-mobile-card-title">{{ $spk['no_spk'] }}</div>
                                </div>
                                <div class="spk-mobile-supplier">{{ $spk['supplier'] }}</div>
                                <div class="spk-mobile-items">
                                    {{ collect($spk['items'])->take(2)->pluck('nama')->join(' · ') }}</div>
                                <div class="spk-mobile-progress">
                                    <div class="spk-mobile-progress-label">
                                        <span>{{ strtoupper($spk['kategori'] ?? '-') }}</span><strong>{{ $spk['deadline_text'] }}</strong>
                                    </div>
                                    <div class="spk-mobile-track">
                                        <div class="deadline-{{ $spk['deadline_color'] }}"
                                            style="width:{{ max(3, min(100, (float) $spk['deadline_percent'])) }}%"></div>
                                    </div>
                                </div>
                                <div class="spk-mobile-metadata">
                                    <span><i class="fa fa-file-text-o"></i> {{ $spk['no_po'] ?? '-' }}</span>
                                    <span><i class="fa fa-calendar"></i> {{ $spk['tgl_terima'] ?? '-' }}</span>
                                    <span><i class="fa fa-flag"></i> {{ $spk['tgl_selesai'] ?? '-' }}</span>
                                    <span class="spk-mobile-qty">{{ count($spk['items']) }} item</span>
                                </div>
                                <div class="spk-mobile-actions">
                                    @if (auth()->user()->role != 'admin produksi' || in_array(auth()->user()->email, ['john@gmail.com', 'aji@gmail.com']))
                                        <button type="button" class="spk-mobile-detail"
                                            data-id="{{ $spk['id'] }}"><i class="fa fa-list-alt"></i> Lihat
                                            Detail</button>
                                    @endif
                                    @php
                                        $mEmail = strtolower(trim((string) auth()->user()->email));
                                        $mRole = strtolower(trim((string) auth()->user()->role));
                                        $mCanAjukan =
                                            $mEmail === 'nurk@gmail.com' ||
                                            ($mRole !== 'admin produksi' &&
                                                !in_array($mEmail, ['john@gmail.com', 'aji@gmail.com'], true));
                                    @endphp
                                    @if ($mCanAjukan && strtolower($spk['status'] ?? '') === 'draft')
                                        <button type="button" class="spk-mobile-ajukan"
                                            data-id="{{ $spk['id'] }}"><i class="fa fa-paper-plane"></i>
                                            Ajukan</button>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="spk-mobile-empty">Tidak ada data SPK</div>
                        @endforelse
                        <div class="spk-mobile-empty spk-mobile-filter-empty" style="display:none">Tidak ada SPK sesuai
                            filter</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ====================================================== --}}
        {{-- MODAL DETAIL inventor --}}
        {{-- ====================================================== --}}
        <div class="modal fade" id="inventorDetailModal" tabindex="-1">
            <div class="modal-dialog modal-full-custom modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-dark text-white">
                        <h4 class="mb-0">
                            DETAIL SPK
                        </h4>
                        <button type="button" class="btn-close btn-close-secondary" data-bs-dismiss="modal"><i
                                class="fa fa-close"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <nav class="spk-mobile-tabs" aria-label="Navigasi detail SPK">
                            <button type="button" class="active" data-panel="info">Info</button>
                            <button type="button" data-panel="items">Items</button>
                            <button type="button" data-panel="timeline">Inventory</button>
                            @if (!$hideHarga)
                                <button type="button" data-panel="payment">Payment</button>
                            @endif
                        </nav>
                        @if (!$hideHarga)
                            <div class="spk-mobile-payment-notice" id="spkMobilePaymentNotice" role="status"
                                aria-live="polite" hidden>
                                <div class="spk-mobile-payment-icon"><i class="fa fa-lock" aria-hidden="true"></i></div>
                                <strong>Payment is not available on mobile</strong>
                                <p>You are using a mobile phone. Payment information can only be viewed on desktop.</p>
                                <small>Silakan gunakan komputer atau laptop untuk melihat informasi payment.</small>
                            </div>
                        @endif
                        <div class="row">
                            {{-- LEFT --}}
                            <div class="col-md-4">
                                <div class="card mb-3 border-0 shadow-sm">
                                    <div class="card-header bg-primary text-white">
                                        INFORMASI SPK
                                    </div>
                                    <div class="card-body" id="spkInfoArea">
                                    </div>
                                </div>
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-secondary text-white">
                                        <p style="color:black"> LIST ITEM</p>
                                    </div>
                                    <div class="card-body">
                                        <div id="itemArea">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- RIGHT --}}
                            <div class="col-md-8">
                                {{-- TIMELINE --}}
                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-header bg-warning fw-bold">
                                        INVENTORY TIMELINE
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive timeline-wrapper">
                                            <table class="table table-bordered mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Tanggal</th>
                                                        <th>Items</th>
                                                        <th>Supplier</th>
                                                        <th>Type</th>
                                                        <th>Qty</th>
                                                        <th>Remark</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="timelineArea">
                                                </tbody>

                                                <tfoot>
                                                    <tr class="table-warning fw-bold">
                                                        <td colspan="5" class="text-end">
                                                            Total IN
                                                        </td>
                                                        <td id="totalTimelineIn">0</td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- bahan baku  -->
                                @if (!$hideHarga)
                                    {{-- <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-success text-white fw-bold">
                                            LIST BAHAN BAKU PENGAMBILAN
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-bordered mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>No</th>
                                                            <th>Tanggal</th>
                                                            <th>Tipe</th>
                                                            <th>Bahan</th>
                                                            <th>potong bahan</th>
                                                            <th>harga inventory</th>
                                                            <th>harga (adjusment)</th>
                                                            <th>Total</th>
                                                            <th>Keterangan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="bahanbakuArea">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div> --}}
                                @endif
                                {{-- PAYMENT --}}
                                @if (!$hideHarga)
                                    <div class="card border-0 shadow-sm">
                                        <div
                                            class="card-header bg-success text-white fw-bold d-flex justify-content-between align-items-center">
                                            LIST PAYMENT
                                            <a id="goToSpkLink" href="#" target="_blank" rel="noopener noreferrer"
                                                class="btn btn-sm text-white fw-bold" title="Buka SPK di tab baru">
                                                <i class="fa fa-external-link"></i> GO TO SPK
                                            </a>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-bordered mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>No</th>
                                                            <th>Tanggal</th>
                                                            <th>Type</th>
                                                            <th>Nilai</th>
                                                            <th>Saldo</th>
                                                            <th>Keterangan</th>
                                                            <th>Harga</th>
                                                            <th>Total</th>
                                                            <th>stst request</th>
                                                            <th>Adjustment Finance</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="paymentArea">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            /* Mobile-only layout: no desktop table or existing event logic modified. */
            .spk-mobile-list,
            .spk-mobile-tabs,
            .spk-mobile-payment-notice {
                display: none
            }

            @media(max-width:767.98px) {
                .container-fluid.py-4 {
                    padding: 10px 9px !important
                }

                .inventor-card {
                    border-radius: 13px !important;
                    box-shadow: none !important;
                    overflow: visible !important
                }

                .inventor-card>.card-header {
                    padding: 13px 12px !important
                }

                .inventor-card>.card-header>.d-flex {
                    display: block !important
                }

                .inventor-card .card-header h4 {
                    font-size: 19px !important;
                    margin-right: 8px !important
                }

                .inventor-card>.card-header>.d-flex>.d-flex:last-child {
                    display: grid !important;
                    grid-template-columns: 1fr 1fr;
                    gap: 8px !important;
                    margin-top: 12px
                }

                #searchSub,
                #searchSpk {
                    grid-column: 1/-1;
                    width: 100% !important
                }

                #filterStatus,
                #filterSpkType {
                    width: 100% !important;
                    min-width: 0
                }

                .inventor-card .card-header .form-control {
                    height: 37px;
                    font-size: 12px;
                    border-radius: 9px
                }

                .inventor-card>.card-body {
                    padding: 8px !important;
                    background: #f5f8fc
                }

                .inventor-card .inventor-wrapper {
                    display: none !important
                }

                .spk-mobile-list {
                    display: grid;
                    gap: 10px
                }

                .spk-mobile-card {
                    cursor: pointer;
                    background: #fff;
                    border: 1px solid #e5ebf3;
                    border-radius: 12px;
                    padding: 12px;
                    box-shadow: 0 2px 8px rgba(20, 46, 83, .04);
                    min-width: 0
                }

                .spk-mobile-card-top {
                    display: flex;
                    align-items: flex-start;
                    justify-content: space-between;
                    gap: 8px
                }

                .spk-mobile-card-title {
                    font-size: 13px;
                    font-weight: 800;
                    color: #1554c5;
                    line-height: 1.35;
                    overflow-wrap: anywhere
                }

                .spk-mobile-more {
                    border: 0;
                    background: #f2f6fc;
                    color: #234273;
                    border-radius: 8px;
                    width: 30px;
                    height: 28px;
                    flex: none
                }

                .spk-mobile-supplier {
                    font-size: 12px;
                    font-weight: 750;
                    color: #1c2e49;
                    margin-top: 5px
                }

                .spk-mobile-items {
                    font-size: 11px;
                    color: #64748b;
                    margin-top: 2px;
                    overflow: hidden;
                    white-space: nowrap;
                    text-overflow: ellipsis
                }

                .spk-mobile-progress {
                    margin-top: 11px
                }

                .spk-mobile-progress-label {
                    display: flex;
                    justify-content: space-between;
                    gap: 8px;
                    font-size: 10px;
                    color: #60738e;
                    margin-bottom: 5px
                }

                .spk-mobile-progress-label strong {
                    color: #324c70;
                    font-weight: 650;
                    text-align: right
                }

                .spk-mobile-track {
                    height: 7px;
                    border-radius: 12px;
                    background: #edf2f8;
                    overflow: hidden
                }

                .spk-mobile-track>div {
                    height: 100%;
                    border-radius: 12px
                }

                .spk-mobile-metadata {
                    display: flex;
                    align-items: center;
                    gap: 9px;
                    flex-wrap: wrap;
                    margin-top: 11px;
                    font-size: 10px;
                    color: #64748b
                }

                .spk-mobile-metadata i {
                    color: #2865c8;
                    margin-right: 2px
                }

                .spk-mobile-qty {
                    margin-left: auto;
                    padding: 4px 7px;
                    background: #e8f8ef;
                    border-radius: 6px;
                    color: #188552;
                    font-weight: 700
                }

                .spk-mobile-actions {
                    display: flex;
                    gap: 7px;
                    margin-top: 11px;
                    border-top: 1px solid #eef1f6;
                    padding-top: 10px;
                    flex-wrap: wrap
                }

                .spk-mobile-actions>a,
                .spk-mobile-actions>button {
                    border: 1px solid #d9e5f6;
                    background: #f7faff;
                    color: #2059a7;
                    border-radius: 7px;
                    padding: 7px 10px;
                    font-size: 11px;
                    font-weight: 700;
                    text-decoration: none
                }

                .spk-mobile-actions>.spk-mobile-ajukan {
                    background: #eaf8ef;
                    color: #187a42;
                    border-color: #bfe8cd
                }

                .spk-mobile-empty {
                    text-align: center;
                    color: #64748b;
                    padding: 28px 10px;
                    font-size: 12px
                }

                #inventorDetailModal .modal-dialog {
                    width: 100% !important;
                    max-width: 100% !important;
                    margin: 0 !important;
                    height: 100dvh !important
                }

                #inventorDetailModal .modal-content {
                    height: 100dvh !important;
                    min-height: 0 !important;
                    border-radius: 0 !important
                }

                #inventorDetailModal .modal-header {
                    padding: 12px 15px !important;
                    background: #fff !important;
                    color: #142b50 !important;
                    border-bottom: 1px solid #e6ebf2
                }

                #inventorDetailModal .modal-header h4 {
                    font-size: 17px !important;
                    font-weight: 750
                }

                #inventorDetailModal .modal-body {
                    padding: 11px !important;
                    overflow-x: hidden !important;
                    background: #f7f9fc
                }

                .spk-mobile-tabs {
                    display: flex;
                    position: sticky;
                    top: -11px;
                    z-index: 15;
                    overflow-x: auto;
                    background: #fff;
                    border-bottom: 1px solid #e2e8f0;
                    margin: -11px -11px 12px;
                    padding: 0 11px;
                    gap: 4px
                }

                .spk-mobile-tabs button {
                    background: none;
                    border: 0;
                    border-bottom: 2px solid transparent;
                    padding: 12px 10px;
                    font-size: 12px;
                    font-weight: 700;
                    color: #66758c;
                    white-space: nowrap
                }

                .spk-mobile-tabs button.active {
                    color: #175cce;
                    border-bottom-color: #175cce
                }

                #inventorDetailModal .spk-mobile-payment-notice:not([hidden]) {
                    display: flex !important;
                    flex-direction: column;
                    align-items: center;
                    text-align: center;
                    gap: 10px;
                    margin: 15px 0;
                    padding: 35px 20px;
                    border: 1px solid #e2e8f0;
                    border-radius: 15px;
                    background: #fff;
                    color: #1e293b
                }

                .spk-mobile-payment-icon {
                    display: grid;
                    place-items: center;
                    width: 56px;
                    height: 56px;
                    border-radius: 50%;
                    background: #eff6ff;
                    color: #2563eb;
                    font-size: 24px
                }

                .spk-mobile-payment-notice strong {
                    font-size: 15px
                }

                .spk-mobile-payment-notice p {
                    margin: 0;
                    color: #475569;
                    font-size: 13px;
                    line-height: 1.6
                }

                .spk-mobile-payment-notice small {
                    color: #64748b;
                    font-size: 11px
                }

                #inventorDetailModal.spk-mobile-payment-locked #goToSpkLink {
                    display: none !important
                }

                #inventorDetailModal .modal-body>.row {
                    display: block !important;
                    margin: 0 !important
                }

                #inventorDetailModal .modal-body>.row>.col-md-4,
                #inventorDetailModal .modal-body>.row>.col-md-8 {
                    width: 100% !important;
                    max-width: 100% !important;
                    padding: 0 !important
                }

                #inventorDetailModal .modal-body>.row .card {
                    margin-bottom: 0 !important;
                    border: 1px solid #e5ebf3 !important;
                    border-radius: 11px !important;
                    overflow: hidden;
                    box-shadow: none !important
                }

                #inventorDetailModal .modal-body>.row .card-header {
                    background: #fff !important;
                    color: #203b65 !important;
                    font-size: 12px !important;
                    font-weight: 750 !important;
                    padding: 11px 12px !important;
                    border-bottom: 1px solid #edf1f6
                }

                #inventorDetailModal .modal-body>.row .card-body {
                    padding: 10px !important
                }

                #inventorDetailModal .modal-body>.row .card-body.p-0 {
                    padding: 0 !important
                }

                #inventorDetailModal .modal-body>.row .card:not(.spk-mobile-panel-active) {
                    display: none !important
                }

                #inventorDetailModal .modal-body>.row .card.spk-mobile-panel-active {
                    display: block !important
                }

                #inventorDetailModal .table-responsive {
                    overflow-x: auto !important;
                    max-width: 100%
                }

                #inventorDetailModal table {
                    font-size: 11px !important
                }

                #inventorDetailModal table th,
                #inventorDetailModal table td {
                    padding: 8px 7px !important;
                    white-space: normal !important;
                    vertical-align: middle !important
                }

                #inventorDetailModal .timeline-wrapper {
                    max-height: 65dvh !important
                }

                #inventorDetailModal #spkInfoArea table {
                    width: 100% !important
                }

                #inventorDetailModal #spkInfoArea table td:first-child {
                    width: 110px;
                    color: #64748b
                }
            }
        </style>
    @endsection

    <style>
        /* Menu yang muncul saat klik kanan baris SPK */
        .spk-row-menu {
            position: fixed;
            z-index: 99999;
            min-width: 150px;
            padding: 5px;
            background: #fff;
            border: 1px solid #d9dee5;
            border-radius: 7px;
            box-shadow: 0 8px 22px rgba(0, 0, 0, .18);
        }

        .spk-row-menu-item {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px 11px;
            color: #263445;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            border-radius: 5px;
            white-space: nowrap;
        }


        .spk-row-menu-item {
            width: 100%;
            border: 0;
            background: transparent;
            cursor: pointer;
            text-align: left;
        }

        .spk-row-menu-item:hover {
            background: #f1f5f9;
            color: #0d6efd;
            text-decoration: none;
        }

        .spk-row-menu-item i {
            width: 16px;
            text-align: center;
        }

        .inventor-table tbody tr.active-row>td {
            background-color: #fff3cd !important;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    @push('scripts')
        <script>
            const hideHarga = @json($hideHarga);
            let tr = $(this).closest('tr');

            /*
                                |--------------------------------------------------------------------------
                                | OPEN INPUT MODAL
                                |--------------------------------------------------------------------------
                                */

            function validateQty() {
                let totals = {};
                $('#inventorTableBody tr').each(function() {
                    let row = $(this);
                    let itemId = row.find('select[name="detail_po_id[]"]').val();
                    let qty = parseInt(
                        row.find('.qty-input').val()
                    ) || 0;
                    if (!totals[itemId]) {
                        totals[itemId] = 0;
                    }
                    totals[itemId] += qty;
                });
                let hasError = false;
                $('#inventorTableBody tr').each(function() {
                    let row = $(this);
                    let select = row.find('select[name="detail_po_id[]"]');
                    let selectedOption =
                        select.find('option:selected');
                    let itemId =
                        select.val();
                    let maxQty =
                        parseInt(
                            selectedOption.data('maxqty')
                        ) || 0;
                    let totalQty =
                        totals[itemId] || 0;
                    let input =
                        row.find('.qty-input');
                    let warning =
                        row.find('.qty-warning');
                    if (totalQty > maxQty) {
                        hasError = true;
                        input.addClass('is-invalid');
                        warning
                            .removeClass('d-none')
                            .html(
                                `Total qty ${totalQty} melebihi batas ${maxQty}`
                            );
                    } else {
                        input.removeClass('is-invalid');
                        warning.addClass('d-none');
                    }
                });
                $('button[type="submit"]')
                    .prop('disabled', hasError);
            }
            /*
            |--------------------------------------------------------------------------
            | VALIDATE EVENT
            |--------------------------------------------------------------------------
            */
            $(document).on(
                'keyup change',
                '.qty-input, select[name="detail_po_id[]"]',
                function() {
                    validateQty();
                }
            );
            /*
            |--------------------------------------------------------------------------
            | ADD ROW BUTTON
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '#btnAddRow', function() {
                addInventorRow();
            });
            /*
            |--------------------------------------------------------------------------
            | REMOVE ROW
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.btn-remove-row', function() {
                let btn = $(this);
                let row = btn.closest('tr');
                let timelineId = btn.data('id');
                // alert(timelineId);
                /*
                |--------------------------------------------------------------------------
                | DELETE DATABASE
                |--------------------------------------------------------------------------
                */
                if (timelineId) {
                    Swal.fire({
                        title: 'Hapus data?',
                        text: 'Data inventory akan dihapus',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya Hapus'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `/inventor/delete/${timelineId}`,
                                type: 'DELETE',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    row.remove();
                                    validateQty();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: res.message
                                    });
                                },
                                error: function() {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops',
                                        text: 'Gagal hapus data'
                                    });
                                }
                            });
                        }
                    });
                } else {
                    /*
                    |--------------------------------------------------------------------------
                    | DELETE ROW BARU (BELUM TERSIMPAN)
                    |--------------------------------------------------------------------------
                    */
                    row.remove();
                    validateQty();
                }
            });
            /*
            |--------------------------------------------------------------------------
            | SUBMIT
            |--------------------------------------------------------------------------
            */

            /*
            |--------------------------------------------------------------------------
            | DETAIL BUTTON
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.btn-detail', function(e) {
                e.stopPropagation();
                let spkId = $(this).data('id');
                if (!spkId) return;
                $('#goToSpkLink').attr('href', `/spk/edit/${encodeURIComponent(spkId)}`);
                $.get(`/inventor/spk/${spkId}`, function(res) {
                    /*
                    |--------------------------------------------------------------------------
                    | INFO
                    |--------------------------------------------------------------------------
                    */
                    let bahanHtml = '';
                    const hasBahanPayment =
                        Array.isArray(res.payments) &&
                        res.payments.some(function(pay) {
                            return pay.note === 'Bahan';
                        });
                    if (Array.isArray(res.bahan_baku) && res.bahan_baku.length > 0) {

                        res.bahan_baku.forEach((row, i) => {

                            let qty = parseFloat(row.qty || 0);
                            let hargaInventory = parseFloat(row.harga || 0);
                            let hargaVivi = parseFloat(row.harga_vivi || 0);

                            let hargaViviCol = '';

                            if (res.can_edit_harga) {

                                hargaViviCol = `
                                <input
                                    type="number"
                                    class="form-control form-control-sm border-0 shadow-none harga-vivi-input"
                                    data-id="${row.id}"
                                    value="${row.harga_vivi ?? ''}">
                            `;

                            } else {

                                hargaViviCol = row.harga_vivi ?
                                    'Rp ' + hargaVivi.toLocaleString('id-ID') :
                                    '-';
                            }

                            // total pakai harga vivi jika ada
                            let total =
                                hargaVivi > 0 ?
                                hargaVivi * qty :
                                hargaInventory * qty;

                            bahanHtml += `
<tr>
    <td>${i + 1}</td>

    <td>${row.tanggal ?? '-'}</td>

    <td>
        <span class="badge bg-${
            row.tipe == 'in' ? 'success' : 'danger'
        }">
            ${row.tipe}
        </span>
    </td>

    <td>
        <div class="fw-bold">${row.nama_barang}</div>
        <small class="text-muted">${row.kode_barang}</small>
    </td>

    <td>${qty} ${row.satuan ?? ''}</td>

    ${
        !hideHarga
        ? `
                                                                                                                                    <td>
                                                                                                                                        ${
                                                                                                                                            hargaInventory
                                                                                                                                                ? 'Rp ' + hargaInventory.toLocaleString('id-ID')
                                                                                                                                                : '-'
                                                                                                                                        }
                                                                                                                                    </td>

                                                                                                                                    <td>
                                                                                                                                        ${hargaViviCol}
                                                                                                                                    </td>

                                                                                                                                    <td class="fw-bold text-success">
                                                                                                                                        Rp ${total.toLocaleString('id-ID')}
                                                                                                                                    </td>
                                                                                                                                `
        : ''
    }

    <td>${row.keterangan ?? '-'}</td>
</tr>
`;
                        });

                    } else {

                        bahanHtml = `
                    <tr>
                        <td colspan="9"
                            class="text-center text-muted py-3">
                            Tidak ada pengambilan bahan baku
                        </td>
                    </tr>
                    `;
                    }

                    $('#bahanbakuArea').html(bahanHtml);
                    let infoHtml = `
                    <table class="table table-sm">
                        <tr>
                            <td width="120">No SPK</td>
                            <td>${res.spk_no}</td>
                        </tr>
                        <tr>
                            <td>Supplier</td>
                            <td>${res.supplier.name}</td>
                        </tr>
                        <tr>
                            <td>Kategori</td>
                            <td>${res.kategori}</td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>${res.status}</td>
                        </tr>
                    </table>
                `;
                    $('#spkInfoArea')
                        .html(infoHtml);
                    /*
                    |--------------------------------------------------------------------------
                    | ITEMS
                    |--------------------------------------------------------------------------
                    */
                    let itemHtml = `
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>Item</th>
                <th width="70">
                    Qty
                </th>
                <th width="80">
                    Passed
                </th>
              
            </tr>
        </thead>
        <tbody>
    `;
                    const isCushion =
                        String(res.kategori || '')
                        .toLowerCase() === 'cushion';
                    res.items.forEach(item => {
                        itemHtml += `
            <tr>
                <td>
                    <div class="fw-bold">
                        ${item.nama}
                    </div>
                    <small class="text-muted">
                        ${item.kode ?? '-'}
                    </small>
                </td>
                <td class="text-center">
                    ${item.qty}
                </td>
                ${
                    !isCushion
                    ? `
                                                                                                                                                                    <td class="text-center text-success fw-bold">
                                                                                                                                                                        ${item.passed}
                                                                                                                                                                    </td>
                                                                                                                                                                  
                                                                                                                                                                    `
                    : ''
                }
            </tr>
        `;
                    });
                    itemHtml += `
        </tbody>
    </table>
    `;
                    $('#itemArea')
                        .html(itemHtml);
                    /*
                    |--------------------------------------------------------------------------
                    | TIMELINE
                    |--------------------------------------------------------------------------
                    */
                    let timelineHtml = '';
                    let totalIn = 0;

                    if (res.timelines.length > 0) {

                        res.timelines.forEach((row, i) => {

                            if (row.type === 'in') {
                                totalIn += Number(row.qty ?? 0);
                            }


                            timelineHtml += `
        <tr>
            <td>${i + 1}</td>
            <td>${row.date}</td>
            <td>
                <div class="fw-bold">${row.item_name ?? '-'}</div>
                <small class="text-muted">${row.item_code ?? '-'}</small>
            </td>
            <td>${res.supplier.name}</td>
            <td>
                <span class="badge bg-${
                    row.type == 'in'
                        ? 'success'
                        : row.type == 'return'
                        ? 'danger'
                        : 'warning'
                }">
                    ${row.type}
                </span>
            </td>
            <td>${row.qty}</td>
            <td>${row.remark ?? '-'}</td>
        </tr>`;
                        });

                    } else {

                        timelineHtml = `
        <tr>
            <td colspan="7" class="text-center text-muted py-4">
                Tidak ada timeline
            </td>
        </tr>`;
                    }

                    $('#timelineArea').html(timelineHtml);
                    $('#totalTimelineIn').text(totalIn);
                    /*
                    |--------------------------------------------------------------------------
                    | PAYMENT
                    |--------------------------------------------------------------------------
                    |
                    | ATURAN:
                    | - Payment normal       -> mengurangi saldo
                    | - Return bahan         -> menambah saldo
                    | - Potongan bahan       -> mengurangi saldo
                    | - SPK dengan item.catatan.remark = "Upah"
                    |   -> payment/upah TIDAK mengurangi saldo
                    |   -> tidak masuk TOTAL PAYMENT
                    |   -> ditampilkan setelah TOTAL PAYMENT
                    |
                    | Penanda UPAH berasal dari:
                    | res.items[].catatan.remark
                    | BUKAN dari pay.note.
                    |
                    | Modal tetap menggunakan Bootstrap/jQuery existing:
                    | $('#inventorDetailModal').modal('show')
                    |--------------------------------------------------------------------------
                    */

                    let paymentHtml = '';
                    let totalSpk = 0;

                    const items = Array.isArray(res.items) ?
                        res.items : [];

                    /*
                    |--------------------------------------------------------------------------
                    | DETEKSI SPK UPAH
                    |--------------------------------------------------------------------------
                    */
                    /*
                    |--------------------------------------------------------------------------
                    | DETEKSI KETERANGAN UPAH DARI ITEM SPK
                    |--------------------------------------------------------------------------
                    | Sumber utama:
                    | res.items[].catatan.remark
                    |
                    | Contoh:
                    | {
                    |     "catatan": {
                    |         "images": [],
                    |         "remark": "Upah"
                    |     }
                    | }
                    |
                    | Penting:
                    | - BUKAN dari pay.note
                    | - BUKAN dari payments
                    | - Jika remark item = Upah, seluruh bahan/upah tidak
                    |   mengurangi saldo Total SPK.
                    |--------------------------------------------------------------------------
                    */

                    function getItemRemark(item) {

                        if (!item) {
                            return '';
                        }

                        let catatan = item.catatan ?? null;

                        /*
                        | Catatan kadang sudah object, kadang JSON string.
                        */
                        if (typeof catatan === 'string') {
                            const rawCatatan = catatan.trim();

                            if (rawCatatan !== '') {
                                try {
                                    catatan = JSON.parse(rawCatatan);
                                } catch (e) {
                                    /*
                                    | Bukan JSON. Gunakan sebagai text remark.
                                    */
                                    catatan = rawCatatan;
                                }
                            }
                        }

                        let remark = '';

                        if (
                            catatan &&
                            typeof catatan === 'object' &&
                            !Array.isArray(catatan)
                        ) {
                            remark =
                                catatan.remark ??
                                catatan.Remark ??
                                catatan.keterangan ??
                                catatan.Keterangan ??
                                '';
                        } else if (typeof catatan === 'string') {
                            remark = catatan;
                        }

                        /*
                        | Fallback untuk format item lama.
                        */
                        if (!remark) {
                            remark =
                                item.remark ??
                                item.keterangan ??
                                item.catatan_remark ??
                                '';
                        }

                        return String(remark).trim();
                    }

                    /*
                    | Kumpulkan semua keterangan item.
                    | Ini juga dipakai untuk menampilkan Keterangan pada
                    | TOTAL SPK / TOTAL PAYMENT.
                    */
                    const keteranganItems = items
                        .map(function(item) {
                            return getItemRemark(item);
                        })
                        .filter(function(value) {
                            return value !== '';
                        });

                    const keteranganSpk = [
                        ...new Set(keteranganItems)
                    ].join(', ');

                    /*
                    | UPAH dianggap aktif jika salah satu item mempunyai
                    | keterangan yang mengandung kata "upah".
                    */
                    const isUpahSpk = keteranganItems.some(function(value) {

                        const text = String(value)
                            .trim()
                            .toLowerCase();

                        return (
                            text === 'upah' ||
                            text.includes('upah')
                        );
                    });

                    /*
                    |--------------------------------------------------------------------------
                    | TOTAL SPK
                    |--------------------------------------------------------------------------
                    */
                    items.forEach(function(item) {

                        totalSpk += parseFloat(
                            item.total || 0
                        );

                        if (
                            Array.isArray(item.custom_columns)
                        ) {

                            item.custom_columns.forEach(function(c) {

                                totalSpk += parseFloat(
                                    c.total || 0
                                );

                            });

                        }

                    });

                    // PPN dari data SPK, bukan tarif hardcode.
                    const ppnRate = res.ppn_enabled ? Number(res.ppn_rate || 0) : 0;
                    const subtotalSpk = totalSpk;
                    const ppnAmount = Math.round((subtotalSpk * ppnRate / 100) * 100) / 100;
                    totalSpk = subtotalSpk + ppnAmount;
                    const ppnInfo = ppnRate > 0 ?
                        `PPN ${ppnRate}%: Rp ${ppnAmount.toLocaleString('id-ID')}` :
                        '';

                    let saldo = totalSpk;
                    let totalPayment = 0;
                    let totalReturnBahan = 0;
                    let paymentNo = 0;

                    /*
                    |--------------------------------------------------------------------------
                    | EVENT TERPISAH
                    |--------------------------------------------------------------------------
                    */
                    let normalPaymentEvents = [];
                    let bahanEvents = [];
                    let upahEvents = [];

                    /*
                    |--------------------------------------------------------------------------
                    | PEMOTONGAN BAHAN
                    |--------------------------------------------------------------------------
                    | Harga:
                    | - Jika harga_vivi sudah diisi -> gunakan harga kontrak
                    | - Jika belum -> gunakan harga inventory
                    |
                    | Harga inventory TIDAK ditampilkan lagi jika sudah ada
                    | adjustment Vivi.
                    |--------------------------------------------------------------------------
                    */
                    if (
                        Array.isArray(res.bahan_baku) &&
                        res.bahan_baku.length > 0
                    ) {

                        res.bahan_baku.forEach(function(row) {

                            const qty =
                                parseFloat(row.qty || 0);

                            const hargaInventory =
                                parseFloat(row.harga || 0);

                            const hargaVivi =
                                parseFloat(row.harga_vivi || 0);

                            const hargaKontrak =
                                hargaVivi > 0 ?
                                hargaVivi :
                                hargaInventory;

                            const total =
                                hargaKontrak * qty;

                            // =====================================================
                            // STATUS PER TRANSAKSI BAHAN
                            // =====================================================

                            const keteranganBahan = String(
                                    row.keterangan ?? ''
                                )
                                .replace(/&nbsp;/gi, ' ')
                                .trim()
                                .toLowerCase();

                            const isJasaBahan =
                                row.is_jasa === true ||
                                row.is_jasa === 1 ||
                                keteranganBahan === 'jasa' ||
                                keteranganBahan === 'upah' ||
                                keteranganBahan === 'upah kerja' ||
                                keteranganBahan === 'jasa/upah kerja';

                            bahanEvents.push({
                                type: 'bahan',
                                date: row.tanggal || '',
                                row: row,
                                qty: qty,
                                hargaInventory: hargaInventory,
                                hargaVivi: hargaVivi,
                                hargaKontrak: hargaKontrak,
                                total: total,

                                // STATUS KHUSUS TRANSAKSI INI
                                isJasa: isJasaBahan
                            });

                            // HANYA BAHAN BIASA masuk total payment
                            if (!isJasaBahan) {
                                totalPayment += total;
                            }

                        });

                    }

                    /*
                    |--------------------------------------------------------------------------
                    | PAYMENT
                    |--------------------------------------------------------------------------
                    */
                    if (
                        Array.isArray(res.payments) &&
                        res.payments.length > 0
                    ) {

                        res.payments.forEach(function(pay) {

                            // Angsuran adalah pembayaran pengurang saldo,
                            // termasuk jika nilai tersimpan memakai tanda minus.
                            const isAngsuran = String(pay.payment_type || pay.type || pay.note || '')
                                .trim().toLowerCase() === 'angsuran';
                            const nilaiRaw = parseFloat(
                                pay.payment_request_amount || pay.amount || 0
                            ) || 0;
                            const nilai = isAngsuran ? Math.abs(nilaiRaw) : nilaiRaw;

                            /*
                            |--------------------------------------------------------------------------
                            | UPAH
                            |--------------------------------------------------------------------------
                            */
                            if (isUpahSpk && !isAngsuran) {

                                upahEvents.push({
                                    type: 'upah',
                                    date: pay.date || '',
                                    pay: pay,
                                    nilai: nilai
                                });

                                return;
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | PAYMENT BAHAN LAMA
                            |--------------------------------------------------------------------------
                            | Jika detail bahan tersedia, jangan dihitung dua kali.
                            |--------------------------------------------------------------------------
                            */
                            if (
                                String(pay.note || '').toLowerCase() ===
                                'bahan'
                            ) {

                                if (
                                    Array.isArray(res.bahan_baku) &&
                                    res.bahan_baku.length > 0
                                ) {
                                    return;
                                }

                                totalPayment += nilai;

                                normalPaymentEvents.push({
                                    type: 'payment',
                                    date: pay.date || '',
                                    pay: pay,
                                    nilai: nilai
                                });

                                return;
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | RETURN BAHAN
                            |--------------------------------------------------------------------------
                            | Return bukan payment keluar, sehingga tidak masuk
                            | TOTAL PAYMENT. Tetapi saldo bertambah.
                            |--------------------------------------------------------------------------
                            */
                            if (
                                String(pay.note || '').toLowerCase() ===
                                'return_bahan'
                            ) {

                                totalReturnBahan += nilai;

                                normalPaymentEvents.push({
                                    type: 'return_bahan',
                                    date: pay.date || '',
                                    pay: pay,
                                    nilai: nilai
                                });

                                return;
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | PAYMENT NORMAL
                            |--------------------------------------------------------------------------
                            */
                            totalPayment += nilai;

                            normalPaymentEvents.push({
                                type: 'payment',
                                date: pay.date || '',
                                pay: pay,
                                isAngsuran: isAngsuran,
                                nilai: nilai
                            });

                        });

                    }

                    /*
                    |--------------------------------------------------------------------------
                    | SORT PAYMENT NORMAL
                    |--------------------------------------------------------------------------
                    */
                    normalPaymentEvents.sort(function(a, b) {

                        const dateA =
                            new Date(a.date || '1900-01-01');

                        const dateB =
                            new Date(b.date || '1900-01-01');

                        return dateA - dateB;
                    });

                    /*
                    |--------------------------------------------------------------------------
                    | SORT BAHAN
                    |--------------------------------------------------------------------------
                    */
                    bahanEvents.sort(function(a, b) {

                        const dateA =
                            new Date(a.date || '1900-01-01');

                        const dateB =
                            new Date(b.date || '1900-01-01');

                        return dateA - dateB;
                    });

                    /*
                    |--------------------------------------------------------------------------
                    | SORT UPAH
                    |--------------------------------------------------------------------------
                    */
                    upahEvents.sort(function(a, b) {

                        const dateA =
                            new Date(a.date || '1900-01-01');

                        const dateB =
                            new Date(b.date || '1900-01-01');

                        return dateA - dateB;
                    });

                    /*
                    |--------------------------------------------------------------------------
                    | RUNNING BALANCE
                    |--------------------------------------------------------------------------
                    | PENTING:
                    | saldo sekarang dihitung saat ROW ditampilkan.
                    |
                    | Sebelumnya:
                    |   saldo sudah dikurangi seluruh payment + bahan di awal,
                    |   lalu nilai saldo akhir tersebut dipakai untuk semua row.
                    |
                    | Sekarang:
                    |   TOTAL SPK = saldo awal
                    |   Payment 1 = saldo awal - payment 1
                    |   Payment 2 = saldo sebelumnya - payment 2
                    |   Bahan 1   = saldo sebelumnya - bahan 1
                    |   dst.
                    |--------------------------------------------------------------------------
                    */
                    let runningSaldo = totalSpk;

                    /*
                    |--------------------------------------------------------------------------
                    | TOTAL SPK
                    |--------------------------------------------------------------------------
                    */
                    paymentHtml += `
                        <tr class="table-primary">
                            <td></td>
                            <td></td>
                            <td class="fw-bold">
                                TOTAL SPK
                            </td>
                            <td class="fw-bold">
                                Rp ${totalSpk.toLocaleString('id-ID')}
                            </td>
                            <td class="fw-bold text-success">
                                Rp ${runningSaldo.toLocaleString('id-ID')}
                            </td>
                            <td>
                                ${
                                    (keteranganSpk || ppnInfo)
                                        ? `
                                                                            <span class="badge bg-warning text-dark">
                                                                                ${[keteranganSpk, ppnInfo].filter(Boolean).join(' | ')}
                                                                            </span>
                                                                          `
                                        : '-'
                                }
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    `;

                    /*
                    |--------------------------------------------------------------------------
                    | RENDER PAYMENT NORMAL
                    |--------------------------------------------------------------------------
                    */
                    normalPaymentEvents.forEach(function(event) {

                        const pay = event.pay;
                        const nilai = event.nilai;

                        /*
                        | Running balance:
                        | - payment normal => kurangi
                        | - return bahan  => tambah
                        */
                        if (event.type === 'return_bahan') {
                            runningSaldo += nilai;
                        } else {
                            runningSaldo -= nilai;
                        }

                        runningSaldo = Math.max(0, runningSaldo);

                        paymentNo++;

                        paymentHtml += `
                            <tr>

                                <td>
                                    ${paymentNo}
                                </td>

                                <td>
                                    ${pay.date ?? '-'}
                                </td>

                                <td>
                                    ${event.isAngsuran ? 'Angsuran' : (pay.note ?? '-')}
                                </td>

                                <td>
                                    ${event.isAngsuran ? '' : ''}Rp ${Number(
                                        event.isAngsuran
                                            ? Math.abs(nilai)
                                            : (pay.amount || nilai || 0)
                                    ).toLocaleString('id-ID')}
                                </td>

                                <td class="
                                    fw-bold
                                    ${
                                        runningSaldo <= 0
                                            ? 'text-danger'
                                            : 'text-success'
                                    }
                                ">
                                    Rp ${runningSaldo.toLocaleString('id-ID')}
                                </td>

                                <td>
                                    ${pay.note_tambahan ?? '-'}
                                </td>

                                <td>-</td>
                                <td>-</td>

                                <td>
                                    ${
                                        pay.is_request
                                        ? `
                                                                            <span class="badge bg-success">
                                                                                ✓ Requested
                                                                            </span>
                                                                          `
                                        : `
                                                                            <span class="badge bg-warning">
                                                                                Belum Request
                                                                            </span>
                                                                          `
                                    }
                                </td>

                                <td>
                                    ${
                                        pay.adjustment > 0
                                        ? `
                                                                            <div>
                                                                                Paid :
                                                                                <b>
                                                                                    Rp ${Number(
                                                                                        pay.payment_request_amount || 0
                                                                                    ).toLocaleString('id-ID')}
                                                                                </b>
                                                                            </div>

                                                                            <small class="text-danger">
                                                                                Sisa :
                                                                                Rp ${Number(
                                                                                    pay.remaining_amount || 0
                                                                                ).toLocaleString('id-ID')}
                                                                            </small>
                                                                          `
                                        : pay.finance_approved
                                        ? `
                                                                            <span class="badge bg-success">
                                                                                Full Payment
                                                                            </span>
                                                                          `
                                        : pay.is_request
                                        ? `
                                                                            <span class="badge bg-warning">
                                                                                Menunggu Finance
                                                                            </span>
                                                                          `
                                        : `
                                                                            <span class="badge bg-secondary">
                                                                                Belum Request
                                                                            </span>
                                                                          `
                                    }
                                </td>

                            </tr>
                        `;

                    });

                    /*
                    |--------------------------------------------------------------------------
                    | POTONGAN BAHAN
                    |--------------------------------------------------------------------------
                    | Ditampilkan setelah payment normal.
                    | Running saldo diteruskan dari transaksi sebelumnya.
                    |--------------------------------------------------------------------------
                    */
                    bahanEvents.forEach(function(event) {

                        const row = event.row;
                        const qty = event.qty;
                        const hargaInventory = event.hargaInventory;
                        const hargaVivi = event.hargaVivi;
                        const hargaKontrak = event.hargaKontrak;
                        const total = event.total;

                        if (!event.isJasa) {
                            runningSaldo -= total;
                            runningSaldo = Math.max(0, runningSaldo);
                        }
                        paymentNo++;

                        let hargaKontrakHtml = '';

                        if (res.can_edit_harga) {

                            hargaKontrakHtml = `
                                <input
                                    type="number"
                                    class="form-control form-control-sm border-0 shadow-none harga-vivi-input"
                                    data-id="${row.id}"
                                    value="${row.harga_vivi ?? ''}"
                                    placeholder="${hargaInventory || 0}"
                                    style="min-width:110px;"
                                >
                            `;

                        } else {

                            hargaKontrakHtml =
                                hargaVivi > 0 ?
                                'Rp ' + hargaVivi.toLocaleString('id-ID') :
                                '-';

                        }

                        if (event.isJasa) {

                            paymentHtml += `
                                <tr class="payment-upah-row"
                                    title="Jasa / upah kerja - tidak mengurangi saldo">

                                    <td>
                                        ${paymentNo}
                                    </td>

                                    <td>
                                        ${row.tanggal ?? '-'}
                                    </td>

                                    <td>
                                        <span class="badge bg-primary">
                                            jasa/upah kerja
                                        </span>

                                        <div class="fw-bold mt-1">
                                            ${row.nama_barang ?? '-'}
                                        </div>

                                        <small class="text-muted">
                                            ${row.kode_barang ?? ''}
                                        </small>
                                    </td>

                                    <td>
                                        Rp ${total.toLocaleString('id-ID')}
                                    </td>

                                    <td class="fw-bold text-success">
                                        Rp ${runningSaldo.toLocaleString('id-ID')}
                                    </td>

                                    <td>
                                        <span class="fw-bold">
                                            ${qty}
                                            ${row.satuan ?? ''}
                                        </span>
                                    </td>

                                    <td>-</td>

                                    <td class="fw-bold text-success">
                                        Rp ${total.toLocaleString('id-ID')}
                                    </td>

                                    <td>
                                        -
                                    </td>

                                    <td>
                                        <span class="badge bg-info">
                                            Tidak mengurangi saldo
                                        </span>
                                    </td>

                                </tr>
                            `;

                        } else {

                            paymentHtml += `
                                <tr class="payment-bahan-row">

                                    <td>
                                        ${paymentNo}
                                    </td>

                                    <td>
                                        ${row.tanggal ?? '-'}
                                    </td>

                                    <td>
                                        <span class="badge ${
    event.isJasa
        ? 'bg-primary'
        : 'bg-danger'
}">
    ${
        event.isJasa
            ? 'jasa/upah kerja'
            : 'potongan bahan'
    }
</span>

                                        <div class="fw-bold mt-1">
                                            ${row.nama_barang ?? '-'}
                                        </div>

                                        <small class="text-muted">
                                            ${row.kode_barang ?? '-'}
                                        </small>
                                    </td>

                                    <td>
                                        Rp ${total.toLocaleString('id-ID')}
                                    </td>

                                    <td class="fw-bold
                                        ${
                                            runningSaldo <= 0
                                                ? 'text-danger'
                                                : 'text-success'
                                        }">
                                        Rp ${runningSaldo.toLocaleString('id-ID')}
                                    </td>

                                    <td>
                                        <span class="fw-bold">
                                            ${qty}
                                            ${row.satuan ?? 'KG'}
                                        </span>
                                    </td>

                                    <td onclick="event.stopPropagation();">
                                        ${
                                            hargaVivi > 0
                                                ? hargaKontrakHtml
                                                : (
                                                    res.can_edit_harga
                                                        ? hargaKontrakHtml
                                                        : (
                                                            hargaInventory
                                                                ? 'Rp ' +
                                                                    hargaInventory.toLocaleString('id-ID')
                                                                : '-'
                                                        )
                                                )
                                        }
                                    </td>

                                    <td class="fw-bold text-success">
                                        Rp ${total.toLocaleString('id-ID')}
                                    </td>

                                    <td>
                                        ${
                                            event.isJasa
                                                ? `
                                                            <span class="badge bg-primary">
                                                                jasa/upah kerja
                                                            </span>
                                                          `
                                                : (
                                                    row.keterangan
                                                        ? `<span class="text-muted">${row.keterangan}</span>`
                                                        : '-'
                                                  )
                                        }
                                    </td>

                                    <td>
                                        -
                                    </td>

                                </tr>
                            `;
                        }

                    });

                    /*
                    |--------------------------------------------------------------------------
                    | TOTAL PAYMENT
                    |--------------------------------------------------------------------------
                    | Ditaruh SETELAH seluruh transaksi agar saldo akhir
                    | benar-benar merupakan saldo terakhir.
                    |
                    | Upah tetap tidak masuk TOTAL PAYMENT.
                    |--------------------------------------------------------------------------
                    */
                    saldo = runningSaldo;

                    paymentHtml += `
                        <tr class="table-success">

                            <td colspan="3"
                                class="text-end fw-bold">
                                TOTAL PAYMENT
                            </td>

                            <td class="fw-bold">
                                Rp ${totalPayment.toLocaleString('id-ID')}
                            </td>

                            <td class="fw-bold
                                ${
                                    saldo <= 0
                                        ? 'text-danger'
                                        : 'text-success'
                                }">
                                Rp ${saldo.toLocaleString('id-ID')}
                            </td>

                            <td>
                                ${
                                    keteranganSpk
                                        ? `
                                                                            <span class="badge bg-warning text-dark">
                                                                                ${keteranganSpk}
                                                                            </span>
                                                                          `
                                        : ''
                                }
                            </td>

                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>

                        </tr>
                    `;

                    /*
                    |--------------------------------------------------------------------------
                    | UPAH KERJA
                    |--------------------------------------------------------------------------
                    | Upah ditampilkan setelah TOTAL PAYMENT dan tidak
                    | mengubah running saldo.
                    |--------------------------------------------------------------------------
                    */
                    if (upahEvents.length > 0) {

                        upahEvents.forEach(function(event) {

                            const pay = event.pay;
                            const nilai = event.nilai;

                            paymentNo++;

                            paymentHtml += `
                                <tr class="payment-upah-row"
                                    title="Upah kerja - tidak mengurangi saldo Total SPK">

                                    <td>
                                        ${paymentNo}
                                    </td>

                                    <td>
                                        ${pay.date ?? '-'}
                                    </td>

                                    <td>
                                        <span class="badge bg-primary">
                                            Upah
                                        </span>

                                        <div class="fw-bold mt-1">
                                            ${
                                                pay.note_tambahan ??
                                                'Upah'
                                            }
                                        </div>
                                    </td>

                                    <td>
                                        Rp ${Number(
                                            pay.amount || nilai || 0
                                        ).toLocaleString('id-ID')}
                                    </td>

                                    <td class="fw-bold text-success">
                                        Rp ${runningSaldo.toLocaleString('id-ID')}
                                    </td>

                                    <td>-</td>
                                    <td>-</td>

                                    <td class="fw-bold">
                                        Rp ${Number(
                                            pay.amount || nilai || 0
                                        ).toLocaleString('id-ID')}
                                    </td>

                                    <td>
                                        ${
                                            pay.is_request
                                            ? `
                                                                                <span class="badge bg-success">
                                                                                    ✓ Requested
                                                                                </span>
                                                                              `
                                            : `
                                                                                <span class="badge bg-warning">
                                                                                    Belum Request
                                                                                </span>
                                                                              `
                                        }
                                    </td>

                                    <td>
                                        ${
                                            pay.adjustment > 0
                                            ? `
                                                                                <div>
                                                                                    Paid :
                                                                                    <b>
                                                                                        Rp ${Number(
                                                                                            pay.payment_request_amount || 0
                                                                                        ).toLocaleString('id-ID')}
                                                                                    </b>
                                                                                </div>

                                                                                <small class="text-danger">
                                                                                    Sisa :
                                                                                    Rp ${Number(
                                                                                        pay.remaining_amount || 0
                                                                                    ).toLocaleString('id-ID')}
                                                                                </small>
                                                                              `
                                            : `
                                                                                <span class="badge bg-secondary">
                                                                                    Tidak mengurangi saldo
                                                                                </span>
                                                                              `
                                        }
                                    </td>

                                </tr>
                            `;

                        });

                    }

                    /*
                    |--------------------------------------------------------------------------
                    | TIDAK ADA TRANSAKSI
                    |--------------------------------------------------------------------------
                    */
                    if (
                        normalPaymentEvents.length === 0 &&
                        bahanEvents.length === 0 &&
                        upahEvents.length === 0
                    ) {

                        paymentHtml += `
                            <tr>
                                <td colspan="10"
                                    class="text-center text-muted py-4">
                                    Tidak ada payment
                                </td>
                            </tr>
                        `;
                    }

                    $('#paymentArea')
                        .html(paymentHtml);
                    $('#inventorDetailModal')
                        .modal('show');
                });
            });
            // NWS filter
        </script>
        <script></script>
        <script>
            // Filter gabungan agar pilihan tidak saling me-reset.
            function applySpkFilters() {
                const status = $('#filterStatus').val();
                const type = ($('#filterSpkType').val() || '').toUpperCase();
                const supplier = ($('#searchSub').val() || '').trim().toLowerCase();
                const keyword = ($('#searchSpk').val() || '').trim().toLowerCase();

                $('.inventor-table tbody tr.spk-row').each(function() {
                    const $row = $(this);
                    const rowStatus = String($row.attr('data-status') || '').toLowerCase();
                    const spkText = $row.find('td:eq(1)').text().trim().toUpperCase();
                    const spkType = (spkText.split('/')[1] || '').trim().split(/\s+/)[0];
                    const supplierText = $row.find('td:eq(2)').text().toLowerCase();
                    const searchText = ($row.find('td:eq(1)').text() + ' ' +
                        $row.find('.po-col').text()).toLowerCase();
                    $row.toggle((!status || rowStatus === status) &&
                        (!type || spkType === type) &&
                        (!supplier || supplierText.includes(supplier)) &&
                        (!keyword || searchText.includes(keyword)));
                });
            }

            // Menggantikan handler filter lama tanpa mengganggu handler lain.
            $(document).off('change', '#filterStatus');
            $(document).off('change', '#filterSpkType');
            $(document).off('keyup', '#searchSpk');
            $('#searchSub').off('input');
            $(document).on('change', '#filterStatus, #filterSpkType', applySpkFilters);
            $(document).on('input', '#searchSpk, #searchSub', applySpkFilters);

            $(document).on('click', '.btn-ajukan', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const $button = $(this);
                if ($button.prop('disabled')) return;
                const id = $button.data('id');
                const $row = $button.closest('tr.spk-row');
                $button.prop('disabled', true).text('Memproses...');

                $.ajax({
                    url: `/spk/${encodeURIComponent(id)}/submit-signature`,
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        $row.attr('data-status', 'diajukan').data('status', 'diajukan');
                        $button.remove();
                        applySpkFilters();
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: res.message || 'Rouf Ganteng',
                            showConfirmButton: false,
                            timer: 1800,
                            timerProgressBar: true
                        });
                    },
                    error: function(xhr) {
                        $button.prop('disabled', false).text('Ajukan');
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Gagal mengajukan SPK',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                });
            });
            applySpkFilters();
            // update vivi
            $(document).on('keypress', '.harga-vivi-input', function(e) {

                if (e.which == 13) {

                    e.preventDefault();

                    let input = $(this);

                    $.ajax({
                        url: '/inventor/update-harga-vivi',
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            id: input.data('id'),
                            harga: input.val()
                        },
                        success: function() {

                            Swal.fire({
                                icon: 'success',
                                title: 'Tersimpan',
                                timer: 1000,
                                showConfirmButton: false
                            });

                        }
                    });
                }
            });
            // filter
            $(document).on('change', '#subSelect', function() {

                let value = $(this).val().toLowerCase();

                $('.inventor-table tbody tr').each(function() {

                    let supplier = $(this)
                        .find('td:eq(2)') // kolom Supplier
                        .text()
                        .trim()
                        .toLowerCase();

                    if (value === '' || supplier.includes(value)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }

                });

            });
            const originalOptions = $('#subSelect').html();

            $('#searchSub').on('keyup', function() {

                let keyword = $(this).val().toLowerCase();

                $('#subSelect').html(originalOptions);

                $('#subSelect option').each(function() {

                    let text = $(this).text().toLowerCase();

                    if (!text.includes(keyword) && $(this).val() != '') {
                        $(this).remove();
                    }

                });

            });
            // pending
            $(document).on('click', '.btn-approve-spk', function() {

                let id = $(this).data('id');

                window.location.href = '/spk/views/' + id;

            });

            function formatRupiah(angka) {
                if (!angka) return '-';

                return 'Rp ' + Number(angka)
                    .toLocaleString('id-ID');
            }
            /*
                |--------------------------------------------------------------------------
                | CLICK ROW = MENU SPK
                |--------------------------------------------------------------------------
                | Klik kiri pada baris SPK menampilkan dropdown kecil:
                | - View SPK    -> /spk/edit/{id}
                | - See Details -> modal detail yang sudah ada
                */

            function closeSpkRowMenu() {
                $('#spkRowMenu').remove();
                $('.inventor-table tbody tr').removeClass('active-row');
            }

            function showSpkRowMenu(e, row) {
                closeSpkRowMenu();

                const spkId = row.data('id');

                if (!spkId) {
                    return;
                }

                row.addClass('active-row');

                const menu = $(`
                    <div id="spkRowMenu" class="spk-row-menu">
                        <a href="/spk/edit/${encodeURIComponent(spkId)}"
                           class="spk-row-menu-item">
                            <i class="fa fa-eye"></i>
                            <span>View to SPK</span>
                        </a>
                        <button type="button"
                                class="spk-row-menu-item spk-see-details">
                            <i class="fa fa-list-alt"></i>
                            <span>See Details</span>
                        </button>
                    </div>
                `);

                $('body').append(menu);
                menu.data('spk-id', spkId);
                menu.data('spk-row', row);

                // Posisi mengikuti titik klik, tetapi jangan sampai keluar viewport.
                const menuWidth = menu.outerWidth();
                const menuHeight = menu.outerHeight();
                const gap = 4;

                let left = e.clientX + gap;
                let top = e.clientY + gap;

                if (left + menuWidth > window.innerWidth - 8) {
                    left = e.clientX - menuWidth - gap;
                }

                if (top + menuHeight > window.innerHeight - 8) {
                    top = e.clientY - menuHeight - gap;
                }

                menu.css({
                    left: Math.max(8, left),
                    top: Math.max(8, top)
                });
            }

            // Klik kiri pada row = tampilkan dropdown.
            $(document).on('click', '.spk-row', function(e) {
                // Tombol/link/input/select di dalam row tetap bekerja normal.
                if ($(e.target).closest('button,a,input,select,textarea').length) {
                    return;
                }

                e.stopPropagation();
                showSpkRowMenu(e, $(this));
            });

            // See Details = jalankan modal detail existing.
            $(document).on('click', '.spk-see-details', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const menu = $('#spkRowMenu');
                const spkId = menu.data('spk-id');
                const row = menu.data('spk-row');

                closeSpkRowMenu();

                if (spkId && row) {
                    $(row).find('.btn-detail').trigger('click');
                }
            });

            // Simpan referensi row/id pada menu setelah dibuat.
            // Klik di luar menu = tutup.
            $(document).on('click', function(e) {
                if (
                    $('#spkRowMenu').length &&
                    !$(e.target).closest('#spkRowMenu').length &&
                    !$(e.target).closest('.spk-row').length
                ) {
                    closeSpkRowMenu();
                }
            });

            // ESC = tutup menu.
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeSpkRowMenu();
                }
            });
        </script>
    @endpush

    @push('scripts')
        <script>
            (function($) {
                'use strict';

                function mobileOnly() {
                    return window.matchMedia('(max-width: 767.98px)').matches;
                }

                function applyMobileFilters() {
                    if (!mobileOnly()) return;
                    var supplier = String($('#searchSub').val() || '').toLowerCase().trim();
                    var search = String($('#searchSpk').val() || '').toLowerCase().trim();
                    var status = String($('#filterStatus').val() || '').toLowerCase();
                    var type = String($('#filterSpkType').val() || '').toUpperCase();
                    var count = 0;
                    $('.spk-mobile-card').each(function() {
                        var $card = $(this);
                        var ok = (!supplier || String($card.attr('data-supplier') || '').includes(supplier)) &&
                            (!search || String($card.attr('data-search') || '').includes(search)) &&
                            (!status || String($card.attr('data-status') || '').toLowerCase() === status) &&
                            (!type || String($card.attr('data-spktype') || '') === type);
                        $card.toggle(ok);
                        if (ok) count++;
                    });
                    $('.spk-mobile-filter-empty').toggle(count === 0);
                }
                $(document).on('input change', '#searchSub,#searchSpk,#filterStatus,#filterSpkType', applyMobileFilters);
                $(window).on('resize', applyMobileFilters);
                $(function() {
                    applyMobileFilters();
                });
                // Mobile: tap kartu langsung buka detail existing; tombol Ajukan tetap mandiri.
                $(document).on('click', '.spk-mobile-card', function(e) {
                    if (!mobileOnly()) return;
                    if ($(e.target).closest('button,a,input,select,textarea').length) return;
                    var $detail = $(this).find('.spk-mobile-detail').first();
                    if ($detail.length) $detail.trigger('click');
                });
                $(document).on('click', '.spk-mobile-detail', function() {
                    var id = String($(this).data('id'));
                    $('.inventor-wrapper .btn-detail').filter(function() {
                        return String($(this).data('id')) === id;
                    }).first().trigger('click');
                });
                $(document).on('click', '.spk-mobile-ajukan', function() {
                    var id = String($(this).data('id'));
                    $('.inventor-wrapper .btn-ajukan').filter(function() {
                        return String($(this).data('id')) === id;
                    }).first().trigger('click');
                });

                function activatePanel(panel) {
                    var $modal = $('#inventorDetailModal');
                    $modal.find('.spk-mobile-tabs button').removeClass('active').filter('[data-panel="' + panel + '"]')
                        .addClass('active');
                    $modal.find('.modal-body>.row .card').removeClass('spk-mobile-panel-active');
                    var isMobile = mobileOnly();
                    var isPaymentLocked = isMobile && panel === 'payment';
                    $modal.toggleClass('spk-mobile-payment-locked', isPaymentLocked);
                    $modal.find('#spkMobilePaymentNotice').prop('hidden', !isPaymentLocked);
                    if (isPaymentLocked) return;
                    var selector = panel === 'info' ? '#spkInfoArea' : panel === 'items' ? '#itemArea' : panel ===
                        'timeline' ? '#timelineArea' : '#paymentArea';
                    $modal.find(selector).closest('.card').addClass('spk-mobile-panel-active');
                }
                $(document).on('click', '.spk-mobile-tabs button', function() {
                    activatePanel($(this).data('panel'));
                });
                $('#inventorDetailModal').on('show.bs.modal', function() {
                    activatePanel('info');
                });
                $(window).on('resize', function() {
                    if ($('#inventorDetailModal').hasClass('show')) {
                        activatePanel($('#inventorDetailModal .spk-mobile-tabs button.active').data('panel') ||
                            'info');
                    }
                });
            })(jQuery);
        </script>
    @endpush
