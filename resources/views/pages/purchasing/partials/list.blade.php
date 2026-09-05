
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
                                <th style="width:80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="submissionListBody">
                            @forelse(($pengajuans ?? collect()) as $index => $pengajuan)
                                @php
                                    $status = strtolower((string)($pengajuan->status ?? 'pending'));
                                    $statusClass = str_contains($status, 'reject') ? 'status-rejected' : (str_contains($status, 'approv') ? 'status-approved' : 'status-pending');
                                    $itemCount = isset($pengajuan->divisiItems) ? $pengajuan->divisiItems->count() : 0;
                                    $tanggal = optional($pengajuan->meta)->tanggal ?? $pengajuan->created_at;
                                    $divisiName = optional($pengajuan->divisi)->nama
    ?? optional($pengajuan->divisi)->name
    ?? ($pengajuan->divisi_id ?? '-');
                                    $creator = optional($pengajuan->user)->name ?? '-';
                                @endphp
                                <tr class="submission-row"
                                    data-search="{{ strtolower($pengajuan->id.' '.$divisiName.' '.$creator.' '.$status) }}">
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td><b>#{{ $pengajuan->id }}</b></td>
                                    <td>{{ $tanggal ? \Carbon\Carbon::parse($tanggal)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $divisiName }}</td>
                                    <td>{{ $creator }}</td>
                                    <td class="text-center">{{ $itemCount }}</td>
                                    <td><span class="status-badge {{ $statusClass }}">{{ strtoupper($status) }}</span></td>
                                    <td class="text-center">
                                        @php
                                            $rowCanEdit = (int) $pengajuan->user_id === (int) auth()->id();
                                        @endphp
                                        <button type="button"
                                                class="btn-view-submission"
                                                data-id="{{ $pengajuan->id }}"
                                                {{ !$rowCanEdit ? 'disabled' : '' }}
                                                title="{{ $rowCanEdit ? 'Edit pengajuan' : 'Hanya pembuat pengajuan yang dapat mengedit' }}">
                                            <i class="fa {{ $rowCanEdit ? 'fa-edit' : 'fa-lock' }}"></i>
                                            {{ $rowCanEdit ? 'Edit' : 'View Only' }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="submission-empty">
                                        <i class="fa fa-folder-open" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                        Belum ada pengajuan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>