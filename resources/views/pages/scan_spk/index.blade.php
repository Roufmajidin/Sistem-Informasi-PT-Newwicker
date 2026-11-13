<!doctype html>

<html lang="id">



<head>

    
    <meta charset="utf-8">

    
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">

    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Scanner QR SPK</title>

    <style>
        :root {

            font-family: Inter, system-ui, -apple-system, 'Segoe UI', sans-serif;

            color: #17233c;

            background: #f2f5fa
        }



        \* {

            box-sizing: border-box
        }



        body {

            margin: 0
        }



        body.modal-open {

            overflow: hidden
        }



        .wrap {

            max-width: 760px;

            margin: 24px auto;

            padding: 0 16px
        }



        .head {

            margin-bottom: 18px
        }



        .head h1 {

            margin: 0 0 5px;

            font-size: 25px
        }



        .head p,

        .muted {

            color: #64748b;

            font-size: 13px
        }



        .head p {

            margin: 0
        }



        .card {

            background: white;

            border: 1px solid #e2e8f0;

            border-radius: 18px;

            padding: 20px;

            box-shadow: 0 8px 28px #15233b0a
        }



        .card h2 {

            font-size: 16px;

            margin: 0 0 15px
        }



        #reader {

            width: 100%;

            min-height: 230px;

            overflow: hidden;

            border: 1px dashed #cbd5e1;

            border-radius: 14px;

            background: #f8fafc
        }



        #reader video {

            border-radius: 12px
        }



        .actions {

            display: flex;

            gap: 9px;

            flex-wrap: wrap;

            margin-top: 14px
        }



        button {

            border: 0;

            border-radius: 11px;

            padding: 12px 16px;

            background: #1d4ed8;

            color: white;

            font-size: 14px;

            font-weight: 700;

            cursor: pointer
        }



        button.secondary {

            background: #e8eefb;

            color: #1e40af
        }



        button:disabled {

            opacity: .5;

            cursor: not-allowed
        }



        .manual {

            display: flex;

            gap: 8px;

            margin-top: 8px
        }



        .manual input {

            flex: 1;

            min-width: 0;

            border: 1px solid #cbd5e1;

            border-radius: 10px;

            padding: 12px;

            font-size: 15px
        }



        .notice {

            margin-top: 15px;

            border-radius: 10px;

            padding: 12px;

            background: #eff6ff;

            color: #1d4ed8;

            font-size: 13px
        }



        .notice.error {

            background: #fef2f2;

            color: #b91c1c
        }



        .notice.success {

            background: #ecfdf5;

            color: #047857
        }



        .stats {

            margin-top: 12px;

            font-size: 13px;

            color: #64748b
        }



        /\* Modal transparan: kamera tetap terlihat di balik overlay \*/ .overlay {

            position: fixed;

            inset: 0;

            z-index: 1000;

            background: rgba(15, 23, 42, .45);

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 16px;

            backdrop-filter: blur(3px);

            *-webkit-backdrop-filter*: blur(3px)
        }



        .overlay[hidden] {

            display: none
        }



        .modal {

            width: min(100%, 480px);

            max-height: min(86dvh, 760px);

            overflow: auto;

            overscroll-behavior: contain;

            border-radius: 20px;

            background: rgba(255, 255, 255, .94);

            box-shadow: 0 20px 70px #0f172a45;

            border: 1px solid #ffffffa8;

            padding: 20px
        }



        .modal-head {

            display: flex;

            justify-content: space-between;

            gap: 12px;

            align-items: flex-start
        }



        .modal-title {

            margin: 0;

            font-size: 19px
        }



        .modal-sub {

            font-size: 13px;

            color: #64748b;

            margin: 5px 0 0
        }



        .modal-icon {

            font-size: 25px
        }



        .modal.duplicate {

            border: 2px solid #f59e0b
        }



        .modal.duplicate .modal-title {

            color: #a16207
        }



        .modal.ok .modal-title {

            color: #047857
        }



        .data-row {

            display: grid;

            grid-template-columns: 110px minmax(0, 1fr);

            gap: 10px;

            padding: 10px 0;

            border-bottom: 1px solid #e8edf4
        }



        .data-row:last-child {

            border: 0
        }



        .data-row span {

            font-size: 12px;

            color: #64748b
        }



        .data-row strong {

            font-size: 13px;

            overflow-wrap: anywhere
        }



        .serial {

            display: inline-block;

            background: #dbeafe;

            color: #1d4ed8;

            border-radius: 8px;

            padding: 5px 9px
        }



        .modal-content {

            margin: 14px 0
        }



        .modal-actions {

            display: flex;

            gap: 8px
        }



        .modal-actions button {

            width: 100%;

            min-height: 45px
        }



        @media(max-width:600px) {

            .wrap {

                margin: 0 auto;

                padding: 12px
            }



            .head h1 {

                font-size: 21px
            }



            .card {

                padding: 14px;

                border-radius: 14px
            }



            #reader {

                min-height: 210px
            }



            .actions button {

                flex: 1
            }



            .overlay {

                align-items: center;

                padding: 12px
            }



            .modal {

                padding: 17px;

                border-radius: 18px;

                max-height: 85dvh
            }



            .data-row {

                padding: 8px 0
            }

        }
    </style>

