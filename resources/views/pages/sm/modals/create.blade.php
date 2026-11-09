<div class="modal fade" id="smCreate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable sm-issue-dialog">
        <form class="modal-content" method="POST" action="{{ route('sm.store') }}">
            @csrf
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold">Create Sourcing Issue</h5><small class="text-muted">Sourcing
                        Monitoring · Status Draft</small>
                </div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info py-2 small">Ketik <strong>-</strong> lalu <strong>Spasi</strong> untuk
                    bullet •. Tekan Enter untuk melanjutkan bullet. Draft hanya terlihat oleh pembuat sampai Release.
                </div>
                <div class="row g-3">
                    <div class="col-md-12"><label class="form-label fw-semibold">Report By</label><input
                            class="form-control" value="{{ auth()->user()->name }}" readonly></div>
                    <div class="col-lg-6"><label class="form-label fw-semibold">Problem <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control sm-bullet-input" name="problem" rows="9" required
                            placeholder="Tuliskan masalah yang ditemukan...&#10;&#10;Contoh:&#10;- Supplier terlambat kirim bahan&#10;- Material belum lengkap">{{ old('problem') }}</textarea><small class="text-muted">Jelaskan masalah secara spesifik dan
                            terukur.</small>
                    </div>
                    <div class="col-lg-6"><label class="form-label fw-semibold">Potential Impact</label>
                        <textarea class="form-control sm-bullet-input" name="potential_impact" rows="9"
                            placeholder="Tuliskan kemungkinan dampak...&#10;&#10;Contoh:&#10;- Produksi tertunda&#10;- Shipment berisiko mundur">{{ old('potential_impact') }}</textarea><small class="text-muted">Dampak pada produksi, kualitas, biaya, atau
                            pengiriman.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white"><button type="button" class="btn btn-outline-secondary"
                    data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan
                    Draft</button></div>
        </form>
    </div>
</div>
