@extends('master.master')

@section('title', 'Review Pengajuan Tomo')

@section('content')

    <style>
        .tr-page {

            padding: 18px;

            max-width: 1400px;

            margin: auto;

            color: #243047
        }



        .tr-title {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 12px;

            flex-wrap: wrap;

            margin-bottom: 16px
        }



        .tr-title h2 {

            font-weight: 800;

            margin: 0
        }



        .tr-btn {

            border: 0;

            border-radius: 8px;

            padding: 9px 13px;

            cursor: pointer;

            font-weight: 700;

            background: #174ea6;

            color: white
        }



        .tr-btn:disabled {

            opacity: .5;

            cursor: not-allowed
        }



        .tr-btn.secondary {

            background: #e8eef6;

            color: #244061
        }



        .tr-btn.green {

            background: #167447
        }



        .tr-btn.red {

            background: #b42318
        }



        .tr-controls {

            display: flex;

            gap: 9px;

            flex-wrap: wrap;

            margin-bottom: 12px
        }



        .tr-controls input,

        .tr-controls select,

        .tr-drawer input,

        .tr-drawer select,

        .tr-drawer textarea {

            border: 1px solid #cbd5e1;

            border-radius: 8px;

            padding: 9px;

            min-height: 38px
        }



        .tr-controls input {

            flex: 1;

            min-width: 180px
        }



        .tr-card {

            background: #fff;

            border: 1px solid #e0e6ee;

            border-radius: 12px;

            overflow: hidden
        }



        .tr-scroll {

            overflow: auto
        }



        .tr-table {

            width: 100%;

            border-collapse: collapse;

            min-width: 860px
        }



        .tr-table th {

            background: #1d4e89;

            color: #fff;

            text-align: left;

            padding: 12px;

            white-space: nowrap
        }



        .tr-table td {

            border-bottom: 1px solid #e9edf2;

            padding: 11px;

            vertical-align: top
        }



        .tr-table small {

            color: #64748b
        }



        .tr-pill {

            font-size: 12px;

            font-weight: 800;

            padding: 5px 9px;

            border-radius: 20px;

            display: inline-block;

            background: #e7eff9;

            color: #244061
        }



        .tr-pill.pending {

            background: #fff0c2;

            color: #805600
        }



        .tr-pill.diproses {

            background: #dbeafe;

            color: #1e40af
        }



        .tr-pill.disetujui {

            background: #dcfce7;

            color: #166534
        }



        .tr-pill.ditolak {

            background: #fee2e2;

            color: #991b1b
        }



        .tr-expand {

            display: none;

            background: #f7f9fc
        }



        .tr-expand.open {

            display: table-row
        }



        .tr-point-table {

            width: 100%;

            border-collapse: collapse;

            max-width: 650px
        }



        .tr-point-table td,

        .tr-point-table th {

            padding: 7px;

            border-bottom: 1px solid #e5e7eb;

            text-align: left
        }



        .tr-point-table th {

            background: #e8eef6;

            color: #243047
        }



        .tr-actions {

            display: flex;

            gap: 6px;

            flex-wrap: wrap
        }



        .tr-empty {

            text-align: center;

            padding: 25px;

            color: #64748b
        }



        .tr-backdrop {

            display: none;

            position: fixed;

            inset: 0;

            background: #0f172a77;

            z-index: 10000
        }



        .tr-backdrop.open {

            display: block
        }



        .tr-drawer {

            position: fixed;

            right: 0;

            top: 0;

            height: 100dvh;

            width: min(94vw, 790px);

            background: #fff;

            z-index: 10001;

            transform: translateX(110%);

            transition: transform .22s ease;

            display: flex;

            flex-direction: column;

            box-shadow: -10px 0 30px #0002
        }



        .tr-drawer.open {

            transform: translateX(0)
        }



        .tr-drawer-head {

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 17px;

            border-bottom: 1px solid #e2e8f0
        }



        .tr-drawer-body {

            padding: 16px;

            overflow: auto;

            flex: 1
        }



        .tr-point-row {

            display: grid;

            grid-template-columns: minmax(160px, 2.4fr) minmax(90px, 1fr) minmax(75px, .8fr) 58px 55px 66px;

            gap: 6px;

            align-items: center;

            margin-bottom: 9px
        }



        .tr-point-row input {

            min-width: 0;

            width: 100%
        }



        /* Master point drawer: wide, compact, no nested horizontal scroll */
        .tr-drawer {
            max-width: 100vw;
        }

        .tr-drawer-head {
            padding: 12px 16px;
            font-size: 13px;
        }

        .tr-drawer-body {
            padding: 12px 14px;
            overflow-x: hidden;
            font-size: 11px;
        }

        .tr-drawer .tr-note {
            font-size: 11px;
            line-height: 1.45;
        }

        .tr-drawer input:not([type="checkbox"]),
        .tr-drawer select {
            font-size: 11px;
            padding: 6px 8px;
            min-height: 32px;
            width: 100%;
        }

        .tr-drawer .tr-btn {
            font-size: 11px;
            padding: 7px 9px;
            white-space: nowrap;
        }

        .tr-point-row {
            border: 1px solid #e5ebf4;
            border-radius: 9px;
            padding: 7px;
            background: #fafcff;
        }

        .tr-point-row label {
            font-size: 10px !important;
            display: flex;
            align-items: center;
            gap: 3px;
            justify-content: center;
        }

        .tr-point-row input[type="checkbox"] {
            width: 15px;
            height: 15px;
            margin: 0;
            accent-color: #2563eb;
        }

        @media (max-width: 650px) {
            .tr-drawer {
                width: 100vw;
            }

            .tr-drawer-body {
                padding: 10px;
            }

            .tr-point-row {
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
                gap: 7px;
            }

            .tr-point-row [data-field="name"] {
                grid-column: 1 / -1;
            }

            .tr-point-row [data-field="satuan"] {
                grid-column: 1;
            }

            .tr-point-row [data-field="sort_order"] {
                grid-column: 2;
            }

            .tr-point-row label {
                justify-content: flex-start;
            }

            .tr-point-row .tr-btn {
                justify-self: stretch;
            }
        }

        .tr-note {

            font-size: 12px;

            color: #64748b;

            margin: 8px 0 14px
        }



        .tr-alert {

            display: none;

            padding: 12px;

            border-radius: 8px;

            margin-bottom: 12px
        }



        .tr-alert.show {

            display: block
        }



        .tr-alert.error {

            background: #fee2e2;

            color: #991b1b
        }



        .tr-alert.ok {

            background: #dcfce7;

            color: #166534
        }



        @media(max-width:650px) {

            .tr-page {

                padding: 9px
            }



            .tr-title h2 {

                font-size: 19px
            }



            .tr-point-row {

                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr)
            }



            .tr-point-row input {

                padding: 5px;

                font-size: 13px
            }

        }



        /* Review modal and active row */

        .tr-table tbody tr.tr-data-row {

            cursor: pointer;

            transition: background .15s
        }



        .tr-table tbody tr.tr-data-row:hover {

            background: #f0f6ff
        }



        .tr-table tbody tr.tr-data-row.tr-selected {

            background: #e1edff;

            box-shadow: inset 4px 0 #174ea6
        }



        .tr-table tbody tr.tr-data-row:focus-visible {

            outline: 3px solid #2563eb;

            outline-offset: -3px
        }



        .tr-modal-mask {

            position: fixed;

            inset: 0;

            background: rgba(12, 24, 45, .65);

            z-index: 11000;

            display: none;

            align-items: center;

            justify-content: center;

            padding: 14px
        }



        .tr-modal-mask.open {

            display: flex
        }



        .tr-modal {

            background: white;

            width: min(100%, 740px);

            max-height: 94dvh;

            border-radius: 14px;

            display: flex;

            flex-direction: column;

            overflow: hidden;

            box-shadow: 0 18px 60px #0003
        }



        .tr-modal-head {

            padding: 14px 18px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            border-bottom: 1px solid #e2e8f0;

            gap: 10px
        }



        .tr-modal-head h3 {

            font-size: 18px;

            font-weight: 800;

            margin: 0
        }



        .tr-modal-body {

            overflow: auto;

            padding: 16px 18px;

            min-height: 0
        }



        .tr-modal-foot {

            display: flex;

            justify-content: flex-end;

            gap: 8px;

            padding: 12px 18px;

            border-top: 1px solid #e2e8f0;

            background: #f8fafc;

            flex-wrap: wrap
        }



        .tr-modal-info {

            background: #f1f6fd;

            border: 1px solid #dce6f3;

            border-radius: 10px;

            padding: 12px;

            margin-bottom: 14px;

            line-height: 1.65
        }



        .tr-modal-summary {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 12px;

            flex-wrap: wrap;

            margin: 10px 0
        }



        .tr-modal-table-wrap {

            overflow-x: auto;

            border: 1px solid #e2e8f0;

            border-radius: 9px
        }



        .tr-modal .tr-point-table {

            max-width: none;

            min-width: 460px
        }



        .tr-modal .tr-point-table th {

            background: #1d4e89;

            color: white
        }



        .tr-modal .tr-point-table td {

            font-size: 13px
        }



        .tr-modal-note {

            margin-top: 12px;

            padding: 12px;

            border: 1px solid #e2e8f0;

            border-radius: 9px;

            overflow-wrap: anywhere
        }



        .tr-modal-note strong {

            display: block;

            margin-bottom: 5px
        }



        .tr-modal-mask[aria-hidden="true"] {

            display: none
        }



        @media(max-width:600px) {

            .tr-modal-mask {

                padding: 5px
            }



            .tr-modal {

                max-height: 98dvh
            }



            .tr-modal-body {

                padding: 10px
            }



            .tr-modal-head,

            .tr-modal-foot {

                padding: 10px
            }



            .tr-modal-head h3 {

                font-size: 16px
            }

        }
    </style>

    <div class="tr-page">

        <div class="tr-title">

            <div>

                <h2>Review Pengajuan Tomo</h2><small>Konfirmasi review terlebih dahulu, kemudian approve atau tolak

                    pengajuan.</small>

            </div><button type="button" class="tr-btn" id="trShowPoints">⚙ Master Points</button>

        </div>

        <div id="trAlert" class="tr-alert" role="status"></div>

        <div class="tr-controls"><input type="search" id="trSearch" placeholder="Cari PO, barang, article..."
                aria-label="Cari pengajuan"><select id="trStatus">

                <option value="">Semua Status</option>

                <option value="pending">Pending</option>

                <option value="diproses">Review</option>

                <option value="disetujui">Disetujui</option>

                <option value="ditolak">Ditolak</option>

            </select><button type="button" class="tr-btn secondary" id="trRefresh">Refresh</button></div>

        <div class="tr-card tr-scroll">

            <table class="tr-table">

                <thead>

                    <tr>

                        <th>No</th>

                        <th>Pengajuan</th>

                        <th>PO / Barang</th>

                        <th>Total</th>

                        <th>Status</th>

                        <th>Rincian Point</th>

                        <th>Tindakan</th>

                    </tr>

                </thead>

                <tbody id="trBody">

                    <tr>

                        <td colspan="7" class="tr-empty">Memuat data...</td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

    <div id="trReviewMask" class="tr-modal-mask" role="dialog" aria-modal="true" aria-hidden="true"
        aria-labelledby="trReviewTitle">

        <div class="tr-modal">

            <div class="tr-modal-head">

                <h3 id="trReviewTitle">Detail Pengajuan</h3><button type="button" class="tr-btn secondary"
                    id="trCloseReview" aria-label="Tutup detail">Tutup ×</button>

            </div>

            <div class="tr-modal-body" id="trReviewBody"></div>

            <div class="tr-modal-foot" id="trReviewFoot"></div>

        </div>

    </div>

    <div id="trBackdrop" class="tr-backdrop"></div>

    <aside id="trDrawer" class="tr-drawer" aria-label="Pengaturan master point" aria-hidden="true">

        <div class="tr-drawer-head"><strong>Master Points Finishing</strong><button type="button" class="tr-btn secondary"
                id="trCloseDrawer">Tutup ×</button></div>

        <div class="tr-drawer-body">

            <p class="tr-note">Perubahan nama point hanya berlaku untuk pengajuan baru. Riwayat lama menyimpan nama point

                saat diajukan.</p>

            <div id="trPointList">Memuat...</div>

            <hr>

            <h4>Tambah Point</h4>

            <div style="display:flex;gap:8px;flex-wrap:wrap"><input id="trNewName" placeholder="Nama point"
                    style="flex:2;min-width:140px"><input id="trNewHarga" type="text" inputmode="decimal"
                    placeholder="Harga mutlak (Rp)" style="flex:1;min-width:120px"><input id="trNewSatuan" maxlength="30"
                    placeholder="Satuan (pcs/set)" value="pcs" style="flex:1;min-width:100px"><button type="button"
                    class="tr-btn green" id="trAddPoint">+ Tambah</button></div>

        </div>

    </aside>

    <script>
        (() => {

            const endpoints = {

                list: @json(route('png.tomo.data')),

                status: @json(route('png.tomo.status', ['id' => '__ID__'])),

                points: @json(route('png.tomo.points')),

                pointUpdate: @json(route('png.tomo.points.update', ['id' => '__ID__'])),

                pointStore: @json(route('png.tomo.points.store'))

            };

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token());

            const $ = id => document.getElementById(id),

                esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({

                    '&': '&amp;',

                    '<': '&lt;',

                    '>': '&gt;',

                    '"': '&quot;',

                    "'": '&#39;'

                } [c]));

            const rupiah = v => 'Rp ' + new Intl.NumberFormat('id-ID', {

                maximumFractionDigits: 2

            }).format(Number(v) || 0);

            const statusName = {

                pending: 'Pending',

                diproses: 'Review',

                disetujui: 'Disetujui',

                ditolak: 'Ditolak'

            };

            let records = [],

                points = [],

                selectedId = null,

                lastFocus = null;



            function message(text, error = false) {

                const el = $('trAlert');

                el.textContent = text;

                el.className = 'tr-alert show ' + (error ? 'error' : 'ok');

            }

            async function request(url, method = 'GET', data = null) {

                const options = {

                    method,

                    headers: {

                        'Accept': 'application/json',

                        'X-CSRF-TOKEN': csrf

                    }

                };

                if (data !== null) {

                    options.headers['Content-Type'] = 'application/json';

                    options.body = JSON.stringify(data)

                }

                const res = await fetch(url, options);

                const json = await res.json().catch(() => ({}));

                if (!res.ok) throw Error(json.message || Object.values(json.errors || {}).flat().join(', ') ||

                    'HTTP ' + res.status);

                return json;

            }



            function render() {

                const term = $('trSearch').value.trim().toLowerCase(),

                    status = $('trStatus').value;

                const shown = records.filter(r => (!status || r.status === status) && (!term || [r.po_code, r.item_name,

                    r.article_code

                ].some(v => String(v || '').toLowerCase().includes(term))));

                if (!shown.length) {

                    $('trBody').innerHTML = '<tr><td colspan="7" class="tr-empty">Tidak ada pengajuan.</td></tr>';

                    return;

                }

                $('trBody').innerHTML = shown.map((r, i) => {

                    const rows = Array.isArray(r.rows) ? r.rows : [];

                    const first = rows.slice(0, 2).map(x => esc(x.point_name || x.process)).join(', ');

                    return `<tr class="tr-data-row ${selectedId===Number(r.id)?'tr-selected':''}" data-row-id="${Number(r.id)}" tabindex="0" aria-label="Buka detail pengajuan ${Number(r.id)}"><td>${i+1}</td><td><b>#${Number(r.id)}</b><br><small>${esc(r.created_at||'')}</small></td><td><b>${esc(r.po_code)}</b><br>${esc(r.item_name)}<br><small>${esc(r.article_code||'-')} · Qty ${esc(r.qty)}</small></td><td><b>${rupiah(r.total)}</b></td><td><span class="tr-pill ${esc(r.status)}">${esc(statusName[r.status]||r.status)}</span></td><td><small>${first||'-'}</small><br><button type="button" class="tr-btn secondary" data-open="${Number(r.id)}">See more (${rows.length})</button></td><td><button type="button" class="tr-btn secondary" data-open="${Number(r.id)}">Lihat Detail</button></td></tr>`

                }).join('');

            }



            function modal(open) {

                $('trReviewMask').classList.toggle('open', open);

                $('trReviewMask').setAttribute('aria-hidden', String(!open));

                document.body.style.overflow = open ? 'hidden' : '';

                if (!open) {

                    selectedId = null;

                    render();

                    if (lastFocus?.isConnected) lastFocus.focus();

                } else $('trCloseReview').focus();

            }



            function openDetail(id) {

                const r = records.find(x => Number(x.id) === Number(id));

                if (!r) return;

                lastFocus = document.activeElement;

                selectedId = Number(id);

                render();

                $('trReviewTitle').textContent = 'Detail Pengajuan #' + r.id;

                const rows = Array.isArray(r.rows) ? r.rows : [];

                $('trReviewBody').innerHTML =

                    `<div class="tr-modal-info"><b>PO: ${esc(r.po_code||'-')}</b><br><b>${esc(r.item_name||'-')}</b><br><small>Article: ${esc(r.article_code||'-')} · Qty: ${esc(r.qty??'-')} · Diajukan: ${esc(r.created_at||'-')}</small></div><div class="tr-modal-summary"><span class="tr-pill ${esc(r.status)}">${esc(statusName[r.status]||r.status)}</span><strong>Total: ${rupiah(r.total)}</strong></div><h4 style="font-size:14px;font-weight:800;margin:15px 0 8px">Rincian Biaya Finishing</h4><div class="tr-modal-table-wrap"><table class="tr-point-table"><thead><tr><th>Proses</th><th>Harga</th><th>Qty</th><th>Stn</th><th>Jumlah</th></tr></thead><tbody>${rows.map(x=>`<tr><td>${esc(x.point_name||x.process||'-')}</td><td>${rupiah(x.harga)}</td><td>${esc(x.qty)}</td><td>${esc(x.satuan||'pcs')}</td><td>${rupiah(x.jumlah)}</td></tr>`).join('')||'<tr><td colspan="5">Tidak ada rincian.</td></tr>'}</tbody></table></div><div class="tr-modal-note"><strong>Catatan Pengajuan</strong>${esc(r.notes||'-')}</div><div class="tr-modal-note"><strong>Catatan Review</strong>${esc(r.review_notes||'-')}</div>`;

                const buttons = r.status === 'pending' ?

                    '<button type="button" class="tr-btn" data-review-action="confirm">Confirm Review</button>' : r

                    .status === 'diproses' ?

                    '<button type="button" class="tr-btn green" data-review-action="approve">Approve</button><button type="button" class="tr-btn red" data-review-action="reject">Tolak</button>' :

                    '<span style="color:#64748b;align-self:center">Pengajuan selesai diproses</span>';

                $('trReviewFoot').innerHTML =

                    '<button type="button" class="tr-btn secondary" id="trModalBack">Tutup</button>' + buttons;

                $('trModalBack').onclick = () => modal(false);

                modal(true);

            }

            async function load() {

                try {

                    const data = await request(endpoints.list);

                    records = Array.isArray(data) ? data : data.data || [];

                    render();

                    if (selectedId !== null && $('trReviewMask').classList.contains('open')) {

                        const id = selectedId;

                        if (records.some(r => Number(r.id) === id)) openDetail(id);

                        else modal(false)

                    }

                } catch (e) {

                    message(e.message, true)

                }

            }

            $('trSearch').addEventListener('input', render);

            $('trStatus').addEventListener('change', render);

            $('trRefresh').onclick = load;

            $('trBody').addEventListener('click', e => {

                const btn = e.target.closest('[data-open]');

                const row = e.target.closest('[data-row-id]');

                if (btn || row) openDetail(Number(btn?.dataset.open || row?.dataset.rowId));

            });

            $('trBody').addEventListener('keydown', e => {

                if ((e.key === 'Enter' || e.key === ' ') && e.target.matches('[data-row-id]')) {

                    e.preventDefault();

                    openDetail(Number(e.target.dataset.rowId));

                }

            });

            $('trCloseReview').onclick = () => modal(false);

            $('trReviewMask').addEventListener('click', e => {

                if (e.target === $('trReviewMask')) modal(false)

            });

            $('trReviewFoot').addEventListener('click', async e => {

                const btn = e.target.closest('[data-review-action]');

                if (!btn) return;

                const id = selectedId,

                    action = btn.dataset.reviewAction;

                const label = {

                    confirm: 'memulai review',

                    approve: 'menyetujui',

                    reject: 'menolak'

                } [action];

                if (!confirm('Yakin ingin ' + label + ' pengajuan #' + id + '?')) return;

                let review_notes = null;

                if (action === 'reject') {

                    review_notes = prompt('Alasan penolakan (wajib):');

                    if (!review_notes?.trim()) return;

                } else if (action === 'approve') {

                    review_notes = prompt('Catatan persetujuan (opsional):') || null;

                }

                btn.disabled = true;

                try {

                    await request(endpoints.status.replace('__ID__', id), 'PATCH', {

                        action,

                        review_notes

                    });

                    message('Status pengajuan #' + id + ' berhasil diperbarui.');

                    await load()

                } catch (err) {

                    message(err.message, true);

                    btn.disabled = false

                }

            });



            function drawer(open) {

                $('trDrawer').classList.toggle('open', open);

                $('trBackdrop').classList.toggle('open', open);

                $('trDrawer').setAttribute('aria-hidden', String(!open));

                if (open) loadPoints()

            }

            $('trShowPoints').onclick = () => drawer(true);

            $('trCloseDrawer').onclick = () => drawer(false);

            $('trBackdrop').onclick = () => drawer(false);

            document.addEventListener('keydown', e => {

                if (e.key === 'Escape') {

                    if ($('trDrawer').classList.contains('open')) drawer(false);

                    else if ($('trReviewMask').classList.contains('open')) modal(false);

                }

            });

            async function loadPoints() {

                try {

                    points = await request(endpoints.points);

                    $('trPointList').innerHTML = points.map(p =>

                        `<div class="tr-point-row" data-point="${p.id}"><input data-field="name" value="${esc(p.name)}" aria-label="Nama point"><input data-field="harga_mutlak" type="text" inputmode="decimal" value="${Number(p.harga_mutlak)||0}" aria-label="Harga mutlak"><input data-field="satuan" maxlength="30" value="${esc(p.satuan||'pcs')}" aria-label="Satuan"><input data-field="sort_order" type="text" inputmode="numeric" value="${Number(p.sort_order)||0}" aria-label="Urutan"><label style="font-size:12px"><input data-field="is_active" type="checkbox" ${p.is_active?'checked':''}> Aktif</label><button class="tr-btn secondary" data-save="${p.id}">Simpan</button></div>`

                    ).join('') || '<p>Belum ada point.</p>'

                } catch (e) {

                    message(e.message, true)

                }

            }

            $('trPointList').addEventListener('click', async e => {

                const b = e.target.closest('[data-save]');

                if (!b) return;

                const row = b.closest('[data-point]'),

                    id = b.dataset.save;

                const data = {

                    name: row.querySelector('[data-field="name"]').value.trim(),

                    harga_mutlak: Number(row.querySelector('[data-field="harga_mutlak"]').value),
                    satuan: row.querySelector('[data-field="satuan"]').value.trim(),
                    sort_order: Number(row.querySelector('[data-field="sort_order"]').value),

                    is_active: row.querySelector('[data-field="is_active"]').checked

                };

                b.disabled = true;

                try {

                    await request(endpoints.pointUpdate.replace('__ID__', id), 'PUT', data);

                    message('Master point berhasil diubah.');

                    await loadPoints()

                } catch (err) {

                    message(err.message, true);

                    b.disabled = false

                }

            });

            $('trAddPoint').onclick = async () => {

                const name = $('trNewName').value.trim();

                if (!name) return message('Isi nama point.', true);

                try {

                    await request(endpoints.pointStore, 'POST', {
                        name,
                        harga_mutlak: Number($('trNewHarga').value),
                        satuan: $('trNewSatuan').value.trim()
                    });

                    $('trNewName').value = '';
                    $('trNewHarga').value = '';
                    $('trNewSatuan').value = 'pcs';

                    message('Point berhasil ditambahkan.');

                    await loadPoints()

                } catch (e) {

                    message(e.message, true)

                }

            };

            load();

        })();
    </script>

@endsection