</head>



<body>

    <main class="wrap">

        <header class="head">

            <h1>Scanner QR SPK</h1>

            <p>Scan label menggunakan kamera atau scanner USB.</p>

            </header>

        <section class="card">

            <h2>Scan QR Code</h2>

            <div id="reader"></div>

            <div id="zoomPanel" class="zoom-panel" hidden>
                <label for="zoomRange">Zoom kamera: <strong id="zoomValue">1.0×</strong></label>
                <input type="range" id="zoomRange" min="1" max="3" step="0.1" value="1"
                    aria-label="Zoom kamera">
                <small id="zoomHint">Geser untuk memperbesar QR dari jarak lebih jauh.</small>
            </div>

            <div class="actions"><button type="button" id="startBtn">Aktifkan Kamera</button><button
                    type="button"                     id="stopBtn" class="secondary" disabled>Matikan Kamera</button>
            </div>

            <p class="muted">Kamera memerlukan HTTPS atau localhost. Tekan Aktifkan Kamera untuk
                mengizinkan audio.</p>

            <form id="manualForm" autocomplete="off"><label for="qrInput" class="muted">Input QR manual /
                    scanner

                    USB</label>

                <div class="manual"><input id="qrInput" placeholder="Scan atau tempel isi QR..."
                        required><button                         type="submit">Cari</button></div>

                </form>

            <div id="status" class="notice" role="status" aria-live="polite">Siap memindai QR SPK.</div>

            <div class="stats" id="stats">Label unik terbaca: 0 (sesi ini)</div>

            </section>

        </main>

    <div id="overlay" class="overlay" hidden>

        <section id="modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle"
            tabindex="-1">

            <div class="modal-head">

                <div>

                    <h2 class="modal-title" id="modalTitle">Hasil Scan</h2>

                    <p class="modal-sub" id="modalSub"></p>

                    </div><span class="modal-icon" id="modalIcon" aria-hidden="true">✓</span>

                </div>

            <div id="modalContent" class="modal-content"></div>

            <div class="modal-actions"><button type="button" id="closeModal">Scan Berikutnya</button></div>

            </section>

        </div>

    
    <script src="https\://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    
    <script>
        (() => {

            'use strict';

            const lookupUrl = @json(route('scan-spk.lookup'));

            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            const $ = id => document.getElementById(id);

            const status = $('status'),

                startBtn = $('startBtn'),

                stopBtn = $('stopBtn'),

                input = $('qrInput');

            const overlay = $('overlay'),

                modal = $('modal'),

                modalContent = $('modalContent');

            let scanner = null,

                scanning = false,

                busy = false,

                lastRaw = '',

                lastAt = 0,

                audioContext = null;

            // Duplikat dihitung pada browser ini selama halaman terbuka, bukan database/global antar operator.

            const scannedLabels = new Set();
            const zoomPanel = $('zoomPanel');
            const zoomRange = $('zoomRange');
            const zoomValue = $('zoomValue');
            const zoomHint = $('zoomHint');
            let zoomTrack = null;

            async function setupCameraZoom() {
                zoomPanel.hidden = true;
                zoomTrack = null;
                // html5-qrcode exposes the active MediaStreamTrack through its video element.
                const video = document.querySelector('#reader video');
                const track = video?.srcObject?.getVideoTracks?.()[0];
                if (!track || !track.getCapabilities || !track.applyConstraints) return;
                const capabilities = track.getCapabilities();
                const zoom = capabilities.zoom;
                if (!zoom || !Number.isFinite(zoom.min) || !Number.isFinite(zoom.max) ||
                    zoom.max <= zoom.min) return;
                zoomTrack = track;
                const settings = track.getSettings?.() || {};
                const current = Number.isFinite(settings.zoom) ? settings.zoom : zoom.min;
                zoomRange.min = String(zoom.min);
                zoomRange.max = String(zoom.max);
                zoomRange.step = String(zoom.step || 0.1);
                zoomRange.value = String(current);
                zoomValue.textContent = current.toFixed(1) + '×';
                zoomHint.textContent = 'Zoom optik/digital kamera didukung perangkat ini.';
                zoomPanel.hidden = false;
                // Apply moderate default zoom, avoiding excessive blur.
                const preferred = Math.min(2, zoom.max);
                if (preferred > current) {
                    try {
                        await track.applyConstraints({
                            advanced: [{
                                zoom: preferred
                            }]
                        });
                        const actual = track.getSettings?.().zoom ?? preferred;
                        zoomRange.value = String(actual);
                        zoomValue.textContent = Number(actual).toFixed(1) + '×';
                    } catch (_) {
                        /* Some mobile browsers expose but reject zoom. */ }
                }
            }

            zoomRange.addEventListener('input', async () => {
                if (!zoomTrack || zoomTrack.readyState !== 'live') return;
                const requested = Number(zoomRange.value);
                try {
                    await zoomTrack.applyConstraints({
                        advanced: [{
                            zoom: requested
                        }]
                    });
                    const actual = zoomTrack.getSettings?.().zoom ?? requested;
                    zoomValue.textContent = Number(actual).toFixed(1) + '×';
                } catch (_) {
                    zoomHint.textContent = 'Browser tidak mengizinkan zoom pada kamera ini.';
                }
            });




            function unlockAudio() {

                try {

                    const C = window.AudioContext || window.webkitAudioContext;

                    if (!C) return;

                    audioContext = audioContext || new C();

                    if (audioContext.state === 'suspended') audioContext.resume().catch(() => {});

                } catch (_) {}

            }



            function tone(freq, start, duration, volume = .16) {

                if (!audioContext || audioContext.state !== 'running') return;

                const o = audioContext.createOscillator(),

                    g = audioContext.createGain(),

                    t = audioContext.currentTime + start;

                o.type = 'sine';

                o.frequency.setValueAtTime(freq, t);

                g.gain.setValueAtTime(.0001, t);

                g.gain.exponentialRampToValueAtTime(volume, t + .012);

                g.gain.exponentialRampToValueAtTime(.0001, t + duration);

                o.connect(g);

                g.connect(audioContext.destination);

                o.start(t);

                o.stop(t + duration + .02);

            }



            function playSound(duplicate) {

                try {

                    if (duplicate) {

                        tone(480, 0, .11);

                        tone(420, .17, .12);

                    } else {

                        tone(980, 0, .16);

                    }

                } catch (_) {}

            }

            document.addEventListener('pointerdown', unlockAudio, {

                once: true

            });

            document.addEventListener('keydown', unlockAudio, {

                once: true

            });



            function setStatus(message, kind = '') {

                status.textContent = message;

                status.className = 'notice' + (kind ? ' ' + kind : '');

            }



            function row(label, value, serial = false) {

                const r = document.createElement('div');

                r.className = 'data-row';

                const k = document.createElement('span');

                k.textContent = label;

                const v = document.createElement('strong');

                v.textContent = value ?? '-';

                if (serial) v.className = 'serial';

                r.append(k, v);

                modalContent.append(r);

            }



            function showModal(d, duplicate) {

                modal.className = 'modal ' + (duplicate ? 'duplicate' : 'ok');

                $('modalTitle').textContent = duplicate ? 'Label Sudah Di-scan' : 'Scan Berhasil';

                $('modalSub').textContent = duplicate ? 'Nomor urutan ini sudah terbaca pada sesi ini.' :

                    'Label berhasil diverifikasi ke SPK.';

                $('modalIcon').textContent = duplicate ? '⚠' : '✓';

                modalContent.replaceChildren();

                row('Nomor SPK', d.no_spk);

                row('Nama Produk', d.name ?? d.nama);

                row('Kode', d.kode);

                row('Material', d.material);

                row('Finishing', d.finishing);

                row('Nomor PO', d.no_po);

                row('SUB', d.supplier);

                row('Nomor Label', d.number ?? d.serial ?? (d.sequence + '/' + d.qty), true);

                row('Detail PO ID', d.detail_po_id);

                overlay.hidden = false;

                document.body.classList.add('modal-open');

                $('closeModal').focus();

            }



            function closeModal() {

                overlay.hidden = true;

                document.body.classList.remove('modal-open');

                lastRaw = '';

                lastAt = 0;

                if (!scanning) input.focus();

            }

            $('closeModal').addEventListener('click', closeModal);

            // Jangan auto-close: operator harus sempat melihat detail. Kamera tetap aktif di belakang modal.

            async function lookup(raw) {

                raw = String(raw || '').trim();

                if (!raw || busy || !overlay.hidden) return;

                if (raw === lastRaw && Date.now() - lastAt < 2000) return;

                lastRaw = raw;

                lastAt = Date.now();

                busy = true;

                setStatus('Memverifikasi QR ke database...');

                try {

                    const response = await fetch(lookupUrl, {

                        method: 'POST',

                        credentials: 'same-origin',

                        headers: {

                            'Content-Type': 'application/json',

                            'Accept': 'application/json',

                            'X-CSRF-TOKEN': csrf

                        },

                        body: JSON.stringify({

                            qr: raw

                        })

                    });

                    const payload = await response.json();

                    if (!response.ok || payload.success === false) throw new Error(payload.message ||

                        'QR gagal diverifikasi.');

                    const d = payload.data;

                    if (!d) throw new Error('Data SPK kosong.');

                    const labelNo = String(d.number ?? d.serial ?? (d.sequence + '/' + d.qty));

                    const key = [d.spk_id, d.detail_po_id, labelNo].join('|');

                    const duplicate = scannedLabels.has(key);

                    if (!duplicate) scannedLabels.add(key);

                    $('stats').textContent = 'Label unik terbaca: ' + scannedLabels.size + ' (sesi ini)';

                    setStatus(duplicate ? 'Label ' + labelNo + ' sudah pernah di-scan pada sesi ini.' : (payload

                        .message || 'QR berhasil diverifikasi.'), duplicate ? 'error' : 'success');

                    playSound(duplicate);

                    showModal(d, duplicate);

                } catch (e) {

                    setStatus(e.message || 'Terjadi kesalahan.', 'error');

                } finally {

                    busy = false;

                }

            }

            startBtn.addEventListener('click', async () => {

                unlockAudio();

                if (scanning) return;

                if (typeof Html5Qrcode === 'undefined') {

                    setStatus('Library scanner tidak termuat. Periksa internet.', 'error');

                    return;

                }

                try {

                    scanner = scanner || new Html5Qrcode('reader');

                    await scanner.start({

                        facingMode: 'environment'

                    }, {

                        fps: 10,

                        qrbox: {

                            width: 220,

                            height: 220

                        }

                    }, text => lookup(text));

                    scanning = true;
                    await setupCameraZoom();

                    startBtn.disabled = true;

                    stopBtn.disabled = false;

                    setStatus('Kamera aktif. Arahkan ke QR SPK.');

                } catch (e) {

                    setStatus('Kamera gagal diaktifkan: ' + (e.message || e), 'error');

                }

            });

            stopBtn.addEventListener('click', async () => {

                if (!scanner || !scanning) return;

                try {

                    await scanner.stop();
                    zoomTrack = null;
                    zoomPanel.hidden = true;

                    scanning = false;

                    startBtn.disabled = false;

                    stopBtn.disabled = true;

                    setStatus('Kamera dimatikan.');

                } catch (_) {

                    setStatus('Gagal menghentikan kamera.', 'error');

                }

            });

            $('manualForm').addEventListener('submit', e => {

                e.preventDefault();

                unlockAudio();

                lookup(input.value);

                input.select();

            });

        })();
    </script>

</body>



</html>
