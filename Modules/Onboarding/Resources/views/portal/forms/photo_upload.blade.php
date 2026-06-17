@extends('onboarding::portal.layout')

@section('title', 'Upload Profile Photo')

@section('styles')
<style>
    .photo-card {
        background: #fff;
        border: 1px solid var(--border-soft);
        border-radius: var(--radius-lg);
        padding: 4rem 2rem;
        text-align: center;
    }

    .preview-container {
        position: relative;
        display: inline-block;
        margin-bottom: 2.5rem;
    }

    #photo-preview {
        width: 200px;
        height: 200px;
        border-radius: 50%;
        object-fit: cover;
        border: 6px solid #fff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: var(--transition);
    }
    
    .preview-container::after {
        content: '\f030';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 44px;
        height: 44px;
        background: var(--primary);
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 4px solid #fff;
    }

    .upload-label {
        font-weight: 800;
        font-size: 1.5rem;
        letter-spacing: -0.03em;
        margin-bottom: 0.5rem;
        color: var(--dark);
    }

    .premium-file-input {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 1rem;
        border-radius: var(--radius-md);
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
        display: block;
    }
</style>
@endsection

@section('content')
<div class="container py-5 fade-in-up">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="d-flex align-items-center justify-content-between mb-5">
                <div>
                    <a href="{{ route('portal.dashboard') }}" class="text-muted small font-weight-bold text-decoration-none mb-2 d-inline-block">
                        <i class="fas fa-arrow-left mr-2"></i> Dashboard
                    </a>
                    <h2 class="font-weight-bold mb-0" style="letter-spacing: -1.5px;">Employee Identity</h2>
                </div>
                <div class="text-right d-none d-md-block">
                    <div class="small text-muted font-weight-bold text-uppercase">Step 02 of 03</div>
                    <div class="font-weight-bold text-primary" style="font-size: 1.2rem;">66% Complete</div>
                </div>
            </div>

            <div class="photo-card shadow-premium">
                <div class="preview-container">
                    <img id="photo-preview" src="{{ $user->profile_image ? asset('uploads/profile/' . $user->profile_image) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=f1f5f9&color=64748b&size=200&rounded=false&bold=true' }}" 
                         alt="Profile Preview">
                </div>
                
                <div class="mb-5">
                    <h3 class="upload-label">Say Cheese!</h3>
                    <p class="text-muted mx-auto" style="max-width: 400px; font-size: 0.95rem;">
                        Your professional photo will be used for your digital ID, Slack, and internal systems. Please ensure good lighting and a clear background.
                    </p>
                </div>

                <form action="{{ route('portal.save-photo') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-5">
                        <input type="file" name="profile_image" id="profile_image" class="premium-file-input" accept="image/*" required onchange="previewImage(this)">
                        <div class="mt-2 small text-muted font-weight-bold">JPG or PNG • Max 2MB</div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-5 border-top">
                        <a href="{{ route('portal.dashboard') }}" class="btn btn-link text-muted font-weight-bold text-decoration-none">
                            <i class="fas fa-chevron-left mr-2"></i> Dashboard
                        </a>
                        <button type="submit" class="btn btn-primary px-5 py-3 shadow-premium">
                            UPLOAD & CONTINUE <i class="fas fa-arrow-right ml-2 small"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('photo-preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
