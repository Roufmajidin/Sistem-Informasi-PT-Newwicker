@extends('master.master')
@section('title', "sales marketing")
@section('content')
<div class="padding">

    <div class="box">
    <div class="box-header">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h2 class="mb-0">PFI Bank</h2>
                <small>Bank Data PFI Rodiyah</small>
            </div>
            <div class="col-sm-6 text-right">
                <label id="scan-again-btn" class="btn btn-sm btn-primary">Scan</label>
            </div>
        </div>
    </div>



        <!-- 01 -->
        <div style="display: flex; justify-content: center;">
            <div id="reader" style="width: 300px;"></div>
        </div>
        <div class="container">
            <div class="card">
                <div class="card-body">

                    <div class="row">
                        <div class="col-lg-5 col-md-3 col-sm-6">
                            <img id="product-image" src="" alt="Product Image" class="img-responsive">
                        </div>
                        <div class="col-lg-7 col-md-7 col-sm-6">
                            <h4 class="box-title mt-5">Product description</h4>
                            <p>Lorem Ipsum available,but the majority have suffered alteration in some form,by injected humour,or randomised words which don't look even slightly believable.but the majority have suffered alteration in some form,by injected humour</p>
                            <h2 class="mt-5">
                                $153<small class="text-success">(36%off)</small>
                            </h2>
                            <button class="btn btn-primary btn-rounded" onclick="addToCart()">Add to cart</button>
                            <i class="fa fa-shopping-cart"></i>
                            </button>

                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <h3 class="box-title mt-5">General Info</h3>
                            <div class="table-responsive">
                                <table class="table table-striped table-product">
                                    <tbody>
                                        <tr>
                                            <td width="390">description</td>
                                            <td id="td-description">-</td>
                                        </tr>
                                        <tr>
                                            <td>articl nr</td>
                                            <td id="td-article-nr">-</td>
                                        </tr>
                                        <tr>
                                            <td>Remark</td>
                                            <td id="td-remark">-</td>
                                        </tr>
                                        <tr>
                                            <td>cushion</td>
                                            <td id="td-cushion">-</td>
                                        </tr>
                                        <tr>
                                            <td>glass</td>
                                            <td id="td-glass">-</td>
                                        </tr>
                                        <tr>
                                            <td>item dimension</td>
                                            <td id="td-item-dimension">W: - D: - H: -</td>
                                        </tr>
                                        <tr>
                                            <td>packing dimention</td>
                                            <td id="td-packing-dimension">W: - D: - H: -</td>
                                        </tr>
                                        <tr>
                                            <td>Materials</td>
                                            <td id="td-material">-</td>
                                        </tr>
                                        <tr>
                                            <td>composition</td>
                                            <td id="td-composition">-</td>
                                        </tr>
                                        <tr>
                                            <td>finishing</td>
                                            <td id="td-finishing">-</td>
                                        </tr>
                                        <tr>
                                            <td>Value in IDR</td>
                                            <td id="td-value-idr">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<form id="exportForm" method="POST" action="{{ route('cart.export') }}">
    @csrf
    <input type="hidden" name="items" id="itemsInput">
    <button type="submit" class="btn btn-success btn-sm mb-3">Export to Excel</button>
</form>
        <div class="table-responsive" id="table-expor">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Photo</th>
                        <th>Description</th>
                        <th>Article Nr.</th>
                        <th>Remark</th>
                        <th>Cushion</th>
                        <th>Glass</th>
                        <th>W</th>
                        <th>D</th>
                        <th>H</th>
                        <th>PW</th>
                        <th>PD</th>
                        <th>PH</th>
                        <th>Materials</th>
                        <th>Finishing</th>
                        <th>QTY</th>
                        <th>CBM</th>
                        <th>Price (IDR)</th>
                        <th>Total CBM</th>
                        <th>Value (IDR)</th>
                    </tr>
                </thead>
                <!-- ⬇⬇⬇ Taro tbody DI SINI ⬇⬇⬇ -->
                <tbody id="product-table-body">
                    <!-- Row akan ditambahkan di sini via JS -->
                </tbody>
            </table>
        </div>

        <div class="col-3"></div>
    </div>
</div>
</div>
@push('scripts')
<!-- exports -->
 <script>
