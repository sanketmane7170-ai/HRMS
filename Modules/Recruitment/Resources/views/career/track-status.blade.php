<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status - {{ config('app.name') }} Careers</title>
    <link rel="shortcut icon" href="{{ getSmallLogo() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e3a8a;
            --success-color: #059669;
            --danger-color: #dc2626;
        }
        .hero-section { 
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 50%, #3b82f6 100%); 
            color: white; 
            padding: 60px 0; 
        }
        .status-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 2.5rem;
            margin-top: -40px;
            position: relative;
            z-index: 10;
        }
        .timeline {
            position: relative;
            padding: 20px 0;
        }
        .timeline-item {
            position: relative;
            padding-left: 40px;
            margin-bottom: 25px;
        }
        .timeline-marker {
            position: absolute;
            left: 0;
            top: 5px;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: #e2e8f0;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #e2e8f0;
        }
        .timeline-item.active .timeline-marker {
            background: var(--success-color);
            box-shadow: 0 0 0 2px var(--success-color);
        }
        .timeline-item:not(:last-child)::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 20px;
            width: 2px;
            height: calc(100% + 5px);
            background-color: #e2e8f0;
        }
        .detail-group {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1.5rem;
        }
        .offer-section {
            border: 2px solid var(--success-color);
            background: #f0fdf4;
            border-radius: 12px;
            padding: 2rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('career.index') }}">{{ config('app.name') }} Careers</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('career.index') }}">Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('career.track-application') }}">Track Application</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section text-center">
        <div class="container">
            <h1 class="fw-bold">Application Status</h1>
            <p class="lead">Tracking ID: #{{ $application->id }}</p>
        </div>
    </section>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="status-card">
                    <div class="row">
                        <div class="col-md-7">
                            <h4 class="mb-4">Application Progress</h4>
                            <div class="timeline">
                                @php
                                    $allStages = [
                                        'applied' => 'Applied',
                                        'screening' => 'Screening',
                                        'shortlisted' => 'Shortlisted',
                                        'interview' => 'Interviewing',
                                        'offer' => 'Offer Extended',
                                        'offer_accepted' => 'Offer Accepted',
                                        'offer_declined' => 'Offer Declined',
                                        'hired' => 'Hired',
                                        'rejected' => 'Rejected',
                                        'withdrawn' => 'Withdrawn'
                                    ];
                                    
                                    $linearStages = ['applied', 'screening', 'shortlisted', 'interview', 'offer', 'hired'];
                                    $currentStage = $application->stage;
                                    
                                    // Determine the progress level within linear stages
                                    // If rejected/withdrawn, we check logs to see where it stopped (Sanket)
                                    $progressStage = $currentStage;
                                    if (in_array($currentStage, ['rejected', 'withdrawn', 'offer_declined'])) {
                                        $lastLog = $application->logs()
                                            ->where('action', 'stage_changed')
                                            ->whereNotIn('new_stage', ['rejected', 'withdrawn', 'offer_declined'])
                                            ->orderBy('created_at', 'desc')
                                            ->first();
                                        $progressStage = $lastLog ? $lastLog->new_stage : 'applied';
                                    }
                                    
                                    $progressIndex = array_search($progressStage, $linearStages);
                                    if ($progressIndex === false) $progressIndex = 0;
                                    
                                    // Build the timeline to show
                                    $timelineStages = array_slice($linearStages, 0, max($progressIndex + 1, 1));
                                    
                                    // Add the terminal stage if it's not already there
                                    if (in_array($currentStage, ['rejected', 'withdrawn', 'offer_declined'])) {
                                        $timelineStages[] = $currentStage;
                                    } elseif ($currentStage == 'offer_accepted') {
                                        $timelineStages[] = 'offer_accepted';
                                    }
                                    
                                    $currentStageIndex = count($timelineStages) - 1;
                                @endphp
                                
                                @foreach($timelineStages as $index => $stage)
                                    <div class="timeline-item {{ $index <= $currentStageIndex ? 'active' : '' }} {{ $application->stage == $stage ? 'current' : '' }}">
                                        <div class="timeline-marker"></div>
                                        <h6 class="mb-1">{{ $allStages[$stage] ?? ucfirst(str_replace('_', ' ', $stage)) }}</h6>
                                        <p class="text-muted small">
                                            @if($index == $currentStageIndex)
                                                Current Status
                                            @elseif($index < $currentStageIndex)
                                                Completed
                                            @else
                                                Pending
                                            @endif
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="detail-group">
                                <h5 class="mb-3">Position Details</h5>
                                <p class="mb-1"><strong>Job Title:</strong><br>{{ $application->job->title }}</p>
                                <p class="mb-1"><strong>Department:</strong><br>{{ $application->job->department->name ?? 'N/A' }}</p>
                                <p class="mb-0"><strong>Applied On:</strong><br>{{ $application->applied_at ? $application->applied_at->format('M d, Y') : ($application->created_at ? $application->created_at->format('M d, Y') : 'N/A') }}</p>
                            </div>
                        </div>
                    </div>

                    @if($application->stage === 'offer')
                        @php $offer = $application->offers()->latest()->first(); @endphp
                        @if($offer && $offer->status === 'sent')
                        <div class="offer-section mt-5">
                            <div class="text-center mb-4">
                                <h3 class="text-success"><i class="fas fa-gift me-2"></i>Congratulations! You have an offer.</h3>
                                <p>We've reviewed your interviews and would love to have you on our team.</p>
                            </div>
                            
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-2"><strong>Position:</strong> {{ $offer->position }}</p>
                                    <p class="mb-2"><strong>Base Salary:</strong> {{ $offer->currency }} {{ number_format($offer->salary) }} ({{ $offer->payment_period }})</p>
                                    <p class="mb-0"><strong>Expected Joining:</strong> {{ $offer->joining_date ? $offer->joining_date->format('M d, Y') : 'TBD' }}</p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-success btn-lg" onclick="respondOffer('accept')">
                                            <i class="fas fa-check me-2"></i>Accept Offer
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="respondOffer('decline')">
                                            <i class="fas fa-times me-2"></i>Decline Offer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function respondOffer(action) {
            const message = action === 'accept' ? 'Are you sure you want to ACCEPT this offer?' : 'Are you sure you want to DECLINE this offer?';
            if (confirm(message)) {
                @if(isset($offer))
                const offerId = {{ $offer->id }};
                $.ajax({
                    url: `/recruitment/offers/${offerId}/${action}`,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            alert('Response recorded successfully!');
                            location.reload();
                        } else {
                            alert(response.message || 'Action failed');
                        }
                    },
                    error: function() {
                        alert('Error communicating with server');
                    }
                });
                @endif
            }
        }
    </script>
</body>
</html>
