@extends('layouts.backend')
@push('css')
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
@endpush
@section('content')
<style>
.kanban-column {
    background-color: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    height: 100%;
}

.kanban-list {
    min-height: 500px;
    background-color: #e9ecef;
    border-radius: 5px;
    padding: 10px;
}

.kanban-item {
    cursor: move;
}

.kanban-item.invisible {
    opacity: 0.4;
}
</style>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('task_list')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('task_list')}}</li>
                    </ul>
                </div>
                <div class="col-auto">

                    <button type="button" class="btn btn-primary me-1" data-bs-toggle="modal"
                        data-bs-target="#createTaskModal" data-status="completed"
                        style="padding-top: 0.5rem; padding-bottom: 0.5rem;"><i class="fas fa-plus"></i></button>
                </div>
            </div>
        </div>
        @php
        $statusColors = [
        'PENDING' => '#042356',
        'IN_PROGRESS' => '#ffbc34 !important',
        'COMPLETED' => '#22C68C',
        'ON_HOLD' => '#FF0000',
        ];

        $priorityColors = [
        'LOW' => '#042356',
        'MEDIUM' => '#ffbc34',
        'HIGH' => '#FF0000',
        'URGENT' => '#343a40',
        ];

        // Calculate contrast text color
        function getTextColor($hexColor) {
        $hex = str_replace('#', '', $hexColor);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $luminance = ($r * 299 + $g * 587 + $b * 114) / 1000;
        return $luminance > 150 ? 'black' : 'white';
        }
        @endphp

        <div class="row">
            <div class="col-md-12">
                <div class="card-body light">

                    {{-- Status cards row --}}
                    <div class="row justify-content-center mb-3">
                        @foreach($statusCounts as $status => $count)
                        @php
                        $key = strtoupper($status);
                        $bgColor = $statusColors[$key] ?? '#f8f9fa';
                        $textColor = getTextColor($bgColor);
                        @endphp
                        <div class="col-md-3 col-6 mb-3">
                            <div class="card shadow-sm border"
                                style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
                                <div class="card-body text-center">
                                    <h6 class="text-uppercase fw-bold">{{ __trans($status) }}</h6>
                                    <h4 class="mb-0">{{ $count }}</h4>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Priority cards row --}}
                    <div class="row justify-content-center">
                        @foreach($priorityCounts as $priority => $count)
                        @php
                        $key = strtoupper($priority);
                        $bgColor = $priorityColors[$key] ?? '#f8f9fa';
                        $textColor = getTextColor($bgColor);
                        @endphp
                        <div class="col-md-3 col-6 mb-3">
                            <div class="card shadow-sm border"
                                style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
                                <div class="card-body text-center">
                                    <h6 class="text-uppercase fw-bold">{{ __trans($priority) }}</h6>
                                    <h4 class="mb-0">{{ $count }}</h4>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>





        <div class="row">
            <div class="col-md-3">
                <div class="kanban-column">
                    <div
                        class="d-flex justify-content-between bg-primary text-white shadow-sm align-items-center px-3 py-2 rounded-top">
                        <h4 class="text-white fw-bolder m-0">{{__trans('pending')}}</h4>
                        <!-- <button type="button" class="btn btn-light" data-bs-toggle="modal"
                            data-bs-target="#createTaskModal" data-status="pending"
                            style="padding-top: 0.5rem; padding-bottom: 0.5rem;">+</button> -->
                    </div>

                    <div class="kanban-list" id="pending">
                        @foreach ($tasks['pending'] ?? [] as $task)
                        <div class="card mb-3 kanban-item" data-id="{{ $task->id }}" draggable="true">
                            <div class="card-body light">

                                <h5 class="card-title">
                                    {{__trans('assigned_by ')}} :-{{$task->assigned_by_user->name}}

                                </h5>

                                <h5 class="card-title">
                                    {{__trans('title ')}} :-{{ $task->title }}

                                </h5>

                                <p class="card-text">{{__trans('description ')}} :-{{ $task->description }}</p>
                                <a href="{{ route('backend.task.show', $task) }}" class="btn btn-primary btn-sm"><i
                                        class="fa fa-eye"></i></a>

                                <a style="float:right" href="#"
                                    class="btn btn-primary btn-sm badge {{ $task->priority == 'low' ? 'bg-success' : ($task->priority == 'medium' ? 'bg-warning' : 'bg-danger') }}"><i
                                        class="badge {{ $task->priority == 'low' ? 'bg-success' : ($task->priority == 'medium' ? 'bg-warning' : 'bg-danger') }}">{{ __trans($task->priority) }}</i></a>

                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kanban-column">
                    <div
                        class="d-flex justify-content-between shadow-sm align-items-center bg-warning px-3 py-2 rounded-top">
                        <h4 class="text-white fw-bolder m-0">{{__trans('in_progress')}}</h4>
                        <!-- <button type="button" class="btn btn-light" data-bs-toggle="modal"
                            data-bs-target="#createTaskModal" data-status="in_progress"
                            style="padding-top: 0.5rem; padding-bottom: 0.5rem;">+</button> -->
                    </div>

                    <div class="kanban-list" id="in_progress">
                        @foreach ($tasks['in_progress'] ?? [] as $task)
                        <div class="card mb-3 kanban-item" data-id="{{ $task->id }}" draggable="true">
                            <div class="card-body light">

                                <h5 class="card-title">
                                    {{__trans('assigned_by ')}} :-{{$task->assigned_by_user->name}}

                                </h5>

                                <h5 class="card-title">
                                    {{__trans('title ')}} :-{{ $task->title }}

                                </h5>

                                <p class="card-text">{{__trans('description ')}} :-{{ $task->description }}</p>
                                <a href="{{ route('backend.task.show', $task) }}" class="btn btn-primary btn-sm"><i
                                        class="fa fa-eye"></i></a>

                                <a style="float:right" href="#"
                                    class="btn btn-primary btn-sm badge {{ $task->priority == 'low' ? 'bg-success' : ($task->priority == 'medium' ? 'bg-warning' : 'bg-danger') }}"><i
                                        class="badge {{ $task->priority == 'low' ? 'bg-success' : ($task->priority == 'medium' ? 'bg-warning' : 'bg-danger') }}">{{ __trans($task->priority) }}</i></a>

                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kanban-column">
                    <div
                        class="d-flex justify-content-between shadow-sm align-items-center bg-success px-3 py-2 rounded-top">
                        <h4 class="text-white fw-bolder m-0">{{__trans('completed')}}</h4>
                        <!-- <button type="button" class="btn btn-light" data-bs-toggle="modal"
                            data-bs-target="#createTaskModal" data-status="completed"
                            style="padding-top: 0.5rem; padding-bottom: 0.5rem;">+</button> -->
                    </div>
                    <div class="kanban-list" id="completed">
                        @foreach ($tasks['completed'] ?? [] as $task)
                        <div class="card mb-3 kanban-item" data-id="{{ $task->id }}" draggable="true">
                            <div class="card-body light">

                                <h5 class="card-title">
                                    {{__trans('assigned_by ')}} :-{{$task->assigned_by_user->name}}

                                </h5>

                                <h5 class="card-title">
                                    {{__trans('title ')}} :-{{ $task->title }}

                                </h5>

                                <p class="card-text">{{__trans('description ')}} :-{{ $task->description }}</p>
                                <a href="{{ route('backend.task.show', $task) }}" class="btn btn-success btn-sm"><i
                                        class="fa fa-eye"></i></a>

                                <a style="float:right" href="#"
                                    class="btn btn-primary btn-sm badge {{ $task->priority == 'low' ? 'bg-success' : ($task->priority == 'medium' ? 'bg-warning' : 'bg-danger') }}"><i
                                        class="badge {{ $task->priority == 'low' ? 'bg-success' : ($task->priority == 'medium' ? 'bg-warning' : 'bg-danger') }}">{{ __trans($task->priority) }}</i></a>

                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kanban-column">
                    <div
                        class="d-flex justify-content-between shadow-sm align-items-center bg-danger px-3 py-2 rounded-top">
                        <h4 class="text-white fw-bolder m-0">{{__trans('on_hold')}}</h4>
                        <!-- <button type="button" class="btn btn-light" data-bs-toggle="modal"
                            data-bs-target="#createTaskModal" data-status="on_hold"
                            style="padding-top: 0.5rem; padding-bottom: 0.5rem;">+</button> -->
                    </div>
                    <div class="kanban-list" id="on_hold">
                        @foreach ($tasks['on_hold'] ?? [] as $task)
                        <div class="card mb-3 kanban-item" data-id="{{ $task->id }}" draggable="true">
                            <div class="card-body light">

                                <h5 class="card-title">
                                    {{__trans('assigned_by ')}} :-{{$task->assigned_by_user->name}}

                                </h5>

                                <h5 class="card-title">
                                    {{__trans('title ')}} :-{{ $task->title }}

                                </h5>

                                <p class="card-text">{{__trans('description ')}} :-{{ $task->description }}</p>
                                <a href="{{ route('backend.task.show', $task) }}" class="btn btn-success btn-sm"><i
                                        class="fa fa-eye"></i></a>

                                <a style="float:right" href="#"
                                    class="btn btn-primary btn-sm badge {{ $task->priority == 'low' ? 'bg-success' : ($task->priority == 'medium' ? 'bg-warning' : 'bg-danger') }}"><i
                                        class="badge {{ $task->priority == 'low' ? 'bg-success' : ($task->priority == 'medium' ? 'bg-warning' : 'bg-danger') }}">{{ __trans($task->priority) }}</i></a>

                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Task Modal -->
        <div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createTaskModalLabel">Create Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{route('backend.employee.task.create_my_task')}}" datatable="true" method="POST"
                            class="ajax-form-submit reset">
                            @csrf
                            <div class="modal-body p-4">
                                <div class="row">



                                    <!-- <div class="col-md-12">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{__trans('assigned_to')}}</label>
                            <select name="assigned_to" id="assigned_to" class="form-control ajax-select2"
                                data-target="{{ route('ajax.select2.fetch.users') }}">
                            </select>
                        </div>
                    </div> -->
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="title" class="form-label">{{__trans('title')}}</label>
                                            <input type="text" name="title" class="form-control"
                                                placeholder="{{__trans('title')}}">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="description"
                                                class="form-label">{{__trans('description')}}</label>
                                            <input type="text" name="description" class="form-control"
                                                placeholder="{{__trans('description')}}">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="priority" class="form-label">{{ __trans('priority') }}</label>
                                            <select name="priority" class="form-control">
                                                <option value="">-- Select Priority --</option>
                                                <option value="low">{{ __trans('Low') }}</option>
                                                <option value="medium">{{ __trans('Medium') }}</option>
                                                <option value="high">{{ __trans('High') }}</option>
                                                <option value="urgent">{{ __trans('Urgent') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- <div class="col-md-12">
                        <div class="mb-3">
                            <label for="status" class="form-label">{{ __trans('status') }}</label>
                            <select name="status" class="form-control">
                                <option value="">-- Select Status --</option>
                                <option value="pending">{{ __trans('Pending') }}</option>
                                <option value="in_progress">{{ __trans('In Progress') }}</option>
                                <option value="completed">{{ __trans('Completed') }}</option>
                                <option value="on_hold">{{ __trans('On Hold') }}</option>
                            </select>
                        </div>
                    </div> -->

                                    <!-- <div class="col-md-12">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">{{__trans('start_date')}}</label>
                            <input type="text" name="start_date" class="form-control datetime"
                                placeholder="{{__trans('start_date')}}">
                        </div>
                    </div> -->

                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="end_date" class="form-label">{{__trans('end_date')}}</label>
                                            <input type="text" name="end_date" class="form-control datetime"
                                                placeholder="{{__trans('end_date')}}">
                                        </div>
                                    </div>



                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary waves-effect"
                                    data-bs-dismiss="modal">{{__trans('close')}}</button>
                                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}}
                                </button>
                            </div>
                        </form>
                    </div>
                    <!-- <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Save changes</button>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', (event) => {
    const kanbanItems = document.querySelectorAll('.kanban-item');
    const kanbanLists = document.querySelectorAll('.kanban-list');
    const createTaskModal = document.getElementById('createTaskModal');
    const taskStatusInput = document.getElementById('task_status');

    createTaskModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        // var status = button.getAttribute('data-status');
        // taskStatusInput.value = status;
    });

    kanbanItems.forEach(item => {
        item.addEventListener('dragstart', handleDragStart);
        item.addEventListener('dragend', handleDragEnd);
    });

    kanbanLists.forEach(list => {
        list.addEventListener('dragover', handleDragOver);
        list.addEventListener('drop', handleDrop);
    });

    function handleDragStart(e) {
        e.dataTransfer.setData('text/plain', e.target.dataset.id);
        setTimeout(() => {
            e.target.classList.add('invisible');
        }, 0);
    }

    function handleDragEnd(e) {
        e.target.classList.remove('invisible');
    }

    function handleDragOver(e) {
        e.preventDefault();
    }

    function handleDrop(e) {
        e.preventDefault();
        const id = e.dataTransfer.getData('text');
        const draggableElement = document.querySelector(`.kanban-item[data-id='${id}']`);
        const dropzone = e.target.closest('.kanban-list');
        dropzone.appendChild(draggableElement);

        const status = dropzone.id;

        updateTaskStatus(id, status);
    }

    function updateTaskStatus(id, status) {
        fetch(`/tasks/${id}/update-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                status
            })
        }).then(response => {
            if (!response.ok) {
                throw new Error('Failed to update task status');
            }
            return response.json();
        }).then(data => {
            console.log('Task status updated:', data);
        }).catch(error => {
            console.error('Error:', error);
        });
    }
});
</script>
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<script>
flatpickr("input.datetime", {
    enableTime: true,
    // minDate: "today",
    dateFormat: "Y-m-d",
});
</script>
@endsection
