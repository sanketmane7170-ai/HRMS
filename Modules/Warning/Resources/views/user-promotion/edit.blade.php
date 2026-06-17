@extends('layouts.backend')

@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('create_user_promotion_letter')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('backend.user_promotion_letter')}}">{{__trans('user-promotion-letter')}}</a></li>
                        <li class="breadcrumb-item active">{{__trans('create_user_promotion_letter')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-06">
                <div class="card card-table">
                    <div class="card-body">
                        <form action="{{route('backend.user_promotion_letter_update',$documentType->id)}}" datatable="true" method="POST" class="ajax-form-submit reset" redirect>
                            @csrf
                            <div class="modal-body p-4">
                                <div class="row">
                                    <div class="col-md-06">
                                        <div class="col-md-06">
                                            <div class="mb-3">
                                                <label for="start_at" class="form-label">{{__trans('employee')}}</label>
                                                <select name="user_id" id="user_id" class="form-control select-search">
                                                    <option value="">{{__trans('select_employee')}}</option>
                                                    @foreach ($users as $user)
                                                        <option value="{{$user->id}}" {{$user->id == $documentType->user_id ? 'selected' : ''}}>{{$user->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-06">
                                            <div class="mb-3">
                                                <label for="start_at" class="form-label">{{__trans('select_letter_type')}}</label>
                                                <select name="letter_type_id" id="letter_type_id" class="form-control select-search">
                                                    <option value="">{{__trans('select_letter')}}</option>
                                                    @foreach ($letters as $letter)
                                                        <option value="{{$letter->id}}" {{$letter->id == $documentType->letter_type_id ? 'selected' : ''}}>{{$letter->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-06">
                                            <div class="mb-3">
                                                <label for="start_at" class="form-label">{{__trans('promotion_date')}}</label>
                                                <input type="date" name="promotion_date" value="{{ $documentType->date ? \Carbon\Carbon::parse($documentType->date)->format('Y-m-d') : '' }}" class="form-control" placeholder="{{__trans('promotion_date')}}">
                                            </div>
                                        </div>
                                        <div class="col-md-06">
                                            <div class="mb-3">
                                                <label for="start_at" class="form-label">{{__trans('new_position')}}</label>
                                                <input type="text" name="new_position" value="{{$documentType->new_position}}" class="form-control" placeholder="{{__trans('new_position')}}">
                                            </div>
                                        </div>
                                        <div class="col-md-06">
                                            <div class="mb-3">
                                                <label for="start_at" class="form-label">{{__trans('user_basic_salary')}}</label>
                                                <input type="text" name="user_basic_salary" value="{{$documentType->user_basic_salary}}" class="form-control" placeholder="{{__trans('user_basic_salary')}}">
                                            </div>
                                        </div>
                                        <div class="col-md-06">
                                            <div class="mb-3">
                                                <label for="start_at" class="form-label">{{__trans('user_transportation_allowances')}}</label>
                                                <input type="text" name="user_transportation_allowances" value="{{$documentType->user_transportation_allowances}}" class="form-control" placeholder="{{__trans('user_transportation_allowances')}}">
                                            </div>
                                        </div>
                                        <div class="col-md-06">
                                            <div class="mb-3">
                                                <label for="start_at" class="form-label">{{__trans('user_housing_allowances')}}</label>
                                                <input type="text" name="user_housing_allowances" value="{{$documentType->user_housing_allowances}}" class="form-control" placeholder="{{__trans('user_housing_allowances')}}">
                                            </div>
                                        </div>
                                        <div class="col-md-06">
                                            <div class="mb-3">
                                                <label for="start_at" class="form-label">{{__trans('user_other_allowances')}}</label>
                                                <input type="text" name="user_other_allowances" value="{{$documentType->user_other_allowances}}" class="form-control" placeholder="{{__trans('user_other_allowances')}}">
                                            </div>
                                        </div>
                                        <div class="col-md-06">
                                            <div class="mb-3">
                                                <label for="start_at" class="form-label">{{__trans('user_gross_salary')}}</label>
                                                <input type="text" name="user_gross_salary" value="{{$documentType->user_gross_salary}}" class="form-control" placeholder="{{__trans('user_gross_salary')}}">
                                            </div>
                                        </div>
                                         <div class="col-md-06">
                                            <div class="mb-3">
                                                <label for="start_at" class="form-label">{{__trans('remarks')}}</label>
                                                <input type="text" name="remarks" value="{{$documentType->remarks}}" class="form-control" placeholder="{{__trans('remarks')}}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" onclick="window.history.back();">{{__trans('close')}}</button>
                                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('update')}} </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    initselect2search();
    initTextEditorWithSource(['template'])
    document.getElementById('close-and-redirect').addEventListener('click', function () {
        window.location.href = "{{ url()->previous() }}";
    });
</script>
@endpush
