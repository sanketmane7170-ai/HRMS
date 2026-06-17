@extends('layouts.backend')
@section('content')

<style>
/* Premium Typography & Global Polish - Sanket */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap');

#job-offers-wrapper {
    font-family: 'Inter', sans-serif !important;
    background: #F8FAFC;
    min-height: 100vh;
}

.offer-title {
    font-family: 'Outfit', sans-serif !important;
    font-size: 1.25rem !important;
    font-weight: 700 !important;
    color: #0F172A !important;
    letter-spacing: -0.02em;
    margin-bottom: 2px;
}

/* Compact Premium Stats Cards */
.stat-card-premium {
    background: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    border-radius: 12px !important;
    padding: 1rem !important;
    height: 100%;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-card-premium:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
}

.stat-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.stat-info {
    flex: 1;
}

.stat-value {
    font-family: 'Outfit', sans-serif !important;
    font-size: 1.25rem !important;
    font-weight: 700 !important;
    color: #0F172A !important;
    line-height: 1;
    margin-bottom: 2px;
}

.stat-label {
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    color: #64748B !important;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

/* Premium Table - High Density */
.job-table-card {
    background: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    border-radius: 12px !important;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05) !important;
    overflow: hidden;
}

.table-premium thead th {
    background: #F8FAFC !important;
    padding: 0.75rem 1rem !important;
    font-size: 0.7rem !important;
    font-weight: 700 !important;
    color: #475569 !important;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid #E2E8F0 !important;
}

.table-premium td {
    padding: 0.75rem 1rem !important;
    font-size: 0.8125rem !important;
    vertical-align: middle !important;
    color: #334155;
    border-bottom: 1px solid #F1F5F9;
}

.table-premium tbody tr {
    transition: all 0.2s ease;
}

.table-premium tbody tr:hover {
    background-color: #F8FAFC !important;
}

/* Action Pills */
.action-btn-pill {
    width: 30px;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.2s;
    background: #F1F5F9;
    color: #64748B;
    border: none;
    text-decoration: none !important;
    margin-left: 4px;
}

