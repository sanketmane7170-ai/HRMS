<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('add_companydocument')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.companydocument.store')}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="legal_trade_name" class="form-label">{{__trans('legal_trade_name')}}</label>
                            <input type="text" name="legal_trade_name" class="form-control" placeholder="{{__trans('legal_trade_name')}}">
                        </div>
                    </div>
                     <div class="col-md-6">
                        <div class="mb-3">
                            <label for="short_name" class="form-label">{{__trans('short_name')}}</label>
                            <input type="text" name="short_name" class="form-control" placeholder="{{__trans('short_name')}}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="license_number" class="form-label">{{__trans('license_number')}}</label>
                            <input type="text" name="license_number" class="form-control" placeholder="{{__trans('license_number')}}">
                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="license_expiry" class="form-label">{{__trans('license_expiry_date')}}</label>
                            <input type="text" name="license_expiry" class="form-control datetime" placeholder="{{__trans('license_expiry')}}">
                        </div>
                    </div>



                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="added_date" class="form-label">{{__trans('added_date')}}</label>
                            <input type="text" name="added_date" class="form-control datetime" placeholder="{{__trans('added_date')}}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="mol_code" class="form-label">{{__trans('mol_code')}}</label>
                            <input type="text" name="mol_code" class="form-control" placeholder="{{__trans('mol_code')}}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="employer_reference" class="form-label">{{__trans('employer_reference')}}</label>
                            <input type="text" name="employer_reference" class="form-control" placeholder="{{__trans('employer_reference')}}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="routing_number" class="form-label">{{__trans('routing_number')}}</label>
                            <input type="text" name="routing_number" class="form-control" placeholder="{{__trans('routing_number')}}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="document" class="form-label">{{__trans('document')}}</label>
                            <input type="file" name="document" class="form-control" placeholder="{{__trans('document')}}">
                        </div>
                    </div>

                    <div class="col-md-6">
                            <div class="mb-3">
                                <label for="logo" class="form-label">{{__trans('logo')}}</label>
                                <input type="file" name="logo" class="form-control" id="logo">
                            </div>
                        </div>

                        <!-- Small Logo Upload -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="small_logo" class="form-label">{{__trans('small_logo')}}</label>
                                <input type="file" name="small_logo" class="form-control" id="small_logo">
                            </div>
                        </div>

                        <!-- Sign Upload -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sign" class="form-label">{{__trans('sign')}}</label>
                                <input type="file" name="sign" class="form-control" id="sign">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="header" class="form-label">{{__trans('header')}}</label>
                                <input type="file" name="header" class="form-control" id="header">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="footer" class="form-label">{{__trans('footer')}}</label>
                                <input type="file" name="footer" class="form-control" id="footer">
                            </div>
                        </div>








                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>
            </div>
        </form>
    </div>
</div>
<script>
    initselect2search();
    flatpickr("input.datetime", {
        enableTime: true,
        // minDate: "today",
        dateFormat: "Y-m-d",
    });
</script>
