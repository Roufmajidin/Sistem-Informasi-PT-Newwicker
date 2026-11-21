                       @foreach($spks as $spk)
                           <article class="spk-mobile-card" data-id="{{ $spk['id'] }}"
                               data-status="{{ $spk['status'] }}"
                               data-supplier="{{ strtolower($spk['supplier'] ?? '') }}"
                               data-search="{{ strtolower(($spk['no_spk'] ?? '') . ' ' . ($spk['no_po'] ?? '') . ' ' . ($spk['supplier'] ?? '')) }}"
                               data-spktype="{{ str_contains(strtoupper($spk['no_spk'] ?? ''), 'NWS') ? 'NWS' : 'NW' }}">
                               <div class="spk-mobile-card-top">
                                   <div class="spk-mobile-card-title">{{ $spk['no_spk'] }}</div>
                               </div>
                               <div class="spk-mobile-supplier">{{ $spk['supplier'] }}</div>
                               <div class="spk-mobile-items">
                                   {{ collect($spk['items'])->take(2)->pluck('nama')->join(' · ') }}</div>
                               <div class="spk-mobile-progress">
                                   <div class="spk-mobile-progress-label">
                                       <span>{{ strtoupper($spk['kategori'] ?? '-') }}</span><strong>{{ $spk['deadline_text'] }}</strong>
                                   </div>
                                   <div class="spk-mobile-track">
                                       <div class="deadline-{{ $spk['deadline_color'] }}"
                                           style="width:{{ max(3, min(100, (float) $spk['deadline_percent'])) }}%"></div>
                                   </div>
                               </div>
                               <div class="spk-mobile-metadata">
                                   <span><i class="fa fa-file-text-o"></i> {{ $spk['no_po'] ?? '-' }}</span>
                                   <span><i class="fa fa-calendar"></i> {{ $spk['tgl_terima'] ?? '-' }}</span>
                                   <span><i class="fa fa-flag"></i> {{ $spk['tgl_selesai'] ?? '-' }}</span>
                                   <span class="spk-mobile-qty">{{ count($spk['items']) }} item</span>
                               </div>
                               <div class="spk-mobile-actions">
                                   @if (auth()->user()->role != 'admin produksi' || in_array(auth()->user()->email, ['john@gmail.com', 'aji@gmail.com']))
                                       <button type="button" class="spk-mobile-detail" data-id="{{ $spk['id'] }}"><i
                                               class="fa fa-list-alt"></i> Lihat
                                           Detail</button>
                                   @endif
                                   @php
                                       $mEmail = strtolower(trim((string) auth()->user()->email));
                                       $mRole = strtolower(trim((string) auth()->user()->role));
                                       $mCanAjukan =
                                           $mEmail === 'nurk@gmail.com' ||
                                           ($mRole !== 'admin produksi' &&
                                               !in_array($mEmail, ['john@gmail.com', 'aji@gmail.com'], true));
                                   @endphp
                                   @if ($mCanAjukan && strtolower($spk['status'] ?? '') === 'draft')
                                       <button type="button" class="spk-mobile-ajukan" data-id="{{ $spk['id'] }}"><i
                                               class="fa fa-paper-plane"></i>
                                           Ajukan</button>
                                   @endif
                               </div>
                           </article>
                       @endforeach
