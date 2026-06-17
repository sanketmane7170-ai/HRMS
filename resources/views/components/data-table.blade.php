<div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-table">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table text-center table-hover" id="dataTable">
                            <thead class="thead-light">
                                <tr>
                                    @foreach ($columns as $column)
                                    <td>{{$getColumnName($column)}}</td>
                                    @endforeach
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    <script type="text/javascript">
        var table = $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{$url}}",
            },
            columns: @json($dataTableColumns)
        });
    </script>
    @endpush
</div>
