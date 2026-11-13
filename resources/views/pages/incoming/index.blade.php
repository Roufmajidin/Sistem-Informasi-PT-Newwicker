<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Warehouse Incoming | NewWicker</title>
    <style>
        :root { color-scheme: light; --navy:#173a68; --blue:#2563eb; --border:#e2e8f0; --muted:#64748b; }
        * { box-sizing:border-box; }
        body { margin:0; background:#f5f7fb; color:#18263a; font-family:Inter,system-ui,-apple-system,'Segoe UI',sans-serif; }
        .container { max-width:1380px; margin:auto; padding:24px; }
        .heading { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:18px; }
        h1 { font-size:25px; margin:0 0 4px; color:var(--navy); }
        .muted { color:var(--muted); font-size:13px; }
        .live { color:#15803d; background:#dcfce7; border-radius:5px; padding:7px 10px; font-size:12px; font-weight:700; }
        .cards { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; margin-bottom:16px; }
        .card,.panel { background:white; border:1px solid var(--border); border-radius:9px; box-shadow:0 2px 10px #18375a08; }
        .card { padding:18px; } .card .value { font-size:30px; font-weight:800; color:var(--navy); margin-top:6px; }
        .panel { overflow:hidden; }
        .toolbar { display:flex; justify-content:space-between; gap:12px; align-items:center; padding:17px; flex-wrap:wrap; }
        .toolbar h2 { font-size:17px; margin:0; }
        input,button { font:inherit; border-radius:6px; }
        input { border:1px solid #cbd5e1; padding:10px 12px; width:280px; max-width:100%; }
        button { cursor:pointer; border:1px solid #cbd5e1; background:white; padding:8px 12px; }
        button:disabled { opacity:.5; cursor:default; }
        .table-scroll { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; min-width:1100px; }
        th { background:#f1f5f9; color:#475569; font-size:12px; text-align:left; }
        th,td { padding:12px 14px; border-top:1px solid #e9eef5; }
        td { font-size:13px; } tr:hover td { background:#f8fbff; }
        .pill { display:inline-block; padding:4px 8px; background:#eff6ff; color:#1d4ed8; border-radius:4px; font-weight:700; }
        .footer { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; padding:14px 18px; border-top:1px solid var(--border); }
        .error { color:#b91c1c; font-size:13px; padding:0 18px 14px; display:none; }
        @media(max-width:640px) { .container{padding:12px;} .cards{gap:8px;} .card{padding:12px;} .card .value{font-size:24px;} h1{font-size:21px;} input{width:100%;} }
    </style>
</head>
<body>
<main class="container">
    <div class="heading">
        <div><h1>Warehouse Incoming</h1><div class="muted">Monitoring scan QR SPK masuk · otomatis setiap 5 detik</div></div>
        <div><span class="live" id="liveStatus">● Menghubungkan...</span> <span class="muted" id="lastUpdate"></span></div>
    </div>
    <section class="cards">
        <div class="card"><div class="muted">Scan berhasil hari ini</div><div class="value" id="todayCount">—</div></div>
        <div class="card"><div class="muted">Total scan berhasil</div><div class="value" id="totalCount">—</div></div>
        <div class="card"><div class="muted">SPK ter-scan</div><div class="value" id="spkCount">—</div></div>
    </section>
    <section class="panel">
        <div class="toolbar"><h2>Riwayat Barang Masuk</h2><input type="search" id="search" placeholder="Cari SPK, detail PO, atau QR..."></div>
        <div class="table-scroll"><table><thead><tr><th>No</th><th>No SPK</th><th>PO</th><th>Items Name</th><th>Labels</th><th>Method</th><th>User</th><th>Scanned At</th><th>Subkon</th></tr></thead><tbody id="rows"><tr><td colspan="9" class="muted">Memuat data...</td></tr></tbody></table></div>
        <div id="error" class="error" role="alert"></div>
        <div class="footer"><span class="muted" id="pageInfo">—</span><div><button id="prev">Sebelumnya</button> <button id="next">Berikutnya</button></div></div>
    </section>
</main>
<script>
(() => {
    'use strict';
    const endpoint = @json(route('warehouse.incoming.data'));
    const el = id => document.getElementById(id);
    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    let page = 1, lastPage = 1, busy = false, requestSeq = 0;
    const fmt = n => Number(n || 0).toLocaleString('id-ID');
    async function load() {
        if (busy || document.hidden) return;
        busy = true;
        const seq = ++requestSeq;
        const params = new URLSearchParams({page: String(page), per_page:'25', search:el('search').value.trim()});
        try {
            const response = await fetch(endpoint + '?' + params.toString(), {headers:{Accept:'application/json'},cache:'no-store',credentials:'same-origin'});
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const result = await response.json();
            if (seq !== requestSeq) return;
            el('todayCount').textContent = fmt(result.summary.today);
            el('totalCount').textContent = fmt(result.summary.total);
            el('spkCount').textContent = fmt(result.summary.spk);
            const records = Array.isArray(result.rows) ? result.rows : [];
            el('rows').innerHTML = records.length ? records.map((r, i) => `<tr>
                <td>${(page - 1) * 25 + i + 1}</td>
                <td><strong>${escapeHtml(r.no_spk)}</strong></td><td>${escapeHtml(r.no_po || '—')}</td>
                <td>${escapeHtml(r.item_name || '—')}</td>
                <td><span class="pill">${escapeHtml(r.label)}</span></td><td>${escapeHtml(r.scan_method)}</td>
                <td>${escapeHtml(r.user_name || '—')}</td><td>${escapeHtml(r.scanned_at)}</td><td>${escapeHtml(r.subkon || '—')}</td>
            </tr>`).join('') : '<tr><td colspan="9" class="muted">Belum ada scan berhasil.</td></tr>';
            lastPage = Math.max(1, Number(result.pagination.last_page) || 1);
            el('pageInfo').textContent = `Halaman ${page} / ${lastPage} · ${fmt(result.pagination.total)} baris`;
            el('prev').disabled = page <= 1;
            el('next').disabled = page >= lastPage;
            el('lastUpdate').textContent = 'Update: ' + (result.server_time || '');
            el('liveStatus').textContent = '● Live';
            el('error').style.display = 'none';
        } catch (e) {
            el('liveStatus').textContent = '● Koneksi terputus';
            el('error').textContent = 'Gagal memuat incoming: ' + e.message;
            el('error').style.display = 'block';
        } finally { busy = false; }
    }
    let searchTimer;
    el('search').addEventListener('input', () => { clearTimeout(searchTimer); searchTimer = setTimeout(() => {page=1;load();}, 350); });
    el('prev').addEventListener('click', () => { if(page>1){page--;load();} });
    el('next').addEventListener('click', () => { if(page<lastPage){page++;load();} });
    document.addEventListener('visibilitychange', () => { if(!document.hidden)load(); });
    load();
    setInterval(load, 5000);
})();
</script>
</body>
</html>
