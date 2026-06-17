@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper recruitment-page">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('edit_interview') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('recruitment.dashboard') }}">{{ __trans('recruitment') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('recruitment.interviews.index') }}">{{ __trans('interviews') }}</a></li>
                        <li class="breadcrumb-item active">{{ __trans('edit') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('recruitment.interviews.show', $interview->id) }}" class="btn btn-outline-primary">
                        <i class="fa fa-arrow-left mr-1"></i> {{ __trans('back_to_details') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Edit Interview Form -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ __trans('interview_information') }}</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('recruitment.interviews.update', $interview->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="application_id">{{ __trans('application') }} <span class="text-danger">*</span></label>
                                        <select name="application_id" id="application_id" class="form-control select2" required>
                                            <option value="">{{ __trans('select_application') }}</option>
                                            @foreach($applications as $application)
                                                <option value="{{ $application->id }}" {{ $interview->application_id == $application->id ? 'selected' : '' }}>
                                                    {{ $application->user->name ?? $application->candidate_name ?? 'Unknown' }} - {{ optional($application->job)->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('application_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="interviewer_id">{{ __trans('interviewer') }} <span class="text-danger">*</span></label>
                                        <select name="interviewer_id" id="interviewer_id" class="form-control select2" required>
                                            <option value="">{{ __trans('select_interviewer') }}</option>
                                            @foreach($interviewers as $interviewer)
                                                <option value="{{ $interviewer->id }}" {{ $interview->interviewer_id == $interviewer->id ? 'selected' : '' }}>
                                                    {{ $interviewer->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('interviewer_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="scheduled_at">{{ __trans('scheduled_date') }} <span class="text-danger">*</span></label>
                                        <input type="datetime-local" name="scheduled_at" id="scheduled_at" class="form-control" 
                                               value="{{ $interview->scheduled_at ? $interview->scheduled_at->format('Y-m-d\TH:i') : '' }}" required>
                                        @error('scheduled_at')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="duration_minutes">{{ __trans('duration_minutes') }}</label>
                                        <select name="duration_minutes" id="duration_minutes" class="form-control">
                                            <option value="30" {{ $interview->duration_minutes == 30 ? 'selected' : '' }}>30 minutes</option>
                                            <option value="45" {{ $interview->duration_minutes == 45 ? 'selected' : '' }}>45 minutes</option>
                                            <option value="60" {{ $interview->duration_minutes == 60 ? 'selected' : '' }}>60 minutes</option>
                                            <option value="90" {{ $interview->duration_minutes == 90 ? 'selected' : '' }}>90 minutes</option>
                                            <option value="120" {{ $interview->duration_minutes == 120 ? 'selected' : '' }}>120 minutes</option>
                                        </select>
                                        @error('duration_minutes')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="type">{{ __trans('interview_type') }}</label>
                                        <select name="type" id="type" class="form-control">
                                            <option value="phone" {{ $interview->type == 'phone' ? 'selected' : '' }}>{{ __trans('phone') }}</option>
                                            <option value="video" {{ $interview->type == 'video' ? 'selected' : '' }}>{{ __trans('video_call') }}</option>
                                            <option value="in_person" {{ $interview->type == 'in_person' ? 'selected' : '' }}>{{ __trans('in_person') }}</option>
                                        </select>
                                        @error('type')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status">{{ __trans('status') }}</label>
                                        <select name="status" id="status" class="form-control">
                                            <option value="scheduled" {{ $interview->status == 'scheduled' ? 'selected' : '' }}>{{ __trans('scheduled') }}</option>
                                            <option value="completed" {{ $interview->status == 'completed' ? 'selected' : '' }}>{{ __trans('completed') }}</option>
                                            <option value="cancelled" {{ $interview->status == 'cancelled' ? 'selected' : '' }}>{{ __trans('cancelled') }}</option>
                                            <option value="rescheduled" {{ $interview->status == 'rescheduled' ? 'selected' : '' }}>{{ __trans('rescheduled') }}</option>
                                        </select>
                                        @error('status')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="location">{{ __trans('location') }}</label>
                                        <input type="text" name="location" id="location" class="form-control" 
                                               value="{{ $interview->location }}" placeholder="{{ __trans('enter_location') }}">
                                        @error('location')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="meeting_link">{{ __trans('meeting_link') }}</label>
                                        <input type="url" name="meeting_link" id="meeting_link" class="form-control" 
                                               value="{{ $interview->meeting_link }}" placeholder="{{ __trans('enter_meeting_link') }}">
                                        @error('meeting_link')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="agenda">{{ __trans('agenda') }}</label>
                                        <textarea name="agenda" id="agenda" class="form-control" rows="3" 
                                                  placeholder="{{ __trans('enter_agenda') }}">{{ $interview->agenda }}</textarea>
                                        @error('agenda')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="preparation_notes">{{ __trans('preparation_notes') }}</label>
                                        <textarea name="preparation_notes" id="preparation_notes" class="form-control" rows="3" 
                                                  placeholder="{{ __trans('enter_preparation_notes') }}">{{ $interview->preparation_notes }}</textarea>
                                        @error('preparation_notes')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group text-right">
                                        <a href="{{ route('recruitment.interviews.show', $interview->id) }}" class="btn btn-outline-secondary mr-2">
                                            {{ __trans('cancel') }}
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-save mr-1"></i> {{ __trans('update_interview') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ __trans('current_details') }}</h4>
                    </div>
                    <div class="card-body">
                        <p><strong>{{ __trans('candidate') }}:</strong><br>
                        {{ $interview->application->user->name ?? $interview->application->candidate_name ?? 'Unknown' }}</p>
                        
                        <p><strong>{{ __trans('job_title') }}:</strong><br>
                        {{ optional($interview->application)->job->title ?? 'N/A' }}</p>
                        
                        <p><strong>{{ __trans('current_status') }}:</strong><br>
                        @php
                            $statusClass = ['scheduled' => 'badge-warning', 'completed' => 'badge-success', 'cancelled' => 'badge-danger'][$interview->status] ?? 'badge-secondary';
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ ucfirst($interview->status) }}</span></p>
                        
                        <p><strong>{{ __trans('created_at') }}:</strong><br>
                        {{ $interview->created_at->format('M d, Y H:i') }}</p>
                        
                        <p><strong>{{ __trans('last_updated') }}:</strong><br>
                        {{ $interview->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        placeholder: 'Select...',
        allowClear: true
    });
});
</script>
@endsection