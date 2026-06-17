@extends('layouts.backend')

@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('update_company_policy')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{route('backend.getCompanyPolicy')}}">{{__trans('company_policy')}}</a></li>
                        <li class="breadcrumb-item active">{{__trans('update_company_policy')}}</li>
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
                        <form action="{{route('backend.updateCompanyPolicy',$policy->id)}}" datatable="true" method="POST"
                            enctype="multipart/form-data"
                            class="ajax-form-submit reset" redirect>
                            @csrf
                            <div class="modal-body p-4">
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="title" class="form-label">{{ __trans('title') }}</label>
                                                    <input type="text" name="title" class="form-control" id="title" value="{{ $policy->title }}" placeholder="{{ __trans('title') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="document" class="form-label">{{__trans('document')}}</label>
                                                    <input type="file" name="document" class="form-control" id="document">
                                                    @if($policy->document)
                                                        @php
                                                            $extension = pathinfo($policy->document, PATHINFO_EXTENSION);
                                                            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                                                        @endphp
                                                        @if(in_array(strtolower($extension), $imageExtensions))
                                                            <img src="{{ asset('uploads/companypolicydocument/' . $policy->document) }}" 
                                                                alt="Document" 
                                                                class="img-fluid mt-2" 
                                                                style="width: 20%; height: 30%;">
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
                                                <label for="policy" class="form-label">{{__trans('policy')}}</label>
                                                <textarea name="policy" id="policy">{{ $policy->policy }}</textarea>
                                            </div>
                                        </div>

                                        {{-- ✅ Acknowledgement Checkbox --}}
                                        <div class="col-md-12">
                                            <div class="form-check mb-3">
                                                <input type="checkbox" class="form-check-input" id="acknowledgement" name="acknowledgement" value="1"
                                                    {{ $policy->acknowledgement ? 'checked' : '' }}>
                                                <label class="form-check-label" for="acknowledgement">
                                                    {{ __trans('require_acknowledgement') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary waves-effect"
                                    data-bs-dismiss="modal">{{__trans('close')}}</button>
                                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('update')}}
                                </button>
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
