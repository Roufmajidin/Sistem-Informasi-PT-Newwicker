                               @foreach($spks as $spk)
                                   <tr class="spk-row" data-id="{{ $spk['id'] }}" data-status="{{ $spk['status'] }}">
                                       <td>
                                           {{ $loop->iteration }}
                                       </td>
                                       {{-- CLICK FOR INPUT --}}
                                       <td class="{{ $hideHarga ? '' : 'inventor-row' }} spk-col"
                                           data-id="{{ $spk['id'] }}">
                                           <div class="fw-bold text-primary">
                                               {{ $spk['no_spk'] }}
                                           </div>
                                       </td>
                                       <td style="font-size:12px" class="inventor-row" data-id="{{ $spk['id'] }}">

                                           <div class="fw-bold">
                                               {{ $spk['supplier'] }}
                                           </div>

                                           @foreach ($spk['items'] as $item)
                                               <div class="item-spk"
                                                   title="{{ $item['nama'] }} ({{ $item['qty'] }} {{ $item['satuan'] ?? 'pcs' }})">
                                                   • {{ $item['nama'] }}
                                                   ({{ $item['qty'] }} {{ $item['satuan'] ?? 'pcs' }})
                                               </div>
                                           @endforeach

                                       </td>
                                       <td class="inventor-row" data-id="{{ $spk['id'] }}">
                                           <span class="badge bg-info">
                                               {{ strtoupper($spk['kategori']) }}
                                           </span>
                                       </td>
                                       <td class="inventor-row po-col" data-id="{{ $spk['id'] }}">
                                           {{ $spk['no_po'] }}
                                       </td>
                                       <td class="inventor-row" data-id="{{ $spk['id'] }}">
                                           {{ $spk['tgl_terima'] }}
                                       </td>
                                       <td class="inventor-row" data-id="{{ $spk['id'] }}">
                                           {{ $spk['tgl_selesai'] }}
                                       </td>
                                       <td style="width:20px">
                                           <div class="deadline-card">

                                               <div class="deadline-bar">

                                                   <div class="deadline-fill deadline-{{ $spk['deadline_color'] }}"
                                                       style="width: {{ $spk['deadline_percent'] }}%">
                                                   </div>

                                               </div>

                                               <div class="deadline-footer">
                                                   <span>
                                                       {{ $spk['deadline_text'] }}
                                                   </span>

                                                   <span>
                                                       <!-- {{ $spk['deadline_percent'] }}% -->
                                                   </span>
                                               </div>

                                           </div>
                                       </td>
                                       {{-- <td>"{{ $spk['status'] }}"</td> --}}
                                       @if (!$hideHarga)
                                           {{-- VIVI --}}
                                           <td class="text-center">

                                               @if (!empty($spk['signature']['checked_at']))
                                                   <a href="{{ url('/spk/edit/' . $spk['id']) }}">
                                                       <span class="badge bg-success">
                                                           <i class="fa fa-check"></i>
                                                           Signed
                                                       </span>
                                                   </a>

                                                   <br>

                                                   <small class="text-muted">
                                                       {{ $spk['signature']['checked_by'] }}
                                                   </small>
                                               @elseif($spk['status'] == 'diajukan')
                                                   <a href="{{ url('/spk/edit/' . $spk['id']) }}">
                                                       <span class="badge bg-warning">
                                                           Pending
                                                       </span>
                                                   </a>
                                               @else
                                                   <span class="text-muted">-</span>
                                               @endif

                                           </td>

                                           {{-- DIDIN --}}
                                           <td class="text-center">

                                               @if (!empty($spk['signature']['checked_at_2']))
                                                   <a href="{{ url('/spk/edit/' . $spk['id']) }}">
                                                       <span class="badge bg-success">
                                                           <i class="fa fa-check"></i>
                                                           Signed
                                                       </span>
                                                   </a>

                                                   <br>

                                                   <small class="text-muted">
                                                       {{ $spk['signature']['checked_by_2'] }}
                                                   </small>
                                               @elseif($spk['status'] == 'diajukan')
                                                   <a href="{{ url('/spk/edit/' . $spk['id']) }}">
                                                       <span class="badge bg-warning">
                                                           Pending
                                                       </span>
                                                   </a>
                                               @else
                                                   <span class="text-muted">-</span>
                                               @endif

                                           </td>
                                           <td class="text-center">
                                               @if (!empty($spk['signature']['approved_at']))
                                                   <a href="{{ url('/spk/edit/' . $spk['id']) }}">
                                                       <span class="badge bg-success">
                                                           <i class="fa fa-check"></i>
                                                           Signed
                                                       </span>
                                                   </a>

                                                   <br>

                                                   <small class="text-muted approved-by"
                                                       title="{{ $spk['signature']['approved_by'] }}">
                                                       {{ $spk['signature']['approved_by'] }}
                                                   </small>
                                               @elseif($spk['status'] == 'diajukan' && auth()->id() == 191)
                                                   <button type="button" class="btn btn-warning btn-sm btn-approve-spk"
                                                       data-id="{{ $spk['id'] }}">
                                                       Pending Approval
                                                   </button>
                                               @elseif($spk['status'] == 'diajukan')
                                                   <a href="{{ url('/spk/edit/' . $spk['id']) }}">
                                                       <span class="badge bg-warning">
                                                           Pending
                                                       </span>
                                                   </a>
                                               @else
                                                   <span class="text-muted">-</span>
                                               @endif
                                           </td>
                                       @endif
                                       <td class="text-center inventor-row" data-id="{{ $spk['id'] }}">
                                           {{ count($spk['items']) }}
                                       </td>
                                       {{-- DETAIL BUTTON --}}
                                       <td>

                                           {{-- Selain Admin Produksi dan bukan John/Aji --}}
                                           @php
                                               $userEmail = strtolower(trim((string) auth()->user()->email));
                                               $userRole = strtolower(trim((string) auth()->user()->role));
                                               $spkStatus = strtolower(trim((string) ($spk['status'] ?? '')));
                                               $isNur = $userEmail === 'nurk@gmail.com';

                                               // Nur boleh mengajukan SPK draft.
                                               // User lain tetap mengikuti aturan lama.
                                               $canAjukan =
                                                   $isNur ||
                                                   ($userRole !== 'admin produksi' &&
                                                       !in_array(
                                                           $userEmail,
                                                           ['john@gmail.com', 'aji@gmail.com'],
                                                           true,
                                                       ));
                                           @endphp

                                           @if ($canAjukan && $spkStatus === 'draft')
                                               <button type="button" class="btn btn-success btn-sm btn-ajukan"
                                                   data-id="{{ $spk['id'] }}" data-spk="{{ $spk['no_spk'] }}">
                                                   Ajukan
                                               </button>
                                           @endif

                                           {{-- Detail boleh untuk semua selain Admin Produksi, termasuk John & Aji --}}
                                           @if (auth()->user()->role != 'admin produksi' || in_array(auth()->user()->email, ['john@gmail.com', 'aji@gmail.com']))
                                               <button type="button" class="btn btn-primary btn-sm btn-detail"
                                                   data-id="{{ $spk['id'] }}">
                                                   Detail
                                               </button>
                                           @endif

                                       </td>
                                   </tr>
                               @endforeach
