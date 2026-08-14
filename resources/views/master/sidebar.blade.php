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
                         $isSuperUser = in_array($user->email, ['info@newwicker.com', 'factory@newwicker.com']);

                     @endphp
                     <li>
                         <a href="/pengajuan">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Pengajuan</span>
                         </a>
                     </li>
                     @if (auth()->user()->role == 'finance')
                         <li>
                             <a href="/marketing-release-order">
                                 <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                                 <span class="nav-text">Release PFI</span>
                             </a>
                         <li>
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
                     @if (Auth::user()->role === 'hrd' || ($a && in_array($a->divisi_id, [38, 34, 25, 26])))
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

                     @if (Auth::user()->role === 'marketing' ||
                             Auth::user()->role === 'hrd' ||
                             Auth::user()->role === 'manager produksi' ||
                             $isSuperUser)
                         @if ($isSuperUser)
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
                                 <li>
                                     <a href="/detail-po">
                                         <span class="nav-text">Master Data</span>
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
                             <a>
                                 <span class="nav-caret">
                                     <i class="fa fa-caret-down"></i>
                                 </span>

                                 <span class="nav-icon">
                                     <i class="material-icons">&#xe85e;</i>
                                 </span>

                                 <span class="nav-text">
                                     Rnd
                                 </span>
                             </a>

                             <ul class="nav-sub">

                                 <li>
                                     <a href="/bom">
                                         <span class="nav-text">
                                             BOM
                                         </span>
                                     </a>
                                 </li>

                                 <li>
                                     <a href="/cad">
                                         <span class="nav-text">
                                             Drawing
                                         </span>
                                     </a>
                                 </li>

                             </ul>
                         </li>

                         <li>
                             <a>
                                 <span class="nav-caret"><i class="fa fa-caret-down"></i></span>
                                 <span class="nav-icon"><i class="material-icons">&#xe5c3;</i></span>
                                 <span class="nav-text">Purchasing</span>
                             </a>
                             <ul class="nav-sub">
                                 <li><a href="/bom-produksi"><span class="nav-text">COG</span></a></li>

                                 <li><a href="/semua-spk"><span class="nav-text">SPK</span></a></li>
                                 <li><a href="/spk/request-r"><span class="nav-text">Draft payment SPK</span></a></li>
                                 <li>
                                     <a href="/produksi/inventor">
                                         <!-- <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span> -->
                                         <span class="nav-text">Monitoring SPK</span>
                                     </a>
                                 </li>
                                 <li>
                                     <a href="/produksi/monitoring-payment-spk">
                                         <!-- <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span> -->
                                         <span class="nav-text">Monitoring payment spk</span>
                                     </a>
                                 </li>
                             </ul>
                         </li>
                         {{-- admin --}}
                            <li>
                             <a>
                                 <span class="nav-caret"><i class="fa fa-caret-down"></i></span>
                                 <span class="nav-icon"><i class="material-icons">&#xe5c3;</i></span>
                                 <span class="nav-text">admin produksi</span>
                             </a>
                             <ul class="nav-sub">
                                 <li><a href="/upah"><span class="nav-text">Upah Borongan</span></a></li>
                                 <li><a href="/upah/transaksi"><span class="nav-text">Rekap Upah</span></a></li>
                                 <li><a href="/subkon/"><span class="nav-text">Subkon</span></a></li>

                                
                                
                             </ul>
                         </li>
                         <li>
                             <a>
                                 <span class="nav-caret"><i class="fa fa-caret-down"></i></span>
                                 <span class="nav-icon"><i class="material-icons">&#xe5c3;</i></span>
                                 <span class="nav-text">Warehouse</span>
                             </a>
                             <ul class="nav-sub">
                                 <li>
                                     <a href="/warehouse/overview">
                                         <span class="nav-icon">
                                             <i class="material-icons">swap_horiz</i>
                                         </span>
                                         <span class="nav-text">Overview </span>
                                     </a>
                                 </li>
                                 <li>
                                     <a href="/laporan">
                                         <span class="nav-icon">
                                             <i class="material-icons">sync_alt</i>
                                         </span>
                                         <span class="nav-text">stok in/out </span>
                                     </a>
                                 </li>
                             </ul>
                         </li>

                         <li>
                             <a>
                                 <span class="nav-caret"><i class="fa fa-caret-down"></i></span>
                                 <span class="nav-icon"><i class="material-icons">&#xe5c3;</i></span>
                                 <span class="nav-text">export</span>
                             </a>
                             <ul class="nav-sub">
                                 <li><a href="/export/index"><span class="nav-text">form gener</span></a></li>
                                 <li><a href="/export/ipl"><span class="nav-text">IPL</span></a></li>
                                 <li><a href="/export/stock"><span class="nav-text">Stock</span></a></li>
                                 <li><a href="/export/doc_exports"><span class="nav-text">Doc Export Form</span></a>
                                 </li>

                             </ul>



                         <li>
                         <li>
                             <a href="/produksi/monitoring-finishing">
                                 <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                                 <span class="nav-text">monitoring Finishing</span>
                             </a>
                         </li>
                         {{-- <li>
                          <li>
                         <a href="/produksi/inventor">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Pengajuan SPK</span>
                         </a>
                     <li> --}}
                         <!-- produksi -->
                         <li>
                             <a href="/qc/laporan">
                                 <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                                 <span class="nav-text">QC page</span>
                             </a>
                         </li>
                         <li>
                         <li>
                             <a href="/produksi/in_out_barang_jadi">
                                 <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                                 <span class="nav-text">monitoring (admin)</span>
                             </a>
                         </li>

                         <a href="/produksi/mn">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">produksi</span>
                         </a>
                         </li>
                         {{-- <li>
                         <a href="/produksi/inventor">
                             <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                             <span class="nav-text">Monitoring SPK</span>
                         </a>
                     </li> --}}





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
                     @if (Auth::user()->role === 'IT')
                         <li>
                             <a>
                                 <span class="nav-caret"><i class="fa fa-caret-down"></i></span>
                                 <span class="nav-icon"><i class="material-icons">&#xe5c3;</i></span>
                                 <span class="nav-text">Monitoring App & Web</span>
                             </a>
                             <ul class="nav-sub">
                                 <li><a href="/it-dashboard"><span class="nav-text">Dashboard</span></a></li>

                             </ul>
                     @endif

                     @if (Auth::user()->role === 'purchasing')
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
                     @if (Auth::user()->role == 'gudang' || Auth::user()->role === 'hrd')
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
                         <li>
                             <a href="/monitoring-invoice">
                                 <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                                 <span class="nav-text">invoice</span>
                             </a>
                         </li>
                         <li>
                             <a href="/produksi/monitoring-finishing">
                                 <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                                 <span class="nav-text">invoice</span>
                             </a>
                         </li>
                     @endif
                     {{-- ðŸ”¹ Role: User biasa --}}
                     @if (Auth::user()->role == null)
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
                         @if (Auth::user()->email == 'johm@gmail.com' || Auth::user()->email == 'aji@gmail.com')
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
                     @if (Auth::user()->role == 'admin produksi')
                       <li>
                             <a>
                                 <span class="nav-caret"><i class="fa fa-caret-down"></i></span>
                                 <span class="nav-icon"><i class="material-icons">&#xe5c3;</i></span>
                                 <span class="nav-text">Admin Produksi</span>
                             </a>
                             <ul class="nav-sub">
                                 <li><a href="/it-dashboard"><span class="nav-text">Dashboard</span></a></li>

                             </ul>
                         
                         <li>
                             <a href="/produksi/in_out_barang_jadi">
                                 <span class="nav-icon"><i class="material-icons">&#xe85e;</i></span>
                                 <span class="nav-text">monitoring (admin)</span>
                             </a>
                         </li>
                     @endif
                     @if (Auth::user()->role === 'rnd')
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
                         @if (auth()->user()->role == 'factory' || auth()->user()->role == 'coo')
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
<script>
$(window).on('load', function () {

    setTimeout(function () {

        const currentPath =
            window.location.pathname.replace(/\/+$/, '') || '/';

        $('#aside ul.nav-sub a[href]').each(function () {

            const href = $(this).attr('href');

            if (!href || href === '#') {
                return;
            }

            let linkPath;

            try {
                linkPath = new URL(
                    href,
                    window.location.origin
                ).pathname.replace(/\/+$/, '') || '/';
            } catch (e) {
                return;
            }

            if (linkPath === currentPath) {

                const $link = $(this);
                const $sub = $link.closest('ul.nav-sub');
                const $parent = $sub.closest('li');

                $link.addClass('active');

                $parent.addClass('active open');

                // Paksa dropdown tetap terbuka
                $sub.css('display', 'block');

                // Kalau ada parent dropdown lagi
                $parent.parents('li').each(function () {

                    $(this).addClass('active open');

                    $(this)
                        .children('ul.nav-sub')
                        .css('display', 'block');
                });
            }

        });

    }, 100);

});
</script>
<style>
/* ============================================================
   SPK CREATE / EDIT SIDEBAR
   HANYA AKTIF:
      /spk/create
      /spk/edit/*
   
   HALAMAN SPK LAIN TETAP NORMAL
   ============================================================ */

