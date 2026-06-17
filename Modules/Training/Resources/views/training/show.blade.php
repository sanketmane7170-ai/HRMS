@extends('layouts.backend')

@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">

        <h3 class="mb-4">{{ __trans('training_view') }}</h3>

        {{-- Training Info --}}
        <div class="row g-4 align-items-stretch">
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <p><strong>{{ __trans('title') }}:</strong> {{ $training->title }}</p>
                        <p><strong>{{ __trans('department') }}:</strong> {{ $training->department->name }}</p>
                        <p><strong>{{ __trans('created_at') }}:</strong> {{ formatDate($training->created_at) }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <p><strong>{{ __trans('description') }}:</strong></p>
                        <div class="text-justify">
                            {{ $training->description }}
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- {{-- Video List --}}
        <div class="mt-5">
            <h5 class="mb-3">{{ __trans('Training Videos') }}</h5>
            @forelse($training->videos as $video)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>{{ basename($video->video_path) }}</span>
                <a class="btn btn-sm btn-outline-primary" href="{{ Storage::url($video->video_path) }}" target="_blank">
                    <i class="fas fa-play"></i> {{ __trans('View') }}
                </a>
            </li>
            @empty
            <p class="text-muted">{{ __trans('No videos uploaded') }}</p>
            @endforelse
        </div> -->
        {{-- Training Files --}}
        <div class="mt-5">
            <h5 class="mb-3">{{ __trans('Training Files') }}</h5>

            @forelse($training->videos as $file)
            @php
            $extension = strtolower(pathinfo($file->video_path, PATHINFO_EXTENSION));
            $url = Storage::url($file->video_path);
            @endphp

            <div class="card mb-3 shadow-sm">
                <div class="card-body">

                    <strong>{{ basename($file->video_path) }}</strong>

                    <div class="mt-2">

                        {{-- Video Preview --}}
                        @if(in_array($extension, ['mp4','avi','mkv','webm']))
                        <video width="100%" height="300" controls>
                            <source src="{{ $url }}">
                            Your browser does not support the video tag.
                        </video>

                        {{-- Image Preview --}}
                        @elseif(in_array($extension, ['jpg','jpeg','png','gif','webp']))
                        <img src="{{ $url }}" class="img-fluid rounded" style="max-height:300px;">

                        {{-- PDF Preview --}}
                        @elseif($extension == 'pdf')
                        <iframe src="{{ $url }}" width="100%" height="400px"></iframe>

                        {{-- Other Files --}}
                        @else
                        <a href="{{ $url }}" target="_blank" class="btn btn-outline-primary">
                            <i class="fas fa-download"></i> {{ __trans('Download File') }}
                        </a>
                        @endif

                    </div>
                </div>
            </div>

            @empty
            <p class="text-muted">{{ __trans('No files uploaded') }}</p>
            @endforelse
        </div>
        <!-- <div class="mt-4">
            <h5>{{ __trans('Training Videos') }}</h5>
            <div class="row">
                @forelse($training->videos as $video)
                <div class="col-md-6 mb-3">
                    <video width="100%" height="300" controls>
                        <a href="{{ Storage::url($video->video_path) }}" target="_blank">
                            {{ basename($video->video_path) }}
                        </a>

                        Your browser does not support the video tag.
                    </video>
                </div>
                @empty
                <p class="text-muted ms-3">{{ __trans('No videos uploaded') }}</p>
                @endforelse
            </div>
        </div> -->


        {{-- CHAT SYSTEM REMOVED - Sanket --}}
        
    </div>
</div>

    </div>
</div>

@endsection
