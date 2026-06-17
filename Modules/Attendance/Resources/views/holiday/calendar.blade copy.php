@extends('layouts.backend')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css" />
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
@endpush
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        @can('Create Holiday')
        <a href="{{route('backend.holidays.create')}}" class="edit-button btn btn-primary d-none">
            <i class="fa fa-plus"></i> {{__trans('add_holiday')}}</a>
        @endcan
        <div class="row" style="padding-bottom: 20px;">
            <div class="card">
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="editModal" class="modal"></div>
@endsection


@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<script>
    var calendar = $('#calendar').fullCalendar({
        header: {
            left: 'prev,next,today',
            right: 'title',
        },
        editable: true,
        events: '{{route("backend.holidays.calendar")}}',
        displayEventTime: false,
        editable: true,
        eventRender: function(event, element, view) {

        },
        selectable: true,
        selectHelper: true,
        select: function(start, end, allDay) {
            $('.edit-button').click();
        },
        eventDrop: function(event, delta) {
            var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD");
            var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD");
            $.ajax({
                url: event.update_url,
                data: {
                    description: event.title,
                    start_date: start,
                    end_date: end,
                    _method: 'PUT'
                },
                type: "POST",
                success: function(response) {
                    showAlert("Event Updated Successfully");
                }
            });
        },
        eventClick: function(event) {
            var deleteMsg = confirm("Do you really want to delete?");
            if (deleteMsg) {
                $.ajax({
                    url: event.delete_url,
                    type: "POST",
                    data: {
                        _method: 'DELETE'
                    },
                    success: function(response) {
                        showAlert("Event Deleted Successfully", 'success');
                    }
                });
            }
        }

    });
</script>
@endpush
