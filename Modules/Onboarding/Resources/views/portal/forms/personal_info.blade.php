@extends('onboarding::portal.layout')

@section('title', 'Personal Information')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Select2 Override */
    .select2-container .select2-selection--single {
        height: 50px !important;
        border: 1px solid #E5E5E5 !important;
        border-radius: var(--radius-input) !important;
        background-color: #F9FAFB !important;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 48px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 1rem;
        color: var(--color-text-main);
        font-size: 1rem;
    }
    
    /* --- Personio Style Overrides for Forms --- */
    :root {
        --color-bg-cream: #FAF9F6;
        --color-text-main: #050505;
        --color-text-sub: #6B7280;
        --radius-pill: 50px;
        --radius-card: 24px;
        --radius-input: 12px;
        --color-accent-orange: #FFC062;
    }

    body {
        background-color: var(--color-bg-cream);
    }

    .form-container {
        max-width: 900px;
        margin: 0 auto;
        padding-top: 3rem;
    }

    /* Premium Card Style */
    .premium-card {
        background: white;
        border-radius: var(--radius-card);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        border: 1px solid #E5E5E5;
        padding: 2.5rem;
        margin-bottom: 2rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .premium-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
    }

    /* Section Headers */
    .form-section-header {
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .section-icon {
        width: 40px;
        height: 40px;
        background-color: var(--color-accent-orange);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--color-text-main);
        font-size: 1.1rem;
    }

    .section-title {
        font-weight: 700;
        color: var(--color-text-main);
        margin: 0;
    }

    /* Inputs */
    .form-label {
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--color-text-sub);
        margin-bottom: 0.5rem;
    }

    .premium-input {
        background-color: #F9FAFB;
        border: 1px solid #E5E5E5;
        border-radius: var(--radius-input);
        padding: 0.75rem 1rem;
        font-size: 1rem;
        color: var(--color-text-main);
        transition: all 0.2s;
        height: auto;
        min-height: 48px;
    }

    .premium-input:focus {
        background-color: white;
        border-color: var(--color-text-main);
        box-shadow: 0 0 0 3px rgba(0,0,0,0.05);
        outline: none;
    }

    .premium-input:disabled {
        background-color: #F3F4F6;
        color: #9CA3AF;
    }

    /* Buttons */
    .btn-personio-black {
        background-color: var(--color-text-main);
        color: white;
        border: none;
        padding: 1rem 3rem;
        border-radius: var(--radius-pill);
        font-weight: 700;
        transition: transform 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-personio-black:hover {
        background-color: #333;
        transform: translateY(-2px);
        color: white;
    }

    .btn-link-subtle {
        color: var(--color-text-sub);
        font-weight: 600;
        text-decoration: none;
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius-pill);
        transition: background-color 0.2s;
    }

    .btn-link-subtle:hover {
        background-color: #E5E7EB;
        color: var(--color-text-main);
    }
</style>
@endsection

