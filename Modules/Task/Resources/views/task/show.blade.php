@extends('layouts.backend')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('task_view') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('backend.task.index') }}">{{ __trans('my_tasks') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __trans('task_view') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        @php
        $authUser = $task->user;
        @endphp

        <!-- Task Details -->
        <div class="row">
            <div class="card">
                <div class="card-body">
                    <div class="row">

                        <!-- Task Info (Left) -->
                        <div class="col-md-6">
                            <table class="table">

                                <tr>
                                    <td><strong>{{ __trans('assigned_to') }}</strong></td>
                                    <td>{{ isset($task->assigned_to_user) ? $task->assigned_to_user->name : "NA" }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __trans('assigned_by') }}</strong></td>
                                    <td>{{ isset($task->assigned_by_user) ?  $task->assigned_by_user->name : "NA" }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __trans('title') }}</strong></td>
                                    <td>{{ $task->title }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __trans('description') }}</strong></td>
                                    <td>{{ $task->description }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Task Info (Right) -->
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <td><strong>{{ __trans('start_date') }}</strong></td>
                                    <td>{{ formatDate($task->start_date) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __trans('end_date') }}</strong></td>
                                    <td>{{ formatDate($task->end_date) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __trans('priority') }}</strong></td>
                                    <td>{{ __trans($task->priority) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __trans('status') }}</strong></td>
                                    <td>{{ __trans($task->status) }}</td>
                                </tr>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Comments Section -->
        <h3>Comments</h3>
        <ul class="list-group">
            @foreach($task->comments as $comment)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ $comment->user->name }}:</strong> {{ $comment->comment }}
                    <br>
                    <small class="text-muted">{{ $comment->created_at->format('d M Y, h:i A') }}</small>
                </div>

                @if($comment->user_id == auth()->id())
                <form action="{{ route('backend.task.comments.destroy', $comment->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete</button>
                </form>
                @endif
            </li>
            @endforeach
        </ul>

        <!-- Add Comment Form -->
        <form action="{{ route('backend.task.comments.store', $task->id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <textarea name="comment" class="form-control" placeholder="Write a comment..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Comment</button>
        </form>

    </div>
</div>
@endsection