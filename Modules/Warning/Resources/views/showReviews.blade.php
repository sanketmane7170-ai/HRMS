@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="row">

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a style="color: white;" class="@if($activeLink =='user-warnings') active @endif"
                                        href="{{ route('backend.user-warnings.index') }}">{{__trans('user-warnings')}}</a>
                                </h6>
                            </div>


                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a style="color: white;" class="@if($activeLink =='user-appreciation') active @endif"
                                        href="{{ route('backend.user-appreciation') }}">{{__trans('User Appreciation')}}</a>
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a style="color: white;" class="@if($activeLink =='user-increment') active @endif"
                                        href="{{ route('backend.user-increment') }}">{{__trans('User Growth')}}</a>
                                </h6>
                            </div>


                        </div>
                    </div>
                </div>
            </div>

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