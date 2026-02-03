 <!-- aside -->
 @php
 use Illuminate\Support\Facades\Auth;
 @endphp

 <div id="aside" class="app-aside modal nav-dropdown">
     <!-- fluid app aside -->
     <div class="left navside dark dk" data-layout="column">
         <div class="navbar no-radius">
             <a class="navbar-brand">
                 <img style="height:80%; max-height:60px;" src="{{ asset('/assets/images/NEWWICKER WHITE.png') }}">
             </a>
         </div>

         <div class="hide-scroll" data-flex>
             <nav class="scroll nav-light">
                 <ul class="nav" ui-nav>
                     <li class="nav-header hidden-folded">
                         <small class="text-muted">Main Menu</small>
                     </li>

                     {{-- Menu umum untuk semua role --}}
                     <li>
                         <a href="/">
                             <span class="nav-icon"><i class="material-icons">&#xe3fc;</i></span>
                             <span class="nav-text">Dashboard</span>
                         </a>
                     </li>

                     {{-- 🔹 Role: HRD --}}
                     @php
                     use App\Models\Karyawan;

                     $a = Karyawan::find(Auth::user()->karyawan_id);
                     @endphp

                     @if(Auth::user()->role === 'hrd' || ($a && in_array($a->divisi_id, [38, 34, 25, 26])))
                     <li>
                         <a href="/karyawan">
                             <span class="nav-icon"><i class="material-icons">&#xe8d2;</i></span>
                             <span class="nav-text">Karyawan</span>
                         </a>
                     </li>

                     <li>
                         <a href="{{ route('karyawan.absen') }}">
                             <span class="nav-icon"><i class="material-icons">&#xe8d2;</i></span>
                             <span class="nav-text">Absen Karyawan</span>
                         </a>
                     </li>

                     <li>
                         <a href="/izin-karyawan">
                             <span class="nav-icon"><i class="material-icons">&#xe8d2;</i></span>
                             <span class="nav-text">Izin Karyawan</span>
                         </a>
                     </li>
                      <li>
                         <a href="/pameran">
                             <span class="nav-icon">
                                 <i class="material-icons">&#xe8d2;
                                     <span ui-include="'../assets/images/i_3.svg'"></span>
                                 </i>
                             </span>
                             <span class="nav-text">Pameran</span>
                         </a>
                     </li>
                     <li>
                         <a href="/cart-buyer">
                             <span class="nav-icon">
                                 <i class="material-icons">&#xe8d2;
                                     <span ui-include="'../assets/images/i_3.svg'"></span>
                                 </i>
                             </span>
                             <span class="nav-text">Cart Buyer</span>
                         </a>
                     </li>
                      <li>
                         <a href="/request">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Agenda</span>
                         </a>
                     </li>
                     <li>
                         <a href="/setting">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Setting</span>
                         </a>
                     </li>
                        <li>
                         <a href="/qc">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">QC page</span>
                         </a>
                     </li>
                      <li>
                         <a href="/marketing-pfi">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">marketing</span>
                         </a>
                     </li>
                     @endif

                     {{-- 🔹 Role: Marketing --}}
                     @if(Auth::user()->role == 'marketing')
                     <li>
                         <a>
                             <span class="nav-caret"><i class="fa fa-caret-down"></i></span>
                             <span class="nav-icon"><i class="material-icons">&#xe5c3;</i></span>
                             <span class="nav-text">Marketing</span>
                         </a>
                         <ul class="nav-sub">
                             <li><a href="/marketing"><span class="nav-text">PFI</span></a></li>
                             <li><a href="/marketing/buyers_list"><span class="nav-text">Buyers List</span></a></li>
                             <li><a href="/marketing/release-pfi"><span class="nav-text">Release PFI</span></a></li>
                         </ul>
                     </li>
 <li>
                         <a href="/request">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Agenda</span>
                         </a>
                     </li>
                     @endif

                     {{-- 🔹 Role: User biasa --}}
                     @if(Auth::user()->role == NULL)
                     <li>
                         <a href="{{ route('absen.riwayat') }}">
                             <span class="nav-icon"><i class="material-icons">&#xe192;</i></span>
                             <span class="nav-text">Riwayat Absen</span>
                         </a>
                     </li>

                     <li>
                         <a href="/karyawan-scan">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Absen Sekarang</span>
                         </a>
                     </li>
                      <li>
                         <a href="/request">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Agenda</span>
                         </a>
                     </li>
                     @endif

                     <!-- {{-- 🔹 Menu tambahan (opsional untuk semua role) --}}
                     <li class="nav-header hidden-folded">
                         <small class="text-muted">Main Menu</small>
                     </li>

                     <li>
                         <a>
                             <span class="nav-caret"><i class="fa fa-caret-down"></i></span>
                             <span class="nav-icon"><i class="material-icons">&#xe429;</i></span>
                             <span class="nav-text">riwayat absensi</span>
                         </a>
                         <ul class="nav-sub nav-mega nav-mega-3">
                             <li>
                                 <a href="/all-event-config">
                                     <span class="nav-text">Sub Years</span>
                                 </a>
                             </li>
                         </ul>
                     </li> -->

                 </ul>
             </nav>
         </div>


         <!-- <div class="b-t">
         <div class="nav-fold">
             <a href="profile.html">
                 <span class="pull-left">
                     <img src="../assets/images/a0.jpg" alt="..." class="w-40 img-circle">
                 </span>
                 <span class="clear hidden-folded p-x">
                     <span class="block _500">Rouf Majid</span>
                     <small class="block text-muted"><i class="fa fa-circle text-success m-r-sm"></i>online</small>
                 </span>
             </a>
         </div>
     </div> -->
     </div>
 </div>
 <!-- / -->