@section('content')
<div class="container form-container pb-5">
    
    <!-- Breadcrumb / Header -->
    <div class="d-flex align-items-center justify-content-between mb-5">
        <div>
            <a href="{{ route('portal.dashboard') }}" class="btn-link-subtle px-0 mb-3 d-inline-flex align-items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h2 class="display-section" style="font-size: 2.5rem; margin-bottom: 0;">Personal Records</h2>
        </div>
        <div class="text-end d-none d-md-block">
            <div class="small text-muted font-weight-bold text-uppercase mb-1">Step 01 of 03</div>
            <div class="progress" style="height: 6px; width: 150px; background-color: #E5E5E5; border-radius: 10px;">
                <div class="progress-bar bg-dark" role="progressbar" style="width: 33%;" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>

    <form action="{{ route('portal.save-personal-info') }}" method="POST">
        @csrf
        
        <!-- Profile Section -->
        <div class="form-section-header">
            <div class="section-icon"><i class="far fa-user"></i></div>
            <div class="section-title h4">Identity & Profile</div>
        </div>

        <div class="premium-card">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control premium-input" disabled value="{{ $user->name }}">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Personal Email</label>
                    <input type="email" name="personal_email" class="form-control premium-input" placeholder="Primary contact email" value="{{ $user->profile->personal_email ?? '' }}">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-control premium-input">
                        <option value="">Select Gender</option>
                        @foreach($genders as $gender)
                            <option value="{{ $gender->value }}" {{ (isset($user->profile->gender) && $user->profile->gender == $gender->value) ? 'selected' : '' }}>{{ $gender->value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Marital Status</label>
                    <select name="martial_status" class="form-control premium-input">
                        <option value="">Select Status</option>
                        @foreach($maritalStatuses as $status)
                            <option value="{{ $status->value }}" {{ (isset($user->profile->martial_status) && $user->profile->martial_status->value == $status->value) ? 'selected' : '' }}>{{ $status->value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Nationality</label>
                    <select name="country_id" class="form-control premium-input select2">
                        <option value="">Select Country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" {{ (isset($user->profile->country_id) && $user->profile->country_id == $country->id) ? 'selected' : '' }}>{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control premium-input" value="{{ $user->profile->date_of_birth ? $user->profile->date_of_birth->format('Y-m-d') : '' }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Residential Address</label>
                    <textarea name="address" class="form-control premium-input" rows="3" placeholder="Enter full residential address">{{ $user->profile->address ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <!-- Bank Details -->
        <div class="form-section-header mt-5">
            <div class="section-icon"><i class="fas fa-university"></i></div>
            <div class="section-title h4">Financial Payout Profile</div>
        </div>

        <div class="premium-card">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" class="form-control premium-input" placeholder="Legal Bank Name" value="{{ $user->bankDetail->bank_name ?? '' }}">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Account Number</label>
                    <input type="text" name="account_number" class="form-control premium-input" placeholder="Primary Account #" value="{{ $user->bankDetail->account_number ?? '' }}">
                </div>
                <div class="col-md-12 mb-4">
                    <label class="form-label">IBAN Number</label>
                    <input type="text" name="iba_number" class="form-control premium-input" placeholder="International Bank Account Number (IBAN)" value="{{ $user->bankDetail->iba_number ?? '' }}">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">SWIFT / BIC Code</label>
                    <input type="text" name="swift_code" class="form-control premium-input" placeholder="Bank SWIFT Code" value="{{ $user->bankDetail->swift_code ?? '' }}">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Routing Number</label>
                    <input type="text" name="routing_number" class="form-control premium-input" placeholder="Sort Code / Routing #" value="{{ $user->bankDetail->routing_number ?? '' }}">
                </div>
            </div>
        </div>

        <!-- Emergency Contact -->
        <div class="form-section-header mt-5">
            <div class="section-icon"><i class="fas fa-heartbeat"></i></div>
            <div class="section-title h4">Emergency Communication</div>
        </div>

        <div class="premium-card">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">Contact Name</label>
                    <input type="text" name="emergency_name" class="form-control premium-input" placeholder="Full Name" value="{{ $user->emergencyContacts->emergency_name ?? '' }}">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Relationship</label>
                    <input type="text" name="emergency_relation" class="form-control premium-input" placeholder="e.g. Spouse, Parent" value="{{ $user->emergencyContacts->emergency_relation ?? '' }}">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="emergency_phone" class="form-control premium-input" placeholder="+971 50 XXXXXXX" value="{{ $user->emergencyContacts->emergency_phone ?? '' }}">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="emergency_email" class="form-control premium-input" placeholder="emergency@example.com" value="{{ $user->emergencyContacts->emergency_email ?? '' }}">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Home Country Address</label>
                    <textarea name="emergency_home_address" class="form-control premium-input" rows="3" placeholder="Full address in home country">{{ $user->emergencyContacts->emergency_home_address ?? '' }}</textarea>
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Local Residency Address</label>
                    <textarea name="emergency_local_address" class="form-control premium-input" rows="3" placeholder="Current local residency address">{{ $user->emergencyContacts->emergency_local_address ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mt-5 mb-5">
            <a href="{{ route('portal.dashboard') }}" class="btn-link-subtle">
                Cancel Changes
            </a>
            <button type="submit" class="btn btn-personio-black">
                Save and Proceed <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            placeholder: "Select an option",
            allowClear: true
        });
    });
</script>
@endsection
