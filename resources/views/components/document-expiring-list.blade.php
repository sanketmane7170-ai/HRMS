<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="icon-shape-premium bg-warning-soft me-3">
                    <i class="fas fa-exclamation-circle text-warning"></i>
                </div>
                <h5 class="card-title mb-0">{{ __trans('documents_expiring_soon') }}</h5>
            </div>
            @if (isModuleEnabled('Analytic'))
            <a href="{{ route('backend.analytic.documetsexpiring.list') }}"
                class="btn-right btn btn-sm btn-outline-primary">
                {{ __trans('view_all') }}
            </a>
            @endif
        </div>

          <div class="card-body">
            <div class="table-responsive">
                <table class="table table-stripped table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __trans('employee') }}</th>
                            <th>{{ __trans('document') }}</th>
                            <th>{{ __trans('expiry_date') }}</th>
                            <th>{{ __trans('status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documentExpiringList as $doc)
                        <tr >
                            <td>{{ $doc->user->name ?? '-' }}</td>
                            <td>{{ $doc->type->name ?? '-' }}</td>
                            <td>{{ formatDate($doc->expiry_date) }}</td>
                            <td>
                                @if ($doc->expiry_date < now()) <span class="badge bg-danger">
                                    {{ __trans('expired') }}</span>
                                    @else
                                    <span class="badge bg-warning text-dark">{{ __trans('expiring_soon') }}</span>
                                    @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">{{ __trans('no_expiring_documents') }}</td>
                        </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
