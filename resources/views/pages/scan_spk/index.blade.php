<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scanner QR SPK</title>
    <style>
        :root {
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #17233c;
            background: #f3f6fb
        }

        * {
            box-sizing: border-box
        }

        body {
            margin: 0
        }

        .wrap {
            max-width: 1040px;
            margin: 32px auto;
            padding: 0 18px
        }

        .head {
            margin-bottom: 22px
        }

        .head h1 {
            font-size: 27px;
            margin: 0 0 6px
        }

        .head p {
            color: #64748b;
            margin: 0
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 18px
        }

        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 6px 24px #17233c08
        }

        .card h2 {
            font-size: 16px;
            margin: 0 0 16px
        }

        .muted {
            font-size: 13px;
            color: #64748b
        }

        #reader {
            width: 100%;
            min-height: 240px;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            overflow: hidden;
            background: #f8fafc
        }

        #reader video {
            border-radius: 10px
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 14px
        }

        button {
            background: #1d4ed8;
            color: #fff;
            border: 0;
            border-radius: 9px;
            padding: 11px 14px;
            font-weight: 600;
            cursor: pointer
        }

        button.secondary {
            background: #e9effb;
            color: #1e40af
        }

        button:disabled {
            opacity: .5;
            cursor: not-allowed
        }

        .manual {
            display: flex;
            gap: 8px;
            margin-top: 12px
        }

        .manual input {
            flex: 1;
            min-width: 0;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            padding: 11px;
            font-size: 14px
        }

        .notice {
            margin-top: 14px;
            border-radius: 9px;
            padding: 12px;
            font-size: 13px;
            background: #eff6ff;
            color: #1d4ed8
        }

        .notice.error {
            background: #fef2f2;
            color: #b91c1c
        }

        .notice.success {
            background: #ecfdf5;
            color: #047857
        }

        .result-empty {
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 36px 16px;
            text-align: center;
            color: #94a3b8
        }

        .data-row {
            padding: 11px 0;
            border-bottom: 1px solid #eef2f7;
            display: grid;
            grid-template-columns: 125px 1fr;
            gap: 12px
        }

        .data-row:last-child {
            border-bottom: 0
        }

        .data-row span {
            font-size: 12px;
            color: #64748b
        }

        .data-row strong {
            font-size: 14px;
            overflow-wrap: anywhere
        }

        .serial {
            display: inline-block;
            background: #dbeafe;
            color: #1d4ed8;
            border-radius: 8px;
            padding: 5px 9px;
            font-weight: 700
        }

        @media(max-width:720px) {
            .wrap {
                margin: 18px auto
            }

            .layout {
                grid-template-columns: 1fr
            }

            .card {
                padding: 16px
            }

            .head h1 {
                font-size: 23px
            }
        }
    </style>
</head>

