@extends('master.master')
@section('title', 'Magic Approval SPK')
@section('content')
@php
    $spkData = is_array($spk->data ?? null) ? $spk->data : (json_decode((string) ($spk->data ?? '{}'), true) ?: []);
    $expiredAt = $magic->expires_at;
@endphp
<style>
    .spk-magic-wrap{max-width:700px;margin:42px auto;padding:0 16px;font-family:inherit}
    .spk-magic-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 12px 35px rgba(15,23,42,.07);overflow:hidden}
    .spk-magic-head{padding:24px 28px;background:#f8fafc;border-bottom:1px solid #e2e8f0}
    .spk-magic-title{font-size:21px;font-weight:750;color:#172033;margin:0 0 6px}
    .spk-magic-sub{font-size:13px;color:#64748b;margin:0}
    .spk-magic-body{padding:26px 28px}
    .spk-magic-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:22px}
    .spk-magic-label{display:block;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#64748b;margin-bottom:5px}
    .spk-magic-value{font-size:14px;font-weight:650;color:#1e293b;overflow-wrap:anywhere}
    .spk-magic-note{background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;border-radius:9px;padding:12px 14px;font-size:12px;margin-bottom:20px}
    .spk-magic-textarea{display:block;width:100%;min-height:100px;padding:12px;border:1px solid #cbd5e1;border-radius:9px;resize:vertical;font-size:13px}
    .spk-magic-textarea:focus{outline:2px solid #93c5fd;outline-offset:1px}
    .spk-magic-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:20px}
    .spk-magic-btn{padding:11px 18px;border-radius:8px;border:1px solid #cbd5e1;background:#fff;color:#334155;font-weight:650;cursor:pointer;text-decoration:none;font-size:13px}
    .spk-magic-btn-primary{background:#166534;color:#fff;border-color:#166534}
    .spk-magic-btn-primary:disabled{opacity:.65;cursor:wait}
    @media(max-width:520px){.spk-magic-grid{grid-template-columns:1fr}.spk-magic-head,.spk-magic-body{padding:20px}.spk-magic-actions{flex-direction:column}.spk-magic-btn{text-align:center}}
</style>
<div class="spk-magic-wrap">
    <div class="spk-magic-card">
        <div class="spk-magic-head">
            <h1 class="spk-magic-title">Approval SPK</h1>
            <p class="spk-magic-sub">Periksa detail dokumen sebelum memberikan persetujuan.</p>
        </div>
        <div class="spk-magic-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <div class="spk-magic-grid">
                <div><span class="spk-magic-label">Nomor SPK</span><span class="spk-magic-value">{{ $spkData['no_spk'] ?? ('SPK #' . $spk->id) }}</span></div>
                <div><span class="spk-magic-label">Nomor PO</span><span class="spk-magic-value">{{ $spkData['no_po'] ?? '-' }}</span></div>
                <div><span class="spk-magic-label">Tahap Approval</span><span class="spk-magic-value">{{ $slot['label'] ?? '-' }}</span></div>
                <div><span class="spk-magic-label">Approver</span><span class="spk-magic-value">{{ auth()->user()->name ?? '-' }}</span></div>
                <div><span class="spk-magic-label">Supplier</span><span class="spk-magic-value">{{ $spkData['sup'] ?? '-' }}</span></div>
                <div><span class="spk-magic-label">Link Berlaku Hingga</span><span class="spk-magic-value">{{ $expiredAt ? $expiredAt->format('d M Y H:i') : '-' }}</span></div>
            </div>
            <div class="spk-magic-note">Persetujuan ini akan dicatat atas akun Anda. Pastikan dokumen SPK telah diperiksa sebelum melanjutkan.</div>
            <form id="spkMagicApprovalForm" method="POST" action="{{ route('spk.magic.approve', ['token' => $token]) }}">
                @csrf
                <label for="spkMagicRemark" class="spk-magic-label">Catatan / Remark (opsional)</label>
                <textarea id="spkMagicRemark" class="spk-magic-textarea" name="remark" maxlength="2000" placeholder="Tambahkan catatan bila diperlukan...">{{ old('remark') }}</textarea>
                <div class="spk-magic-actions">
                    <a class="spk-magic-btn" href="{{ route('spk.edit', ['id' => $spk->id]) }}">Lihat SPK</a>
                    <button type="submit" id="spkMagicSubmit" class="spk-magic-btn spk-magic-btn-primary">Approve SPK</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
(function(){
    const form = document.getElementById('spkMagicApprovalForm');
    const button = document.getElementById('spkMagicSubmit');
    if (!form || !button) return;
    form.addEventListener('submit', function(event){
        if (!window.confirm('Yakin ingin menyetujui SPK ini?')) {event.preventDefault();return;}
        button.disabled = true;
        button.textContent = 'Memproses...';
    });
})();
</script>
@endsection
