@extends('layouts.backend')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="page-header">
            <h3>{{ __('Monthly Performance Report') }}</h3>
        </div>

       <div class="row mb-3">
    <div class="col-md-3">
        <input type="month" id="monthFilter" class="form-control">
    </div>

    <div class="col-md-3">
        <select id="templateFilter" class="form-control select-search">
            <option value="">{{ __('All Templates') }}</option>
        </select>
    </div>

    <div class="col-md-3">
        <select id="branchFilter" class="form-control select-search">
            <option value="">{{ __('All Branches') }}</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>
</div>

        <div class="card light">
            <div class="card-body light">
                <table class="table table-bordered light" id="monthlyTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Reviewer</th>
                            <th>Month</th>
                            <th>Monthly Gain Score</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
@push('scripts')
<script>
$(function() {

    initselect2search();

    // 🔹 Load Templates
    function loadTemplates() {
        $.get('{{ url("performance/templates/by-branch") }}', function(res) {
            $('#templateFilter').html('<option value="">{{ __("All Templates") }}</option>');
            res.forEach(template => {
                $('#templateFilter').append(
                    `<option value="${template.id}">${template.name}</option>`
                );
            });

            $('#templateFilter').trigger('change.select2');
        });
    }

    loadTemplates();

    // 🔹 DataTable
    let table = $('#monthlyTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('performance.monthly.report') }}",
            data: function(d) {
                d.month = $('#monthFilter').val();
                d.template_id = $('#templateFilter').val();
                 d.branch_id = $('#branchFilter').val(); 
            }
        },
        columns: [{
                data: 'DT_RowIndex',
                searchable: false
            },
            {
                data: 'employee_name'
            },
            {
                data: 'reviewer_name'
            },
            {
                data: 'month'
            },
            {
                data: 'monthly_score',
                render: function(data) {
                    return `<span class="fw-bold">${data}</span>`;
                }
            }
        ]
    });

    // 🔄 Reload on month change
    $('#monthFilter').on('change', function() {
        table.ajax.reload();
    });

    // 🔄 Reload on template change
    $('#templateFilter').on('change', function() {
        table.ajax.reload();
    });

    $('#branchFilter').on('change', function () {
    table.ajax.reload();
});

});
</script>
@endpush