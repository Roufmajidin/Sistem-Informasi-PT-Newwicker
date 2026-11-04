
@extends('master.master')

@section('content')
<div class="container-fluid py-3" id="momList">

    <style>
        #momList .mom-nav-tabs {
            display: flex;
            gap: 5px;
            border-bottom: 2px solid #d8e4f4;
            margin-bottom: 20px;
            overflow-x: auto;
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
            border-radius: 9px 9px 0 0;
        }

        #momList .mom-nav-tab:hover {
            color: #1559be !important;
            background: #eaf1fc;
        }

        #momList .mom-nav-tab.is-active {
            background: #fff;
            color: #1258c5 !important;
            border-color: #d8e4f4;
            border-bottom-color: #1760d5;
        }

        #momList .mom-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        #momList .mom-actions form {
            margin: 0;
        }

        #momList .mom-actions .btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        #momList .mom-table th,
        #momList .mom-table td {
            vertical-align: middle;
        }

        #momList .mom-table th:last-child {
            min-width: 160px;
        }

        @media (max-width: 560px) {
            #momList .mom-nav-tab {
                flex: 1;
                justify-content: center;
                padding: 11px 12px;
            }
        }
    </style>

    {{-- NAVIGATION TABS --}}
    <nav class="mom-nav-tabs"
         aria-label="Navigasi Minutes of Meeting">

        <a class="mom-nav-tab"
           href="{{ route('mom.index') }}">
            <i class="fa fa-edit" aria-hidden="true"></i>
            Form MoM
        </a>

        <a class="mom-nav-tab is-active"
           href="{{ route('mom.list') }}"
           aria-current="page">
            <i class="fa fa-list-alt" aria-hidden="true"></i>
            Daftar MoM
        </a>
    </nav>

    {{-- TITLE --}}
    <h5 class="mb-3">Daftar Minutes of Meeting</h5>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success" role="alert">
            <i class="fa fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- ERROR MESSAGE --}}
    @if(session('error'))
        <div class="alert alert-danger" role="alert">
            <i class="fa fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- TOOLBAR --}}
    <div class="d-flex justify-content-between
                align-items-center flex-wrap gap-2 mb-3">

        <a class="btn btn-primary btn-sm"
           href="{{ route('mom.index') }}">
            <i class="fa fa-plus"></i>
            MoM Baru
        </a>

        <span id="momListNetwork"
              class="small text-muted text-end"
              role="status">
            Memeriksa koneksi server...
        </span>
    </div>

    {{-- TABLE --}}
    <div class="table-responsive">
        <table class="table table-bordered table-striped
                      align-middle mom-table">

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

                        <td>
                            {{ $item->meeting_date?->format('d/m/Y') }}
                        </td>

                        <td>{{ $item->subject }}</td>

                        <td>{{ $item->divisi }}</td>

                        <td>{{ $item->write_by }}</td>

                        <td>
                            @if($item->status === 'draft')
                                <span class="badge badge-warning">
                                    Draft
                                </span>
                            @else
                                <span class="badge badge-success">
                                    {{ ucfirst($item->status) }}
                                </span>
                            @endif
                        </td>

                        <td>
                            <div class="mom-actions">

                                {{-- EDIT --}}
                                <a href="{{ route('mom.edit', $item) }}"
                                   class="btn btn-sm btn-primary">
                                    <i class="fa fa-edit"
                                       aria-hidden="true"></i>
                                    Edit
                                </a>

                                {{-- DELETE --}}
                                <form method="POST"
                                      action="{{ route('mom.destroy', $item) }}"
                                      class="mom-delete-form"
                                      data-id="{{ $item->id }}"
                                      data-subject="{{ $item->subject }}">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-sm btn-danger">
                                        <i class="fa fa-trash"
                                           aria-hidden="true"></i>
                                        Delete
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7"
                            class="text-center text-muted py-4">
                            Belum ada data Minutes of Meeting.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-3">
        {{ $moms->links() }}
    </div>

</div>

<script>
(() => {
    'use strict';

    const status = document.getElementById('momListNetwork');
    const rows = document.getElementById('momListRows');

    const key = @json(
        'newwicker_mom_list_' .
        auth()->id() . '_' .
        $moms->currentPage()
    );

    // Preserve existing local cache behavior
    try {
        localStorage.setItem(key, rows.innerHTML);
    } catch (e) {}

    // ==========================================
    // DELETE CONFIRMATION
    // ==========================================
    document.querySelectorAll('.mom-delete-form')
        .forEach(form => {

            form.addEventListener('submit', function(e) {

                const id = this.dataset.id || '';
                const subject = this.dataset.subject || 'MoM';

                const message =
                    'Are you sure?\n\n' +
                    'Apakah Anda yakin ingin menghapus MoM ini?\n\n' +
                    'ID: ' + id + '\n' +
                    'Subject: ' + subject + '\n\n' +
                    'Data yang dihapus tidak dapat dikembalikan.';

                const confirmed = window.confirm(message);

                if (!confirmed) {
                    e.preventDefault();
                    return;
                }

                const button = this.querySelector(
                    'button[type="submit"]'
                );

                if (button) {
                    button.disabled = true;
                    button.innerHTML =
                        '<i class="fa fa-spinner fa-spin"></i> Deleting...';
                }
            });
        });

    // ==========================================
    // NETWORK STATUS
    // ==========================================
    let checking = false;

    function setStatus(message, cls) {
        if (!status) return;

        status.textContent = message;

        status.classList.remove(
            'text-muted',
            'text-success',
            'text-danger',
            'text-warning'
        );

        status.classList.add(cls);
    }

    async function check() {
        if (checking) return;

        if (!navigator.onLine) {
            setStatus(
                'Offline — data mungkin tidak terbaru',
                'text-warning'
            );
            return;
        }

        checking = true;

        setStatus(
            'Memeriksa koneksi server...',
            'text-muted'
        );

        try {
            const url =
                @json(route('mom.list')) +
                '?page=' +
                @json($moms->currentPage());

            const response = await fetch(url, {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            const result = await response.json();

            setStatus(
                'Online — server terhubung · ' +
                result.total +
                ' data',
                'text-success'
            );

        } catch (e) {
            setStatus(
                'Server tidak dapat dihubungi / koneksi lemah',
                'text-danger'
            );

        } finally {
            checking = false;
        }
    }

    window.addEventListener('online', check);

    window.addEventListener('offline', () => {
        setStatus(
            'Offline — data mungkin tidak terbaru',
            'text-warning'
        );
    });

    check();

})();
</script>
@endsection
