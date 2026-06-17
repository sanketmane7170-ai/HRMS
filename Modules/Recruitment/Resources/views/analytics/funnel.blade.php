@extends('layouts.backend')

@section('content')
<div class="page-wrapper bg-white">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header border-0 pb-0 mb-5">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="page-title text-dark font-weight-bold mb-1">{{ __trans('recruitment_funnel') }}</h4>
                    <p class="text-muted small mb-0">{{ __trans('visualize_your_hiring_pipeline_and_conversion_rates') }}</p>
                </div>
                <div class="col-auto">
                    <div class="d-flex align-items-center gap-3">
                         <a href="{{ route('recruitment.analytics.index') }}" class="btn btn-sm btn-light px-3 fw-bold shadow-none" style="border-radius: 8px;">
                            <i class="fas fa-chart-line me-2"></i> {{ __trans('insights_dashboard') }}
                        </a>
                        <button type="button" class="btn btn-sm btn-dark px-3 fw-bold shadow-none" style="border-radius: 8px;">
                            <i class="fas fa-print me-2"></i> {{ __trans('print_report') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <!-- Pipeline Visualization -->
            <div class="col-lg-8">
                <div class="card border border-light shadow-none mb-4" style="border-radius: 16px;">
                    <div class="card-header bg-transparent border-0 py-4 px-4">
                        <h6 class="m-0 fw-black text-dark text-uppercase ls-1">{{ __trans('conversion_pipeline') }}</h6>
                    </div>
                    <div class="card-body px-4 pb-5">
                        <div class="funnel-container pt-4">
                            @php
                                $funnel = $metrics['funnel']['funnel_data'] ?? [];
                                $maxCount = collect($funnel)->max('count') ?: 1;
                                $prevCount = null;
                            @endphp

                            @foreach($funnel as $stageName => $stage)
                                @php
                                    $width = ($stage['count'] / $maxCount) * 100;
                                    $dropRate = $prevCount ? round((1 - ($stage['count'] / $prevCount)) * 100, 1) : 0;
                                    $prevCountForNext = $stage['count'];
                                @endphp
                                
                                <!-- Step Connector -->
                                @if(!$loop->first)
                                <div class="step-connector d-flex flex-column align-items-center py-2">
                                    <div class="line bg-light shadow-none" style="width: 2px; height: 30px;"></div>
                                    @if($dropRate > 0)
                                    <div class="drop-off-label my-1">
                                        <span class="badge bg-danger-subtle text-danger border-0 px-3 py-2 fw-bold" style="border-radius: 20px; font-size: 10px;">
                                            <i class="fas fa-arrow-down me-1"></i> {{ $dropRate }}% {{ __trans('drop_off') }}
                                        </span>
                                    </div>
                                    @endif
                                    <div class="line bg-light shadow-none" style="width: 2px; height: 30px;"></div>
                                </div>
                                @endif

                                <div class="funnel-step-enhanced">
                                    <div class="card border-0 bg-light p-4 shadow-none" style="border-radius: 12px;">
                                        <div class="row align-items-center">
                                            <div class="col-md-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="step-number bg-dark text-white fw-black rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px;">
                                                        {{ $loop->iteration }}
                                                    </div>
                                                    <div>
                                                        <span class="text-muted smaller fw-bold text-uppercase ls-1 d-block mb-1">{{ __trans('stage') }}</span>
                                                        <h6 class="m-0 fw-black text-dark tracking-tight">{{ ucfirst(str_replace('_', ' ', $stageName)) }}</h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="px-2">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="smaller fw-bold text-muted">{{ __trans('volume') }}</span>
                                                        <span class="smaller fw-black text-dark">{{ $stage['count'] }} {{ __trans('candidates') }}</span>
                                                    </div>
                                                    <div class="progress" style="height: 8px; border-radius: 4px; background-color: #e5e7eb;">
                                                        <div class="progress-bar bg-dark" style="width: {{ $width }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 text-end">
                                                <span class="text-muted smaller fw-bold text-uppercase ls-1 d-block mb-1">{{ __trans('retention') }}</span>
                                                <h5 class="m-0 fw-black text-dark">{{ $stage['percentage'] }}%</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @php $prevCount = $stage['count']; @endphp
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conversion Insights -->
            <div class="col-lg-4">
                <div class="sticky-top" style="top: 100px;">
                    <div class="card border border-light shadow-none mb-4" style="border-radius: 16px; background-color: #111827;">
                        <div class="card-body p-4 text-white">
                            <h6 class="m-0 fw-black text-muted text-uppercase ls-1 mb-4">{{ __trans('funnel_efficiency') }}</h6>
                            
                            @php
                                $conversionRates = $metrics['funnel']['conversion_rates'] ?? [];
                            @endphp

                            @foreach($conversionRates as $stage => $rate)
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="smaller fw-bold text-uppercase opacity-75 ls-1">{{ ucfirst(str_replace('_', ' ', $stage)) }} {{ __trans('rate') }}</span>
                                        <span class="smaller fw-black">{{ $rate }}%</span>
                                    </div>
                                    <div class="progress" style="height: 6px; border-radius: 3px; background-color: rgba(255,255,255,0.1);">
                                        <div class="progress-bar bg-white" role="progressbar" style="width: {{ $rate }}%;"></div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="mt-5 pt-4 border-top border-secondary border-opacity-25 text-center">
                                <span class="smaller fw-bold text-uppercase opacity-75 ls-1 d-block mb-2">{{ __trans('overall_success_rate') }}</span>
                                <h1 class="m-0 fw-black tracking-tight">{{ $metrics['funnel']['funnel_data']['hired']['percentage'] ?? 0 }}%</h1>
                                <p class="smaller opacity-50 mt-3">{{ __trans('from_application_to_final_hire') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="card border border-light shadow-none" style="border-radius: 16px;">
                        <div class="card-body p-4">
                            <h6 class="m-0 fw-black text-dark text-uppercase ls-1 mb-4">{{ __trans('strategic_insights') }}</h6>
                            
                            <div class="d-flex mb-4">
                                <div class="avatar avatar-sm bg-light text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; flex-shrink: 0;">
                                    <i class="fas fa-bolt smaller"></i>
                                </div>
                                <div>
                                    <h6 class="small fw-bold text-dark mb-1">{{ __trans('candidate_velocity') }}</h6>
                                    <p class="smaller text-muted mb-0 lh-base">{{ __trans('screening_is_taking_longer_than_average') }}</p>
                                </div>
                            </div>

                            <div class="d-flex">
                                <div class="avatar avatar-sm bg-light text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; flex-shrink: 0;">
                                    <i class="fas fa-chart-line smaller"></i>
                                </div>
                                <div>
                                    <h6 class="small fw-bold text-dark mb-1">{{ __trans('quality_of_hire') }}</h6>
                                    <p class="smaller text-muted mb-0 lh-base">{{ __trans('higher_conversion_from_referrals') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
    
    .page-wrapper.bg-white { 
        background-color: #fff !important; 
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .fw-black { font-weight: 800; }
    .tracking-tight { letter-spacing: -0.025em; }
    .ls-1 { letter-spacing: 0.05em; }
    .smaller { font-size: 0.75rem; }
    .text-dark { color: #111827 !important; }
    .text-muted { color: #6b7280 !important; }
    .border-light { border-color: #f3f4f6 !important; }
    .bg-light { background-color: #f9fafb !important; }
    .bg-danger-subtle { background-color: rgba(239, 68, 68, 0.08) !important; }
    .text-danger { color: #ef4444 !important; }
    .lh-base { line-height: 1.5; }
</style>
@endsection
