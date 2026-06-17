@extends('layouts.backend')

@section('title', 'Job Offer Details')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Job Offer Details</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.employee.offers.index') }}">My Offers</a></li>
                        <li class="breadcrumb-item active">Offer #{{ $offer->id }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('backend.employee.offers.index') }}" ...>
                        <i class="fas fa-arrow-left"></i> Back to My Offers
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <!-- Offer Details -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">
                                {{ $offer->application->job->title ?? 'Job Offer' }}
                            </h4>
                            @php
                                $statusClass = [
                                    'pending' => $offer->is_sent ? 'badge-info' : 'badge-warning',
                                    'accepted' => 'badge-success',
                                    'declined' => 'badge-danger'
                                ][$offer->status] ?? 'badge-secondary';
                                
                                $statusText = [
                                    'pending' => $offer->is_sent ? 'Awaiting Your Response' : 'Pending',
                                    'accepted' => 'Accepted',
                                    'declined' => 'Declined'
                                ][$offer->status] ?? ucfirst($offer->status);
                            @endphp
                            <span class="badge {{ $statusClass }} badge-lg">{{ $statusText }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Offer Summary -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <h6 class="info-label">Position</h6>
                                    <p class="info-value">{{ $offer->application->job->title ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <h6 class="info-label">Department</h6>
                                    <p class="info-value">{{ $offer->application->job->department ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Compensation Details -->
                        <div class="compensation-section mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-dollar-sign text-success"></i> Compensation Package
                            </h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="compensation-card">
                                        <div class="comp-icon">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <div class="comp-details">
                                            <h6>Base Salary</h6>
                                            <p class="comp-amount">${{ number_format($offer->salary) }}</p>
                                            @if($offer->payment_period)
                                                <small class="text-muted">per {{ ucfirst($offer->payment_period) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if($offer->currency && $offer->currency !== 'USD')
                                <div class="col-md-4">
                                    <div class="compensation-card">
                                        <div class="comp-icon">
                                            <i class="fas fa-coins"></i>
                                        </div>
                                        <div class="comp-details">
                                            <h6>Currency</h6>
                                            <p class="comp-amount">{{ $offer->currency }}</p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if($offer->pay_frequency)
                                <div class="col-md-4">
                                    <div class="compensation-card">
                                        <div class="comp-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div class="comp-details">
                                            <h6>Pay Frequency</h6>
                                            <p class="comp-amount">{{ ucfirst($offer->pay_frequency) }}</p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Benefits -->
                        @if($offer->benefits)
                        <div class="benefits-section mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-heart text-primary"></i> Benefits & Perks
                            </h5>
                            <div class="benefits-content">
                                {!! nl2br(e($offer->benefits)) !!}
                            </div>
                        </div>
                        @endif

                        <!-- Important Dates -->
                        <div class="dates-section mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-calendar text-info"></i> Important Dates
                            </h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="date-card">
                                        <div class="date-label">Offer Date</div>
                                        <div class="date-value">
                                            {{ $offer->offer_date ? $offer->offer_date->format('M d, Y') : 'Not Set' }}
                                        </div>
                                    </div>
                                </div>
                                @if($offer->sent_at)
                                <div class="col-md-4">
                                    <div class="date-card">
                                        <div class="date-label">Sent Date</div>
                                        <div class="date-value">
                                            {{ $offer->sent_at->format('M d, Y g:i A') }}
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if($offer->response_deadline)
                                <div class="col-md-4">
                                    <div class="date-card">
                                        <div class="date-label">Response Deadline</div>
                                        <div class="date-value text-{{ $offer->deadline_color ?? 'muted' }}">
                                            {{ \Carbon\Carbon::parse($offer->response_deadline)->format('M d, Y') }}
                                            @if($offer->is_expired)
                                                <br><small class="text-danger">(Expired)</small>
                                            @elseif($offer->days_remaining !== null)
                                                <br><small>({{ abs($offer->days_remaining) }} day{{ abs($offer->days_remaining) != 1 ? 's' : '' }} {{ $offer->days_remaining >= 0 ? 'left' : 'overdue' }})</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            
                            @if($offer->joining_date || $offer->start_date)
                            <div class="row mt-3">
                                @if($offer->joining_date)
                                <div class="col-md-6">
                                    <div class="date-card">
                                        <div class="date-label">Joining Date</div>
                                        <div class="date-value">
                                            {{ \Carbon\Carbon::parse($offer->joining_date)->format('M d, Y') }}
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if($offer->start_date)
                                <div class="col-md-6">
                                    <div class="date-card">
                                        <div class="date-label">Start Date</div>
                                        <div class="date-value">
                                            {{ \Carbon\Carbon::parse($offer->start_date)->format('M d, Y') }}
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>

                        <!-- Terms & Conditions -->
                        @if($offer->terms_conditions)
                        <div class="terms-section mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-file-contract text-secondary"></i> Terms & Conditions
                            </h5>
                            <div class="terms-content">
                                {!! nl2br(e($offer->terms_conditions)) !!}
                            </div>
                        </div>
                        @endif

                        <!-- Additional Notes -->
                        @if($offer->notes)
                        <div class="notes-section mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-sticky-note text-warning"></i> Additional Notes
                            </h5>
                            <div class="notes-content">
                                {!! nl2br(e($offer->notes)) !!}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Panel -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Actions</h4>
                    </div>
                    <div class="card-body">
                        <!-- Response Deadline Alert -->
                        @if($offer->response_deadline && $offer->status === 'pending')
                        <div class="alert alert-{{ $offer->deadline_color ?? 'info' }} mb-3">
                            <i class="fas fa-clock"></i>
                            <strong>Response Deadline:</strong><br>
                            {{ \Carbon\Carbon::parse($offer->response_deadline)->format('M d, Y') }}
                            @if($offer->is_expired)
                                <br><small>This offer has expired</small>
                            @elseif($offer->days_remaining !== null)
                                <br><small>{{ abs($offer->days_remaining) }} day{{ abs($offer->days_remaining) != 1 ? 's' : '' }} {{ $offer->days_remaining >= 0 ? 'remaining' : 'overdue' }}</small>
                            @endif
                        </div>
                        @endif

                        <!-- Download Offer Letter -->
                        @if($offer->offer_letter_url)
                        <div class="action-item mb-3">
                            <a href="{{ route('backend.employee.offers.download', $offer->id) }}" ...>
                               class="btn btn-primary btn-block">
                                <i class="fas fa-download"></i> Download Offer Letter
                            </a>
                            <small class="text-muted d-block mt-1">
                                Download the complete offer letter in PDF format
                            </small>
                        </div>
                        @endif

                        <!-- Response Actions -->
                        @if($offer->status === 'pending' && $offer->is_sent && (!$offer->response_deadline || !$offer->is_expired))
                        <div class="response-actions">
                            <h6 class="mb-3">Respond to Offer</h6>
                            
                            <div class="action-item mb-3">
                                <button class="btn btn-success btn-block accept-offer" 
                                        data-id="{{ $offer->id }}">
                                    <i class="fas fa-check"></i> Accept Offer
                                </button>
                                <small class="text-muted d-block mt-1">
                                    Accept this job offer and proceed with onboarding
                                </small>
                            </div>
                            
                            <div class="action-item mb-3">
                                <button class="btn btn-danger btn-block decline-offer" 
                                        data-id="{{ $offer->id }}">
                                    <i class="fas fa-times"></i> Decline Offer
                                </button>
                                <small class="text-muted d-block mt-1">
                                    Politely decline this job offer
                                </small>
                            </div>
                        </div>
                        @elseif($offer->status === 'accepted')
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Offer Accepted!</strong><br>
                            @if($offer->responded_at)
                                Accepted on {{ $offer->responded_at->format('M d, Y g:i A') }}
                            @endif
                        </div>
                        @elseif($offer->status === 'declined')
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i>
                            <strong>Offer Declined</strong><br>
                            @if($offer->responded_at)
                                Declined on {{ $offer->responded_at->format('M d, Y g:i A') }}
                            @endif
                        </div>
                        @elseif($offer->is_expired)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Offer Expired</strong><br>
                            The response deadline has passed.
                        </div>
                        @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Offer Pending</strong><br>
                            This offer has not been sent yet.
                        </div>
                        @endif

                        <!-- Contact Information -->
                        <div class="contact-section mt-4">
                            <h6>Need Help?</h6>
                            <p class="text-muted small">
                                If you have questions about this offer, please contact our HR team.
                            </p>
                            <a href="mailto:hr@company.com" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-envelope"></i> Contact HR
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Accept Offer Modal -->
<div class="modal fade" id="acceptOfferModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Accept Job Offer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>Congratulations!</strong> You are about to accept this job offer.
                </div>
                <p>By accepting this offer, you agree to:</p>
                <ul>
                    <li>The salary and compensation package as outlined</li>
                    <li>The terms and conditions specified</li>
                    <li>The joining/start date mentioned</li>
                </ul>
                <p class="text-warning"><strong>Note:</strong> This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmAccept">
                    <i class="fas fa-check"></i> Yes, Accept Offer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Decline Offer Modal -->
<div class="modal fade" id="declineOfferModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Decline Job Offer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="declineOfferForm">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Important:</strong> You are about to decline this job offer.
                    </div>
                    <div class="form-group">
                        <label class="form-label">Reason for declining (Optional)</label>
                        <textarea name="reason" class="form-control" rows="4" 
                                placeholder="Please share your reason for declining this offer. This helps us improve our recruitment process."></textarea>
                    </div>
                    <p class="text-warning small"><strong>Note:</strong> This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Decline Offer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
$(document).ready(function() {
    let currentOfferId = {{ $offer->id }};

    // Accept offer
    $('.accept-offer').on('click', function() {
        $('#acceptOfferModal').modal('show');
    });

    $('#confirmAccept').on('click', function() {
        $.ajax({
            url: `/employee/offers/${currentOfferId}/accept`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#acceptOfferModal').modal('hide');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                toastr.error(response?.message || 'Something went wrong!');
            }
        });
    });

    // Decline offer
    $('.decline-offer').on('click', function() {
        $('#declineOfferModal').modal('show');
    });

    $('#declineOfferForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: `/employee/offers/${currentOfferId}/decline`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                reason: $('textarea[name="reason"]').val()
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#declineOfferModal').modal('hide');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                toastr.error(response?.message || 'Something went wrong!');
            }
        });
    });
});
</script>

<style>
.section-title {
    color: #5a5c69;
    border-bottom: 2px solid #e3e6f0;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.section-title i {
    margin-right: 8px;
}

.info-item {
    margin-bottom: 20px;
}

.info-label {
    font-weight: 600;
    color: #5a5c69;
    margin-bottom: 5px;
}

.info-value {
    font-size: 16px;
    margin-bottom: 0;
}

.compensation-card {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #f8f9fc;
    border-radius: 8px;
    margin-bottom: 15px;
}

.comp-icon {
    width: 50px;
    height: 50px;
    background: #4e73df;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
}

.comp-details h6 {
    margin: 0 0 5px 0;
    color: #5a5c69;
    font-weight: 600;
}

.comp-amount {
    font-size: 18px;
    font-weight: 700;
    color: #5a5c69;
    margin: 0;
}

.date-card {
    background: #f8f9fc;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 15px;
}

.date-label {
    font-size: 12px;
    font-weight: 600;
    color: #858796;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.date-value {
    font-size: 14px;
    font-weight: 600;
    color: #5a5c69;
}

.benefits-content, .terms-content, .notes-content {
    background: #f8f9fc;
    padding: 20px;
    border-radius: 8px;
    line-height: 1.6;
}

.action-item {
    border-bottom: 1px solid #e3e6f0;
    padding-bottom: 15px;
}

.action-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.badge-lg {
    padding: 8px 16px;
    font-size: 14px;
}

@media (max-width: 768px) {
    .compensation-card {
        flex-direction: column;
        text-align: center;
    }
    
    .comp-icon {
        margin-right: 0;
        margin-bottom: 10px;
    }
}
</style>
@endsection