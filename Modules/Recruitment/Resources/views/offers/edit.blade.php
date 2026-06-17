@extends('layouts.backend')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Edit Offer</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('recruitment.offers.index') }}">Offers</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('recruitment.offers.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Back to Offers
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <p class="text-center text-muted">
                            <i class="fas fa-info-circle me-2"></i>
                            This will redirect you to the Offer Letter Generator with pre-filled data for editing.
                        </p>
                        <div class="text-center">
                            <a href="{{ route('recruitment.offer-letters.create', ['application_id' => $offer->application_id, 'offer_id' => $offer->id]) }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-edit me-2"></i>
                                Edit Offer Letter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-redirect to offer letter editor
setTimeout(function() {
    window.location.href = "{{ route('recruitment.offer-letters.create', ['application_id' => $offer->application_id, 'offer_id' => $offer->id]) }}";
}, 2000);
</script>

@endsection