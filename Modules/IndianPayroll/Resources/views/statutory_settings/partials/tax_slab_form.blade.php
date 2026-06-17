<form method="POST" action="{{ route('backend.indian-payroll.tax-slabs.store') }}" class="row g-2 align-items-end">
    @csrf
    <input type="hidden" name="financial_year" value="{{ $financialYear }}">
    <input type="hidden" name="regime" value="{{ $regime }}">
    <div class="col-4">
        <label style="font-size:.74rem;color:#6b7280;">From (₹)</label>
        <input type="number" step="0.01" name="slab_from" class="form-control form-control-sm" placeholder="0" required>
    </div>
    <div class="col-4">
        <label style="font-size:.74rem;color:#6b7280;">To (₹) — blank = above</label>
        <input type="number" step="0.01" name="slab_to" class="form-control form-control-sm" placeholder="e.g. 300000">
    </div>
    <div class="col-2">
        <label style="font-size:.74rem;color:#6b7280;">Rate %</label>
        <input type="number" step="0.01" name="rate" class="form-control form-control-sm" placeholder="5" required>
    </div>
    <div class="col-2">
        <button type="submit" class="btn btn-sm btn-primary w-100" title="Add slab"><i class="fas fa-plus"></i></button>
    </div>
</form>
