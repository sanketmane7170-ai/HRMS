@extends('layouts.backend')

@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('preview_document_requested')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('backend.document-requests.index')}}">{{__trans('document_request_list')}}</a></li>
                        <li class="breadcrumb-item active">{{__trans('preview_document_requested')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @if($documentRequest->file_path)
                    <a href="{{route('backend.document-requests.download',$documentRequest)}}" target="__blank" class="btn btn-primary">
                        <i class="fa fa-download"></i> {{__trans('download')}}</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <form action="{{route('backend.document-requests.generate',$documentRequest)}}" datatable="true" method="POST">
                            @csrf
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col"></div>
                                    <div class="col-auto">
                                        <button type="submit" class="btn btn-primary">{{__trans('generate')}} </button>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="html" class="form-label">{{__trans('document')}}</label>
                                            <textarea style="height: 1250px;"  name="html" id="html" id="html">{{$html}}</textarea>
                                        </div>
                                    </div>
                                </div>
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
    initTextEditor(['html'])
</script>
@endpush
