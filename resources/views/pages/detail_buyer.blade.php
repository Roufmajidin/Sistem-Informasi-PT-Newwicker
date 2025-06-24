@extends('master.master')
@section('title', "Buyyers list")
@section('content')
<div class="padding">
    <div class="box">
        <div class="box-header">
            <h2>Detail Buyer</h2>
            <small>{{$db->name}}</small>
        </div>
        <div class="form-group col-lg-4" style="height: 20px;margin-bottom:15px">
            <div class="input-group" style="height: 30px">
                <div class="input-group-addon">@</div>
                <input id="searchBuyer" class="form-control" type="text" placeholder="Cari buyyers">
            </div>
        </div>

        <div class="col-12">
            <div class="table-wrapper">
                <table>
                    <thead style="background-color:#5bc0de">

                        <tr class="sticky-header" style="font-size: 12px;">
                            <th>No.</th>
                            <th>Photo</th>
                            <th>buyer_s_code</th>
                            <th class="sticky">Description</th>
                            <th style="font-size: 10px;">Article Nr.</th>
                            <th style="font-size: 10px;">Remark</th>
                              <th style="font-size: 10px;">W</th>
                            <th style="font-size: 10px;">D</th>
                            <th style="font-size: 10px;">H</th>
                            <th style="font-size: 10px;">Materials</th>

                            <th style="font-size: 10px;">Cushion</th>
                            <th style="font-size: 10px;">glass/mirror</th>
                            <th style="font-size: 10px;">Weight Capacity (Kg/Lt)</th>

                            <th style="font-size: 10px;">Finsihing Color of Item</th>
                            <th style="font-size: 10px;">USD Selling price</th>
                            <th colspan="1" style="font-size: 10px;">packing Dimension</th>
                            <th style="font-size: 10px;">Nw (kg)</th>
                            <th style="font-size: 10px;">Gw (kg)</th>
                            <th style="font-size: 10px;">CBM</th>
                            <th style="font-size: 10px;">Accessories</th>
                            <th style="font-size: 10px;">Picture of Accessories</th>
                            <th style="font-size: 10px;">Finishing steps</th>
                            <th style="font-size: 10px;">Harga suplier/Bask</th>
                            <th style="font-size: 10px;">Loadability</th>
                            <th style="font-size: 10px;">Electricity</th>
                            <th style="font-size: 10px;">comment_visit</th>

                        </tr>


                    </thead>
                    <tbody>
                        @php $no = 1;


                        @endphp

                        <tr style="font-size: 10px;">
                            @foreach($buyer as $i)
                            @php
                            $basePath = storage_path('app/public/products/');
                            $extensions = ['jpeg', 'jpg', 'png', 'webp'];
                            $imagePath = 'default.png'; // fallback

                            // Hilangkan garis miring (/) dari article_nr
                            $cleanArticleNr = preg_replace('/\s*\/\s*/', ' ', $i->article_nr);

                            foreach ($extensions as $ext) {
                            $file = $basePath . $cleanArticleNr . '.' . $ext;
                            if (file_exists($file)) {
                            $imagePath = 'storage/products/' . $cleanArticleNr . '.' . $ext;
                            break;
                            }
                            }

                            @endphp
                            <td style="font-size: 10px;">{{ $no++ }}</td>
                            <td>
                                <img src="{{ asset('storage/' . $i->photo) }}" alt="product" width="60">

                            </td>
                            <td style="font-size: 10px;">
                                <a href="#" class="editable-buyer_s_code" data-name="buyer_s_code" data-pk="{{ $i->id }}" data-type="text" data-url="/post-name" data-title="Enter Buyer Code">
                                    {{ $i->buyer_s_code ?: '-' }}
                                </a>
                            </td>
                            <td class="sticky" style="font-size: 10px;">
                                <a href="#" class="editable-description" data-name="description" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter Description">
                                    {{ $i->description ?: '-' }}
                                </a>
                            </td>
                            <td style="font-size: 10px;">
                                <a href="#" class="editable-article_nr" data-name="article_nr" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter Article Nr">
                                    {{ $i->article_nr ?: '-' }}
                                </a>
                            </td>
                            <td style="font-size: 10px;width: 200px">
                                <a href="#" class="editable-remark" data-name="remark" data-pk="{{ $i->id }}" data-type="textarea" data-url="/buyers/update" data-title="Enter Remark">
                                    {{ $i->remark ?: '-' }}
                                </a>
                            </td>
                              <!-- wdh -->
                              <td style="font-size: 10px;">
                                <a href="#" class="editable-w" data-name="w" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter W Capacity">
                                    {{ $i->w ?: '-' }}
                                </a>
                            </td>

  <td style="font-size: 10px;">
                                <a href="#" class="editable-d" data-name="d" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter D Capacity">
                                    {{ $i->d ?: '-' }}
                                </a>
                            </td>
                              <td style="font-size: 10px;">
                                <a href="#" class="editable-h" data-name="h" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter H Capacity">
                                    {{ $i->h ?: '-' }}
                                </a>
                            </td>

                            <!--  -->
                            <td style="font-size: 10px;">
                                <a href="#" class="editable-materials" data-name="materials" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter Glass/Mirror">
                                    {{ $i->materials ?: '-' }}
                                </a>
                            </td>
                            <td style="font-size: 10px;">
                                <a href="#" class="editable-cushion" data-name="cushion" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter Cushion">
                                    {{ $i->cushion ?: '-' }}
                                </a>
                            </td>

                            <td style="font-size: 10px;">
                                <a href="#" class="editable-glass_orMirror" data-name="glass_orMirror" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter Glass/Mirror">
                                    {{ $i->glass_orMirror ?: '-' }}
                                </a>
                            </td>

                            <td style="font-size: 10px;">
                                <a href="#" class="editable-weight_capacity" data-name="weight_capacity" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter Weight Capacity">
                                    {{ $i->weight_capacity ?: '-' }}
                                </a>
                            </td>

                            <td style="font-size: 10px;">
                                <a href="#" class="editable-finishes_color" data-name="finishes_color" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter Finishes Color">
                                    {{ $i->finishes_color ?: '-' }}
                                </a>
                            </td>
                            <td style="font-size: 10px;">
                                <a href="#" class="editable-usd_selling_price" data-name="usd_selling_price" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter USD Selling Price">
                                    {{ $i->usd_selling_price ?: '-' }}
                                </a>
                            </td>
                            <td style="font-size: 10px;">
                                <a href="#" class="editable-packing_dimention" data-name="packing_dimention" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter Packing Dimension">
                                    {{ $i->packing_dimention ?: '-' }}
                                </a>
                            </td>
                            <td style="font-size: 10px;">
                                <a href="#" class="editable-nw" data-name="nw" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter NW">
                                    {{ $i->nw ?: '-' }}
                                </a>
                            </td>
                            <td style="font-size: 10px;">
                                <a href="#" class="editable-gw" data-name="gw" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter GW">
                                    {{ $i->gw ?: '-' }}
                                </a>
                            </td>
                            <td style="font-size: 10px;">
                                <a href="#" class="editable-cbm" data-name="cbm" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter CBM">
                                    {{ $i->cbm ?: '-' }}
                                </a>
                            </td>
                            <td style="font-size: 10px;">
                                <a href="#" class="editable-accessories" data-name="accessories" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter Accessories">
                                    {{ $i->accessories ?: '-' }}
                                </a>
                            </td>
                            <td style="font-size: 10px;">
                                <a href="#" class="editable-picture_of_accessories" data-name="picture_of_accessories" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter Accessories Picture">
                                    {{ $i->picture_of_accessories ?: '-' }}
                                </a>
                            </td>
                            <td style="font-size: 10px;">
                                <a href="#" class="editable-finish_steps" data-name="finish_steps" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter Finish Steps">
                                    {{ $i->finish_steps ?: '-' }}
                                </a>
                            </td>
                            <td style="font-size: 10px;">
                                <a href="#" class="editable-harga_supplier" data-name="harga_supplier" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter Supplier Price">
                                    {{ $i->harga_supplier ?: '-' }}
                                </a>
                            </td>
                            <td style="font-size: 10px;">
                                <a href="#" class="editable-loadability" data-name="loadability" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter Loadability">
                                    {{ $i->loadability ?: '-' }}
                                </a>
                            </td>
                            <td style="font-size: 10px;">
                                <a href="#" class="editable-electricity" data-name="electricity" data-pk="{{ $i->id }}" data-type="text" data-url="/buyers/update" data-title="Enter Electricity">
                                    {{ $i->electricity ?: '-' }}
                                </a>
                            </td>
                            <td style="font-size: 10px;">
                                <a href="#" class="editable-comment_visit" data-name="comment_visit" data-pk="{{ $i->id }}" data-type="textarea" data-url="/buyers/update" data-title="Enter Visit Comment">
                                    {{ $i->comment_visit ?: '-' }}
                                </a>
                            </td>


                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>

</div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const tableWrapper = document.querySelector(".table-wrapper");
        const headerCells = document.querySelectorAll("thead th");

        tableWrapper.addEventListener("scroll", function() {
            if (tableWrapper.scrollTop > 0) {
                headerCells.forEach(th => th.classList.add("scrolled"));
            } else {
                headerCells.forEach(th => th.classList.remove("scrolled"));
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{csrf_token()}}'
            }
        });



    })
</script>
@endsection
