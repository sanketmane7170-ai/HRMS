@extends('layouts.backend')

@push('css')
<style>
    .info {
        margin-top: 0.5rem !important;
    }
</style>

@endpush
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('set_allowance_deducation')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item">{{__trans('set_allowance_&_deducation')}}</li>
                    </ul>
                </div>
                <div class="col-auto" >
                    <a href="{{route('backend.payroll.user.bulk.allowance')}}" class="btn btn-sm inline-block me-2  btn-success"> <i class="fa fa-plus"></i> Add Bulk Allowance & Deducation </a>
                </div>
            </div>
        </div>
        <div class="col-xl-12">
            <div class="row">
                <div class="col-sm-12 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col">
                                    <h5>{{__trans('set_allowance')}}</h5>
                                </div>
                                <div class="col-auto">
                                    <a href="{{route('backend.payroll.user.allowance')}}" class="btn btn-sm inline-block me-2  btn-success edit-button"> <i class="fa fa-plus"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" style=" overflow:auto">
                            <div class="table-responsive">
                                <table class="table text-left table-hover" id="dataTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{__trans('title')}}</th>
                                            {{--  <th>{{__trans('amount')}}</th>  --}}
                                            <th>{{__trans('action')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($allowanceDeducation as $allowance)
                                        @if($allowance->type==1)
                                            <tr>
                                                <td>{{ $allowance->name }}</td>
                                                {{--  <td>{{ $allowance->amount }}</td>  --}}
                                                <td>
                                                    <a href="{{ route('backend.payroll.user.updateAllowance', $allowance->id) }}" class='btn btn-sm inline-block me-2  btn-warning edit-button'><i class='fa fa-edit'></i></a>
                                                    <a href="{{ route('backend.payroll.user.deleteAllowance', $allowance->id) }}" class='btn btn-sm inline-block me-2  btn-danger action-button' method='delete' ><i class='fa fa-trash'></i></a>
                                                </td>
                                            </tr>
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12 col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col">
                                    <h5>{{__trans('set_deduction')}}</h5>
                                </div>
                                <div class="col-auto">
                                    <a href="{{route('backend.payroll.user.deduction')}}" class="btn btn-sm inline-block me-2  btn-success edit-button"> <i class="fa fa-plus"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" style=" overflow:auto">
                            <div class="table-responsive">
                                <table class="table text-left table-hover" id="deduction">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{__trans('title')}}</th>
                                            {{--  <th>{{__trans('amount')}}</th>  --}}
                                            <th>{{__trans('action')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($allowanceDeducation as $deducation)
                                        @if($deducation->type==2)
                                            <tr>
                                                <td>{{ $deducation->name }}</td>
                                                {{--  <td>{{ $deducation->amount }}</td>  --}}
                                                <td>
                                                    <a href="{{ route('backend.payroll.user.updateDeduction', $deducation->id) }}" class='btn btn-sm inline-block me-2  btn-warning edit-button'><i class='fa fa-edit'></i></a>
                                                    <a href="{{ route('backend.payroll.user.deleteDeduction', $deducation->id) }}" class='btn btn-sm inline-block me-2  btn-danger action-button' method='delete' ><i class='fa fa-trash'></i></a>
                                                </td>
                                            </tr>
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="editModal" class="modal"></div>
@endsection

@push('scripts')

<script type="text/javascript">
    @if(Session::has('store'))
        toastr.success("{{ Session::get('store') }}");
    @endif
</script>
@endpush