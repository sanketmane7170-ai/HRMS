@section('title', 'Complete Probation Review | ' . $review->employee->name)

@push('css')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        --focus-primary: #4f46e5;
    }

    .page-wrapper {
        background-color: #f8fafc;
    }

    /* Profile Hero Header */
    .review-hero {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        margin-bottom: 30px;
        border-left: 6px solid #4f46e5;
        display: flex;
        align-items: center;
        gap: 30px;
    }

    .hero-avatar {
        width: 100px;
        height: 100px;
        border-radius: 20px;
        object-fit: cover;
        box-shadow: 0 10px 20px -5px rgba(0,0,0,0.1);
    }

    .hero-info h2 { font-weight: 800; color: #1e293b; margin-bottom: 5px; }
    .hero-info .role { font-size: 14px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }

    .meta-item { display: flex; align-items: center; gap: 8px; font-size: 14px; color: #475569; margin-top: 5px; }
    .meta-item i { color: #4f46e5; width: 16px; }

    /* Assessment Form */
    .assessment-card {
        background: white;
        border-radius: 24px;
        border: none;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .form-section-title {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #94a3b8;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .form-section-title::after { content: ""; flex: 1; height: 1px; background: #f1f5f9; }

    /* Performance Score Bubbles */
    .score-container {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 12px;
    }

    .score-option {
        position: relative;
        text-align: center;
    }

    .score-option input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }

    .score-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px 10px;
        background: #f8fafc;
        border: 2px solid #f1f5f9;
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .score-option input:checked + .score-label {
        background: #eef2ff;
        border-color: #4f46e5;
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.1);
    }

    .score-label .score-num { font-size: 20px; font-weight: 800; color: #64748b; margin-bottom: 4px; }
    .score-label .score-text { font-size: 11px; font-weight: 600; text-transform: uppercase; color: #94a3b8; }

    .score-option input:checked + .score-label .score-num { color: #4f46e5; }
    .score-option input:checked + .score-label .score-text { color: #4f46e5; }

    /* Form Contols */
    .custom-control {
        border: 2px solid #f1f5f9;
        border-radius: 14px;
        padding: 12px 18px;
        font-weight: 500;
        transition: all 0.2s;
        background: #fcfcfd;
    }
    .custom-control:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        background: white;
    }

    .submit-bar {
        background: #1e293b;
        padding: 24px 30px;
        margin-top: 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 20px;
        color: white;
    }

    .btn-premium {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 14px 40px;
        border-radius: 14px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        transition: all 0.3s;
    }
    .btn-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px -5px rgba(79, 70, 229, 0.4);
        color: white;
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Hero Profile Section -->
        <div class="review-hero">
            <img src="{{ $review->employee->profile_image_url ?? asset('assets/backend/img/profiles/avatar-01.jpg') }}" class="hero-avatar" alt="Avatar">
            <div class="hero-info">
                <span class="role">{{ $review->employee->designation->name ?? 'Staff Member' }}</span>
                <h2>{{ $review->employee->name }}</h2>
                <div class="d-flex flex-wrap gap-4 mt-1">
                    <div class="meta-item"><i class="fas fa-building"></i> {{ $review->employee->department->name ?? 'General' }}</div>
                    <div class="meta-item"><i class="fas fa-calendar-alt"></i> Joined {{ $review->employee->workDetail?->joining_date->format('d M, Y') }}</div>
                    <div class="meta-item"><i class="fas fa-hourglass-half"></i> Probation Ends: {{ $review->employee->probation_end_date ? $review->employee->probation_end_date->format('d M, Y') : '--' }}</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-9 col-lg-10 mx-auto">
                <form action="{{ route('onboarding.probation.update', $review->id) }}" method="POST">
                    @csrf
                    
                    <div class="card assessment-card">
                        <div class="card-body p-5">
                            
                            <!-- Phase 1: Performance -->
                            <div class="mb-5">
                                <h6 class="form-section-title">Performance Assessment</h6>
                                <label class="fw-bold text-dark mb-4">How would you rate the employee's overall performance during the probation period?</label>
                                
                                <div class="score-container">
                                    @php $scores = ['Poor', 'Fair', 'Good', 'Very Good', 'Excellent']; @endphp
                                    @for($i=1; $i<=5; $i++)
                                    <div class="score-option">
                                        <input type="radio" name="performance_score" id="score_{{$i}}" value="{{$i}}" required>
                                        <label for="score_{{$i}}" class="score-label">
                                            <span class="score-num">{{$i}}</span>
                                            <span class="score-text">{{ $scores[$i-1] }}</span>
                                        </label>
                                    </div>
                                    @endfor
                                </div>
                            </div>

                            <!-- Phase 2: Comments -->
                            <div class="mb-5">
                                <h6 class="form-section-title">Managerial Commentary</h6>
                                <label class="fw-bold text-dark mb-2">Detailed Feedback <span class="text-danger">*</span></label>
                                <p class="text-muted small mb-3">Provide specific details about strengths, achievements, and areas for development.</p>
                                <textarea name="manager_comments" class="form-control custom-control" rows="6" 
                                    placeholder="Start typing your assessment here..." required minlength="10"></textarea>
                            </div>

                            <!-- Phase 3: Recommendation -->
                            <div class="mb-4">
                                <h6 class="form-section-title">Final Decision & Path Forward</h6>
                                <div class="row">
                                    <div class="col-md-7">
                                        <label class="fw-bold text-dark mb-2">Employment Recommendation <span class="text-danger">*</span></label>
                                        <select name="recommendation" id="recommendation_select" class="form-select custom-control" required>
                                            <option value="">Select Outcome...</option>
                                            <option value="confirmed">Confirm Employment (Pass)</option>
                                            <option value="extended">Extend Probation Period</option>
                                            <option value="terminated">Terminate Employment</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5 d-none" id="extension_duration_div">
                                        <label class="fw-bold text-dark mb-2">Extension Path (Months)</label>
                                        <select name="option_to_extend_duration_months" class="form-select custom-control">
                                            <option value="1">1 Month Extension</option>
                                            <option value="2">2 Months Extension</option>
                                            <option value="3">3 Months Extension</option>
                                            <option value="6">6 Months Extension</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sticky-style Submit Bar -->
                    <div class="submit-bar">
                        <div class="d-none d-md-block">
                            <span class="opacity-75 small">Reviewing as <strong>{{ Auth::user()->name }}</strong></span>
                        </div>
                        <div class="d-flex gap-3">
                            <a href="{{ route('onboarding.probation.index') }}" class="btn btn-outline-light border-0 fw-bold">Cancel</a>
                            <button type="submit" class="btn btn-premium">
                                Submit Assessment
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#recommendation_select').on('change', function() {
            if($(this).val() == 'extended') {
                $('#extension_duration_div').hide().removeClass('d-none').fadeIn();
                $('select[name="option_to_extend_duration_months"]').prop('required', true);
            } else {
                $('#extension_duration_div').fadeOut(function() {
                    $(this).addClass('d-none');
                });
                $('select[name="option_to_extend_duration_months"]').prop('required', false);
            }
        });
    });
</script>
@endpush
@endsection