<body>
    <main class="wrap">
        <div class="head">
            <h1>Scanner QR SPK</h1>
            <p>Scan label SPK menggunakan kamera atau scanner USB.</p>
        </div>
        <div class="layout">
            <section class="card">
                <h2>Scan QR Code</h2>
                <div id="reader"></div>
                <div class="actions">
                    <button type="button" id="startBtn">Aktifkan Kamera</button>
                    <button type="button" id="stopBtn" class="secondary" disabled>Matikan Kamera</button>
                </div>
                <p class="muted">Kamera membutuhkan HTTPS atau localhost dan izin kamera dari browser.</p>
                <form id="manualForm" autocomplete="off">
                    <label for="qrInput" class="muted">Input QR manual / scanner USB</label>
                    <div class="manual"><input id="qrInput" placeholder="Scan atau tempel isi QR..." required><button
                            type="submit">Cari</button></div>
                </form>
                <div id="status" class="notice" role="status" aria-live="polite">Siap memindai QR SPK.</div>
            </section>
            <section class="card">
                <h2>Hasil Scan</h2>
                <div id="empty" class="result-empty">Belum ada QR yang dipindai.</div>
                <div id="result" hidden></div>
            </section>
        </div>
    </main>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        (() => {
            const lookupUrl = @json(route('scan-spk.lookup'));
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const status = document.getElementById('status');
            const startBtn = document.getElementById('startBtn');
            const stopBtn = document.getElementById('stopBtn');
            const result = document.getElementById('result');
            const empty = document.getElementById('empty');
            const input = document.getElementById('qrInput');
            let scanner = null,
                scanning = false,
                busy = false,
                lastRaw = '',
                lastAt = 0;
            let audioContext = null;

            function unlockAudio() {
                try {
                    const AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (!AudioCtx) return;
                    audioContext = audioContext || new AudioCtx();
                    if (audioContext.state === 'suspended') audioContext.resume().catch(() => {});
                } catch (_) {
                    /* Audio not available on this device */ }
            }

            function beepSuccess() {
                try {
                    if (!audioContext || audioContext.state !== 'running') return;
                    const oscillator = audioContext.createOscillator();
                    const gain = audioContext.createGain();
                    const now = audioContext.currentTime;
                    oscillator.type = 'sine';
                    oscillator.frequency.setValueAtTime(1100, now);
                    gain.gain.setValueAtTime(0.0001, now);
                    gain.gain.exponentialRampToValueAtTime(0.18, now + 0.012);
                    gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.16);
                    oscillator.connect(gain);
                    gain.connect(audioContext.destination);
                    oscillator.start(now);
                    oscillator.stop(now + 0.17);
                } catch (_) {
                    /* Scanning must continue even if audio fails */ }
            }
            // Mobile browsers require an explicit user gesture before audio can play.
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

            function addRow(label, value, isSerial = false) {
                const row = document.createElement('div');
                row.className = 'data-row';
                const key = document.createElement('span');
                key.textContent = label;
                const val = document.createElement('strong');
                val.textContent = value ?? '-';
                if (isSerial) val.className = 'serial';
                row.append(key, val);
                result.appendChild(row);
            }
            async function lookup(raw) {
                raw = String(raw || '').trim();
                if (!raw || busy) return;
                if (raw === lastRaw && Date.now() - lastAt < 2500) return;
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
                    if (!response.ok) throw new Error(payload.message || 'QR gagal diverifikasi.');
                    const d = payload.data;
                    result.replaceChildren();
                    empty.hidden = true;
                    result.hidden = false;
                    addRow('Nomor SPK', d.no_spk);
                    addRow('Nama Produk', d.nama);
                    addRow('Kode', d.kode);
                    addRow('Material', d.material);
                    addRow('Nomor PO', d.no_po);
                    addRow('SUB', d.supplier);
                    addRow('Nomor Label', d.number ?? d.serial ?? ((d.sequence && d.qty) ? (d.sequence + '/' + d
                        .qty) : '-'), true);
                    addRow('Detail PO ID', d.detail_po_id);
                    setStatus(payload.message || 'QR berhasil diverifikasi.', 'success');
                    beepSuccess();
                } catch (error) {
                    setStatus(error.message || 'Terjadi kesalahan.', 'error');
                } finally {
                    busy = false;
                }
            }
            startBtn.addEventListener('click', async () => {
                unlockAudio();
                if (scanning) return;
                if (typeof Html5Qrcode === 'undefined') {
                    setStatus('Library scanner tidak termuat. Periksa koneksi internet.', 'error');
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
                    scanning = false;
                    startBtn.disabled = false;
                    stopBtn.disabled = true;
                    setStatus('Kamera dimatikan.');
                } catch (e) {
                    setStatus('Gagal menghentikan kamera.', 'error');
                }
            });
            document.getElementById('manualForm').addEventListener('submit', e => {
                e.preventDefault();
                unlockAudio();
                lookup(input.value);
                input.select();
            });
        })();
    </script>
</body>

</html>
