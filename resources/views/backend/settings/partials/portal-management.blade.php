<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col">
                <h4>{{__trans('all_portal_information')}}</h4>
            </div>
            <div class="col-auto">
                    @can('Create Portal')
                    <a href="{{route('backend.settings.portals.info.create')}}" data-bs-toggle="modal" data-bs-target="#addResourceModal" class="btn btn-primary btn-sm me-1">
                        <i class="fas fa-plus"></i>
                    </a>
                    @endcan
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table text-center table-hover" id="dataTable">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>{{__trans('name')}}</th>
                        <th>{{__trans('base_url')}}</th>
                        <th>{{__trans('unique_code')}}</th>
                        <th>{{__trans('creation_date')}}</th>
                        <th>{{__trans('actions')}}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<div id="editModal" class="modal" role="dialog" aria-labelledby="myModalLabel" aria-modal="true">

</div>
@include('backend.settings.portal.create')
@push('scripts')

<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{route('backend.settings.portals.info')}}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'name',
                name: 'name'
            },
            {
                data: 'base_url',
                name: 'base_url'
            },
            {
                data: 'unique_code',
                name: 'unique_code'
            },
            {
                data: 'created_at',
                name: 'created_at'
            },

            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
        ]
    });
</script>
@endpush
