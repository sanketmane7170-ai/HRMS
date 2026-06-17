    $(document).ready(function() {
        console.log('🎯 Interview page loaded - using simple table with form filters');
        
        // Simple and reliable filter functionality - auto-submit form when any filter changes
        $('#filterForm select, #filterForm input[type="date"]').on('change', function() {
            console.log('🔄 Filter changed: ' + $(this).attr('name') + ' = ' + $(this).val());
            console.log('📋 Submitting form for immediate filtering...');
            $(this).closest('form')[0].submit();
        });
        
        // Remove any alert from page load
        $('.alert').not('.alert-permanent').fadeOut(3000);
    });

    // Interview management functions

    // Complete interview
    $('.complete-interview').click(function() {
        const interviewId = $(this).data('id');
        $('#completeInterviewId').val(interviewId);
        $('#completeModal').modal('show');
    });

    // Complete interview form submission  
    $('#completeForm').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const interviewId = $('#completeInterviewId').val();
        
        $.ajax({
            url: `/recruitment/interviews/${interviewId}/complete`,
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Interview completed successfully!');
                    $('#completeModal').modal('hide');
                    location.reload();
                } else {
                    toastr.error(response.message || 'Failed to complete interview');
                }
            },
            error: function(xhr) {
                let message = 'Failed to complete interview';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    message = Object.values(xhr.responseJSON.errors).join(', ');
                }
                toastr.error(message);
            }
        });
    });

    // Reschedule interview
    $('.reschedule-interview').click(function() {
        const interviewId = $(this).data('id');
        $('#rescheduleInterviewId').val(interviewId);
        $('#rescheduleModal').modal('show');
    });

    // Reschedule interview form submission
    $('#rescheduleForm').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const interviewId = $('#rescheduleInterviewId').val();
        
        $.ajax({
            url: `/recruitment/interviews/${interviewId}/reschedule`,
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Interview rescheduled successfully!');
                    $('#rescheduleModal').modal('hide');
                    location.reload();
                } else {
                    toastr.error(response.message || 'Failed to reschedule interview');
                }
            },
            error: function() {
                toastr.error('Something went wrong!');
            }
        });
    });

    // Delete interview
    $(document).on('click', '.delete-interview', function(e) {
        e.preventDefault();
        let interviewId = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteInterview(interviewId);
            }
        });
    });

    function deleteInterview(id) {
        console.log('Attempting to delete interview ID:', id);
        
        // Show loading state
        Swal.fire({
            title: 'Deleting...',
            text: 'Please wait while we delete the interview.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: `/recruitment/interviews/${id}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                console.log('Delete response:', response);
                
                if (response.success) {
                    Swal.fire('Deleted!', response.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', response.message || 'Failed to delete interview', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Delete failed:', xhr.status, error, xhr.responseText);
                
                let errorMessage = 'Failed to delete interview';
                if (xhr.status === 403) {
                    errorMessage = 'Permission denied. You do not have access to delete interviews.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    errorMessage = 'Interview not found.';
                } else if (xhr.status >= 500) {
                    errorMessage = 'Server error. Please try again later.';
                }
                
                Swal.fire('Error!', errorMessage, 'error');
            }
        });
    }

    // Toast notification helper
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
        toast.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        
        document.body.appendChild(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }