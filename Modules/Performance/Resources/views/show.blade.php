<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ __trans('View Performance Appraisal') }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="container my-5">
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __trans('Employee') }}</label>
                <input type="text" class="form-control" value="{{ $appraisal->employee->name }}" readonly>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">{{ __trans('Reviewer') }}</label>
                <input type="text" class="form-control" value="{{ $appraisal->reviewer->name ?? '-' }}" readonly>
            </div>

            <!-- <div class="mb-4">
                <label class="form-label fw-bold">{{ __trans('Appraisal Period') }}</label>
                <input type="text" class="form-control" value="{{ $appraisal->period }}" readonly>
            </div> -->
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __trans('Appraisal Date') }}</label>
                <input type="text" class="form-control" value="{{ $appraisal->appraisal_date?->format('d M Y') }}" readonly>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">{{ __trans('Status') }}</label>
                <input type="text" class="form-control" value="{{ ucfirst($appraisal->status) }}" readonly>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">{{ __trans('Reviewer Comments') }}</label>
                <textarea class="form-control" rows="3" readonly>{{ $appraisal->reviewer_comments }}</textarea>
            </div>

             @php
                            $totalScore = 0;
                            $totalWeight = 0;
                        @endphp
            <div class="mb-4">
                <label class="form-label fw-bold">{{ __trans('Criteria and Scores') }}</label>
                <table class="table table-bordered light">
                    <thead>
                        <tr>
                            <th>{{ __trans('Criteria') }}</th>
                            <th>{{ __trans('Weight (%)') }}</th>
                            <th>{{ __trans('Self Score') }}</th>
                            <th>{{ __trans('Reviewer Score') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       
                        @foreach ($appraisal->criteria as $criterion)
                            <tr>
                                <td>{{ $criterion->criteria_name }}</td>
                                <td>{{ $criterion->weight }}</td>
                                <td>{{ $criterion->self_score ?? '-' }}</td>
                                <td>{{ $criterion->score ?? '-' }}</td>
                                @php
                                    if (!is_null($criterion->score)) {
                                        $totalScore += $criterion->score * $criterion->weight;
                                        $totalWeight += $criterion->weight;
                                    }
                                @endphp
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">{{ __trans('Final Weighted Score') }}</label>
                <input type="text" class="form-control" value="{{ $totalWeight > 0 ? round($totalScore / $totalWeight, 2) : '-' }}" readonly>
            </div>

            @if ($appraisal->status === 'approved'  )
                <div class="text-center mt-4">
                    <a href="{{ route('performance.certificate', $appraisal->id) }}" target="_blank" class="btn btn-success">
                        <i class="fa fa-download"></i> {{ __trans('Download Certificate') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

