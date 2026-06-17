@extends('layouts.backend')

@section('title', 'My Job Offers')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">My Job Offers</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">My Offers</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Offer Statistics -->
        <div class="row">
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-primary border-primary">
                                <i class="fas fa-file-contract"></i>
                            </span>
                            <div class="dash-count">
                                <h3>{{ $totalOffers }}</h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">Total Offers</h6>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-warning border-warning">
                                <i class="fas fa-clock"></i>
                            </span>
                            <div class="dash-count">
                                <h3>{{ $pendingOffers }}</h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">Pending Response</h6>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-success border-success">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            <div class="dash-count">
                                <h3>{{ $acceptedOffers }}</h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">Accepted</h6>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-danger border-danger">
                                <i class="fas fa-times-circle"></i>
                            </span>
                            <div class="dash-count">
                                <h3>{{ $declinedOffers }}</h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">Declined</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Offers List -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-header">
                        <h4 class="card-title">My Job Offers</h4>
                    </div>
                    <div class="card-body">
                        @if($offers->count() > 0)
                            <div class="row">
                                @foreach($offers as $offer)
                                <div class="col-lg-6 col-xl-4 col-md-6 mb-4">
                                    <div class="card offer-card h-100">
                                        <div class="card-header bg-light">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h5 class="mb-1">{{ $offer->application->job->title ?? 'Job Title' }}</h5>
                                                    <small class="text-muted">Offer #{{ $offer->id }}</small>
                                                </div>
                                                <div class="text-right">
                                                    @php
                                                        $statusClass = [
                                                            'pending' => $offer->is_sent ? 'badge-info' : 'badge-warning',
                                                            'accepted' => 'badge-success',
                                                            'declined' => 'badge-danger'
                                                        ][$offer->status] ?? 'badge-secondary';
                                                        
                                                        $statusText = [
                                                            'pending' => $offer->is_sent ? 'Awaiting Response' : 'Pending',
                                                            'accepted' => 'Accepted',
                                                            'declined' => 'Declined'
                                                        ][$offer->status] ?? ucfirst($offer->status);
                                                    @endphp
                                                    <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="offer-details">
                                                <div class="mb-3">
                                                    <strong>Salary:</strong>
                                                    <span class="text-success">${{ number_format($offer->salary) }}</span>
                                                    @if($offer->payment_period)
                                                        <small class="text-muted">/ {{ ucfirst($offer->payment_period) }}</small>
                                                    @endif
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <strong>Offer Date:</strong>
                                                    <span>{{ $offer->offer_date ? $offer->offer_date->format('M d, Y') : 'Not Set' }}</span>
                                                </div>

                                                @if($offer->response_deadline)
                                                <div class="mb-3">
                                                    <strong>Response Deadline:</strong>
                                                    <br>
                                                    @php
                                                        $deadline = \Carbon\Carbon::parse($offer->response_deadline);
                                                        $isExpired = $deadline->isPast();
                                                        $daysLeft = $isExpired ? 0 : $deadline->diffInDays(now());
                                                        $badgeClass = $isExpired ? 'badge-danger' : ($daysLeft <= 3 ? 'badge-warning' : 'badge-success');
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">
                                                        {{ $deadline->format('M d, Y') }}
                                                        @if($isExpired)
                                                            (Expired)
                                                        @elseif($daysLeft == 0)
                                                            (Today!)
                                                        @elseif($daysLeft == 1)
                                                            (1 day left)
                                                        @else
                                                            ({{ $daysLeft }} days left)
                                                        @endif
                                                    </span>
                                                </div>
                                                @endif

                                                @if($offer->joining_date)
                                                <div class="mb-3">
                                                    <strong>Joining Date:</strong>
                                                    <span>{{ \Carbon\Carbon::parse($offer->joining_date)->format('M d, Y') }}</span>
                                                </div>
                                                @endif

                                                @if($offer->start_date)
                                                <div class="mb-3">
                                                    <strong>Start Date:</strong>
                                                    <span>{{ \Carbon\Carbon::parse($offer->start_date)->format('M d, Y') }}</span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="card-footer bg-light">
                                            <div class="btn-group w-100" role="group">
                                                <a href="{{ route('backend.employee.offers.show', $offer->id) }}" ...>
                                                   class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i> View Details
                                                </a>
                                                
                                                @if($offer->offer_letter_url)
                                                <a href="{{ route('backend.employee.offers.download', $offer->id) }}" ...>
                                                   class="btn btn-secondary btn-sm">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                                @endif
                                                
                                                @if($offer->status === 'pending' && $offer->is_sent && (!$offer->response_deadline || !$offer->is_expired))
                                                <button class="btn btn-success btn-sm accept-offer" 
                                                        data-id="{{ $offer->id }}">
                                                    <i class="fas fa-check"></i> Accept
                                                </button>
                                                <button class="btn btn-danger btn-sm decline-offer" 
                                                        data-id="{{ $offer->id }}">
                                                    <i class="fas fa-times"></i> Decline
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="fas fa-file-contract fa-4x text-muted"></i>
                                </div>
                                <h4>No Job Offers Yet</h4>
                                <p class="text-muted">You don't have any job offers at the moment. Keep applying to great opportunities!</p>
                            </div>
                        @endif
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
                    This action cannot be undone.
                </div>
                <p>Are you sure you want to accept this job offer?</p>
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
                        This action cannot be undone.
                    </div>
                    <div class="form-group">
                        <label class="form-label">Reason for declining (Optional)</label>
                        <textarea name="reason" class="form-control" rows="3" 
                                placeholder="Please share your reason for declining this offer..."></textarea>
                        <small class="form-text text-muted">
                            Your feedback helps us improve our recruitment process.
                        </small>
                    </div>
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
    let currentOfferId = null;

    // Accept offer
    $(document).on('click', '.accept-offer', function() {
        currentOfferId = $(this).data('id');
        $('#acceptOfferModal').modal('show');
    });

    $('#confirmAccept').on('click', function() {
        if (currentOfferId) {
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
        }
    });

    // Decline offer
    $(document).on('click', '.decline-offer', function() {
        currentOfferId = $(this).data('id');
        $('#declineOfferModal').modal('show');
    });

    $('#declineOfferForm').on('submit', function(e) {
        e.preventDefault();
        if (currentOfferId) {
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
        }
    });
});
</script>

<style>
.offer-card {
    border: 1px solid #e3e6f0;
    transition: all 0.3s ease;
}

.offer-card:hover {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    transform: translateY(-2px);
}

.offer-details strong {
    color: #5a5c69;
}

.btn-group .btn {
    flex: 1;
}

@media (max-width: 576px) {
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 5px;
        border-radius: 4px !important;
    }
}
</style>
@endsection