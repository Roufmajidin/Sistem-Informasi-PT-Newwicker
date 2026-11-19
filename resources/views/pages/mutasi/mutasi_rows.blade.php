                        @forelse($a as $spk)
                            @php

                                $searchItem = '';

                                foreach ($spk->data['items'] ?? [] as $item) {
                                    $searchItem .= ' ' . ($item['nama'] ?? '') . ' ' . ($item['kode'] ?? '');
                                }

                            @endphp

                            <tr class="pilih-spk" data-id="{{ $spk->id }}"
                                data-items-b64="{{ base64_encode(json_encode($spk->data['items'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}"
                                style="cursor:pointer"
                                data-search="{{ strtolower(
                                    ($spk->po->order_no ?? '') .
                                        ' ' .
                                        ($spk->po->company_name ?? '') .
                                        ' ' .
                                        ($spk->data['sup'] ?? '') .
                                        ' ' .
                                        ($spk->data['no_spk'] ?? '') .
                                        ' ' .
                                        ($spk->data['kategori'] ?? '') .
                                        ' ' .
                                        $searchItem,
                                ) }}">

                                <td>{{ ($a->currentPage() - 1) * $a->perPage() + $loop->iteration }}</td>

                                <td>
                                    {{ $spk->data['no_spk'] ?? '-' }}
                                </td>

                                <td>{{ $spk->po->order_no ?? '-' }}</td>

                                <td>{{ $spk->po->company_name ?? '-' }}</td>

                                <td>{{ $spk->data['kategori'] ?? '-' }}</td>

                                <td>{{ $spk->data['sup'] ?? '-' }}</td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center">
                                    Tidak ada data SPK
                                </td>
                            </tr>
                        @endforelse
