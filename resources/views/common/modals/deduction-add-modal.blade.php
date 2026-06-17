<div id="import-deduction-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="importModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">{{ __trans('import_deduction') }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ $importUrl }}" method="POST" enctype="multipart/form-data" class="ajax-form-submit reset" datatable="true">
                @csrf

                <div class="modal-body p-4">
                    <div class="row">

                        <!-- Month -->
                        <div class="col-md-6">
                            <label class="form-label">{{ __trans('month') }}</label>
                            <select name="month" id="sample_month" class="form-control">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ date('m') == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0,0,0,$m,1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <!-- Year -->
                        <div class="col-md-6">
                            <label class="form-label">{{ __trans('year') }}</label>
                            <select name="year" id="sample_year" class="form-control">
                                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                    <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="mb-3">

                                @isset($flag)
                                    <a href="javascript:void(0)" id="download_deduction_sample">
                                        {{ __trans('download_sample') }}
                                    </a>
                                @endisset

                                <input type="file" name="file" class="form-control mt-2" accept=".xlsx" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer OUTSIDE modal-body -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __trans('close') }}
                    </button>
                    <button type="submit" class="btn btn-info">
                        {{ __trans('save') }}
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).on('click', '#download_deduction_sample', function () {

    let modal = $('#import-deduction-modal');

    let month = modal.find('#sample_month').val();
    let year  = modal.find('#sample_year').val();

    let url = "{{ route('backend.users.deduction.export.excel') }}";

    let finalUrl = `${url}?month=${encodeURIComponent(month)}&year=${encodeURIComponent(year)}`;

    window.location.href = finalUrl;
});
</script>
@endpush
