<!-- jQuery -->
<script src="{{asset('assets/backend/js/jquery-3.6.0.min.js')}}"></script>
<!-- Bootstrap Core JS -->
<script src="{{asset('assets/backend/js/popper.min.js')}}"></script>
<script src="{{asset('assets/backend/js/bootstrap.min.js')}}"></script>
<!-- Feather Icon JS -->
<script src="{{asset('assets/backend/js/feather.min.js')}}"></script>
<!-- Slimscroll JS -->
<script src="{{asset('assets/backend/plugins/slimscroll/jquery.slimscroll.min.js')}}"></script>
<!-- Datatables JS -->
<script src="{{asset('assets/backend/plugins/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/backend/plugins/datatables/datatables.min.js')}}"></script>
<!-- Select2 JS -->
<script src="{{asset('assets/backend/plugins/select2/js/select2.min.js')}}"></script>
<!-- Sweetalert2 JS -->
<script src="{{asset('assets/backend/plugins/sweetalert/sweetalert2.all.min.js')}}"></script>

<!-- Chart JS -->
<!--<script src="{{asset('assets/backend/plugins/apexchart/apexcharts.min.js')}}"></script>
<script src="{{asset('assets/backend/plugins/apexchart/chart-data.js')}}"></script>-->
<!-- Custom JS -->
<script src="{{asset('assets/backend/js/form-ajax.js')}}"></script>

<script src="{{asset('assets/backend/js/script.js')}}"></script>

<script>
function clearModalState() {
    document.querySelectorAll('.modal-backdrop').forEach(function (el) { el.remove(); });
    document.querySelectorAll('.modal.show').forEach(function (el) {
        el.classList.remove('show');
        el.style.display = 'none';
        el.setAttribute('aria-hidden', 'true');
        el.removeAttribute('aria-modal');
    });
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
}

// Clean up after every modal close
document.addEventListener('hidden.bs.modal', clearModalState);

// If backdrop is still visible and clicked, force-clear everything
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal-backdrop') || e.target.classList.contains('modal')) {
        clearModalState();
    }
});

// Escape key always clears stuck state
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { clearModalState(); }
});
</script>

@viteReactRefresh
@vite(['resources/js/app.jsx'])

<script src="{{asset('assets/backend/js/moment.min.js')}}"></script>
<script src="{{asset('assets/backend/js/dropzone.min.js')}}"></script>
<script src="{{asset('assets/backend/plugins/fullcalendar/script.js')}}"></script>

<script type="text/javascript">
var pdfIconPath = '{{asset("assets/backend/img/icon-pdf.svg")}}';
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
</script>
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>

<script>
document.querySelectorAll('.notification-link').forEach(link => {
    link.addEventListener('click', function() {
        const notificationId = this.getAttribute('data-notification-id');
        var url = "{{ route('backend.markAsRead', ['notificationId' => ':notificationId']) }}";
        url = url.replace(':notificationId', notificationId);
        $.ajax({
            url: url,
            type: 'POST',
            contentType: 'application/json',
            success: function(response) {
                // Handle successful response if needed
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Handle error if needed
            }
        });
    });
});

$('#readallButton').click(function() {
    $.ajax({
        url: "{{ route('backend.readnotifications') }}",
        type: 'DELETE',
        dataType: 'json',
        data: {
            userId: "{{ auth()->id() }}"
        },
        success: function(response) {
            showAlert(response.message, "success");
            setTimeout(function() {
                location.reload();
            }, 2000);
        },
        error: function(error) {
            // Handle error, show an error message
            console.error(error);
        }
    });
});
</script>

