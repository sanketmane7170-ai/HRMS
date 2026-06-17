
@extends('layouts.backend')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="row">
            @if (isModuleEnabled('Apparel'))
            @can('Manage Apparel')
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <a style="color: white;" class="@if($activeLink =='apparel') active @endif" href="{{ route('backend.apparel') }}">
                            <div class="dash-widget-header">
                                <div class="dash-title">
                                    <h6> 
                                        {{__trans('uniform')}}
                                    </h6>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <a style="color: white;" class="@if($activeLink =='apparel_request') active @endif" href="{{ route('backend.apparel-request') }}">
                            <div class="dash-widget-header">
                                <div class="dash-title">
                                    <h6> 
                                        {{__trans('uniform_request')}}
                                    </h6>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <a style="color: white;" class="@if($activeLink =='apparel_report') active @endif" href="{{ route('backend.apparel-report') }}">
                            <div class="dash-widget-header">
                                <div class="dash-title">
                                    <h6> 
                                        {{__trans('uniform_report')}}
                                    </h6>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            @endcan
            @endif
            
        </div>
    </div>
</div>
<!-- /Page Wrapper -->
@endsection
@push('scripts')
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    $(document).ready(function() {

    });
</script>

<script>

</script>
@endpush
