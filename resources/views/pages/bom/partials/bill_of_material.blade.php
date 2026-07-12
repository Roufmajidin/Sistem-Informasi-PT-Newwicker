<div class="table-responsive">

    <table class="table table-bordered">

        <thead>

            <tr>

                <th>No</th>
                <th>Name</th>
                <th>Article Number</th>
                <th width="200">Action</th>

            </tr>

        </thead>

        <tbody>

            @foreach($boms as $bom)

            <tr>

                <td>
                    {{ $loop->iteration }}
                </td>

                <td>
                    {{ $bom->name }}
                </td>

                <td>
                    {{ $bom->article_number }}
                </td>

                <td>

                    <a
                        href="{{ route('bom.show',$bom->id) }}"
                        class="btn btn-info btn-sm">

                        Detail

                    </a>

                    <a
                        href="{{ route('bom.edit',$bom->id) }}"
                        class="btn btn-warning btn-sm">

                        Edit

                    </a>
                    <a
                        href="{{ route('bom.export.excel',$bom->id) }}"
                        class="btn btn-success">

                        <i class="fa fa-file-excel"></i>

                        Export Excel

                    </a>
                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

</div>
