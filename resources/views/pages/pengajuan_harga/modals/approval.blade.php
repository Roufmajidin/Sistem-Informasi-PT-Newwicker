@php
    $header = $pengajuan->pengajuanHarga;
    $photo = $header?->image;
    $photoUrl = $photo
        ? (preg_match('~^https?://~i', $photo)
            ? $photo
            : asset('storage/' . ltrim(preg_replace('~^/?storage/~', '', $photo), '/')))
        : null;
    $details = $pengajuan->details ?? collect();
    $subtotal = $details->sum(fn($d) => (float) $d->qty * (float) $d->harga);
    $status = $pengajuan->status ?? 'draft';
    $isFactory = str_starts_with(strtolower(trim((string) (auth()->user()?->email ?? ''))), 'factory@');
    $canApprove = $isFactory && in_array($status, ['pending', 'revision'], true);
    $isOwner = (int) $header?->user_id === (int) auth()->id();
    $canDelete = ($isOwner || $isFactory) && $status !== 'approved';
    $canEdit = $isOwner && $status !== 'approved';
    $statusColor =
        [
            'approved' => 'success',
            'pending' => 'warning',
            'revision' => 'info',
            'rejected' => 'danger',
            'draft' => 'secondary',
        ][$status] ?? 'secondary';
@endphp
<div class="modal fade" id="approvalModal{{ $pengajuan->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="width:96vw;max-width:96vw">
        <div class="modal-content" style="max-height:95vh">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">{{ $canEdit ? 'Edit Pengajuan Harga' : 'Detail / Approval Pengajuan Harga' }}
                        — {{ $header?->article_nr ?? '-' }}</h5>
                    <small class="text-muted">{{ $pengajuan->kategori?->name }} ·
                        {{ $pengajuan->subBahan?->name }}</small>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto ph-export-actions">
                    <button type="button" class="btn btn-sm btn-outline-primary ph-export-image" data-format="png"
                        title="Export detail sebagai PNG">⬇ PNG</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary ph-export-image" data-format="jpeg"
                        title="Export detail sebagai JPEG">⬇ JPEG</button>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-3">
                <div class="ph-approval-layout">
                    <section class="border rounded p-3 bg-white">
                        <h6 class="fw-bold mb-3">01 —
                            {{ $canEdit ? 'Edit Detail Produk & Pembahanan' : 'Detail Pengajuan' }}</h6>
                        @if ($canEdit)
                            <div class="alert alert-info small py-2">Anda adalah writer pengajuan ini. Seluruh field di
                                bawah dapat diedit seperti form Create. Setelah disimpan, status menjadi
                                <strong>Draft</strong> dan perlu dikirim ulang untuk approval.
                            </div>
                            <form id="phEditForm{{ $pengajuan->id }}" method="POST"
                                action="{{ route('pengajuan-harga.update', $pengajuan->id) }}" class="ph-edit-form"
                                data-ph-edit="{{ $pengajuan->id }}" enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <div class="row g-2 mb-3">
                                    <div class="col-md-4">
                                        <div class="border rounded bg-light d-flex justify-content-center align-items-center"
                                            style="height:160px">
                                            <img id="phEditPhoto{{ $pengajuan->id }}" src="{{ $photoUrl ?: '' }}"
                                                data-original-src="{{ $photoUrl ?: '' }}" alt="Foto produk"
                                                style="max-height:150px;max-width:100%;object-fit:contain;border-radius:3px!important;{{ $photoUrl ? '' : 'display:none' }}"><small
                                                id="phEditNoPhoto{{ $pengajuan->id }}" class="text-muted"
                                                style="{{ $photoUrl ? 'display:none' : '' }}">Foto tidak
                                                tersedia</small>
                                        </div>
                                        <div class="ph-photo-upload mt-2"
                                            data-preview="phEditPhoto{{ $pengajuan->id }}"
                                            data-placeholder="phEditNoPhoto{{ $pengajuan->id }}">
                                            <input type="file" name="image_file"
                                                accept="image/png,image/jpeg,image/webp,image/gif"
                                                class="ph-photo-input d-none">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary ph-photo-browse">Browse
                                                Foto</button>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-secondary ph-photo-clear">Batalkan Foto
                                                Baru</button>
                                            <div class="ph-photo-paste border p-2 mt-2 small text-muted" tabindex="0"
                                                role="button">Klik lalu Ctrl+V untuk paste foto, atau
                                                drag & drop</div>
                                            <small class="ph-photo-error text-danger d-block"></small>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="row g-2">
                                            <div class="col-md-8"><label class="form-label small mb-1">Nama
                                                    Item</label><input name="item_name"
                                                    class="form-control form-control-sm"
                                                    value="{{ $header?->item_name }}" required></div>
                                            <div class="col-md-4"><label class="form-label small mb-1">Article
                                                    Nr.</label><input name="article_nr"
                                                    class="form-control form-control-sm"
                                                    value="{{ $header?->article_nr }}" required></div>
                                            <div class="col-md-6"><label
                                                    class="form-label small mb-1">Kategori</label><select
                                                    name="kategori_id"
                                                    class="form-select form-select-sm ph-edit-category" required>
                                                    @foreach ($kategoriList as $kat)
                                                        <option value="{{ $kat->id }}"
                                                            @selected((int) $pengajuan->kategori_id === (int) $kat->id)>{{ $kat->name }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="col-md-6"><label class="form-label small mb-1">Sub
                                                    Bahan</label><select name="sub_bahan_id"
                                                    class="form-select form-select-sm ph-edit-sub" required>
                                                    @foreach ($subBahanList as $sb)
                                                        <option value="{{ $sb->id }}"
                                                            data-kategori="{{ $sb->bahan_kategori_id }}"
                                                            @selected((int) $pengajuan->sub_bahan_id === (int) $sb->id)>{{ $sb->name }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="col-md-6"><label class="form-label small mb-1">Tujuan /
                                                    Supplier</label><input name="sub_name"
                                                    class="form-control form-control-sm ph-edit-supplier"
                                                    list="phSupplierSuggestions{{ $pengajuan->id }}" autocomplete="off"
                                                    value="{{ $header?->sub_name }}" required><datalist
                                                    id="phSupplierSuggestions{{ $pengajuan->id }}"></datalist></div>
                                            <div class="col-md-6"><label class="form-label small mb-1">Dimensi W × D × H
                                                    (cm)</label><input name="wdh"
                                                    class="form-control form-control-sm" value="{{ $header?->wdh }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle ph-edit-table">
                                        <thead>
                                            <tr class="ph-dark-heading">
                                                <th colspan="6" class="ph-edit-sub-heading"
                                                    style="padding:10px 12px;text-transform:uppercase">
                                                    {{ mb_strtoupper($pengajuan->subBahan?->name ?? 'SUB BAHAN') }}
                                                </th>
                                            </tr>
                                            <tr class="ph-dark-heading">
                                                <th style="min-width:180px">Nama Bahan</th>
                                                <th style="width:85px">Qty</th>
                                                <th style="width:85px">Unit</th>
                                                <th style="width:125px">Harga</th>
                                                <th style="width:130px">Total</th>
                                                <th style="width:42px"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="ph-edit-rows">
                                            @foreach ($details as $i => $d)
                                                <tr>
                                                    <td class="ph-edit-material-cell"><input
                                                            class="form-control form-control-sm ph-edit-material-name"
                                                            name="details[{{ $i }}][nama_bahan]"
                                                            value="{{ $d->nama_bahan }}" autocomplete="off" required>
                                                        <div class="ph-edit-material-results"></div>
                                                    </td>
                                                    <td><input class="form-control form-control-sm ph-edit-qty"
                                                            type="number" min="0.001" step="any"
                                                            name="details[{{ $i }}][qty]"
                                                            value="{{ $d->qty }}" required></td>
                                                    <td><input class="form-control form-control-sm"
                                                            name="details[{{ $i }}][unit]"
                                                            value="{{ $d->unit }}" required></td>
                                                    <td><input class="form-control form-control-sm ph-edit-price"
                                                            type="number" min="0" step="any"
                                                            name="details[{{ $i }}][harga]"
                                                            value="{{ $d->harga }}" required></td>
                                                    <td class="text-end ph-edit-line">Rp
                                                        {{ number_format((float) $d->qty * (float) $d->harga, 0, ',', '.') }}
                                                    </td>
                                                    <td><button type="button"
                                                            class="btn btn-sm btn-outline-danger ph-edit-remove"
                                                            title="Hapus baris">×</button></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="ph-subtotal">
                                                <th colspan="4">TOTAL</th>
                                                <th class="text-end ph-edit-total">Rp
                                                    {{ number_format($subtotal, 0, ',', '.') }}</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary ph-edit-add">+ Tambah
                                    Baris</button>
                                <div class="mt-3"><label class="form-label small mb-1">Remark Writer / Catatan
                                        Pengaju</label>
                                    <textarea name="remark" class="form-control" rows="2">{{ $header?->remark }}</textarea>
                                </div>
                            </form>
                        @else
                            @if ($isOwner && $status === 'approved')
                                <div class="alert alert-success py-2 small">Pengajuan sudah Approved. Data writer
                                    terkunci dan tidak dapat diedit.</div>
                            @endif
                            <div class="row g-3 mb-3">
                                <div class="col-lg-4">
                                    <div class="border rounded bg-light d-flex justify-content-center align-items-center"
                                        style="height:180px">
                                        @if ($photoUrl)
                                            <img src="{{ $photoUrl }}" alt="Foto produk"
                                            style="max-height:170px;max-width:100%;object-fit:contain;border-radius:3px!important">@else<small
                                                class="text-muted">Foto belum tersedia</small>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <table class="table table-sm table-bordered">
                                        <tbody>
                                            <tr>
                                                <th>Nama Item</th>
                                                <td>{{ $header?->item_name ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Article Nr.</th>
                                                <td>{{ $header?->article_nr ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Kategori</th>
                                                <td>{{ $pengajuan->kategori?->name ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Tujuan / Supplier</th>
                                                <td>{{ $header?->sub_name ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Dimensi</th>
                                                <td>{{ $header?->wdh ?? '-' }} cm</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle">
                                    <thead>
                                        <tr class="ph-dark-heading">
                                            <th colspan="5" style="padding:10px 12px;text-transform:uppercase">
                                                {{ mb_strtoupper($pengajuan->subBahan?->name ?? 'SUB BAHAN') }}</th>
                                        </tr>
                                        <tr class="ph-dark-heading">
                                            <th>Nama Bahan</th>
                                            <th>Qty</th>
                                            <th>Unit</th>
                                            <th>Harga</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($details as $d)
                                            <tr>
                                                <td>{{ $d->nama_bahan }}</td>
                                                <td>{{ $d->qty }}</td>
                                                <td>{{ $d->unit }}</td>
                                                <td class="text-end">Rp
                                                    {{ number_format((float) $d->harga, 0, ',', '.') }}</td>
                                                <td class="text-end">Rp
                                                    {{ number_format((float) $d->qty * (float) $d->harga, 0, ',', '.') }}
                                                </td>
                                        </tr>@empty<tr>
                                                <td colspan="5">Belum ada rincian</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr class="ph-subtotal">
                                            <th colspan="4">TOTAL</th>
                                            <th class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif
                    </section>
                    <section class="border rounded p-3" style="background:#f7f9fc">
                        <h6 class="fw-bold mb-3">02 — Approval Factory</h6>
                        <div class="mb-3">Status: <span
                                class="badge bg-{{ $statusColor }}">{{ ucfirst($status) }}</span></div>
                        <div class="mb-3"><label class="form-label">Remark Writer / Pengaju</label>
                            <div class="form-control bg-white" style="min-height:58px;white-space:pre-wrap">
                                {{ $header?->remark ?: '-' }}</div>
                        </div>
                        <form id="approvalForm{{ $pengajuan->id }}" method="POST"
                            action="{{ route('pengajuan-harga.approve', $pengajuan->id) }}">
                            @csrf
                            <label class="form-label">Harga Pengajuan</label>
                            <div class="form-control bg-light mb-3">Rp {{ number_format($subtotal, 0, ',', '.') }}
                            </div>
                            <label class="form-label">Harga Negosiasi (Rp)</label>
                            <input class="form-control mb-3" name="approve_negosiasi_harga" type="number"
                                min="0" step="0.01"
                                value="{{ old('approve_negosiasi_harga', $pengajuan->approved_total ?? round($subtotal, 2)) }}"
                                required @disabled(!$canApprove)>
                            <label class="form-label">Remark Approval / Factory</label>
                            <textarea class="form-control mb-3" name="remark_2" rows="4" @disabled(!$canApprove)>{{ old('remark_2', $header?->remark_2) }}</textarea>
                        </form>
                        @if ($status === 'draft')
                            <div class="alert alert-warning small">Kirim Draft menjadi Pending sebelum approval.</div>
                            @if ($isOwner)
                                <form method="POST" action="{{ route('pengajuan-harga.submit', $pengajuan->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100">Kirim Pengajuan
                                        (Pending)</button>
                                </form>
                            @endif
                        @elseif($canApprove)
                            <button type="submit" form="approvalForm{{ $pengajuan->id }}"
                                class="btn btn-success w-100"
                                onclick="return confirm('Setujui pengajuan ini?')">Setujui Harga
                                Negosiasi</button>
                        @elseif($status === 'approved')
                            <div class="alert alert-success small">Disetujui: Rp
                                {{ number_format((float) $pengajuan->approved_total, 0, ',', '.') }}</div>
                        @else
                            <div class="alert alert-secondary small">Hanya akun terkait yang dapat menyetujui
                                pengajuan Pending/Revision.</div>
                        @endif
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                @if ($canDelete)
                    <button type="submit" form="deletePengajuan{{ $pengajuan->id }}"
                        class="btn btn-outline-danger me-auto"
                        onclick="return confirm('Hapus pengajuan ini beserta seluruh rincian bahannya? Tindakan ini tidak bisa dibatalkan.')">Hapus
                        Pengajuan</button>
                @endif
                @if ($canEdit)
                    <button type="submit" form="phEditForm{{ $pengajuan->id }}" class="btn btn-primary">Simpan
                        Perubahan Writer</button>
                @endif
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@once
    <style>
        .ph-approval-layout {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(300px, 1fr);
            gap: 16px
        }

        .ph-approval-layout>section {
            min-width: 0
        }

        .ph-approval-layout table th,
        .ph-approval-layout table td {
            padding: 8px
        }

        .ph-dark-heading th {
            background: #26394e !important;
            color: #fff !important
        }

        .ph-subtotal th {
            background: #9bdef4 !important;
            color: #153347 !important
        }

        .ph-edit-material-cell {
            position: relative;
            min-width: 190px
        }

        .ph-edit-material-results {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            width: max(100%, 310px);
            max-width: 480px;
            max-height: 230px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #ced4da;
            box-shadow: 0 5px 18px #0002;
            z-index: 1100
        }

        .ph-edit-material-option {
            display: block;
            width: 100%;
            padding: 8px 10px;
            text-align: left;
            border: 0;
            border-bottom: 1px solid #eee;
            background: #fff;
            cursor: pointer
        }

        .ph-edit-material-option:hover,
        .ph-edit-material-option.active {
            background: #e5f0ff;
            color: #143f77
        }

        .ph-edit-material-option small {
            display: block;
            color: #667085
        }

        .ph-edit-table {
            overflow: visible
        }

        .ph-edit-form .table-responsive {
            overflow: visible
        }

        @media(max-width:900px) {
            .ph-approval-layout {
                grid-template-columns: minmax(0, 1fr)
            }
        }
    </style>
@endonce

@once
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.ph-edit-form').forEach(function(form) {
                const rows = form.querySelector('.ph-edit-rows');
                const category = form.querySelector('.ph-edit-category');
                const sub = form.querySelector('.ph-edit-sub');
                const allSubOptions = Array.from(sub.options).map(o => ({
                    id: o.value,
                    name: o.textContent,
                    kategori: o.dataset.kategori
                }));
                const money = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');

                function syncSubHeading() {
                    const heading = form.querySelector('.ph-edit-sub-heading');
                    if (heading) heading.textContent = (sub.selectedOptions[0]?.textContent || 'SUB BAHAN')
                        .toUpperCase();
                }

                function filterSubs() {
                    const old = sub.value;
                    sub.innerHTML = '';
                    allSubOptions.filter(o => o.kategori === category.value).forEach(o => sub.add(
                        new Option(o.name, o.id)));
                    if (Array.from(sub.options).some(o => o.value === old)) sub.value = old;
                    syncSubHeading();
                }
                category.addEventListener('change', filterSubs);
                sub.addEventListener('change', syncSubHeading);
                filterSubs();
                let next = rows.querySelectorAll('tr').length;

                function recalc() {
                    let total = 0;
                    rows.querySelectorAll('tr').forEach(tr => {
                        const line = (Number(tr.querySelector('.ph-edit-qty')?.value) || 0) * (
                            Number(tr.querySelector('.ph-edit-price')?.value) || 0);
                        tr.querySelector('.ph-edit-line').textContent = money(line);
                        total += line;
                    });
                    form.querySelector('.ph-edit-total').textContent = money(total);
                }
                form.querySelector('.ph-edit-add').addEventListener('click', () => {
                    const i = next++;
                    const tr = document.createElement('tr');
                    tr.innerHTML =
                        `<td class="ph-edit-material-cell"><input class="form-control form-control-sm ph-edit-material-name" name="details[${i}][nama_bahan]" autocomplete="off" required><div class="ph-edit-material-results"></div></td><td><input class="form-control form-control-sm ph-edit-qty" type="number" min="0.001" step="any" name="details[${i}][qty]" value="1" required></td><td><input class="form-control form-control-sm" name="details[${i}][unit]" value="pcs" required></td><td><input class="form-control form-control-sm ph-edit-price" type="number" min="0" step="any" name="details[${i}][harga]" value="0" required></td><td class="text-end ph-edit-line">Rp 0</td><td><button type="button" class="btn btn-sm btn-outline-danger ph-edit-remove">×</button></td>`;
                    rows.appendChild(tr);
                    recalc();
                });
                rows.addEventListener('input', recalc);
                rows.addEventListener('click', e => {
                    if (e.target.closest('.ph-edit-remove')) {
                        if (rows.children.length <= 1) return alert('Minimal satu bahan.');
                        e.target.closest('tr').remove();
                        recalc();
                    }
                });
                // Autocomplete material untuk baris lama dan baris yang baru ditambahkan.
                const materialUrl = @json(route('pengajuan-harga.search-material'));
                let materialTimer = null;
                let materialAbort = null;

                function closeMaterialLists() {
                    rows.querySelectorAll('.ph-edit-material-results').forEach(list => {
                        list.style.display = 'none';
                        list.innerHTML = '';
                    });
                }

                function selectMaterial(input, item) {
                    const tr = input.closest('tr');
                    input.value = item.name || '';
                    if (item.unit !== null && item.unit !== undefined) {
                        tr.querySelector('[name$="[unit]"]').value = item.unit;
                    }
                    if (item.harga !== null && item.harga !== undefined) {
                        tr.querySelector('.ph-edit-price').value = item.harga;
                    }
                    closeMaterialLists();
                    recalc();
                }
                rows.addEventListener('input', function(e) {
                    if (!e.target.matches('.ph-edit-material-name')) return;
                    const input = e.target;
                    const q = input.value.trim();
                    const list = input.nextElementSibling;
                    clearTimeout(materialTimer);
                    if (materialAbort) materialAbort.abort();
                    closeMaterialLists();
                    if (q.length < 2) return;
                    materialTimer = setTimeout(async function() {
                        const abort = new AbortController();
                        materialAbort = abort;
                        try {
                            const response = await fetch(materialUrl + '?q=' +
                                encodeURIComponent(q), {
                                    headers: {
                                        Accept: 'application/json'
                                    },
                                    signal: abort.signal
                                });
                            if (!response.ok) throw new Error('HTTP ' + response
                                .status);
                            const payload = await response.json();
                            const items = Array.isArray(payload) ? payload : (payload
                                .data || []);
                            if (!input.isConnected || input.value.trim() !== q) return;
                            list.innerHTML = '';
                            items.forEach(function(item, index) {
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'ph-edit-material-option' + (
                                    index === 0 ? ' active' : '');
                                const title = document.createElement('strong');
                                title.textContent = item.name || '';
                                const meta = document.createElement('small');
                                meta.textContent = (item.source ===
                                        'finishing' ? 'Finishing' : 'Material'
                                    ) + ' · ' +
                                    (item.unit || '-') +
                                    ' · ' +
                                    (item.harga == null ? 'Harga manual' :
                                        money(Number(item.harga)));
                                btn.append(title, meta);
                                btn._material = item;
                                btn.addEventListener('mousedown', function(ev) {
                                    ev.preventDefault();
                                    selectMaterial(input, item);
                                    input.focus();
                                });
                                list.appendChild(btn);
                            });
                            list.style.display = items.length ? 'block' : 'none';
                        } catch (err) {
                            if (err.name !== 'AbortError') console.error(
                                'Autocomplete material:', err);
                        }
                    }, 250);
                });
                rows.addEventListener('keydown', function(e) {
                    if (!e.target.matches('.ph-edit-material-name')) return;
                    const list = e.target.nextElementSibling;
                    if (list.style.display !== 'block') return;
                    const options = Array.from(list.querySelectorAll('.ph-edit-material-option'));
                    if (!options.length) return;
                    let index = options.findIndex(o => o.classList.contains('active'));
                    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                        e.preventDefault();
                        index = (index + (e.key === 'ArrowDown' ? 1 : -1) + options.length) %
                            options.length;
                        options.forEach((o, i) => o.classList.toggle('active', i === index));
                        options[index].scrollIntoView({
                            block: 'nearest'
                        });
                    } else if (e.key === 'Enter' || e.key === 'Tab') {
                        if (e.key === 'Enter') e.preventDefault();
                        selectMaterial(e.target, options[index < 0 ? 0 : index]._material);
                        if (e.key === 'Enter') e.target.closest('tr').querySelector('.ph-edit-qty')
                            .focus();
                    } else if (e.key === 'Escape') {
                        closeMaterialLists();
                    }
                });
                form.addEventListener('focusout', function(e) {
                    if (e.target.matches('.ph-edit-material-name')) {
                        setTimeout(function() {
                            if (!form.contains(document.activeElement) || !document
                                .activeElement.closest('.ph-edit-material-results'))
                                closeMaterialLists();
                        }, 180);
                    }
                });
                const supplier = form.querySelector('.ph-edit-supplier');
                const suggestions = form.querySelector('datalist');
                let timer, controller;
                supplier.addEventListener('input', () => {
                    clearTimeout(timer);
                    if (controller) controller.abort();
                    suggestions.innerHTML = '';
                    const q = supplier.value.trim();
                    if (q.length < 2) return;
                    timer = setTimeout(async () => {
                        controller = new AbortController();
                        try {
                            const res = await fetch(@json(route('pengajuan-harga.search-supplier')) +
                                '?q=' + encodeURIComponent(q), {
                                    signal: controller.signal,
                                    headers: {
                                        Accept: 'application/json'
                                    }
                                });
                            if (!res.ok) return;
                            const data = await res.json();
                            if (supplier.value.trim() !== q) return;
                            (Array.isArray(data) ? data : (data.data || [])).forEach(
                                item => {
                                    const opt = document.createElement('option');
                                    opt.value = typeof item === 'string' ? item : (
                                        item.name || '');
                                    suggestions.appendChild(opt);
                                });
                        } catch (err) {
                            if (err.name !== 'AbortError') console.error(err);
                        }
                    }, 250);
                });
            });
        });
    </script>
@endonce

@once
    <style>
        .ph-export-actions {
            flex-shrink: 0
        }

        .ph-export-actions .btn {
            white-space: nowrap
        }

        @media(max-width:575px) {
            .ph-export-actions .btn {
                padding: 4px 7px;
                font-size: 11px
            }
        }
    </style>
    <script>
        (function() {
            'use strict';
            let loadingLibrary = null;

            function ensureHtml2Canvas() {
                if (typeof window.html2canvas === 'function') return Promise.resolve(window.html2canvas);
                if (loadingLibrary) return loadingLibrary;
                loadingLibrary = new Promise(function(resolve, reject) {
                    const script = document.createElement('script');
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
                    script.onload = function() {
                        if (typeof window.html2canvas === 'function') resolve(window.html2canvas);
                        else reject(new Error('Library export tidak tersedia.'));
                    };
                    script.onerror = function() {
                        reject(new Error(
                            'Gagal memuat html2canvas. Periksa koneksi internet atau pasang library secara lokal.'
                        ));
                    };
                    document.head.appendChild(script);
                }).catch(function(error) {
                    loadingLibrary = null;
                    throw error;
                });
                return loadingLibrary;
            }

            function makeTextCell(element, value) {
                const box = document.createElement('div');
                box.className = 'form-control bg-white';
                box.style.cssText =
                    'height:auto;min-height:30px;white-space:pre-wrap;overflow-wrap:anywhere;color:#212529;';
                box.textContent = value || '—';
                element.replaceWith(box);
            }

            function buildExportSheet(modal) {
                const original = modal.querySelector('.modal-content');
                const clone = original.cloneNode(true);
                const originalInputs = original.querySelectorAll('input,textarea,select');
                const cloneInputs = clone.querySelectorAll('input,textarea,select');
                Array.from(cloneInputs).forEach(function(el, i) {
                    const source = originalInputs[i];
                    if (!source) return;
                    el.dataset.exportValue = source.tagName === 'SELECT' ? (source.selectedOptions[0]
                        ?.textContent || '') : source.value;
                    el.dataset.exportChecked = source.checked ? '1' : '0';
                });
                clone.querySelectorAll(
                    '.ph-export-actions,.modal-footer,.ph-photo-upload,.ph-edit-add,.ph-edit-remove,.ph-edit-material-results,.ph-edit-material-option,.ph-photo-paste,.ph-photo-browse,.ph-photo-clear'
                ).forEach(el => el.remove());
                // Capture all sections, even those below the modal's scroll position.
                clone.style.cssText =
                    'width:1200px;max-width:none;max-height:none;height:auto;overflow:visible;background:white;border:0;border-radius:0;box-shadow:none;';
                const body = clone.querySelector('.modal-body');
                if (body) body.style.cssText += ';overflow:visible;max-height:none;height:auto;';
                clone.querySelectorAll('.table-responsive').forEach(el => {
                    el.style.overflow = 'visible';
                });
                // Read live input values, including unsaved edits, rather than HTML attributes.
                Array.from(clone.querySelectorAll('input,textarea,select')).forEach(function(el) {
                    if (el.type === 'hidden' || el.type === 'file' || el.type === 'submit' || el.type ===
                        'button') {
                        el.remove();
                        return;
                    }
                    if (el.type === 'checkbox' || el.type === 'radio') {
                        makeTextCell(el, el.dataset.exportChecked === '1' ? 'Ya' : 'Tidak');
                        return;
                    }
                    const value = el.dataset.exportValue;
                    makeTextCell(el, value);
                });
                // Remove form action buttons, keeping all actual data and approval remarks.
                clone.querySelectorAll('button, .ph-edit-material-results, datalist').forEach(el => el.remove());
                clone.querySelectorAll('img').forEach(function(img) {
                    img.style.objectFit = 'contain';
                });
                const stage = document.createElement('div');
                stage.setAttribute('aria-hidden', 'true');
                stage.style.cssText =
                    'position:fixed;left:0;top:0;width:1200px;z-index:-9999;pointer-events:none;background:white;visibility:visible;';
                stage.appendChild(clone);
                document.body.appendChild(stage);
                return stage;
            }
            document.addEventListener('click', async function(event) {
                const button = event.target.closest('.ph-export-image');
                if (!button) return;
                event.preventDefault();
                const modal = button.closest('.modal');
                if (!modal) return;
                const format = button.dataset.format === 'jpeg' ? 'jpeg' : 'png';
                const initial = button.innerHTML;
                button.disabled = true;
                button.textContent = 'Exporting...';
                let stage;
                try {
                    const render = await ensureHtml2Canvas();
                    stage = buildExportSheet(modal);
                    await document.fonts?.ready;
                    const canvas = await render(stage.firstElementChild, {
                        backgroundColor: '#ffffff',
                        scale: 2,
                        useCORS: true,
                        allowTaint: false,
                        logging: false,
                        scrollX: 0,
                        scrollY: 0,
                        windowWidth: Math.max(document.documentElement.clientWidth, 1300),
                        onclone: function(doc) {
                            doc.querySelectorAll('.ph-edit-material-results').forEach(el => el
                                .remove());
                        }
                    });
                    const mime = format === 'jpeg' ? 'image/jpeg' : 'image/png';
                    const data = canvas.toDataURL(mime, 0.95);
                    const title = modal.querySelector('.modal-title')?.textContent || 'Pengajuan Harga';
                    const article = (title.split('—').pop() || 'pengajuan').trim().replace(
                        /[^a-zA-Z0-9_-]/g, '_');
                    const link = document.createElement('a');
                    link.href = data;
                    link.download = 'Pengajuan_Harga_' + article + '.' + (format === 'jpeg' ? 'jpg' :
                        'png');
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                } catch (error) {
                    console.error('Export gambar pengajuan:', error);
                    alert('Export gambar gagal: ' + error.message +
                        '\nJika foto dari server eksternal, pastikan CORS diizinkan.');
                } finally {
                    if (stage) stage.remove();
                    button.disabled = false;
                    button.innerHTML = initial;
                }
            });
        })
        ();
    </script>
@endonce