.action-btn-pill:hover { transform: translateY(-1px); }
.btn-view:hover { background: #E0F2FE; color: #0369A1; }
.btn-edit:hover { background: #EEF2FF; color: #4338CA; }

/* Enhanced Badges */
.badge-premium {
    padding: 4px 10px !important;
    border-radius: 6px !important;
    font-weight: 600 !important;
    font-size: 0.7rem !important;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.badge-pending { background: #FEF3C7 !important; color: #92400E !important; border: 1px solid #FDE68A !important; }
.badge-sent { background: #E0F2FE !important; color: #0369A1 !important; border: 1px solid #BAE6FD !important; }
.badge-accepted { background: #DCFCE7 !important; color: #15803D !important; border: 1px solid #BBF7D0 !important; }
.badge-declined { background: #FEE2E2 !important; color: #B91C1C !important; border: 1px solid #FECACA !important; }

</style>

<div id="job-offers-wrapper" class="page-wrapper">
    <div class="content container-fluid">
        <!-- Premium Header -->
        <div class="page-header d-flex justify-content-between align-items-center mb-3 pt-3">
            <div>
                <h1 class="offer-title">{{ __trans('job_offers') }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-muted">Admin</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('recruitment.dashboard') }}" class="text-muted">Recruitment</a></li>
                        <li class="breadcrumb-item active fw-600 text-primary">Job Offers</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                @can('recruitment.offers.create')
                <a href="{{ route('recruitment.offer-letters.selection') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 shadow-sm" style="border-radius: 10px; height: 36px; font-weight: 600; font-size: 0.8125rem;">
                    <i class="fas fa-file-pdf"></i> Letter Generator
                </a>
                @endcan
            </div>
        </div>

        <!-- Statistics Cards - Horizontal Layout -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-sm-6">
                <div class="stat-card-premium">
                    <div class="stat-icon-box" style="background: #EEF2FF; color: #6366F1;">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="totalOffers">{{ $statistics['totalOffers'] ?? 0 }}</div>
                        <div class="stat-label">{{ __trans('total_offers') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="stat-card-premium">
                    <div class="stat-icon-box" style="background: #FEF3C7; color: #D97706;">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="sentOffers">{{ $statistics['sentOffers'] ?? 0 }}</div>
                        <div class="stat-label">{{ __trans('sent_offers') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="stat-card-premium">
                    <div class="stat-icon-box" style="background: #DCFCE7; color: #16A34A;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="acceptedOffers">{{ $statistics['acceptedOffers'] ?? 0 }}</div>
                        <div class="stat-label">{{ __trans('accepted_offers') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="stat-card-premium">
                    <div class="stat-icon-box" style="background: #FEE2E2; color: #DC2626;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="declinedOffers">{{ $statistics['declinedOffers'] ?? 0 }}</div>
                        <div class="stat-label">{{ __trans('declined_offers') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="job-table-card shadow-sm border-0">
                    <div class="px-3 py-2 bg-light border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="fw-700 text-slate-700 mb-0" style="font-size: 0.875rem;">{{ __trans('offer_management') }}</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-premium mb-0" id="offersTable">
                            <thead>
                                <tr>
                                    <th width="40">#</th>
                                    <th>{{ __trans('candidate') }}</th>
                                    <th>{{ __trans('job_details') }}</th>
                                    <th>{{ __trans('compensation') }}</th>
                                    <th>{{ __trans('status') }}</th>
                                    <th>Offer Timeline</th>
                                    <th width="100" class="text-end">{{ __trans('actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($offers) && $offers->count() > 0)
                                    @foreach($offers as $index => $offer)
                                    <tr>
                                        <td class="text-muted fw-500">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-700" style="width: 28px; height: 28px; background: #EEF2FF; color: #6366F1; font-size: 0.7rem;">
                                                    {{ strtoupper(substr($offer->application && $offer->application->user ? $offer->application->user->name : ($offer->application->candidate_name ?? 'N'), 0, 1)) }}
                                                </div>
                                                <span class="fw-600 text-slate-900">
                                                    @if($offer->application && $offer->application->user)
                                                        {{ $offer->application->user->name }}
                                                    @elseif($offer->application)
                                                        {{ $offer->application->candidate_name ?? 'N/A' }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-600" style="font-size: 0.875rem;">{{ $offer->application && $offer->application->job ? $offer->application->job->title : 'N/A' }}</div>
                                            <div class="text-muted small d-flex align-items-center gap-1">
                                                <i class="fas fa-building opacity-50" style="font-size: 0.7rem;"></i>
                                                {{ $offer->application && $offer->application->job && $offer->application->job->department ? $offer->application->job->department->name : 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-700 text-slate-800">${{ number_format($offer->salary) }}</span>
                                            <div class="text-muted small">Per Annum</div>
                                        </td>
                                        <td>
                                            @php
                                                $wasSent = \Modules\Recruitment\Entities\ApplicationLog::where('application_id', $offer->application_id)
                                                    ->where('new_stage', 'offer_sent')
                                                    ->where('description', 'like', '%Offer ID: ' . $offer->id . '%')
                                                    ->exists();
                                                
                                                $statusClass = 'badge-pending';
                                                $icon = 'fa-clock';
                                                $statusText = ucfirst($offer->status);
                                                
                                                if ($offer->status === 'accepted') { $statusClass = 'badge-accepted'; $icon = 'fa-check-circle'; }
                                                elseif ($offer->status === 'declined') { $statusClass = 'badge-declined'; $icon = 'fa-times-circle'; }
                                                elseif ($wasSent) { $statusText = 'Sent'; $statusClass = 'badge-sent'; $icon = 'fa-paper-plane'; }
                                            @endphp
                                            <span class="badge badge-premium {{ $statusClass }}">
                                                <i class="fas {{ $icon }} opacity-75"></i>
                                                {{ $statusText }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="small d-flex flex-column gap-1">
                                                <span class="text-muted"><i class="fas fa-calendar-plus opacity-50 me-1"></i> Issued: {{ $offer->offer_date ? $offer->offer_date->format('M d, Y') : 'Not Set' }}</span>
                                                <span class="text-muted"><i class="fas fa-hourglass-end opacity-50 me-1"></i> Exp: {{ $offer->response_deadline ? $offer->response_deadline->format('M d, Y') : 'No Deadline' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end">
                                                <a href="{{ route('recruitment.offers.show', $offer->id) }}" class="action-btn-pill btn-view" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('recruitment.offer-letters.create', ['application_id' => $offer->application_id, 'offer_id' => $offer->id]) }}" class="action-btn-pill btn-edit" title="Edit Offer">
                                                    <i class="fas fa-pencil-alt" style="font-size: 0.85rem;"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
</div>

<!-- Send Offer Modal -->
<div class="modal fade" id="sendOfferModal" tabindex="-1" aria-labelledby="sendOfferModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendOfferModalLabel">{{ __trans('send_offer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ __trans('are_you_sure_send_offer') }}</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    {{ __trans('offer_will_be_sent_to_candidate_email') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __trans('cancel') }}</button>
                <button type="button" id="confirmSendOffer" class="btn btn-success">{{ __trans('send_offer') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Withdraw Offer Modal -->
<div class="modal fade" id="withdrawOfferModal" tabindex="-1" aria-labelledby="withdrawOfferModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="withdrawOfferModalLabel">{{ __trans('withdraw_offer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="withdrawForm">
                <div class="modal-body">
                    <input type="hidden" id="withdrawOfferId">
                    <div class="form-group">
                        <label class="form-label">{{ __trans('reason_for_withdrawal') }} <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Please provide a reason for withdrawing this offer..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __trans('cancel') }}</button>
                    <button type="submit" class="btn btn-warning">{{ __trans('withdraw_offer') }}</button>
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

        // Initialize basic DataTable
        let table = $('#offersTable').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": false,
            "scrollX": true,
            initComplete: function() {
                console.log('DataTable initialized successfully');
            }
        });

        // Load statistics
        loadOfferStatistics();

        // Send offer modal
        $(document).on('click', '.send-offer', function() {
            currentOfferId = $(this).data('id');
            $('#sendOfferModal').modal('show');
        });

        // Confirm send offer
        $('#confirmSendOffer').on('click', function() {
            if (currentOfferId) {
                $.ajax({
                    url: `/recruitment/offers/${currentOfferId}/send`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if(response.success) {
                            toastr.success(response.message);
                            $('#sendOfferModal').modal('hide');
                            table.draw();
                            loadOfferStatistics();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Something went wrong!');
                    }
                });
            }
        });

        // Withdraw offer modal
        $(document).on('click', '.withdraw-offer', function() {
            let offerId = $(this).data('id');
            $('#withdrawOfferId').val(offerId);
            $('#withdrawOfferModal').modal('show');
        });

        // Withdraw offer form submission
        $('#withdrawForm').on('submit', function(e) {
            e.preventDefault();
            let offerId = $('#withdrawOfferId').val();
            
            $.ajax({
                url: `/recruitment/offers/${offerId}/withdraw`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    reason: $('textarea[name="reason"]').val()
                },
                success: function(response) {
                    if(response.success) {
                        toastr.success(response.message);
                        $('#withdrawOfferModal').modal('hide');
                        table.draw();
                        loadOfferStatistics();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Something went wrong!');
                }
            });
        });

        // Delete offer
        $(document).on('click', '.delete-offer', function(e) {
            e.preventDefault();
            let offerId = $(this).data('id');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/recruitment/offers/${offerId}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if(response.success) {
                                toastr.success(response.message);
                                table.draw();
                                loadOfferStatistics();
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function() {
                            toastr.error('Something went wrong!');
                        }
                    });
                }
            });
        });

        // Load offer statistics
        function loadOfferStatistics() {
            $.ajax({
                url: '{{ route("recruitment.offers.statistics") }}',
                type: 'GET',
                success: function(response) {
                    if (response.success && response.data) {
                        let stats = response.data;
                        
                        $('#totalOffers').text(stats.total);
                        $('#sentOffers').text(stats.sent);
                        $('#acceptedOffers').text(stats.accepted);
                        $('#declinedOffers').text(stats.declined);
                    }
                },
                error: function() {
                    // Error loading offer statistics
                    console.error('Failed to load offer statistics');
                }
            });
        }
    });
</script>
@endsection