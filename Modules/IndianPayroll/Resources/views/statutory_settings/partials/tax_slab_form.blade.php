<form method="POST" action="{{ route('backend.indian-payroll.tax-slabs.store') }}" class="row g-2 mt-2">
    @csrf
    <input type="hidden" name="financial_year" value="{{ $financialYear }}">
    <input type="hidden" name="regime" value="{{ $regime }}">
    <div class="col-md-3"><input type="number" step="0.01" name="slab_from" class="form-control" placeholder="{{ __trans('from') }}" required></div>
    <div class="col-md-3"><input type="number" step="0.01" name="slab_to" class="form-control" placeholder="{{ __trans('to_blank_for_above') }}"></div>
    <div class="col-md-3"><input type="number" step="0.01" name="rate" class="form-control" placeholder="{{ __trans('rate_percent') }}" required></div>
    <div class="col-md-3"><button type="submit" class="btn btn-outline-primary w-100">{{ __trans('add') }}</button></div>
</form>
