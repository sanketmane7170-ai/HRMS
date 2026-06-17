<div class="modal-dialog modal-xl">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ __trans('View Appraisal Template') }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body p-4">
            {{-- Template Info --}}
            <div class="mb-4">
                <strong>{{ __trans('Template Name') }}:</strong> {{ $template->name }}
            </div>

            <div class="mb-4">
                <strong>{{ __trans('Period Type') }}:</strong>
                {{ ucfirst(str_replace('_',' ',$template->period_type)) }}
            </div>

            <div class="mb-4">
                <strong>{{ __trans('Status') }}:</strong>
                {!! $template->is_active
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-danger">Inactive</span>' !!}
            </div>

            <hr>

            {{-- Criteria Table --}}
            <h5>{{ __trans('Criteria') }}</h5>

            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr class="light">
                            <th>{{ __trans('Name') }}</th>
                            <th>{{ __trans('Description') }}</th>
                            <th>{{ __trans('Weight') }}</th>
                            <th>{{ __trans('Max Score') }}</th>
                            <th>{{ __trans('Comments') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($template->criteria as $c)
                        <tr class="light">
                            <td>{{ $c->criteria_name }}</td>
                            <td>{{ $c->description }}</td>
                            <td>{{ $c->weight }}</td>
                            <td>{{ $c->max_score }}</td>
                            <td>{{ $c->comments }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>

        <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">
                {{ __trans('close') }}
            </button>
        </div>
    </div>
</div>