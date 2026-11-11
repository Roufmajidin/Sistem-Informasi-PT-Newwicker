@extends('master.master')

@section('content')
    <style>
        #momList {
            font-size: 13px;
            color: #334155
        }

        #momList .mom-nav-tabs {
            display: flex;
            gap: 6px;
            border-bottom: 1px solid #dbe4f0;
            margin-bottom: 18px;
            overflow-x: auto
        }

        #momList .mom-nav-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 17px;
            text-decoration: none !important;
            white-space: nowrap;
            color: #52647e !important;
            font-weight: 600;
            border-bottom: 3px solid transparent
        }

        #momList .mom-nav-tab.is-active {
            color: #1d5fbe !important;
            border-bottom-color: #1d5fbe;
            background: #f3f7ff
        }

        #momList .mom-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin: 14px 0 18px
        }

        #momList .mom-table-wrap {
            width: 100%;
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            background: #fff
        }

        #momList table.mom-table {
            width: 100%;
            margin: 0 !important;
            border-collapse: collapse !important;
            table-layout: auto;
            background: #fff
        }

        #momList .mom-table thead th {
            background: #293d52 !important;
            color: #fff !important;
            font-size: 12px;
            font-weight: 600;
            padding: 12px 11px !important;
            border: 0 !important;
            white-space: nowrap;
            vertical-align: middle
        }

        #momList .mom-table tbody td {
            padding: 11px !important;
            vertical-align: middle !important;
            border: 0 !important;
            border-bottom: 1px solid #e8edf4 !important;
            background: transparent !important;
            line-height: 1.4
        }

        #momList .mom-table tbody tr {
            height: auto !important;
            background: #fff !important
        }

        #momList .mom-table tbody tr:nth-child(even) {
            background: #f8fafc !important
        }

        #momList .mom-table tbody tr:hover {
            background: #f0f6ff !important
        }

        #momList .mom-table th:nth-child(1),
        #momList .mom-table td:nth-child(1) {
            width: 55px;
            text-align: center
        }

        #momList .mom-table th:nth-child(2),
        #momList .mom-table td:nth-child(2) {
            width: 115px;
            white-space: nowrap
        }

        #momList .mom-table th:nth-child(3) {
            min-width: 190px
        }

        #momList .mom-table th:nth-child(4) {
            min-width: 100px
        }

        #momList .mom-table th:nth-child(5) {
            min-width: 145px
        }

        #momList .mom-table th:nth-child(6) {
            width: 105px;
            text-align: center
        }

        #momList .mom-table td:nth-child(6) {
            text-align: center
        }

        #momList .mom-table th:nth-child(7),
        #momList .mom-table td:nth-child(7) {
            width: 270px;
            min-width: 270px
        }

        #momList .mom-actions {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: flex-start !important;
            flex-wrap: nowrap !important;
            gap: 6px !important;
            white-space: nowrap
        }

        #momList .mom-actions form {
            display: inline-flex !important;
            margin: 0 !important;
            padding: 0 !important;
            width: auto !important
        }

        #momList .mom-actions .btn {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            gap: 5px;
            white-space: nowrap !important;
            min-width: 0 !important;
            width: auto !important;
            height: 31px !important;
            margin: 0 !important;
            padding: 5px 10px !important;
            font-size: 12px !important;
            line-height: 1.2 !important;
            border-radius: 5px !important
        }

        #momList .mom-download-btn[disabled] {
            cursor: wait;
            opacity: .7
        }

        #momPdfLoading {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 20000;
            background: rgba(15, 23, 42, .42);
            align-items: center;
            justify-content: center;
            padding: 20px
        }

        #momPdfLoading .mom-loading-panel {
            width: min(360px, 100%);
            background: #fff;
            border-radius: 12px;
            padding: 27px;
            text-align: center;
            box-shadow: 0 18px 50px rgba(0, 0, 0, .2)
        }

        #momPdfLoading .mom-loading-spinner {
            width: 38px;
            height: 38px;
            border: 4px solid #dbeafe;
            border-top-color: #2563eb;
            border-radius: 50%;
            animation: momPdfSpin .75s linear infinite;
            margin: 0 auto 15px
        }

        @keyframes momPdfSpin {
            to {
                transform: rotate(360deg)
            }
        }

        @media(max-width:767px) {
            #momList .mom-table {
                min-width: 920px
            }

            #momList .mom-toolbar {
                align-items: flex-start
            }

            #momList .mom-nav-tab {
                padding: 10px 12px
            }
        }
    </style>

    <div class="container-fluid py-3" id="momList">
        <nav class="mom-nav-tabs" aria-label="Navigasi Minutes of Meeting">
            <a class="mom-nav-tab" href="{{ route('mom.index') }}"><i class="fa fa-edit"></i> Form MoM</a>
            <a class="mom-nav-tab is-active" href="{{ route('mom.list') }}" aria-current="page"><i class="fa fa-list-alt"></i>
                Daftar MoM</a>
        </nav>

        <h5 class="mb-3" style="font-weight:700">Daftar Minutes of Meeting</h5>
        @if (session('success'))
            <div class="alert alert-success"><i class="fa fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        <div class="mom-toolbar">
            <a class="btn btn-primary btn-sm" href="{{ route('mom.index') }}"><i class="fa fa-plus"></i> MoM Baru</a>
            <span class="small text-muted">Data dari database — tanpa sinkronisasi otomatis</span>
        </div>

        <div class="mom-table-wrap">
            <table class="table mom-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Subject</th>
                        <th>Divisi</th>
                        <th>Recorded By</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="momListRows">
                    @forelse($moms as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->meeting_date?->format('d/m/Y') }}</td>
                            <td>{{ $item->subject }}</td>
                            <td>{{ $item->divisi ?: '—' }}</td>
                            <td>{{ $item->write_by }}</td>
                            <td>
                                @if ($item->status === 'draft')
                                    <span class="badge badge-warning bg-warning text-dark">Draft</span>
                                @else
                                    <span class="badge badge-success bg-success">{{ ucfirst($item->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="mom-actions">
                                    <button type="button" class="btn btn-sm btn-success mom-download-btn"
                                        data-url="{{ route('mom.download', $item) }}"
                                        data-filename="Mom-{{ \Illuminate\Support\Str::slug($item->subject ?: 'tanpa-subject') }}-{{ $item->meeting_date?->format('d-m-Y') ?? 'tanpa-tanggal' }}-newwicker.id.pdf"
                                        title="Download PDF MoM #{{ $item->id }}">
                                        <i class="fa fa-download" aria-hidden="true"></i><span>Download</span>
                                    </button>
                                    <a href="{{ route('mom.edit', $item) }}" class="btn btn-sm btn-primary"><i
                                            class="fa fa-edit"></i> Edit</a>
                                    <form method="POST" action="{{ route('mom.destroy', $item) }}" class="mom-delete-form"
                                        data-id="{{ $item->id }}" data-subject="{{ $item->subject }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i>
                                            Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada data Minutes of Meeting.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $moms->links() }}</div>
    </div>

    <div id="momPdfLoading" aria-hidden="true" role="status" aria-live="polite">
        <div class="mom-loading-panel">
            <div class="mom-loading-spinner"></div>
            <div style="font-weight:700;font-size:16px;margin-bottom:5px">Menyiapkan PDF MoM...</div>
            <div class="text-muted" style="font-size:13px">Mohon tunggu hingga proses download dimulai.</div>
        </div>
    </div>

    <script>
        (function() {
            'use strict';

            document.querySelectorAll('#momList .mom-delete-form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const id = this.dataset.id || '';
                    const subject = this.dataset.subject || '';
                    if (!window.confirm('Hapus MoM #' + id + ' — ' + subject +
                            '?\nData tidak dapat dikembalikan.')) {
                        e.preventDefault();
                        return;
                    }
                    const button = this.querySelector('button[type="submit"]');
                    if (button) {
                        button.disabled = true;
                        button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Deleting...';
                    }
                });
            });

            // Satu mekanisme download: navigasi browser (kompatibel dengan IDM).
            // Tidak menggunakan fetch, Blob, atau iframe tersembunyi.
            let downloading = false;
            document.addEventListener('click', function(event) {
                const btn = event.target.closest('#momList .mom-download-btn');
                if (!btn || btn.disabled || downloading) return;
                event.preventDefault();

                const url = btn.dataset.url;
                if (!url) {
                    window.alert('URL download PDF tidak ditemukan.');
                    return;
                }

                const oldHTML = btn.innerHTML;
                const overlay = document.getElementById('momPdfLoading');
                downloading = true;
                btn.disabled = true;
                btn.setAttribute('aria-busy', 'true');
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Menyiapkan...';
                if (overlay) {
                    overlay.style.display = 'flex';
                    overlay.setAttribute('aria-hidden', 'false');
                }

                // Navigasi langsung mengirim satu request ke route download.
                // Browser/IDM menangani PDF tanpa fetch kedua.
                window.location.assign(url);

                // Browser tidak memberi sinyal selesai saat IDM mengambil alih.
                // Timer hanya mereset indikator UI, bukan status keberhasilan PDF.
                window.setTimeout(function() {
                    downloading = false;
                    btn.disabled = false;
                    btn.removeAttribute('aria-busy');
                    btn.innerHTML = oldHTML;
                    if (overlay) {
                        overlay.style.display = 'none';
                        overlay.setAttribute('aria-hidden', 'true');
                    }
                }, 3500);
            });
        })();
    </script>
@endsection
