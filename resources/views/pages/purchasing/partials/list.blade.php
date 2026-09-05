            <div class="submission-list-section">
                <div class="submission-list-toolbar">
                    <div>
                        <div class="table-title" style="margin-bottom:2px;">List Pengajuan</div>
                        <div style="font-size:11px;color:#7b8794;">Daftar pengajuan purchasing yang tersimpan.</div>
                    </div>
                    <input type="text" id="submissionSearch" class="submission-search"
                           placeholder="Cari No / Departemen / Status...">
                </div>

                <div class="submission-table-wrapper">
                    <table class="submission-table">
                        <thead>
                            <tr>
                                <th style="width:45px;">No</th>
                                <th>ID</th>
                                <th>Tanggal</th>
                                <th>Departemen</th>
                                <th>Pembuat</th>
                                <th>Jumlah Item</th>
                                <th>Status</th>
                                <th>draft</th>
                                <th style="width:80px;">Aksi</th>
                            </tr>
                        </thead>
                    <tbody id="submissionListBody">
    @forelse(($pengajuans ?? collect()) as $index => $pengajuan)

        @php
            $status = strtolower((string)($pengajuan->status ?? 'pending'));

            $statusClass =
                str_contains($status, 'reject')
                    ? 'status-rejected'
                    : (
                        str_contains($status, 'approv')
                            ? 'status-approved'
                            : 'status-pending'
                    );

            $itemCount = isset($pengajuan->divisiItems)
                ? $pengajuan->divisiItems->count()
                : 0;

            $tanggal = optional($pengajuan->meta)->tanggal
                ?? $pengajuan->created_at;

            $divisiName =
                optional($pengajuan->divisi)->nama
                ?? optional($pengajuan->divisi)->name
                ?? ($pengajuan->divisi_id ?? '-');

            $creator = optional($pengajuan->user)->name ?? '-';

            // NILAI DRAFT DARI DATABASE
            $isDraft = (int) ($pengajuan->is_draft ?? 0);
        @endphp

        <tr class="submission-row"
            data-search="{{ strtolower(
                $pengajuan->id . ' ' .
                $divisiName . ' ' .
                $creator . ' ' .
                $status . ' ' .
                $isDraft
            ) }}">

            {{-- NO --}}
            <td class="text-center">
                {{ $index + 1 }}
            </td>

            {{-- ID --}}
            <td>
                <b>#{{ $pengajuan->id }}</b>
            </td>

            {{-- TANGGAL --}}
            <td>
                {{ $tanggal
                    ? \Carbon\Carbon::parse($tanggal)->format('d/m/Y')
                    : '-'
                }}
            </td>

            {{-- DEPARTEMEN --}}
            <td>
                {{ $divisiName }}
            </td>

            {{-- PEMBUAT --}}
            <td>
                {{ $creator }}
            </td>

            {{-- JUMLAH ITEM --}}
            <td class="text-center">
                {{ $itemCount }}
            </td>

            {{-- STATUS --}}
            <td>
                <span class="status-badge {{ $statusClass }}">
                    {{ strtoupper($status) }}
                </span>
            </td>

            {{-- IS DRAFT --}}
            <td class="text-center">
                @if($isDraft === 1)
                    <span class="status-badge status-pending">
                        1
                    </span>
                @else
                    <span class="status-badge status-approved">
                        0
                    </span>
                @endif
            </td>
            {{-- AKSI --}}
            <td class="text-center">
                @php
                    $rowCanEdit =
                        (int) $pengajuan->user_id === (int) auth()->id();
                @endphp

                {{-- Semua user boleh membuka detail.
                     Pembuat = Edit.
                     User lain / penanda tangan = View. --}}
                <button type="button"
                        class="btn-view-submission"
                        data-id="{{ $pengajuan->id }}"
                        data-view-only="{{ $rowCanEdit ? '0' : '1' }}"
                        title="{{ $rowCanEdit
                            ? 'Edit pengajuan'
                            : 'Lihat detail dan proses tanda tangan' }}">

                    <i class="fa {{ $rowCanEdit ? 'fa-edit' : 'fa-eye' }}"></i>
                    {{ $rowCanEdit ? 'Edit' : 'View' }}

                </button>

                {{-- Hanya pembuat yang dapat Publish --}}
                @if($rowCanEdit && $isDraft === 0)
                    <button type="button"
                            class="btn-publish-submission"
                            data-id="{{ $pengajuan->id }}"
                            title="Publish pengajuan">
                        <i class="fa fa-paper-plane"></i>
                        Publish
                    </button>
                @elseif($isDraft === 1)
                    <span class="publish-done"
                          title="Pengajuan sudah dipublish">
                        <i class="fa fa-check-circle"></i>
                        Published
                    </span>
                @endif
            </td>

        </tr>

    @empty

        <tr>
            <td colspan="9" class="submission-empty">
                <i class="fa fa-folder-open"
                   style="font-size:28px;display:block;margin-bottom:8px;">
                </i>

                Belum ada pengajuan.
            </td>
        </tr>

    @endforelse
