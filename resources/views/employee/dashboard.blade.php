@extends('layouts.backend')

@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="row">
            <div class="col">
                Hi {{auth()->user()->name}}
            </div>
            <!-- <div class="col-auto user-checkin-checkout">
                <x-attendance::checkin selector=".user-checkin-checkout" />
            </div> -->
        </div>
        @php
        $authUser = auth()->user();
        @endphp

        @can('Dashboard Leave')
        <x-user-leave-balance :user=$authUser />
        @endcan
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <x-announcements />
            </div>
            <div class="col-lg-6 col-12">
                <x-birthday-list />
            </div>
            <div class="col-lg-6 col-12">
                <x-anniversary-list />
            </div>
        </div>
    </div>
</div>

<!-- Extra Modal Code Added for edit leave on dashboard -->
<div class="modal" id="editModal">

</div>
<!-- /Page Wrapper -->
@endsection