@media (min-width: 992px) {

    /* ========================================================
       COLLAPSED
       ======================================================== */

    body.spk-form-sidebar #aside {
        width: 54px !important;
        min-width: 54px !important;
        max-width: 54px !important;

        transition:
            width .22s ease,
            min-width .22s ease,
            max-width .22s ease;

        overflow: visible !important;
    }


    body.spk-form-sidebar #aside .left.navside {
        width: 54px !important;
        min-width: 54px !important;
        max-width: 54px !important;

        overflow: hidden !important;

        transition:
            width .22s ease,
            min-width .22s ease,
            max-width .22s ease;
    }


    /* CONTENT IKUT MELEBAR */

    body.spk-form-sidebar #content {
        margin-left: 54px !important;

        width: calc(100% - 54px) !important;

        transition:
            margin-left .22s ease,
            width .22s ease;
    }


    /* ========================================================
       LOGO
       ======================================================== */

    body.spk-form-sidebar #aside .navbar {
        width: 54px !important;
        min-width: 54px !important;

        padding: 0 !important;

        display: flex !important;

        align-items: center !important;
        justify-content: center !important;
    }


    body.spk-form-sidebar #aside .navbar-brand {
        width: 54px !important;
        min-width: 54px !important;

        padding: 0 !important;

        display: flex !important;

        align-items: center !important;
        justify-content: center !important;
    }


    body.spk-form-sidebar #aside .navbar-brand img {
        width: 36px !important;

        height: auto !important;

        max-height: 38px !important;

        object-fit: contain;
    }


    /* ========================================================
       HEADER MENU
       ======================================================== */

    body.spk-form-sidebar #aside .nav-header {
        display: none !important;
    }


    /* ========================================================
       TEXT MENU HILANG
       ======================================================== */

    body.spk-form-sidebar #aside .nav-text {
        display: none !important;
    }


    /* caret submenu hilang */
    body.spk-form-sidebar #aside .nav-caret {
        display: none !important;
    }


    /* ========================================================
       MENU ITEM
       ======================================================== */

    body.spk-form-sidebar #aside .nav > li > a {

        width: 54px !important;
        min-width: 54px !important;

        height: 45px !important;

        padding: 0 !important;
        margin: 0 !important;

        display: flex !important;

        align-items: center !important;
        justify-content: center !important;

        position: relative;

        white-space: nowrap;
    }


    /* ICON */

    body.spk-form-sidebar #aside .nav-icon {

        width: 54px !important;
        min-width: 54px !important;

        margin: 0 !important;
        padding: 0 !important;

        display: flex !important;

        align-items: center !important;
        justify-content: center !important;
    }


    body.spk-form-sidebar #aside .nav-icon i {

        margin: 0 !important;

        font-size: 19px !important;
    }


    /* SUBMENU TIDAK DIBUKA SAAT COMPACT */

    body.spk-form-sidebar #aside .nav-sub {

        display: none !important;
    }


    /* ========================================================
       TOGGLE BUTTON
       ======================================================== */

    #spkSidebarToggle {

        position: fixed;

        left: 54px;
        top: 72px;

        width: 27px;
        height: 30px;

        padding: 0;

        border: 0;

        border-radius: 0 6px 6px 0;

        background: #304783;

        color: #fff;

        cursor: pointer;

        display: flex;

        align-items: center;
        justify-content: center;

        z-index: 99999;

        box-shadow:
            2px 2px 8px rgba(0,0,0,.12);

        transition:
            left .22s ease,
            background .15s ease;
    }


    #spkSidebarToggle:hover {

        background: #3d5ba0;
    }


    #spkSidebarToggle::before {

        content: '›';

        font-size: 23px;

        line-height: 1;
    }


    /* ========================================================
       EXPANDED
       ======================================================== */

    body.spk-form-sidebar.sidebar-expanded #aside {

        width: 230px !important;
        min-width: 230px !important;
        max-width: 230px !important;
    }


    body.spk-form-sidebar.sidebar-expanded #aside .left.navside {

        width: 230px !important;
        min-width: 230px !important;
        max-width: 230px !important;

        overflow: hidden !important;
    }


    body.spk-form-sidebar.sidebar-expanded #content {

        margin-left: 230px !important;

        width: calc(100% - 230px) !important;
    }


    /* ========================================================
       LOGO EXPANDED
       ======================================================== */

    body.spk-form-sidebar.sidebar-expanded
    #aside .navbar {

        width: 230px !important;
    }


    body.spk-form-sidebar.sidebar-expanded
    #aside .navbar-brand {

        width: 230px !important;

        justify-content: center !important;
    }


    body.spk-form-sidebar.sidebar-expanded
    #aside .navbar-brand img {

        width: auto !important;

        height: 48px !important;

        max-height: 52px !important;
    }


    /* ========================================================
       MENU TEXT KEMBALI
       ======================================================== */

    body.spk-form-sidebar.sidebar-expanded
    #aside .nav-header {

        display: block !important;
    }


    body.spk-form-sidebar.sidebar-expanded
    #aside .nav-text {

        display: inline-block !important;
    }


    body.spk-form-sidebar.sidebar-expanded
    #aside .nav-caret {

        display: inline-block !important;
    }


    /* ========================================================
       MENU EXPANDED
       ======================================================== */

    body.spk-form-sidebar.sidebar-expanded
    #aside .nav > li > a {

        width: 230px !important;
        min-width: 230px !important;

        height: 45px !important;

        padding: 0 14px !important;

        display: flex !important;

        align-items: center !important;

        justify-content: flex-start !important;
    }


    body.spk-form-sidebar.sidebar-expanded
    #aside .nav-icon {

        width: 34px !important;
        min-width: 34px !important;

        margin-right: 8px !important;
    }


    body.spk-form-sidebar.sidebar-expanded
    #aside .nav-icon i {

        font-size: 19px !important;
    }


    /* ========================================================
       SUBMENU KEMBALI
       ======================================================== */

    body.spk-form-sidebar.sidebar-expanded
    #aside .nav-sub {

        display: block !important;
    }


    /* ========================================================
       TOGGLE SAAT EXPAND
       ======================================================== */

    body.spk-form-sidebar.sidebar-expanded
    #spkSidebarToggle {

        left: 230px;
    }


    body.spk-form-sidebar.sidebar-expanded
    #spkSidebarToggle::before {

        content: '‹';
    }

}


/* ============================================================
   MOBILE
   Jangan ganggu sidebar Bootstrap existing
   ============================================================ */


</style>