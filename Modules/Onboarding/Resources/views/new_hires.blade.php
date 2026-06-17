@extends('layouts.backend')

@section('page-title')
    {{ __('New Hire Management') }}
@endsection

@section('content')
<div class="page-wrapper">
    <style>
        /* Sharp UI Overrides for Consistency */
        .card, .btn, .form-control, .nav-link, .progress, .progress-bar, .badge, .dropdown-menu, .modal-content, .avatar-img {
            border-radius: 0 !important;
        }
        .page-title {
            letter-spacing: 0.5px;
        }
    </style>
    <div class="content container-fluid">
        
        <!-- Header -->
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title text-uppercase font-weight-bold text-dark">
                        @if(request('status') == 'pending')
                            New Hire Queue
                        @elseif(request('status') == 'incomplete')
                            Incomplete Records
                        @else
                            New Hire Management
                        @endif
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('onboarding.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">
                            @if(request('status'))
                                <a href="{{ route('onboarding.new-hires') }}" class="text-muted">New Hires</a> / {{ ucfirst(str_replace('_', ' ', request('status'))) }}
                            @else
                                New Hires
                            @endif
                        </li>
                    </ul>
                </div>
                <div class="col-auto">
                    @if(request('status'))
                        <a href="{{ route('onboarding.new-hires') }}" class="btn btn-outline-secondary font-weight-bold mr-2">
                            <i class="fas fa-times mr-1"></i> Clear Filter
                        </a>
                    @endif
                    <button class="btn btn-white border shadow-sm text-dark font-weight-bold mr-2" data-bs-toggle="modal" data-bs-target="#import_modal">
                        <i class="fas fa-file-import mr-1"></i> Import Excel
                    </button>
                    <button class="btn btn-primary font-weight-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#add_new_hire">
                        <i class="fas fa-plus mr-1"></i> Add New Hire
                    </button>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0 bg-white">
                    <div class="card-body p-0 bg-white">
                        <div class="table-responsive">
                            <table class="table table-hover table-center mb-0 custom-table bg-white">
                                <thead style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                    <tr>
                                        <th class="py-4 pl-4" style="color: #64748b; font-weight: 700; font-size: 12px; text-transform: uppercase;">Name</th>
                                        <th class="py-4" style="color: #64748b; font-weight: 700; font-size: 12px; text-transform: uppercase;">Department</th>
                                        <th class="py-4" style="color: #64748b; font-weight: 700; font-size: 12px; text-transform: uppercase;">Branch</th>
                                        <th class="py-4" style="color: #64748b; font-weight: 700; font-size: 12px; text-transform: uppercase;">Joining Date</th>
                                        <th class="py-4" style="color: #64748b; font-weight: 700; font-size: 12px; text-transform: uppercase;">Status</th>
                                        <th class="py-4" style="color: #64748b; font-weight: 700; font-size: 12px; text-transform: uppercase;">Progress</th>
                                        <th class="text-right py-4 pr-4" style="color: #64748b; font-weight: 700; font-size: 12px; text-transform: uppercase;">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    @forelse($newHires as $hire)
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td class="pl-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="avatar avatar-sm mr-3">
                                                    <img class="avatar-img rounded-circle" 
                                                         src="https://ui-avatars.com/api/?name={{ urlencode($hire->full_name) }}&background=random&color=fff" 
                                                         alt="{{ $hire->full_name }}">
                                                </a>
                                                <div>
                                                    <a href="#" class="text-dark font-weight-bold" style="font-size: 14px;">{{ $hire->full_name }}</a>
                                                    <span class="d-block text-muted small mt-1">{{ $hire->email }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3" style="font-weight: 500; color: #334155;">{{ $hire->division->name ?? 'General' }}</td>
                                        <td class="py-3" style="font-weight: 500; color: #334155;">{{ $hire->department->name ?? '-' }}</td>
                                        <td class="py-3" style="font-weight: 500; color: #334155;">{{ \Carbon\Carbon::parse($hire->joining_date)->format('d M, Y') }}</td>
                                        <td class="py-3">
                                            @if($hire->status == 'pending')
                                                <span class="badge bg-light text-warning border border-warning rounded-pill px-3">Pending</span>
                                            @elseif($hire->status == 'in_progress')
                                                <span class="badge bg-light text-info border border-info rounded-pill px-3">In Progress</span>
                                            @elseif($hire->status == 'completed')
                                                <span class="badge bg-light text-success border border-success rounded-pill px-3">Completed</span>
                                            @endif
                                        </td>
                                        <td class="py-3" style="min-width: 150px;">
                                            <div class="d-flex align-items-center">
                                                <div class="progress progress-xs w-100 mr-2" style="height: 6px; border-radius: 10px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $hire->progress_percent }}%; border-radius: 10px;"></div>
                                                </div>
                                                <span class="small font-weight-bold">{{ $hire->progress_percent }}%</span>
                                            </div>
                                        </td>
                                        <td class="text-right pr-4 py-3">
                                            <div class="dropdown dropdown-action position-static">
                                                <a href="#" class="action-icon dropdown-toggle text-muted" 
                                                   data-bs-toggle="dropdown" 
                                                   aria-expanded="false" 
                                                   data-bs-boundary="viewport">
                                                    <i class="fas fa-ellipsis-h"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right shadow border-0" style="border-radius: 10px;">
                                                    <a class="dropdown-item py-2" href="{{ route('onboarding.show', $hire->id) }}">
                                                        <i class="fas fa-eye mr-2 text-info"></i> View Details
                                                    </a>
                                                    <a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#edit_hire_{{ $hire->id }}">
                                                        <i class="fas fa-pencil-alt mr-2 text-warning"></i> Edit
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item py-2 text-danger" href="#" onclick="confirmDelete('{{ $hire->id }}')">
                                                        <i class="fas fa-trash-alt mr-2"></i> Delete
                                                    </a>
                                                    <form id="delete-form-{{ $hire->id }}" action="{{ route('onboarding.destroy', $hire->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal for each row (Simplified for MVP, ideally dynamic via JS) -->
                                    <div id="edit_hire_{{ $hire->id }}" class="modal custom-modal fade" role="dialog">
                                        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Employee</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('onboarding.update', $hire->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="row">
                                                            <div class="col-sm-6">
                                                                <div class="form-group">
                                                                    <label class="col-form-label">Full Name <span class="text-danger">*</span></label>
                                                                    <input class="form-control" type="text" name="full_name" value="{{ $hire->full_name }}" required>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="form-group">
                                                                    <label class="col-form-label">Joining Date <span class="text-danger">*</span></label>
                                                                    <input class="form-control" type="date" name="joining_date" value="{{ \Carbon\Carbon::parse($hire->joining_date)->format('Y-m-d') }}" min="{{ date('Y-m-d') }}" required>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="form-group">
                                                                    <label class="col-form-label">Email <span class="text-danger">*</span></label>
                                                                    <input class="form-control" type="email" name="email" value="{{ $hire->email }}" required>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="form-group">
                                                                    <label class="col-form-label">Status</label>
                                                                    <select class="form-control" name="status">
                                                                        <option value="pending" {{ $hire->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                                        <option value="in_progress" {{ $hire->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                                        <option value="completed" {{ $hire->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="form-group">
                                                                    <label class="col-form-label">Department (Division)</label>
                                                                    <select class="form-control" name="division_id">
                                                                        <option value="">Select Department</option>
                                                                        @foreach($divisions as $div)
                                                                            <option value="{{ $div->id }}" {{ $hire->division_id == $div->id ? 'selected' : '' }}>{{ $div->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="form-group">
                                                                    <label class="col-form-label">Branch</label>
                                                                    <select class="form-control" name="department_id">
                                                                        <option value="">Select Branch</option>
                                                                        @foreach($departments as $dept)
                                                                            <option value="{{ $dept->id }}" {{ $hire->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="form-group">
                                                                    <label class="col-form-label">Progress (%)</label>
                                                                    <input class="form-control" type="number" name="progress_percent" value="{{ $hire->progress_percent }}" min="0" max="100">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="submit-section">
                                                            <button class="btn btn-primary submit-btn">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 bg-white">
                                            <div class="py-5">
                                                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486777.png" width="80" class="mb-4 opacity-25">
                                                <h5 class="text-muted font-weight-bold">No new hires found</h5>
                                                <p class="text-muted small mb-0">Add a new hire manually or import from Excel to get started.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($newHires->isNotEmpty())
                    <div class="card-footer bg-white border-top-0 py-3">
                        {{ $newHires->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add New Hire Modal -->
<div id="add_new_hire" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Hire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('onboarding.new-hires.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="col-form-label">Full Name <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="full_name" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="col-form-label">Email <span class="text-danger">*</span></label>
                                <input class="form-control" type="email" name="email" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="col-form-label">Joining Date <span class="text-danger">*</span></label>
                                <input class="form-control" type="date" name="joining_date" min="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="col-form-label">Department (Division)</label>
                                <select class="form-control" name="division_id">
                                    <option value="">Select Department</option>
                                    @foreach($divisions as $div)
                                        <option value="{{ $div->id }}">{{ $div->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="col-form-label">Branch</label>
                                <select class="form-control" name="department_id">
                                    <option value="">Select Branch</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="submit-section">
                        <button class="btn btn-primary submit-btn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div id="import_modal" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Employees</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('onboarding.new-hires.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group mb-4">
                        <label class="font-weight-bold">Upload Excel/CSV File <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" required>
                        <div class="mt-2 d-flex justify-content-between align-items-center">
                            <small class="text-muted">Expected: full_name, email, joining_date</small>
                            <a href="{{ route('onboarding.new-hires.template') }}" class="small font-weight-bold text-primary">
                                <i class="fas fa-download mr-1"></i> Download Template
                            </a>
                        </div>
                    </div>
                    <div class="alert alert-info border-0 shadow-none px-3 py-2" style="background-color: #f0f9ff; border-radius: 8px;">
                        <ul class="small mb-0 pl-3" style="color: #0369a1;">
                            <li>File must include <strong>Full Name</strong> and <strong>Email</strong>.</li>
                            <li>Existing emails will be automatically skipped.</li>
                            <li>Supported headers: "Name", "Full Name", "Email Address".</li>
                        </ul>
                    </div>
                    <div class="submit-section">
                        <button class="btn btn-primary submit-btn">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Fix for dropdown being clipped in table-responsive
        $('.dropdown-toggle').on('click', function () {
            // No custom JS needed if we use Popper's boundary
        });
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }
</script>
@endsection