{{-- WorkPilot widget only loads for authenticated users, hidden on login/guest pages --}}
@auth
<!-- Defensive script: intercept iframe src/setAttribute and window.open before widget loads -->
<script>
    (function(){
        try {
            const widgetUrlConfig = "{{ config('services.wp_ai.widget_url') }}";
            const orgIdConfig = "{{ config('services.wp_ai.org_id') }}";
            const resolvedWidgetUrl = widgetUrlConfig && widgetUrlConfig.includes('?') ? widgetUrlConfig : (widgetUrlConfig + '?organizationId=' + orgIdConfig);

            function rewriteUrl(url) {
                try {
                    if (!url) return url;
                    const u = new URL(url, window.location.href);
                    if (u.hostname === 'localhost' && (u.port === '3002' || u.port === '3001')) {
                        return resolvedWidgetUrl;
                    }
                    return url;
                } catch (e) { return url; }
            }

            // Override Element.prototype.setAttribute to catch iframe src before navigation
            const origSetAttr = Element.prototype.setAttribute;
            Element.prototype.setAttribute = function(name, value) {
                try {
                    if (this && this.tagName === 'IFRAME' && name === 'src') {
                        value = rewriteUrl(value);
                    }
                } catch (e) {}
                return origSetAttr.call(this, name, value);
            };

            // Override HTMLIFrameElement.prototype.src setter (best-effort)
            try {
                const proto = HTMLIFrameElement.prototype;
                const desc = Object.getOwnPropertyDescriptor(proto, 'src');
                if (desc && desc.set) {
                    const origSet = desc.set;
                    Object.defineProperty(proto, 'src', {
                        configurable: true,
                        enumerable: desc.enumerable,
                        get: desc.get,
                        set: function(v) { origSet.call(this, rewriteUrl(v)); }
                    });
                }
            } catch (e) { /* ignore */ }

            // Override window.open
            const origOpen = window.open.bind(window);
            window.open = function(url, name, specs) {
                return origOpen(rewriteUrl(url), name, specs);
            };

        } catch (err) { /* silent fail in production */ }
    })();
</script>

@if(!in_array(request()->getHost(), ['127.0.0.1', 'localhost']) && config('services.wp_ai.widget_url'))
<!-- WorkPilot AI Widget (external) -->
<script id="wp-ai-widget-script" src="{{ config('services.wp_ai.widget_url') }}" data-organization-id="{{ config('services.wp_ai.org_id') }}"></script>
@endif

{{-- Auto-open widget once per day on first login; polls until button DOM element exists --}}
<script>
(function () {
    var today = new Date().toISOString().slice(0, 10); // "YYYY-MM-DD"
    var storageKey = 'wp_ai_auto_opened_' + today;

    if (!localStorage.getItem(storageKey)) {
        var attempts = 0;
        var maxAttempts = 40; // poll for up to 20 seconds (40 × 500ms)
        var interval = setInterval(function () {
            attempts++;
            // EchoWidget.show() only works after the internal g() function has created the button/container DOM elements
            if (window.EchoWidget && document.getElementById('echo-widget-button')) {
                clearInterval(interval);
                window.EchoWidget.show();
                localStorage.setItem(storageKey, '1');
            } else if (attempts >= maxAttempts) {
                clearInterval(interval);
            }
        }, 500);
    }
})();
</script>
@endauth

<script>
$(document).ready(function() {
    function formatCountry(country) {
        if (!country.id) {
            return country.text; // Return default text for placeholder
        }

        // Retrieve the flag URL from the data attribute
        const flagUrl = $(country.element).data('flag');
        const countryName = country.text;

        return $(
            `<span>
                    <img src="${flagUrl}" style="width: 20px; height: 15px; margin-right: 8px; vertical-align: middle;">
                    ${countryName}
                </span>`
        );
    }

    $('.flag_country').select2({
        templateResult: formatCountry, // Function for rendering options
        templateSelection: formatCountry, // Function for rendering the selected item
        placeholder: 'Select A Option',
        // allowClear: true
    });
});

/**
 * Update real-time employee work status
 */
function updateWorkStatus(statusValue) {
    const $btn = $('#statusPickerBtn');
    const $label = $btn.find('.status-label');
    const $indicator = $btn.find('.status-indicator');
    const $spinner = $btn.find('.status-spinner');
    
    // UI Feedback
    $spinner.show();
    $indicator.hide();
    
    $.ajax({
        url: "{{ route('backend.work-status.update') }}",
        type: 'POST',
        data: {
            status: statusValue,
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {
            if (response.success) {
                // Update UI elements
                $label.text(response.status.label);
                $indicator.css('background-color', response.status.color).show();
                
                // Optional: Show a small toast notification
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                }
            }
        },
        error: function(xhr) {
            console.error('Status update failed');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred'
                });
            }
        },
        complete: function() {
            $spinner.hide();
            $indicator.show();
        }
    });
}
</script>
