@extends('layouts.backend')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('Performance Review Details') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('performancereview.index') }}">{{ __trans('review_list') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __trans('review_details') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="card light">
                <div class="card-body">
                    <div class="container my-4">

                        {{-- EMPLOYEES --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Employees</label>
                            <input type="text" class="form-control" readonly
                                value="{{ $review->employees->pluck('name')->join(', ') }}">
                        </div>

                        {{-- SCORE GRADE --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Score Grade</label>
                            <input type="text" class="form-control" readonly
                                value="{{ $review->scoreCriteria->title ?? '-' }}">
                        </div>

                        {{-- GRADE DESCRIPTION --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Grade Description</label>
                            <textarea class="form-control" rows="2" readonly>{{ $review->scoreCriteria->description ?? '-' }}</textarea>
                        </div>

                        

                        {{-- QUESTION SET --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Question Set</label>
                            <input type="text" class="form-control" readonly
                                value="{{ $review->questionSet->name ?? '-' }}">
                        </div>

                        {{-- STATUS --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <input type="text" class="form-control" readonly value="{{ $review->status }}">
                        </div>

                        {{-- START DATE --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Start Date</label>
                            <input type="text" class="form-control" readonly value="{{ $review->start_date }}">
                        </div>

                        {{-- SCORE --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Score</label>
                            <input type="text" class="form-control" readonly value="{{ $review->score ?? '-' }}">
                        </div>

                        {{-- SUBMITTED AT --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Submitted At</label>
                            <input type="text" class="form-control" readonly value="{{ $review->submitted_at ?? '-' }}">
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
