<div class="ph-monitor-scroll">
    <table class="ph-monitor-table">
        <thead>
            <tr>
                <th>No</th>
                <th>PO</th>
                <th>Item</th>
                <th>Pekerjaan</th>
                <th>Sub Name</th>
                <th>PPIC Price</th>
                <th>Director Price</th>
                <th>Pengajuan Sub</th>
                <th>PPIC Remark</th>
                <th>Sub Remark</th>
                <th>Director Remark</th>
                <th>Status</th>
                <th>Update Director</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $p)
                @php
                    $d = $p->detailPo?->detail ?? [];
                    if (is_string($d)) {
                        $d = json_decode($d, true) ?: [];
                    }
                    if (is_object($d)) {
                        $d = (array) $d;
                    }
                    if (!is_array($d)) {
                        $d = [];
                    }
                    $code =
                        $d['nw_code'] ??
                        ($d['nw_code_'] ?? ($d['nwCode'] ?? ($d['article_nr'] ?? ($d['article'] ?? '-'))));
                    $desc = $d['nw_description'] ?? ($d['description'] ?? ($d['buyer_desc'] ?? '-'));
                    $fmt = fn($v) => $v === null ? '—' : 'Rp ' . number_format((float) $v, 2, ',', '.');
                    $updated = $p->director_price !== null || $p->directur_date !== null;
                @endphp
                <tr>
                    <td>{{ $rows->firstItem() + $loop->index }}</td>
                    <td><strong>{{ $p->detailPo?->po?->order_no ?? '—' }}</strong></td>
                    <td><strong>{{ $code }}</strong>
                        <div class="ph-monitor-muted">{{ $desc }}</div>
                    </td>
                    <td>{{ $categories[$p->bahan_kategori_id] ?? '—' }}</td>
                    <td>{{ $p->sub_name ?: '—' }}</td>
                    <td><strong>{{ $fmt($p->ppic_price) }}</strong>
                        <div class="ph-monitor-muted">Update:
                            {{ $p->ppic_date ? \Illuminate\Support\Carbon::parse($p->ppic_date)->format('d/m/Y H:i') : '—' }}
                        </div>
                    </td>
                    <td><strong>{{ $fmt($p->director_price) }}</strong>
                        <div class="ph-monitor-muted">Update:
                            {{ $p->directur_date ? \Illuminate\Support\Carbon::parse($p->directur_date)->format('d/m/Y H:i') : '—' }}
                        </div>
                    </td>
                    <td><strong>{{ $fmt($p->pengajuan_sub_price) }}</strong>
                        <div class="ph-monitor-muted">Diajukan:
                            {{ $p->sub_date ? \Illuminate\Support\Carbon::parse($p->sub_date)->format('d/m/Y H:i') : '—' }}
                        </div>
                        @if (strtoupper(trim((string) ($p->submission_by ?? ''))) === 'TOMO')
                            <div class="ph-monitor-muted" style="color:#2563eb;font-weight:600">Submission by Tomo</div>
                        @endif
                    </td>
                    <td class="ph-monitor-remark">{{ $p->ppic_remark ?: '—' }}</td>
                    <td class="ph-monitor-remark">{{ $p->sub_remark ?: '—' }}</td>
                    <td class="ph-monitor-remark">{{ $p->director_remark ?: '—' }}</td>
                    <td><span
                            class="ph-monitor-badge {{ $p->closed ? 'is-closed' : 'is-open' }}">{{ $p->closed ? 'Closed' : 'Open' }}</span>
                        @if (!$p->closed && !($isFactory ?? false))
                            <div style="margin-top:7px"><button type="button"
                                    class="ph-btn ph-outline ph-monitor-do-close"
                                    data-id="{{ $p->id }}">Close</button></div>
                        @endif
                    </td>
                    <td><span
                            class="ph-monitor-badge {{ $updated ? 'is-closed' : 'is-open' }}">{{ $updated ? 'Sudah update' : 'Belum update' }}</span>
                        <div class="ph-monitor-muted">{{ $p->directur_date?->format('d/m/Y H:i') ?? '—' }}</div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" style="text-align:center;padding:28px">Tidak ada pengajuan sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="ph-monitor-footer"><span>Total {{ $rows->total() }} pengajuan</span>
    <div>{{ $rows->links() }}</div>
</div>
