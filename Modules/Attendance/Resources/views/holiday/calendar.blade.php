@extends('layouts.backend')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.2.0/main.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.3.0/main.min.css">
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
<div class="modal fade" id="add-holiday-modal">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{__trans('add_holiday')}} </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('backend.holidays.store')}}" datatable="true" method="POST" class="ajax-form-submit reset">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="detail">{{__trans('description')}}</label>
                                <div class="mb-3">
                                    <input name="detail" id="detail" class="form-control" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start-date" class="form-label">{{__trans('holiday_start_date')}}</label>
                                <div class="mb-3">
                                    <input type="text" name="start_date" id="start-date" class="form-control datepicker" placeholder="{{__trans('select_start_date')}}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end-date" class="form-label">{{__trans('holiday_end_date')}}</label>
                                <div class="mb-3">
                                    <input type="text" name="end_date" id="end-date" class="form-control datepicker" placeholder="{{__trans('select_end_date')}}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="is_recurring">
                                    <input type="checkbox" value="1" class="form-check-input" name="is_recurring" id="is_recurring"> {{__trans('is_recurring_holiday')}}
                                </label>
                            </div>
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
@endsection


@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.2.0/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.2.0/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@4.2.0/main.js"></script>
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<script>
    loadAjaxSelect2();
    var config = {
        dateFormat: "Y-m-d",
        config: {
            mode: 'single'
        }
    };
    var startDate = flatpickr("#start-date", config);
    var endDate = flatpickr("#start-date", config);
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        customButtons: {
            customButton: {
                text: '{{__trans("add_holiday")}}',
                click: function() {
                    $('#add-holiday-modal').modal('show');
                }
            }
        },
        header: {
            right: 'customButton',
            center: 'prev,next '
        },
        plugins: ['dayGrid', 'interaction'],
        editable: true,
        selectable: true,
        unselectAuto: false,
        displayEventTime: false,
        events: '{{route("backend.holidays.calendar.events")}}',
        eventRender: function(data) {

        },
        eventDrop: function(data) {
            console.log(data.event);
            var start = moment(data.event.start).format('YYYY-MM-DD');
            var end = data.event.end ? moment(data.event.end).format('YYYY-MM-DD') : start;
            $.ajax({
                url: data.event.extendedProps.update_url,
                data: {
                    detail: data.event.title,
                    start_date: start,
                    end_date: end,
                    _method: 'PUT'
                },
                type: "POST",
                success: function(response) {
                    if (response.success) {
                        showAlert(response.message);
                    }
                }
            });
        },
        eventClick: function(data) {
            var deleteMsg = confirm("Do you really want to delete?");
            if (deleteMsg) {
                $.ajax({
                    url: data.event.extendedProps.delete_url,
                    type: "POST",
                    data: {
                        _method: 'DELETE'
                    },
                    success: function(response) {
                        if (response.success) {
                            calendar.refetchEvents();
                            showAlert(response.message);
                        }
                    }
                });
            }
        }
    });
    calendar.render();
    calendar.on('select', function(info) {
        startDate.setDate(info.startStr);
        const end_date = moment(info.endStr, 'YYYY-MM-DD').subtract(1, 'day').format('YYYY-MM-DD');
        endDate.setDate(end_date);
    });
</script>
@endpush
