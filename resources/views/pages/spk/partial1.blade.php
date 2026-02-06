  <tr class="no-border">
                <td colspan="10" style="vertical-align: top; border:none">

                    <textarea
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
9. PT. NewWicker dapat memutuskan kerjasama jika terjadi pelanggaran' }}</textarea>

                    <div style="margin-top:10px;">
                        <textarea
                            name="spk_closing"
                            class="form-control spk-textarea"
                            rows="2">{{ $spk['closing'] ?? 'Dengan ini,' }}</textarea>
                    </div>

                </td>
                <td colspan="3" style="vertical-align: top;margin-left:12px">
                    <table class="table table-bordered" style="font-size:12px;">
                        <tr class="text-center">
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Note</th>
                        </tr>

                        @for($i=0; $i<5; $i++)
                            <tr>
                            <td class="editable" contenteditable></td>
                            <td class="editable" contenteditable></td>
                            <td class="editable" contenteditable></td>
                            <td class="editable" contenteditable></td>
            </tr>
            @endfor
        </table>
        </td>
        </tr>
