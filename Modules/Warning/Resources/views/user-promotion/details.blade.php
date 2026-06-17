@extends('layouts.backend')

@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('preview_promotion_letter')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('backend.user_promotion_letter')}}">{{__trans('user-promotion-letter')}}</a></li>
                        <li class="breadcrumb-item active">{{__trans('preview_promotion_letter')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{route('backend.user_promotion_letter_download',$letter->id)}}" target="__blank" class="btn btn-primary">
                        <i class="fa fa-download"></i> {{__trans('download')}}
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <form action="#" datatable="true" >
                            @csrf
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <label style="color: white">{{__trans('document')}}</label>
                                    </div>
                                    <div class="col-auto">
                                        {{--  <button type="submit" class="btn btn-primary">{{__trans('generate')}} </button>  --}}
                                    </div>
                                </div>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            {{--  <textarea name="html" id="html" id="html">{!! $html !!}</textarea>  --}}
                                            {!! $html !!}
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
    {{--  initTextEditor(['html'])  --}}
</script>
@endpush
