@extends('layouts.backend')

@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('company_policy')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{route('backend.getCompanyPolicy')}}">{{__trans('company_policy')}}</a></li>
                        <li class="breadcrumb-item active">{{__trans('company_policy')}}</li>
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
                        <form action="#" datatable="true" method="POST"
                            class="ajax-form-submit reset" redirect>
                            @csrf
                            <div class="modal-body p-4">
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="name" class="form-label"><u>{{ __trans('title') }}</u></label><br>
                                                    <h3>{{ $policy->title }}</h3>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    @if($policy->document)
                                                        @php
                                                            $extension = pathinfo($policy->document, PATHINFO_EXTENSION);
                                                            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                                                        @endphp
                                                        <label for="document" class="form-label"><u>{{ __trans('document') }}</u></label><br>
                                                        @if(in_array(strtolower($extension), $imageExtensions))
                                                            <img src="{{ asset('uploads/companypolicydocument/' . $policy->document) }}" 
                                                                alt="Document" 
                                                                class="img-fluid mt-2" 
                                                                style="width: 30%; height: 30%;">
                                                        @else
                                                            <a href="{{ asset('uploads/companypolicydocument/' . $policy->document) }}" 
                                                            target="_blank" 
                                                            class="btn btn-primary mt-2">
                                                            View Document ({{ strtoupper($extension) }})
                                                            </a>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="policy" class="form-label"><u>{{__trans('policy')}}</u></label>
                                                <p>{!! $policy->policy !!}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                
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
initTextEditorWithSource(['policy'])
</script>
@endpush