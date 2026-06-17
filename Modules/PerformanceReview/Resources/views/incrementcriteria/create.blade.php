<div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ __trans('Add Increment Criteria') }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('incrementcriteria.store') }}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label for="label" class="form-label fw-bold">Label</label>
                    <input type="text" name="label" class="form-control" placeholder="e.g. Excellent" required>
                </div>

                <div class="mb-3">
                    <label for="min_score" class="form-label fw-bold">Min Score</label>
                    <input type="number" name="min_score" class="form-control" min="0" max="100" required>
                </div>

                <div class="mb-3">
                    <label for="max_score" class="form-label fw-bold">Max Score</label>
                    <input type="number" name="max_score" class="form-control" min="0" max="100" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="basic_percent" class="form-label fw-bold">Basic (%)</label>
                        <input type="number" step="0.01" name="basic_percent" class="form-control component-input" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="housing_percent" class="form-label fw-bold">Housing Allowance (%)</label>
                        <input type="number" step="0.01" name="housing_percent" class="form-control component-input" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="transport_percent" class="form-label fw-bold">Transportation Allowance (%)</label>
                        <input type="number" step="0.01" name="transport_percent" class="form-control component-input" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="other_percent" class="form-label fw-bold">Other Allowance (%)</label>
                        <input type="number" step="0.01" name="other_percent" class="form-control component-input" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="incentive_percent" class="form-label fw-bold">Incentive (%)</label>
                        <input type="number" step="0.01" name="incentive_percent" class="form-control component-input" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Total Increment (%)</label>
                        <input type="number" step="0.01" class="form-control" id="total_increment" value="0.00" readonly>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    flatpickr("input.datetime", {
        enableTime: false,
        dateFormat: "Y-m-d",
    });
</script>

<script>
    function updateTotalIncrement() {
        let total = 0;
        document.querySelectorAll('.component-input').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        document.getElementById('total_increment').value = total.toFixed(2);
    }

    document.querySelectorAll('.component-input').forEach(input => {
        input.addEventListener('input', updateTotalIncrement);
    });

    updateTotalIncrement();
</script>