 <!-- aside -->
 @php
 use Illuminate\Support\Facades\Auth;
 @endphp
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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

                     {{-- ðŸ”¹ Role: HRD --}}
                     @php
                     use App\Models\Karyawan;

                     $user = Auth::user();
                     $a = $user ? Karyawan::find($user->karyawan_id) : null;
                         $isSuperUser = in_array($user->email, [
                            'info@newwicker.com',
                            'factory@newwicker.com',
                        ]);

                     @endphp
                     <li>
                         <a href="/pengajuan">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Pengajuan</span>
                         </a>
                     </li>
                      @if(auth()->user()->role == 'finance')
                    <li>
                        <a href="/produksi/inventor">
                            <span class="nav-icon">
                                <i class="material-icons">&#xe85e;</i>
                            </span>
                            <span class="nav-text">SPK</span>
                        </a>
                    </li>
                      <li>
                        <a href="/spk/request-r">
                            <span class="nav-icon">
                                <i class="material-icons">&#xe85e;</i>
                            </span>
                            <span class="nav-text">Pengajuan SPK</span>
                        </a>
                    </li>
                    @endif
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
                         <a href="{{ route('karyawan.lembur') }}">
                             <span class="nav-icon"><i class="material-icons">&#xe8d2;</i></span>
                             <span class="nav-text">Lembur Karyawan</span>
                         </a>
                     </li>
                     <li>
                         <a href="/izin-karyawan">
                             <span class="nav-icon"><i class="material-icons">&#xe8d2;</i></span>
                             <span class="nav-text">Izin Karyawan</span>
                         </a>
                     </li>
                     <li>
                         <a href="/employee-loan">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">employee loan</span>
                         </a>
                     </li>
                     @endif

                                        @if(Auth::user()->role === 'marketing' || Auth::user()->role === 'hrd'||  Auth::user()->role === 'manager produksi'
                                         ||$isSuperUser)
                      @if($isSuperUser)
                       <li>
                         <a href="{{ route('karyawan.absen') }}">
                             <span class="nav-icon"><i class="material-icons">&#xe8d2;</i></span>
                             <span class="nav-text">Absen Karyawan</span>
                         </a>
                     </li>
                     @endif

                     <li>
                         <a href="/karyawan-scan">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Absen Sekarang</span>
                         </a>
                     <li>

                     <li>
                         <a>
                             <span class="nav-caret">
                                 <i class="fa fa-caret-down"></i>
                             </span>

                             <span class="nav-icon">
                                 <i class="material-icons">&#xe8d2;
                                     <span ui-include="'../assets/images/i_3.svg'"></span>
                                 </i>
                             </span>

                             <span class="nav-text">Exhibition</span>
                         </a>

                         <ul class="nav-sub">
                             <li>
                                 <a href="/pameran">
                                     <span class="nav-text">Pameran</span>
                                 </a>
                             </li>

                             <li>
                                 <a href="/cart-buyer">
                                     <span class="nav-text">Cart Buyer</span>
                                 </a>
                             </li>
                         </ul>
                     </li>
                     <li>
                         <a href="/marketing-release-order">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Release PFI</span>
                         </a>
                     <li>
                     <li>
                     <li>
                     <li>
                         <a href="/marketing-pfi">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Upload excel</span>
                         </a>
                     <li>

                         <a href="/produksi/mn">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Monitoring Produksi</span>
                         </a>
                     </li>


                     <li>
                         <a href="/qc/laporan">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">QC page</span>
                         </a>
                     </li>
