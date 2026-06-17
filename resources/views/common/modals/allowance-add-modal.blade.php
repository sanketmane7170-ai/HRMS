
<div id="import-allowance-modal" class="modal" role="dialog" aria-labelledby="importModal" aria-modal="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">{{__trans('import_allowance')}}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{$importUrl}}" datatable="true" method="POST" class="ajax-form-submit reset" enctype="multipart/form-data">
                @csrf

                <div class="modal-body p-4">

                    <div class="row">

                        <div class="col-md-6">
                            <label class="form-label">{{__trans('month')}}</label>
                            <select name="month" id="sample_month" class="form-control">
                                @for($m=1;$m<=12;$m++)
                                <option value="{{$m}}" {{date('m')==$m?'selected':''}}>
                                    {{date('F',mktime(0,0,0,$m,1))}}
                                </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{__trans('year')}}</label>
                            <select name="year" id="sample_year" class="form-control">
                                @for($y=date('Y');$y>=date('Y')-5;$y--)
                                <option value="{{$y}}" {{date('Y')==$y?'selected':''}}>
                                    {{$y}}
                                </option>
                                @endfor
                            </select>
                        </div>

                    </div>

                    <div class="row mt-3">

                        <div class="col-md-12">
                            <div class="mb-3">

                                @isset($flag)
                                <label class="form-label">
                                    {{__trans('Upload_excel_file')}} 
                                    (
                                    <a href="javascript:void(0)" id="download_sample">
                                        {{__trans('download_sample')}}
                                    </a>
                                    )
                                </label>
                                @endisset

                                <input type="file" name="file" class="form-control" accept=".xlsx">

                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">
                        {{__trans('close')}}
                    </button>

                    <button type="submit" class="btn btn-info waves-effect waves-light">
                        {{__trans('save')}}
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
@push('scripts')
<script>

$(document).on('click','#download_sample',function(){

    let month = $('#sample_month').val();
    let year  = $('#sample_year').val();

    let url = "{{ route('backend.users.allowance.export.excel') }}";

    window.location.href = url + "?month=" + month + "&year=" + year;

});

</script>

@endpush