document.getElementById('exportForm').addEventListener('submit', function (e) {
    const input = document.getElementById('itemsInput');
    input.value = JSON.stringify(cartItems);
});
</script>
<script>
    document.getElementById("scan-again-btn").addEventListener("click", function () {
    // Kosongkan elemen reader dulu (jika perlu)
    document.getElementById("reader").innerHTML = "";

    html5QrcodeScanner = new Html5QrcodeScanner(
        "reader", {
            fps: 10,
            qrbox: {
                width: 250,
                height: 250
            }
        },
        false
    );
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
});
</script>
<script>
    let cartItems = []; // simpan list produk sementara di client

    function addToCart() {
        if (!window.lastScannedProduct) {
            alert("Tidak ada produk untuk ditambahkan.");
            return;
        }

        const product = window.lastScannedProduct;

        // Cek jika sudah pernah ditambahkan (berdasarkan article_nr)
        if (cartItems.find(item => item.article_nr === product.article_nr)) {
            alert("Produk sudah ditambahkan ke cart.");
            return;
        }

        cartItems.push(product);

        const row = document.createElement('tr');
        row.style.fontSize = "10px";

        const imgSrc = product.photo ? `/storage/${product.photo}` : null;
        const itemW = product.w ?? '-';
        const itemD = product.d ?? '-';
        const itemH = product.h ?? '-';

        const packingW = product.pw ?? '-';
        const packingD = product.pd ?? '-';
        const packingH = product.ph ?? '-';

        row.innerHTML = `
        <td>${cartItems.length}</td>
        <td>${imgSrc ? `<img src="${imgSrc}" alt="product" width="60">` : '<span>No image</span>'}</td>
        <td>${product.description || '-'}</td>
        <td>${product.article_nr || '-'}</td>
        <td>${product.remark || '-'}</td>
        <td>${product.cushion || '-'}</td>
        <td>${product.glass_orMirror || '-'}</td>
        <td>${itemW}</td>
        <td>${itemD}</td>
        <td>${itemH}</td>
        <td>${packingW}</td>
        <td>${packingD}</td>
        <td>${packingH}</td>
        <td>${product.materials || '-'}</td>
        <td>${product.finishes_color || '-'}</td>
        <td>${product.qty || 0}</td>
        <td>${product.cbm || '0.00'}</td>
        <td>${formatIDR(product.usd_selling_price)}</td>
        <td>${product.total_cbm || '0.00'}</td>
        <td>${formatIDR(product.value_in_usd)}</td>
    `;

        document.getElementById("product-table-body").appendChild(row);
    }
</script>
<script>
    // function formatIDR(value) {
    // return parseInt(value).toLocaleString("id-ID", {
    //     minimumFractionDigits: 0
    // });
    function formatIDR(usd) {
        const exchangeRate = 16000; // atau ambil dari config/backend
        const idr = parseFloat(usd || 0) * exchangeRate;
        return idr.toLocaleString("id-ID", {
            minimumFractionDigits: 0
        });

    }

    function onScanSuccess(decodedText, decodedResult) {
        html5QrcodeScanner.clear().then(() => {
            fetch(`/marketing/scan/${encodeURIComponent(decodedText)}`)
                .then(response => {
                    if (!response.ok) throw new Error("Produk tidak ditemukan");
                    return response.json();
                })
                .then(({
                    data

                }) => {
                    // Update image
                    window.lastScannedProduct = data;

                    if (data.photo) {
                        document.getElementById("product-image").src = "/storage/" + data.photo;
                    }

                    // Update text fields
                    document.getElementById("td-description").innerText = data.description || '-';
                    document.getElementById("td-article-nr").innerText = data.article_nr || '-';
                    document.getElementById("td-remark").innerText = data.remark || '-';
                    document.getElementById("td-cushion").innerText = data.cushion || '-';
                    document.getElementById("td-glass").innerText = data.glass_orMirror || '-';
                    document.getElementById("td-composition").innerText = data.weaving_composition || '-';
                    document.getElementById("td-material").innerText = data.materials || '-';
                    document.getElementById("td-finishing").innerText = data.finishes_color || '-';
                    document.getElementById("td-value-idr").innerText = "Rp " + formatIDR(data.usd_selling_price || 0);
                    document.getElementById("td-item-dimension").innerText =
                        `W: ${data.w || '-'} D: ${data.d || '-'} H: ${data.h || '-'}`;
                    document.getElementById("td-packing-dimension").innerText =
                        `W: ${data.pw || '-'} D: ${data.pd || '-'} H: ${data.ph || '-'}`;
                })

                .catch(error => {
                    alert("Error: " + error.message);
                });
        });


    }

    function onScanFailure(error) {
        // handle scan failure, usually better to ignore and keep scanning.
        // for example:
        console.warn(`Code scan error = ${error}`);
    }

    let html5QrcodeScanner = new Html5QrcodeScanner(
        "reader", {
            fps: 10,
            qrbox: {
                width: 250,
                height: 250
            }
        },
        /* verbose= */
        false);
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
</script>

@endpush

@endsection
