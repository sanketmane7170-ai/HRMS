<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ __trans('Edit Performance Appraisal') }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('performance.update', $appraisal->id) }}" method="POST" class="ajax-form-submit reset"
            datatable="true">
        <form action="{{ route('performance.update', $appraisal->id) }}" method="POST" class="ajax-form-submit reset"
            datatable="true">
            @csrf
            @method('PUT')

            <div class="modal-body p-4">
                @php
                $isEmployee = auth()->id() === $appraisal->employee_id;
                $isReviewer = auth()->id() === $appraisal->reviewer_id || auth()->user()->hasRole('Admin');
                $isEmployee = auth()->id() === $appraisal->employee_id;
                $isReviewer = auth()->id() === $appraisal->reviewer_id || auth()->user()->hasRole('Admin');
                @endphp

                


                <div class="mb-4">
                    <label for="reporting_manager_id" class="form-label fw-bold">
                        {{ __trans('Reporting Manager') }}
                    </label>
                    <select disabled name="reporting_manager_id" id="reporting_manager_id" class="form-control select-search">
                        <option value="">{{ __trans('All / Default') }}</option>
                        @foreach ($managers as $manager)
                            <option value="{{ $manager->id }}" @if($appraisal->reviewer_id == $manager->id) selected @endif>{{ $manager->name }}</option>
                        @endforeach
                    </select>
                </div>

                


                


                <div class="mb-4">
                    <label for="reporting_manager_id" class="form-label fw-bold">
                        {{ __trans('Reporting Manager') }}
                    </label>
                    <select disabled name="reporting_manager_id" id="reporting_manager_id" class="form-control select-search">
                        <option value="">{{ __trans('All / Default') }}</option>
                        @foreach ($managers as $manager)
                            <option value="{{ $manager->id }}" @if($appraisal->reviewer_id == $manager->id) selected @endif>{{ $manager->name }}</option>
                        @endforeach
                    </select>
                </div>

                


                <div class="mb-4">
                    <label class="form-label fw-bold">{{ __trans('Employee') }}</label>
                    <input type="text" class="form-control" value="{{ $appraisal->employee->name }}" disabled>
                </div>

                <!-- <div class="mb-4">
                    <label class="form-label fw-bold">{{ __trans('Appraisal Period') }}</label>
                    <input type="text" class="form-control" value="{{ $appraisal->period }}" disabled>
                </div> -->
                <div class="mb-4">
                    <label class="form-label fw-bold">{{ __trans('Appraisal Date') }}</label>
                    <input type="text" name="appraisal_date" class="form-control appraisal-date"
                        value="{{ optional($appraisal->appraisal_date)->format('Y-m-d') }}"
                        {{ !$isReviewer || $appraisal->is_locked ? 'readonly' : '' }}>
                </div>


                <div class="mb-4">
                    <label class="form-label fw-bold">{{ __trans('Criteria Evaluation') }}</label>
                    <div class="row g-3">
                        @foreach ($appraisal->criteria as $criterion)
                        <div class="col-md-12 mb-3">
                            <label class="form-label">{{ $criterion->criteria_name }} <span class="text-muted">(Weight:
                                    {{ $criterion->weight }}%)</span></label>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="number" step="0.1" min="0" max="10"
                                        name="criteria[{{ $criterion->id }}][self_score]" class="form-control"
                                        placeholder="{{ __trans('Self Score') }}" value="{{ $criterion->self_score }}"
                                        {{ !$isEmployee ? 'readonly' : '' }}>
                                </div>
                                <div class="col-md-6">
                                    <input type="number" step="0.1" min="0" max="10"
                                        name="criteria[{{ $criterion->id }}][score]" class="form-control"
                                        placeholder="{{ __trans('Reviewer Score') }}" value="{{ $criterion->score }}"
                                        {{ !$isReviewer ? 'readonly' : '' }}>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                @if ($isReviewer)
                <div class="mb-4">
                    <label class="form-label fw-bold">{{ __trans('Reviewer Comments') }}</label>
                    <textarea name="reviewer_comments" class="form-control"
                        rows="4">{{ $appraisal->reviewer_comments }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">{{ __trans('Status') }}</label>
                    <select name="status" class="form-control" required>
                        <option value="draft" {{ $appraisal->status == 'draft' ? 'selected' : '' }}>
                            {{ __trans('Draft') }}</option>
                        <option value="submitted" {{ $appraisal->status == 'submitted' ? 'selected' : '' }}>
                            {{ __trans('Submitted') }}</option>
                        <option value="approved" {{ $appraisal->status == 'approved' ? 'selected' : '' }}>
                            {{ __trans('Approved') }}</option>
                        <option value="rejected" {{ $appraisal->status == 'rejected' ? 'selected' : '' }}>
                            {{ __trans('Rejected') }}</option>
                    </select>
                </div>
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __trans('close') }}</button>
                <button type="submit" class="btn btn-info">{{ __trans('save') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
flatpickr(".appraisal-date", {
    enableTime: false,
    dateFormat: "Y-m-d",
    maxDate: "today" // ✅ prevent future dates
});
loadAjaxSelect2();
</script>