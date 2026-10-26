@extends('master.master')
@section('title', 'Portal NewWicker')
@section('content')
@php
    $auth = auth()->user();
    $karyawan = \App\Models\Karyawan::find($auth->karyawan_id);
    $divisiId = $karyawan->divisi_id ?? null;
    $allowedDivisi = [26, 38, 32, 46, 37];
@endphp
<style>
.nw-dashboard{padding:18px 20px 26px;background:#f6f8fc;color:#17233c;font-family:inherit}
.nw-dashboard *{box-sizing:border-box}
.nw-dash-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0 0 16px}
.nw-dash-heading h2{font-size:20px;font-weight:700;letter-spacing:-.4px;margin:0;color:#17233c}
.nw-dash-heading p{margin:4px 0 0;color:#8290a4;font-size:12px}
.nw-shortcuts{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:18px}
.nw-shortcut{display:flex;align-items:center;gap:12px;min-height:78px;padding:15px;background:#fff;border:1px solid #e8edf5;border-radius:12px;color:inherit;text-decoration:none!important;transition:border-color .2s,transform .2s,box-shadow .2s;min-width:0}
.nw-shortcut:hover{transform:translateY(-2px);border-color:#cad7ec;box-shadow:0 8px 20px rgba(30,48,78,.06);color:inherit}
.nw-shortcut-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex:none;font-size:20px}
.nw-shortcut-icon.purple{background:#f1edff;color:#8061d9}.nw-shortcut-icon.teal{background:#e5faf6;color:#00a995}.nw-shortcut-icon.blue{background:#eaf1ff;color:#3775e9}.nw-shortcut-icon.orange{background:#fff3e5;color:#e99935}
.nw-shortcut-body{min-width:0;flex:1}.nw-shortcut-title{font-size:13px;font-weight:700;color:#253450;margin-bottom:3px}.nw-shortcut-sub{font-size:11px;color:#8995a8;line-height:1.4}.nw-shortcut-arrow{color:#a9b5c5;font-size:17px}
.nw-approval-content{font-size:11px;color:#68778e;margin-top:5px}.nw-approval-content hr{margin:6px 0}
.nw-timeline-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;align-items:start}
.nw-timeline-card{background:#fff;border:1px solid #e7ecf3;border-radius:13px;overflow:hidden;min-width:0;box-shadow:0 3px 14px rgba(18,38,72,.025)}
.nw-timeline-head{padding:15px 17px 12px;border-bottom:1px solid #edf1f6;display:flex;align-items:center;justify-content:space-between;gap:8px}
.nw-timeline-head h3{margin:0;font-size:14px;font-weight:700;color:#24314a}.nw-timeline-head p{margin:4px 0 0;color:#98a3b3;font-size:11px}
.nw-head-icon{display:inline-flex;align-items:center;justify-content:center;width:31px;height:31px;border-radius:9px;background:#edf3ff;color:#3c6cdb;font-size:15px}
.nw-timeline-scroll{height:330px;max-height:330px;overflow-y:auto;overflow-x:hidden;overscroll-behavior:contain;padding:14px 17px 12px;scrollbar-width:thin;scrollbar-color:#cad5e5 transparent}
.nw-timeline-scroll::-webkit-scrollbar{width:6px}.nw-timeline-scroll::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:10px}
.nw-timeline-scroll .streamline{margin-bottom:0!important;padding-left:16px!important;border-left:1px solid #e6ebf2!important}
.nw-timeline-scroll .sl-item{margin-bottom:0!important;padding:0 0 15px 5px;position:relative;min-width:0}
.nw-timeline-scroll .sl-item:last-child{padding-bottom:0}
.nw-timeline-scroll .sl-item:before{width:8px;height:8px;background:#70b69b;border:2px solid #fff;box-shadow:0 0 0 1px #dce5ee;left:-21px;top:5px}
.nw-timeline-scroll .sl-content{font-size:12px;color:#334155;line-height:1.5;overflow-wrap:anywhere}
.nw-timeline-scroll .sl-content p{margin:3px 0 0}
.nw-timeline-scroll .sl-date{font-size:10px;color:#9aa5b5!important;margin-bottom:3px}
.nw-timeline-scroll .badge{font-size:9px;padding:4px 6px;border-radius:5px}
.nw-timeline-scroll .d-flex{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:5px}
.nw-timeline-scroll .text-muted{color:#8a97a9!important}
.nw-timeline-scroll .collapse{margin-top:8px;padding:10px;background:#f7f9fd;border:1px solid #e9eef6;border-radius:7px;overflow-wrap:anywhere}
.nw-timeline-scroll code{white-space:normal;overflow-wrap:anywhere}
.nw-timeline-foot{padding:10px 17px;border-top:1px solid #edf1f6;background:#fbfcff;font-size:10px;color:#95a0b2;display:flex;align-items:center;gap:6px}
.nw-empty{padding:40px 10px;text-align:center;color:#9ba6b7;font-size:12px}
@media(max-width:900px){.nw-shortcuts{grid-template-columns:repeat(2,minmax(0,1fr))}.nw-timeline-grid{grid-template-columns:1fr}}
@media(max-width:520px){.nw-dashboard{padding:12px}.nw-shortcuts{grid-template-columns:1fr}.nw-timeline-scroll{height:300px;max-height:300px}}

/* SPK overview: four responsive KPI cards, dummy values only */
.nw-spk-section{margin:0 0 18px}
.nw-spk-section-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:0 0 11px}
.nw-spk-section-head h3{font-size:14px;font-weight:750;color:#253450;margin:0}
.nw-spk-dummy{font-size:10px;color:#8794a8;background:#eef2f8;border:1px solid #e1e8f1;padding:5px 9px;border-radius:20px}
.nw-spk-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
.nw-spk-stat{min-width:0;display:flex;flex-direction:column;gap:12px;background:#fff;border:1px solid #e7ecf3;border-radius:13px;padding:16px;box-shadow:0 3px 14px rgba(18,38,72,.025)}
.nw-spk-stat-top{display:flex;align-items:center;justify-content:space-between;gap:8px}
.nw-spk-stat-icon{width:35px;height:35px;border-radius:10px;display:flex;align-items:center;justify-content:center}
.nw-spk-stat-icon .material-icons{font-size:19px}
.nw-spk-stat-icon.nw{background:#edf3ff;color:#3265dc}
.nw-spk-stat-icon.nws{background:#e5faf6;color:#0caa93}
.nw-spk-stat-icon.nwd{background:#f4edff;color:#9060d5}
.nw-spk-stat-icon.total{background:#fff2e5;color:#e58b2c}
.nw-spk-stat-name{font-size:11px;font-weight:700;color:#53627b}
.nw-spk-stat-number{font-size:28px;font-weight:800;line-height:1.1;letter-spacing:-.8px;color:#17233c;font-variant-numeric:tabular-nums}
.nw-spk-stat-number small{font-size:11px;font-weight:500;color:#94a0b2;letter-spacing:0}
.nw-spk-stat-sub{font-size:10px;color:#97a2b2;margin-top:3px}
@media(max-width:900px){.nw-spk-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:520px){.nw-spk-stats{gap:9px}.nw-spk-stat{padding:12px}.nw-spk-stat-number{font-size:24px}}
</style>
<div class="nw-dashboard">
    <div class="nw-dash-heading"><div><h2>Dashboard</h2><p>Aktivitas dan akses cepat NewWicker</p></div></div>
    <div class="nw-shortcuts">
        @if(in_array($divisiId, $allowedDivisi))
            <div class="nw-shortcut"><span class="nw-shortcut-icon blue"><i class="material-icons">assignment_turned_in</i></span><div class="nw-shortcut-body"><div class="nw-shortcut-title"><a href="/riwayat-absen">Pengajuan</a></div><div id="approval-box" class="nw-approval-content"></div></div></div>
            <a class="nw-shortcut" href="/spk/request-r"><span class="nw-shortcut-icon orange"><i class="material-icons">receipt_long</i></span><div class="nw-shortcut-body"><div class="nw-shortcut-title">Pengajuan Pembayaran SPK</div><div class="nw-shortcut-sub">Buka halaman pengajuan</div></div><span class="nw-shortcut-arrow">›</span></a>
        @endif
        <a class="nw-shortcut" href="/karyawan-scan"><span class="nw-shortcut-icon purple"><i class="material-icons">event_available</i></span><div class="nw-shortcut-body"><div class="nw-shortcut-title">Absen Sekarang</div><div class="nw-shortcut-sub">Catat kehadiran hari ini</div></div><span class="nw-shortcut-arrow">›</span></a>
        <a class="nw-shortcut" href="/riwayat-absen"><span class="nw-shortcut-icon teal"><i class="material-icons">history</i></span><div class="nw-shortcut-body"><div class="nw-shortcut-title">Riwayat Absen</div><div class="nw-shortcut-sub">Lihat riwayat kehadiran</div></div><span class="nw-shortcut-arrow">›</span></a>
        <a class="nw-shortcut" href="#"><span class="nw-shortcut-icon blue"><i class="material-icons">lock_outline</i></span><div class="nw-shortcut-body"><div class="nw-shortcut-title">Ubah Password</div><div class="nw-shortcut-sub">Pengaturan keamanan akun</div></div><span class="nw-shortcut-arrow">›</span></a>
    </div>
    {{-- SPK counters are intentionally dummy data until the database query is ready. --}}
    @php
        $spkDummy = [
            ['label' => 'SPK NW', 'count' => 00000, 'icon' => 'precision_manufacturing', 'type' => 'nw', 'desc' => 'SPK Produksi'],
            ['label' => 'SPK NWS', 'count' => 00000, 'icon' => 'inventory_2', 'type' => 'nws', 'desc' => 'SPK Samples'],
            ['label' => 'SPK NWD', 'count' => 00000, 'icon' => 'layers', 'type' => 'nwd', 'desc' => 'SPK NWD'],
        ];
        $spkDummyTotal = array_sum(array_column($spkDummy, 'count'));
    @endphp
    <section class="nw-spk-section" aria-label="Ringkasan SPK">
        <div class="nw-spk-section-head"><h3>Ringkasan SPK</h3><span class="nw-spk-dummy">Data simulasi</span></div>
        <div class="nw-spk-stats">
            @foreach($spkDummy as $stat)
                <div class="nw-spk-stat">
                    <div class="nw-spk-stat-top"><span class="nw-spk-stat-name">{{ $stat['label'] }}</span><span class="nw-spk-stat-icon {{ $stat['type'] }}"><i class="material-icons">{{ $stat['icon'] }}</i></span></div>
                    <div><div class="nw-spk-stat-number">{{ number_format($stat['count']) }} <small>SPK</small></div><div class="nw-spk-stat-sub">{{ $stat['desc'] }}</div></div>
                </div>
            @endforeach
            <div class="nw-spk-stat">
                <div class="nw-spk-stat-top"><span class="nw-spk-stat-name">TOTAL SPK</span><span class="nw-spk-stat-icon total"><i class="material-icons">assessment</i></span></div>
                <div><div class="nw-spk-stat-number">{{ number_format($spkDummyTotal) }} <small>SPK</small></div><div class="nw-spk-stat-sub">NW + NWS + NWD</div></div>
            </div>
        </div>
    </section>
    <div class="nw-timeline-grid">
        <section class="nw-timeline-card"><div class="nw-timeline-head"><div><h3>PO Timeline</h3><p>Riwayat aktivitas Purchase Order</p></div><span class="nw-head-icon"><i class="material-icons">history</i></span></div><div class="nw-timeline-scroll"><div class="streamline b-l m-b" id="timelineContainer"><div class="nw-empty">Memuat aktivitas PO...</div></div></div><div class="nw-timeline-foot"><i class="material-icons" style="font-size:13px">unfold_more</i>Scroll untuk melihat aktivitas lainnya</div></section>
        <section class="nw-timeline-card"><div class="nw-timeline-head"><div><h3>SPK Timeline</h3><p>Riwayat pembuatan dan perubahan SPK</p></div><span class="nw-head-icon"><i class="material-icons">assignment</i></span></div><div class="nw-timeline-scroll"><div class="streamline b-l m-l" id="spkTimelineContainer"><div class="nw-empty">Memuat aktivitas SPK...</div></div></div><div class="nw-timeline-foot"><i class="material-icons" style="font-size:13px">unfold_more</i>Scroll untuk melihat aktivitas lainnya</div></section>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function loadSpkTimeline() {

        $.get("{{ route('spk.time') }}", function(res) {

            let html = '';

            res.forEach((row, index) => {

                let badge = '';

                let content = '';

                let collapseId = `collapse-${row.id}`;

                if (row.type === 'create') {

                    badge = `<span class="badge badge-success">CREATE</span>`;

                    let itemsHtml = '';

                    if (row.after?.items && Array.isArray(row.after.items)) {

                        itemsHtml = `

            <ul class="mb-0 pl-3 small text-muted">

                ${row.after.items.map(item => `

                    <li>

                        ${item.nama}

                        <b>

                            (${item.qty} ${item.satuan})

                        </b>

                    </li>

                `).join('')}

            </ul>

        `;

                    }

                    content = `

        <div class="mt-1 text-muted">

            <div><b>SPK dibuat</b></div>

            <div>No SPK : <b>${row.after?.no_spk ?? '-'}</b></div>

            <div>Supplier : <b>${row.after?.sup ?? '-'}</b></div>

            ${itemsHtml}

        </div>

    `;

                }

                if (row.type === 'update') {

                    badge = `<span class="badge badge-warning">UPDATE</span>`;

                    let changeCount = row.changes ?

                        Object.keys(row.changes).length :

                        0;

                    // HEADER RINGKAS

                    content = `

                    <div class="mt-1">

                        <a data-toggle="collapse"

                           href="#${collapseId}"

                           style="text-decoration:none">

                            SPK diperbarui

                            <span class="text-muted">

                                (${changeCount} perubahan)

                            </span>

                            <i class="ml-1 fa fa-chevron-down"></i>

                        </a>

                        <div class="collapse mt-2" id="${collapseId}">

                            ${renderChanges(row.changes)}

                        </div>

                    </div>

                `;

                }

                html += `

                <div class="sl-item mb-2">

                    <div class="sl-content">

                        <div class="d-flex justify-content-between">

                            <div>

                                ${badge}

                                <strong class="ml-1">${row.user ?? '-'}</strong>

                            </div>

                            <small class="text-muted">

                                ${formatTanggal(row.time)}

                            </small>

                        </div>

                        ${content}

                    </div>

                </div>

            `;

            });

            $('#spkTimelineContainer').html(html);

        });

    }

    function renderChanges(changes) {

        if (!changes) return '';

        let html = '<ul class="list-unstyled small mb-0">';

        Object.keys(changes).forEach(key => {

            let before = formatValue(changes[key].before);

            let after = formatValue(changes[key].after);

            html += `

            <li>

                <code>${key}</code> :

                <span class="text-danger">${before}</span>

                →

                <span class="text-success">${after}</span>

            </li>

        `;

        });

        html += '</ul>';

        return html;

    }

    function formatValue(val) {

        if (val === null) return '<i>null</i>';

        // IMAGE DETECT

        if (typeof val === 'string' && val.match(/\.(jpg|png|jpeg|webp)$/)) {

            return `<img src="${val}" style="height:30px;border:1px solid #ccc">`;

        }

        if (typeof val === 'object') {

            return JSON.stringify(val);

        }

        return val;

    }

    function formatTanggal(date) {

        return new Date(date).toLocaleString('id-ID');

    }

    loadSpkTimeline();

    // timeline lama

    function loadTimeline() {

        $.ajax({

            url: "{{ route('timeline.data') }}",

            type: "GET",

            dataType: "json",

            success: function(data) {

                let html = '';

                console.log(data)

                data.forEach(function(item) {

                    let userName = item.user?.name ?? 'Unknown';

                    let remark = item.isi?.remark ?? '-';

                    let created = item.isi?.timestamp ?

                        new Date(item.isi.timestamp).toLocaleString() :

                        new Date(item.created_at).toLocaleString();

                    let jenisClass = item.jenis === 'edit pfi' ? 'b-success' : '';

                    html += `

                        <div class="sl-item ${jenisClass}">

                            <div class="sl-content">

                                <div class="sl-date text-muted">${created}</div>

                                <p><strong>${userName}</strong>: ${remark}</p>

                            </div>

                        </div>

                    `;

                });

                $('#timelineContainer').html(html);

            }

        });

    }

    // 🔥 WAJIB DIPANGGIL

    loadTimeline();

    // loadSpkTimeline();

    function loadMyApproval() {

        $.get('/dashboard/pending-approval', function(res) {

            let html = '';

            html += `

        <b>${res.total_pengajuan} pengajuan</b><br>

        <small>${res.total_step} approval menunggu persetujuanmu</small>

        <hr>

    `;

            res.data.forEach(item => {

                html += `

            <div>

               <a href="/pengajuan?pengajuan_id=${item.id}">

                    Pengajuan #${item.id}

                </a>

            </div>

        `;

            });

            $('#approval-box').html(html);

        });

    }

    loadMyApproval();


</script>
@endsection
