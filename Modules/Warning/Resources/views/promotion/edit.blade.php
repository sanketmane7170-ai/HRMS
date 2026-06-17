@extends('layouts.backend')

@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('update_letter_type')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('backend.user-promotion')}}">{{__trans('user-promotion')}}</a></li>
                        <li class="breadcrumb-item active">{{__trans('edit_user_promotion_letter')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <form action="{{route('backend.update_promotion_letter_type',$documentType->id)}}" datatable="true" method="POST" class="ajax-form-submit reset" redirect>
                            @csrf
                            <div class="modal-body p-4">
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">{{__trans('name')}}</label>
                                                <input type="text" name="name" class="form-control" value="{{ $documentType->name }}" placeholder="{{__trans('name')}}">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="template" class="form-label">{{__trans('template')}}</label>
                                                <textarea name="template" id="template" id="template">{{ $documentType->template }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="">{{__trans('available_keywords')}}</label><br>
                                            [[date]] , [[promotion_date]] , [[name]] , [[department]] , [[designation]] , [[new_position]] , [[user_basic_salary]] , [[user_transportation_allowances]] , [[user_housing_allowances]] , [[user_other_allowances]] , [[user_gross_salary]] , [[header]], [[footer]] , [[logo]] , [[small_logo]] , [[sign]]
                                        <br>
                                        <small class="text-danger">** {{__trans('use_keyword_as_it_to_load_user_data')}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>
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
    initTextEditorWithSource(['template'])
</script>
@endpush