<li>
                         <a href="/bom">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">BOM</span>
                         </a>
                     </li>
                       <li>
                         <a href="/cad">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">CAD Drawing</span>
                         </a>
                     </li>
                     <li>
                         <a>
                             <span class="nav-caret"><i class="fa fa-caret-down"></i></span>
                             <span class="nav-icon"><i class="material-icons">&#xe5c3;</i></span>
                             <span class="nav-text">Purchasing</span>
                         </a>
                         <ul class="nav-sub">
                             <li><a href="/semua-spk"><span class="nav-text">SPK</span></a></li>
                             <li><a href="/spk/request-r"><span class="nav-text">Draft payment SPK</span></a></li>
                             <li>
                                 <a href="/produksi/inventor">
                                     <!-- <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span> -->
                                     <span class="nav-text">Monitoring SPK</span>
                                 </a>
                             </li>
                         </ul>


                     {{-- <li>
                          <li>
                         <a href="/produksi/inventor">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Pengajuan SPK</span>
                         </a>
                     <li> --}}
                         <!-- produksi -->
                     <li>
                         <a href="/produksi/mn">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">produksi</span>
                         </a>
                     </li>
                     <li>
                         <a href="/produksi/inventor">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Monitoring SPK</span>
                         </a>
                     </li>
                       <li>
                         <a href="/laporan">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Warehouse</span>
                         </a>
                     </li>

                     <li>
                         <a href="/supplier">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Bank data sub</span>
                         </a>
                     </li>
                     <!-- </li>


                         <a href="/request">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Agenda</span>
                         </a>
                     </li> -->

                     <li>
                         <a href="/request">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Agenda</span>
                         </a>
                     </li>



                     @endif
                     @if (Auth::user()->role === "purchasing"  )
                     <!-- purchasing -->
                     <li>
                         <a href="/karyawan-scan">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Absen Sekarang</span>
                         </a>
                     </li>
                     <li>
                         <a href="{{ route('absen.riwayat') }}">
                             <span class="nav-icon"><i class="material-icons">&#xe192;</i></span>
                             <span class="nav-text">Riwayat Absen</span>
                         </a>

                     </li>
                          <li>
                         <a href="/marketing-release-order">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Release PFI</span>
                         </a>
                     <li>
                <li>
                         <a>
                             <span class="nav-caret"><i class="fa fa-caret-down"></i></span>
                             <span class="nav-icon"><i class="material-icons">&#xe5c3;</i></span>
                             <span class="nav-text">Purchasing</span>
                         </a>
                         <ul class="nav-sub">
                             <li><a href="/semua-spk"><span class="nav-text">SPK</span></a></li>
                             <li><a href="/spk/request-r"><span class="nav-text">Draft payment SPK</span></a></li>
                             <li>
                                 <a href="/produksi/inventor">
                                     <!-- <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span> -->
                                     <span class="nav-text">Mutasi Barang jadi</span>
                                 </a>
                             </li>
                         </ul>


                     <li>
                         <li>
                         <a href="/bom">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">BOM</span>
                         </a>
                     </li>
                         <!-- produksi -->
                     <li>
                         <a href="/produksi/mn">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">produksi</span>
                         </a>
                     </li>
                     <li>
                         <a href="/produksi/inventor">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">invent. prod</span>
                         </a>
                     </li>

                     <li>
                         <a href="/supplier">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Supplier</span>
                         </a>
                     </li>
                     <li>
                         <a href="/produksi">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">produksi</span>
                         </a>
                     </li>
                     @endif
                     @if (Auth::user()->role == "gudang" || Auth::user()->role === 'hrd')
                     <!-- qc -->
                           <li>
                         <a href="/marketing-release-order">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Release PFI</span>
                         </a>
                     <li>
                     <li>
                         <a href="/setting">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Setting</span>
                         </a>
                     </li>
                     <li>
                         <a href="/qc/laporan">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">QC page</span>
                         </a>
                     </li>
                     <!-- qc -->
                     <li>
                         <a href="/laporan">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Mutasi Barng in/o</span>
                         </a>
                     </li>
                     @endif
                     {{-- ðŸ”¹ Role: User biasa --}}
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
                              @if(Auth::user()->email == 'johm@gmail.com')
                               <li>

                                    <a href="/produksi/mn">
                                        <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                                        <span class="nav-text">Monitoring Produksi</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/produksi/inventor">
                                        <span class="nav-icon">
                                            <i class="material-icons">&#xe85e;</i>
                                        </span>
                                        <span class="nav-text">SPK Monitoring</span>
                                    </a>
                                </li>
                              @endif

                     @endif
                              @if(Auth::user()->role == 'admin produksi')
                                <li>
                                 <a href="/produksi/inventor">
                                      <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                                     <span class="nav-text">Mutasi Barang jadi</span>
                                 </a>
                             </li>

                              @endif
                     @if(Auth::user()->role === "rnd")
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
                         <a href="/marketing-release-order">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Release PFI</span>
                         </a>
                     </li>

                     <li>
                         <a href="/semua-spk?spk=rnd_spk">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">SPK SAMPLES</span>
                         </a>
                     </li>
                    <li>
                        <a href="/produksi/inventor">
                            <span class="nav-icon">
                                <i class="material-icons">&#xe85e;</i>
                            </span>
                            <span class="nav-text">SPK Monitoring</span>
                        </a>
                    </li>

                     <li>
                         <a href="/bom">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">BOM</span>
                         </a>
                     </li>
                      <li>
                         <a href="/cad">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">CAD Drawing</span>
                         </a>
                     </li>
                     @endif
                       @auth
                    @if(auth()->user()->role == 'factory' || auth()->user()->role == 'coo' )
                    <li>
                        <a href="/produksi/inventor">
                            <span class="nav-icon">
                                <i class="material-icons">&#xe85e;</i>
                            </span>
                            <span class="nav-text">SPK</span>
                        </a>
                    </li>
                      <li>
                        <a href="/spk/request-r">
                            <span class="nav-icon">
                                <i class="material-icons">&#xe85e;</i>
                            </span>
                            <span class="nav-text">Pengajuan SPK</span>
                        </a>
                    </li>
                    @endif
                    @endauth
                     <li>
                         <a href="/inventory">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Inventory (alat)</span>
                         </a>
                     </li>
                     <!-- {{-- ðŸ”¹ Menu tambahan (opsional untuk semua role) --}}
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
