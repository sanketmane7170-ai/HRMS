<div>
    <div class="card h-100">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="me-4">
                    <img src="{{asset('assets/backend/img/icon-documents-expiring.svg')}}" class="img-fluid" style="width: 60px;" />
                </div>
                <div class="doc-exp-right flex-grow-1">
                    <h3>{{$documentExpiredCount}}</h3>
                    @php
                    $expiry_days = 90;
                    if (!empty(getSetting('document_expiry_days'))) {
                    $expiry_days = getSetting('document_expiry_days');
                    }
                    @endphp
                    <p class="text-muted">Expiring documents in next {{ $expiry_days }} days</p>
                </div>
            </div>
            <div class="mt-4">
                @if (isModuleEnabled('Analytic'))
                <a href="{{route('backend.analytic.document.expired.list')}}" class="btn btn-sm btn-outline-primary w-100 text-center">
                    {{__trans('view_all')}} <i class="fas fa-external-link-alt ms-1" style="font-size: 10px;"></i>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