</tbody>
                    </table>
                </div>
            </div>

<style>
    .btn-view-submission,
    .btn-publish-submission {
        border: 1px solid #d7dee8;
        background: #fff;
        border-radius: 5px;
        padding: 4px 7px;
        font-size: 10px;
        cursor: pointer;
        margin: 1px;
        white-space: nowrap;
    }

    .btn-view-submission:hover,
    .btn-publish-submission:hover {
        background: #f5f8fb;
    }

    .btn-publish-submission {
        color: #1769aa;
    }

    .btn-publish-submission:disabled {
        opacity: .6;
        cursor: not-allowed;
    }

    .publish-done {
        display: inline-block;
        color: #18864b;
        font-size: 10px;
        font-weight: 600;
        white-space: nowrap;
        margin: 1px;
    }
</style>

<script>
(function () {

    // SEMUA USER BOLEH MEMBUKA DETAIL.
    $(document).off('click.purchasingView', '.btn-view-submission');

    $(document).on('click.purchasingView', '.btn-view-submission', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const id = $(this).data('id');
        const viewOnly = String($(this).data('view-only')) === '1';

        if (!id) {
            alert('ID pengajuan tidak ditemukan.');
            return;
        }

        let url = "{{ url('/pengajuan_purchasing/edit') }}/" + id;

        // User selain pembuat masuk sebagai detail/view untuk proses tanda tangan.
        if (viewOnly) {
            url += '?view_only=1';
        }

        window.location.href = url;
    });


    // PUBLISH: is_draft 0 -> 1
    $(document).off('click.purchasingPublish', '.btn-publish-submission');

    $(document).on('click.purchasingPublish', '.btn-publish-submission', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const button = $(this);
        const id = button.data('id');

        if (!id) {
            alert('ID pengajuan tidak ditemukan.');
            return;
        }

        if (!confirm(
            'Publish pengajuan #' + id + '\n\n' +
            'Setelah OK, status draft akan berubah dari 0 menjadi 1.'
        )) {
            return;
        }

        button
            .prop('disabled', true)
            .html('<i class="fa fa-spinner fa-spin"></i> Publishing...');

        $.ajax({
            url: "{{ route('pengajuan_purchasing.publish', ['id' => '__ID__']) }}"
                .replace('__ID__', id),
            type: 'POST',
            dataType: 'json',
            data: {
                _token: "{{ csrf_token() }}"
            },

            success: function (response) {
                if (response.success) {
                    alert(response.message || 'Pengajuan berhasil dipublish.');

                    const row = button.closest('tr');

                    // Kolom draft: 0 -> 1
                    row.find('td').eq(7).html(
                        '<span class="status-badge status-pending">1</span>'
                    );

                    button.replaceWith(
                        '<span class="publish-done">' +
                            '<i class="fa fa-check-circle"></i> Published' +
                        '</span>'
                    );
                } else {
                    alert(response.message || 'Gagal publish pengajuan.');

                    button
                        .prop('disabled', false)
                        .html('<i class="fa fa-paper-plane"></i> Publish');
                }
            },

            error: function (xhr) {
                console.error(
                    'PUBLISH ERROR:',
                    xhr.responseJSON || xhr.responseText
                );

                let message = 'Gagal publish pengajuan.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                alert(message);

                button
                    .prop('disabled', false)
                    .html('<i class="fa fa-paper-plane"></i> Publish');
            }
        });
    });

})();
</script>