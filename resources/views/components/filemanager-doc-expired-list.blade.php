<div>
    <div class="card h-100">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="me-4">
                    <img style="width: 60px" src="{{asset('assets/backend/img/icon-file-manager.svg')}}" class="img-fluid" />
                </div>
                <div class="doc-exp-right flex-grow-1">
                    <h3>{{$documentExpiredCount}}</h3>
                    <p class="text-muted">{{__trans('filemanager_expiring_documents')}}</p>
                </div>
            </div>
            <div class="mt-4">
                @if (isModuleEnabled('Analytic'))
                <a href="{{route('backend.analytic.filemanager.expired.list')}}" class="btn btn-sm btn-outline-primary w-100 text-center">
                    {{__trans('view_all')}} <i class="fas fa-external-link-alt ms-1" style="font-size: 10px;"></i>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
