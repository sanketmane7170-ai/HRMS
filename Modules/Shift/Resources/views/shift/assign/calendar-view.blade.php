@extends('layouts.backend')
@section('content')
<style>
  .fc-time {
      display: none;
  }
  .fc-month-button {
    display: none;
  }
</style>
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('assign_shift')}} : {{ __trans($user->name) }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('shift_scheduler')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('backend.assign_shift.multiple',[$user]) }}" class="btn btn-primary btn-md edit-button">
                        <i class="fas fa-plus"></i> Assign MultiShift
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                    <div class="container">
                    <!-- <div class="alert alert-warning">
                        <i class="glyphicon glyphicon-exclamation-sign"></i> From <strong>1st</strong> of <strong>Aprial</strong> As per labour law first 5 paid holiday consider as planning holidays.
                    </div> -->
                    <div id='calendarFull' data-delete-route="{{ route('backend.shift.destroy', ['shift' => ':id']) }}"></div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="event-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    <form action="{{ route('backend.assign_shift.toUser',[$user]) }}" method="post" class="ajax-form-submit">
    @csrf
      <div class="modal-header">
        <h4 class="modal-title">Assign Shift to Employee</h4>
      </div>
      <div class="modal-body">
          <div class="form-group">
            <label>Date</label>
            <input type="text" name="assigned_for_date" class="form-control col-xs-3" readonly/>
          </div>
        <div class="form-group">
            <label>Shift</label>
            <select class="form-control select2" name="schedule_id" id="shiftSelect">
                <option>Select Shift</option>
                @foreach($shifts as $shift)
                  <x-shift::ShiftOption :shift="$shift" />
                @endforeach
            </select>
        </div>      
      </div>
      <div class="modal-footer">
        <button type="button" id="closeModalButton" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-info waves-effect waves-light">Add</button>
      </div>
      </form>
    </div>

  </div>

</div>
<!-- jQuery -->
<div class="modal" id="editModal"></div>

@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
@endpush
@push('scripts')

<script>
    loadAjaxSelect2();
    initselect2();
   
   $('#shiftSelect').select2({
        placeholder: '{{ __trans("search_shift ...") }}',
         dropdownParent: $('#event-modal'),
        width: '100%'
    });
</script>
<script>
    var laravelEvents = @json($formattedEvents);
    console.log('myevents',laravelEvents);
</script>
<script src="{{asset('assets/backend/plugins/fullcalendar/custom-script.js')}}"></script>

<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
@endpush

