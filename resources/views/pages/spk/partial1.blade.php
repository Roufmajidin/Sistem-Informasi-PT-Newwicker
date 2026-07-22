  <tr class="no-border">
      <td colspan="10" style="vertical-align: top; border:none">
  <div class="card border-0 shadow-sm" style="left:-12px">
                                    <div class="card-header bg-success text-white fw-bold">
                                        LIST BAHAN BAKU PENGAMBILAN (by warehoue)
                                    </div>
                                    <div class="card-body ">
                                        <div class="table-responsive">
                                            <table class="table table-bordered mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Tanggal</th>
                                                        <th>Tipe</th>
                                                        <th>Bahan</th>
                                                        <th>potong bahan</th>
                                                        <th>harga inventory</th>
                                                        <th>harga (adjusment)</th>
                                                        <th>Total</th>
                                                        <th>Keterangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="bahanbakuArea">

    @forelse($bahanBaku as $i => $row)

        <tr>

            <td>{{ $i + 1 }}</td>

            <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>

            <td>{{ strtoupper($row->tipe) }}</td>

            <td>{{ $row->stok->nama_barang ?? '-' }}</td>

            <td class="text-end">
                {{ number_format($row->qty, 3) }}
            </td>

            <td class="text-end">
                Rp {{ number_format($row->stok->harga ?? 0, 0, ',', '.') }}
            </td>

            <td class="text-end">
                Rp {{ number_format($row->harga_vivi ?? 0, 0, ',', '.') }}
            </td>

            <td class="text-end">
                Rp {{ number_format(($row->qty * ($row->harga_vivi ?? $row->stok->harga ?? 0)), 0, ',', '.') }}
            </td>

            <td>
                {{ $row->keterangan }}
            </td>

        </tr>

    @empty

        <tr>
            <td colspan="9" class="text-center py-4 text-muted">
                <i class="fas fa-clock text-warning me-2"></i>
                <strong>Not Yet</strong><br>
                Waiting Warehouse to Out Bahan Baku's
            </td>
        </tr>

    @endforelse

</tbody>
                                                       </table>
                                        </div>
                                    </div>
                                </div>
                                </div>
           <!-- <textarea
              name="spk_note"
              class="form-control spk-textarea"
              rows="12">{{ $spk['note'] ??
'1. Spesifikasi barang harus sesuai dengan sample
2. Harga belum sudah termasuk transportasi sampai gudang NewWicker
3. Supplier bertanggung jawab atas ketidaksesuaian spesifikasi barang
4. Final Quality Controlling akan dilakukan di gudang NewWicker
5. Supplier akan dikenakan penalty 1% setiap harinya atas keterlambatan produksi
6. Supplier berkewajiban melaporkan perkembangan produksi
7. Penyelesaian pembayaran dilakukan setelah kewajiban terpenuhi
8. Supplier memberikan hak penuh kepada pihak NewWicker
9. PT. NewWicker dapat memutuskan kerjasama jika terjadi pelanggaran' }}
</textarea> -->

          <div style="margin-top:10px;">
              <!-- <textarea
                  name="spk_closing"
                  class="form-control spk-textarea"
                  rows="2">{{ $spk['closing'] ?? 'Dengan ini,' }}</textarea>

                   -->
          </div>

      </td>

