@extends('master.master')

@section('content')
    <div class="container-fluid py-3" id="momList">
        <style>
            #momList .mom-nav-tabs {
                display: flex;
                gap: 5px;
                border-bottom: 2px solid #d8e4f4;
                margin-bottom: 20px;
                overflow-x: auto
            }

            #momList .mom-nav-tab {
                display: inline-flex;
                align-items: center;
                gap: 9px;
                padding: 12px 19px;
                text-decoration: none !important;
                white-space: nowrap;
                font-size: 14px;
                font-weight: 700;
                color: #536987 !important;
                border: 1px solid transparent;
                border-bottom: 3px solid transparent;
                border-radius: 9px 9px 0 0
            }

            #momList .mom-nav-tab:hover {
                color: #1559be !important;
                background: #eaf1fc
            }

            #momList .mom-nav-tab.is-active {
                background: #fff;
                color: #1258c5 !important;
                border-color: #d8e4f4;
                border-bottom-color: #1760d5
            }

            #momList .mom-actions {
                display: flex;
                align-items: center;
                gap: 6px;
                white-space: nowrap
            }

            #momList .mom-actions form {
                margin: 0
            }

            #momList .mom-actions .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 5px
            }

            #momList .mom-table th,
            #momList .mom-table td {
                vertical-align: middle
            }

            #momList .mom-table th:last-child {
                min-width: 235px
            }

            #momList .mom-download-btn[disabled] {
                cursor: wait;
                opacity: .75
            }

            @media(max-width:560px) {
                #momList .mom-nav-tab {
                    flex: 1;
                    justify-content: center;
                    padding: 11px 12px
                }
            }
        </style>
        <nav class="mom-nav-tabs" aria-label="Navigasi Minutes of Meeting">
            <a class="mom-nav-tab" href="{{ route('mom.index') }}"><i class="fa fa-edit" aria-hidden="true"></i> Form MoM</a>
            <a class="mom-nav-tab is-active" href="{{ route('mom.list') }}" aria-current="page"><i class="fa fa-list-alt"
                    aria-hidden="true"></i> Daftar MoM</a>
        </nav>
        <h5 class="mb-3">Daftar Minutes of Meeting</h5>
        @if (session('success'))
            <div class="alert alert-success" role="alert"><i class="fa fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" role="alert"><i class="fa fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <a class="btn btn-primary btn-sm" href="{{ route('mom.index') }}"><i class="fa fa-plus"></i> MoM Baru</a>
            <span class="small text-muted">Data dari database — tanpa sinkronisasi otomatis</span>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mom-table">
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
                            <td>{{ $item->divisi }}</td>
                            <td>{{ $item->write_by }}</td>
                            <td>
                                @if ($item->status === 'draft')
                                <span class="badge badge-warning">Draft</span>@else<span
                                        class="badge badge-success">{{ ucfirst($item->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="mom-actions">
                                    <button type="button" class="btn btn-sm btn-success mom-download-btn"
                                        data-url="{{ route('mom.download', $item) }}"
                                        data-filename="Mom-{{ \Illuminate\Support\Str::slug($item->subject ?: 'tanpa-subject') }}-{{ $item->meeting_date?->format('d-m-Y') ?? 'tanpa-tanggal' }}-newwicker.id.pdf"
                                        title="Download PDF MoM #{{ $item->id }}"><i class="fa fa-download"
                                            aria-hidden="true"></i><span>Download</span></button>
                                    <a href="{{ route('mom.edit', $item) }}" class="btn btn-sm btn-primary"><i
                                            class="fa fa-edit" aria-hidden="true"></i> Edit</a>
                                    <form method="POST" action="{{ route('mom.destroy', $item) }}" class="mom-delete-form"
                                        data-id="{{ $item->id }}" data-subject="{{ $item->subject }}">@csrf
                                        @method('DELETE')<button type="submit" class="btn btn-sm btn-danger"><i
                                                class="fa fa-trash" aria-hidden="true"></i> Delete</button></form>
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

    <script>
        (function() {
            'use strict';

            // ==========================================
            // 1. DELETE MOM
            // ==========================================
            document.querySelectorAll('.mom-delete-form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const id = this.dataset.id || '';
                    const subject = this.dataset.subject || '';

                    const msg =
                        'Hapus MoM #' + id + ' — ' + subject +
                        '?\nData tidak dapat dikembalikan.';

                    if (!window.confirm(msg)) {
                        e.preventDefault();
                        return;
                    }

                    const button = this.querySelector('button[type="submit"]');

                    if (button) {
                        button.disabled = true;
                        button.innerHTML =
                            '<i class="fa fa-spinner fa-spin"></i> Deleting...';
                    }
                });
            });

            // ==========================================
            // 2. DOWNLOAD PDF MOM
            // ==========================================
            document.addEventListener('click', async function(event) {
                const btn = event.target.closest('.mom-download-btn');

                if (!btn || btn.disabled) return;

                const url = btn.dataset.url;
                const filename = btn.dataset.filename || 'Mom-newwicker.id.pdf';
                const originalHTML = btn.innerHTML;

                if (!url) {
                    window.alert('URL download PDF tidak ditemukan.');
                    return;
                }

                // Aktifkan loading
                btn.disabled = true;
                btn.setAttribute('aria-busy', 'true');

                btn.innerHTML =
                    '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> ' +
                    '<span>Downloading...</span>';

                try {
                    const response = await fetch(url, {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/pdf'
                        },
                        cache: 'no-store'
                    });
                    if (response.status === 204) {
    console.info('MoM download: server mengembalikan HTTP 204.');
    return;
}

                    // Ambil response sebagai Blob
                    const blob = await response.blob();

                    // Validasi berdasarkan signature PDF,
                    // bukan hanya Content-Type.
                    const signature = await blob.slice(0, 5).text();

                    if (!response.ok || signature !== '%PDF-') {
                        const errorBody = await blob.text();

                        console.error('MoM PDF Download Error:', {
                            url: url,
                            status: response.status,
                            contentType: response.headers.get('content-type'),
                            response: errorBody.substring(0, 3000)
                        });

                        let message = 'Gagal mengunduh PDF.';

                        if (!response.ok) {
                            message += ' HTTP ' + response.status + '.';
                        } else {
                            message += ' Server tidak mengembalikan file PDF.';
                        }

                        message += ' Periksa Console browser untuk detail.';

                        throw new Error(message);
                    }

                    // ==========================================
                    // 3. TRIGGER DOWNLOAD
                    // ==========================================
                    const objectUrl = URL.createObjectURL(blob);

                    const link = document.createElement('a');
                    link.href = objectUrl;
                    link.download = filename;

                    document.body.appendChild(link);
                    link.click();
                    link.remove();

                    // Beri waktu browser memulai download
                    setTimeout(function() {
                        URL.revokeObjectURL(objectUrl);
                    }, 60000);

                } catch (error) {
                    console.error('Download MoM gagal:', error);

                    window.alert(
                        error.message || 'Terjadi kesalahan saat download PDF.'
                    );

                } finally {
                    // Reset tombol setelah proses selesai/gagal
                    btn.disabled = false;
                    btn.removeAttribute('aria-busy');
                    btn.innerHTML = originalHTML;
                }
            });

        })();
    </script>
@endsection
