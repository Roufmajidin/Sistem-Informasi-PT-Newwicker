<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Warehouse Incoming | NewWicker</title>
    <style>
        :root {
            color-scheme: light;
            --navy: #173a68;
            --blue: #2563eb;
            --border: #e2e8f0;
            --muted: #64748b
        }

        * {
            box-sizing: border-box
        }

        body {
            margin: 0;
            background: #f5f7fb;
            color: #18263a;
            font-family: Inter, system-ui, -apple-system, 'Segoe UI', sans-serif
        }

        .container {
            max-width: 1380px;
            margin: auto;
            padding: 24px
        }

        .heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 18px
        }

        h1 {
            font-size: 25px;
            margin: 0 0 4px;
            color: var(--navy)
        }

        .muted {
            color: var(--muted);
            font-size: 13px
        }

        .live {
            color: #15803d;
            background: #dcfce7;
            border-radius: 5px;
            padding: 7px 10px;
            font-size: 12px;
            font-weight: 700
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 16px
        }

        .card,
        .panel {
            background: white;
            border: 1px solid var(--border);
            border-radius: 9px;
            box-shadow: 0 2px 10px #18375a08
        }

        .card {
            padding: 18px
        }

        .card .value {
            font-size: 30px;
            font-weight: 800;
            color: var(--navy);
            margin-top: 6px
        }

        .panel {
            overflow: hidden
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            padding: 17px;
            flex-wrap: wrap
        }

        .toolbar h2 {
            font-size: 17px;
            margin: 0
        }

        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 9px;
            flex-wrap: wrap
        }

        .search-wrap {
            position: relative;
            min-width: 280px
        }

        .search-wrap input {
            width: 300px;
            max-width: 100%;
            padding-right: 36px
        }

        .clear-search {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            padding: 4px 8px;
            font-size: 20px;
            color: #64748b
        }

        .clear-search[hidden] {
            display: none
        }

        input,
        button {
            font: inherit;
            border-radius: 6px
        }

        input {
            border: 1px solid #cbd5e1;
            padding: 10px 12px
        }

        button {
            cursor: pointer;
            border: 1px solid #cbd5e1;
            background: white;
            padding: 9px 12px
        }

        button:disabled {
            opacity: .5;
            cursor: default
        }

        .classify-btn {
            background: #1e4e87;
            border-color: #1e4e87;
            color: #fff;
            font-weight: 700
        }

        .classify-btn.active {
            background: #0f766e;
            border-color: #0f766e
        }

        .table-scroll {
            overflow-x: auto
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1100px
        }

        th:nth-child(1),
        td:nth-child(1) {
            width: 55px
        }

        th:nth-child(5),
        td:nth-child(5) {
            text-align: center;
            white-space: nowrap
        }

        th:nth-child(7),
        td:nth-child(7) {
            min-width: 115px
        }

        th {
            background: #f1f5f9;
            color: #475569;
            font-size: 12px;
            text-align: left
        }

        th,
        td {
            padding: 12px 14px;
            border-top: 1px solid #e9eef5
        }

        td {
            font-size: 13px
        }

        tbody tr:hover td {
            background: #f8fbff
        }

        .pill {
            display: inline-block;
            padding: 4px 8px;
            background: #eff6ff;
            color: #1d4ed8;
            border-radius: 4px;
            font-weight: 700
        }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            padding: 14px 18px;
            border-top: 1px solid var(--border)
        }

        .error {
            color: #b91c1c;
            font-size: 13px;
            padding: 0 18px 14px;
            display: none
        }

        .filter-info {
            padding: 0 18px 12px;
            color: #475569;
            font-size: 13px
        }

        .filter-info[hidden] {
            display: none
        }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .65);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px
        }

        .modal-backdrop.open {
            display: flex
        }

        .modal {
            background: white;
            border-radius: 14px;
            width: min(100%, 650px);
            max-height: 90dvh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 70px #0003
        }

        .modal-head {
            padding: 17px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border)
        }

        .modal-head h3 {
            margin: 0;
            color: var(--navy);
            font-size: 20px
        }

        .modal-body {
            padding: 18px 20px;
            overflow-y: auto
        }

        .modal-foot {
            padding: 13px 20px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end
        }

        .label-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px
        }

        .show-labels {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            padding: 5px 9px;
            font-size: 12px;
            font-weight: 700
        }

        .label-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 14px;
            text-align: left;
            border: 1px solid #dbe4ef;
            border-radius: 10px;
            min-height: 60px
        }

        .label-option:hover,
        .label-option.selected {
            background: #eff6ff;
            border-color: #2563eb
        }

        .label-option span {
            overflow-wrap: anywhere
        }

        .label-count {
            background: #e2e8f0;
            color: #334155;
            padding: 4px 9px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700
        }

        .modal-hint {
            margin: 0 0 14px;
            font-size: 13px;
            color: #64748b
        }

        .modal-close {
            font-size: 23px;
            line-height: 1;
            border: 0;
            background: #f1f5f9
        }

        .loading {
            text-align: center;
            padding: 25px;
            color: #64748b
        }

        @media(max-width:640px) {
            .container {
                padding: 12px
            }

            .cards {
                gap: 8px
            }

            .card {
                padding: 12px
            }

            .card .value {
                font-size: 24px
            }

            h1 {
                font-size: 21px
            }

            .toolbar-right {
                width: 100%
            }

            .search-wrap {
                flex: 1;
                min-width: 150px
            }

            .search-wrap input {
                width: 100%
            }

            .label-list {
                grid-template-columns: 1fr
            }
        }

        /* Grid status label barang */
        .label-status-modal {
            width: min(100%, 680px);
        }

        .label-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 16px;
        }

        .label-summary span {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
            font-weight: 700;
        }

        .label-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .label-tile {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 5px;
            min-height: 76px;
            border: 1px solid #86efac;
            background: #dcfce7;
            color: #166534;
            border-radius: 10px;
            font-weight: 800;
        }

        .label-tile.missing {
            border-color: #fca5a5;
            background: #fee2e2;
            color: #991b1b;
        }

        .label-tile small {
            font-size: 11px;
            font-weight: 600;
        }

        .label-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            margin-top: 16px;
            font-size: 12px;
            color: #475569;
        }

        .legend-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 3px;
            margin-right: 6px;
        }

        @media(max-width:420px) {
            .label-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
    </style>
</head>

<body>
    <main class="container">
        <div class="heading">
            <div>
                <h1>Warehouse Incoming</h1>
                <div class="muted">Monitoring scan QR SPK masuk · otomatis setiap 5 detik</div>
            </div>
            <div><span class="live" id="liveStatus">● Menghubungkan...</span> <span class="muted"
                    id="lastUpdate"></span></div>
        </div>
        <section class="cards">
            <div class="card">
                <div class="muted">Scan berhasil hari ini</div>
                <div class="value" id="todayCount">—</div>
            </div>
            <div class="card">
                <div class="muted">Total scan berhasil</div>
                <div class="value" id="totalCount">—</div>
            </div>
            <div class="card">
                <div class="muted">SPK ter-scan</div>
                <div class="value" id="spkCount">—</div>
            </div>
        </section>
        <section class="panel">
            <div class="toolbar">
                <h2>Riwayat Barang Masuk</h2>
                <div class="toolbar-right"><button type="button" id="classifyBtn" class="classify-btn">☷
                        Klasifikasi</button>
                    <div class="search-wrap"><input type="search" id="search"
                            placeholder="Cari SPK, PO, item, atau QR..." autocomplete="off"><button type="button"
                            id="clearSearch" class="clear-search" aria-label="Hapus pencarian" hidden>×</button></div>
                </div>
            </div>
            <div class="filter-info" id="filterInfo" hidden></div>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No SPK</th>
                            <th>PO</th>
                            <th>Items Name</th>
                            <th>Total</th>
                            <th>Method</th>
                            <th>Label List</th>
                            <th>User</th>
                            <th>Scanned At</th>
                            <th>Subkon</th>
                        </tr>
                    </thead>
                    <tbody id="rows">
                        <tr>
                            <td colspan="10" class="muted">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="error" class="error" role="alert"></div>
            <div class="footer"><span class="muted" id="pageInfo">—</span>
                <div><button id="prev">Sebelumnya</button> <button id="next">Berikutnya</button></div>
            </div>
        </section>
    </main>
    <div class="modal-backdrop" id="classifyModal" role="dialog" aria-modal="true" aria-labelledby="classifyTitle"
        aria-hidden="true">
        <div class="modal">
            <div class="modal-head">
                <h3 id="classifyTitle">Klasifikasi SPK / PO / Item</h3><button type="button" class="modal-close"
                    id="closeClassify" aria-label="Tutup">×</button>
            </div>
            <div class="modal-body">
                <p class="modal-hint">Pilih kelompok SPK, PO, dan barang yang sama. Setiap kelompok menampilkan jumlah
                    scan dan daftar label.</p>
                <div id="labelList" class="label-list">
                    <div class="loading">Memuat daftar label...</div>
                </div>
            </div>
            <div class="modal-foot"><button type="button" id="resetClassify">Tampilkan Semua</button></div>
        </div>
    </div>

    <div class="modal-backdrop" id="labelsModal" role="dialog" aria-modal="true" aria-labelledby="labelsTitle"
        aria-hidden="true">
        <div class="modal label-status-modal">
            <div class="modal-head">
                <h3 id="labelsTitle">Daftar Label Barang</h3>
                <button type="button" class="modal-close" id="closeLabels" aria-label="Tutup">×</button>
            </div>
            <div class="modal-body">
                <div id="labelsDescription" class="modal-hint"></div>
                <div id="labelsSummary" class="label-summary"></div>
                <div id="labelsGrid" class="label-grid"></div>
                <div class="label-legend">
                    <span><i class="legend-dot" style="background:#22c55e"></i>Sudah scan</span>
                    <span><i class="legend-dot" style="background:#ef4444"></i>Belum scan</span>
                </div>
            </div>
            <div class="modal-foot"><button type="button" id="doneLabels">Tutup</button></div>
        </div>
    </div>
    <script>
        (() => {
            'use strict';
            const endpoint = @json(route('warehouse.incoming.data'));
            // Menggunakan endpoint data yang sudah ada, tanpa route baru.
            const labelsEndpoint = endpoint + '?labels_only=1';
            const el = id => document.getElementById(id);
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));
            const fmt = n => Number(n || 0).toLocaleString('id-ID');
            let page = 1,
                lastPage = 1,
                requestSeq = 0,
                controller = null,
                grouped = false,
                selectedGroup = null,
                searchTimer = null,
                groupsCache = [];
            const perPage = 25;

            function updateFilter() {
                el('classifyBtn').classList.toggle('active', grouped);
                el('classifyBtn').textContent = grouped ? '☷ Klasifikasi Aktif' : '☷ Klasifikasi';
                el('filterInfo').hidden = !grouped;
                el('filterInfo').textContent = grouped ? (selectedGroup ? 'Filter kelompok SPK / PO / Item aktif' :
                    'Klasifikasi: SPK, PO, dan item sama digabung menjadi satu baris') : '';
            }

            function updateClear() {
                el('clearSearch').hidden = !el('search').value;
            }
            async function load() {
                if (document.hidden) return;
                if (controller) controller.abort();
                controller = new AbortController();
                const seq = ++requestSeq;
                const params = new URLSearchParams({
                    page: String(page),
                    per_page: String(perPage),
                    search: el('search').value.trim()
                });
                if (grouped) params.set('grouped', '1');
                if (selectedGroup) {
                    params.set('group_spk_id', selectedGroup.spk_id);
                    params.set('group_detail_po_id', selectedGroup.detail_po_id);
                }
                try {
                    const response = await fetch(endpoint + '?' + params, {
                        headers: {
                            Accept: 'application/json'
                        },
                        cache: 'no-store',
                        credentials: 'same-origin',
                        signal: controller.signal
                    });
                    if (!response.ok) throw Error('HTTP ' + response.status);
                    const result = await response.json();
                    if (seq !== requestSeq) return;
                    el('todayCount').textContent = fmt(result.summary?.today);
                    el('totalCount').textContent = fmt(result.summary?.total);
                    el('spkCount').textContent = fmt(result.summary?.spk);
                    const records = Array.isArray(result.rows) ? result.rows : [];
                    el('rows').innerHTML = records.length ? records.map((r, i) =>
                            `<tr><td>${(page-1)*perPage+i+1}</td><td><strong>${esc(r.no_spk||'—')}</strong></td><td>${esc(r.no_po||'—')}</td><td>${esc(r.item_name||'—')}</td><td>${esc(r.total ?? r.total_qty ?? r.qty ?? '—')}</td><td>${esc(r.scan_method||'—')}</td><td>${Array.isArray(r.labels)?`<button type="button" class="show-labels" data-labels="${esc(JSON.stringify(r.labels))}" data-row="${esc(JSON.stringify({no_spk:r.no_spk,no_po:r.no_po,item_name:r.item_name}))}">Lihat ${r.labels.length} label</button>`:`<span class="pill">${esc(r.label||'—')}</span>`}</td><td>${esc(r.user_name||'—')}</td><td>${esc(r.scanned_at||'—')}</td><td>${esc(r.subkon||'—')}</td></tr>`
                        ).join('') :
                        '<tr><td colspan="10" class="muted">Tidak ada data sesuai pencarian / klasifikasi.</td></tr>';
                    lastPage = Math.max(1, Number(result.pagination?.last_page) || 1);
                    el('pageInfo').textContent =
                        `Halaman ${page} / ${lastPage} · ${fmt(result.pagination?.total)} baris`;
                    el('prev').disabled = page <= 1;
                    el('next').disabled = page >= lastPage;
                    el('lastUpdate').textContent = 'Update: ' + (result.server_time || '');
                    el('liveStatus').textContent = '● Live';
                    el('error').style.display = 'none';
                } catch (e) {
                    if (e.name === 'AbortError') return;
                    if (seq !== requestSeq) return;
                    el('liveStatus').textContent = '● Koneksi terputus';
                    el('error').textContent = 'Gagal memuat incoming: ' + e.message;
                    el('error').style.display = 'block';
                }
            }

            function renderGroups() {
                const all = [{
                    spk_id: '',
                    detail_po_id: '',
                    no_spk: 'Semua kelompok',
                    no_po: '',
                    item_name: '',
                    total: null
                }, ...groupsCache];
                el('labelList').innerHTML = all.map((g, i) =>
                    `<button type="button" class="label-option" data-group-index="${i}"><span><strong>${esc(g.no_spk)}</strong><br><small>${esc(g.no_po)} ${esc(g.item_name)}</small></span>${g.total!==null?`<b class="label-count">${fmt(g.total)} scan</b>`:''}</button>`
                ).join('');
            }
            async function openModal() {
                el('classifyModal').classList.add('open');
                el('classifyModal').setAttribute('aria-hidden', 'false');
                el('labelList').innerHTML = '<div class="loading">Memuat kelompok...</div>';
                try {
                    const res = await fetch(labelsEndpoint, {
                        headers: {
                            Accept: 'application/json'
                        },
                        cache: 'no-store',
                        credentials: 'same-origin'
                    });
                    if (!res.ok) throw Error('HTTP ' + res.status);
                    const json = await res.json();
                    groupsCache = Array.isArray(json.groups) ? json.groups : [];
                    renderGroups();
                } catch (e) {
                    el('labelList').innerHTML = '<div class="loading">Gagal memuat kelompok: ' + esc(e.message) +
                        '</div>';
                }
            }

            function closeModal() {
                el('classifyModal').classList.remove('open');
                el('classifyModal').setAttribute('aria-hidden', 'true');
            }
            el('classifyBtn').addEventListener('click', openModal);
            el('closeClassify').addEventListener('click', closeModal);
            el('classifyModal').addEventListener('click', e => {
                if (e.target === el('classifyModal')) closeModal();
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape' && el('classifyModal').classList.contains('open')) closeModal();
            });
            el('labelList').addEventListener('click', e => {
                const btn = e.target.closest('[data-group-index]');
                if (!btn) return;
                const i = Number(btn.dataset.groupIndex);
                const group = i === 0 ? null : groupsCache[i - 1];
                grouped = true;
                selectedGroup = group;
                page = 1;
                updateFilter();
                closeModal();
                load();
            });
            el('resetClassify').addEventListener('click', () => {
                grouped = false;
                selectedGroup = null;
                page = 1;
                updateFilter();
                closeModal();
                load();
            });

            function closeLabelsModal() {
                el('labelsModal').classList.remove('open');
                el('labelsModal').setAttribute('aria-hidden', 'true');
            }

            function openLabelsModal(labels, row) {
                const parsed = labels.map(value => {
                    const match = String(value).trim().match(/^(\d+)\s*\/\s*(\d+)$/);
                    return match ? {
                        sequence: Number(match[1]),
                        qty: Number(match[2])
                    } : null;
                }).filter(Boolean);
                // Total diambil dari penyebut label, bukan jumlah scan.
                const total = parsed.reduce((max, item) => Math.max(max, item.qty), 0);
                const scanned = new Set(parsed.filter(item => item.sequence >= 1 && item.sequence <= total).map(item =>
                    item.sequence));
                const missing = total - scanned.size;
                el('labelsDescription').textContent = [row?.no_spk, row?.no_po, row?.item_name].filter(Boolean).join(
                    ' · ');
                el('labelsSummary').innerHTML =
                    `<span>Total ${fmt(total)} label</span><span style="color:#166534">${fmt(scanned.size)} sudah scan</span><span style="color:#b91c1c">${fmt(missing)} belum scan</span>`;
                el('labelsGrid').innerHTML = total > 0 ?
                    Array.from({
                        length: total
                    }, (_, index) => {
                        const n = index + 1;
                        const exists = scanned.has(n);
                        return `<div class="label-tile ${exists ? '' : 'missing'}"><span>${exists ? '✓' : '✕'}</span><span>${n}/${total}</span><small>${exists ? 'Sudah scan' : 'Belum scan'}</small></div>`;
                    }).join('') :
                    '<div class="muted">Nomor urut label tidak tersedia.</div>';
                el('labelsModal').classList.add('open');
                el('labelsModal').setAttribute('aria-hidden', 'false');
                el('closeLabels').focus();
            }
            el('rows').addEventListener('click', e => {
                const btn = e.target.closest('[data-labels]');
                if (!btn) return;
                try {
                    const labels = JSON.parse(btn.dataset.labels);
                    const row = JSON.parse(btn.dataset.row || '{}');
                    openLabelsModal(Array.isArray(labels) ? labels : [], row);
                } catch (error) {
                    console.error('Gagal membaca label', error);
                }
            });
            el('closeLabels').addEventListener('click', closeLabelsModal);
            el('doneLabels').addEventListener('click', closeLabelsModal);
            el('labelsModal').addEventListener('click', e => {
                if (e.target === el('labelsModal')) closeLabelsModal();
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape' && el('labelsModal').classList.contains('open')) closeLabelsModal();
            });
            el('search').addEventListener('input', () => {
                updateClear();
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    page = 1;
                    load();
                }, 300);
            });
            el('search').addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    page = 1;
                    load();
                }
            });
            el('clearSearch').addEventListener('click', () => {
                el('search').value = '';
                updateClear();
                page = 1;
                load();
                el('search').focus();
            });
            el('prev').addEventListener('click', () => {
                if (page > 1) {
                    page--;
                    load();
                }
            });
            el('next').addEventListener('click', () => {
                if (page < lastPage) {
                    page++;
                    load();
                }
            });
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) load();
            });
            updateClear();
            updateFilter();
            load();
            setInterval(load, 5000);
        })();
    </script>
</body>

</html>
