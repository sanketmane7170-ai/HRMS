@can('Import Department')
<div id="importmedicalpremium-modal" class="modal" role="dialog" aria-labelledby="importModal" aria-modal="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{__trans('import_medical_premium_records')}}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{$importUrl}}" datatable="true" method="POST" class="ajax-form-submit reset">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                @isset($flag)
                                 <label class="form-label">{{__trans('Upload_excel_file')}} ( <a href="{{route('backend.samplemedicalpremium.export.excel')}}"> {{__trans('download')}}</a> )</label>
                                @endisset
                                <input type="file" name="file" class="form-control" accept=".xlsx">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                        <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
