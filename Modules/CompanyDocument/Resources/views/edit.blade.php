<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_company_document')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.companydocument.update',$companydocument)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">



                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="legal_trade_name" class="form-label">{{__trans('legal_trade_name')}}</label>
                            <input type="text" name="legal_trade_name" class="form-control" placeholder="{{__trans('legal_trade_name')}}" value="{{$companydocument->legal_trade_name}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="short_name" class="form-label">{{__trans('short_name')}}</label>
                            <input type="text" name="short_name" class="form-control" placeholder="{{__trans('short_name')}}" value="{{$companydocument->short_name}}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="license_number" class="form-label">{{__trans('license_number')}}</label>
                            <input type="text" name="license_number" class="form-control" placeholder="{{__trans('license_number')}}" value="{{$companydocument->license_number}}">
                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="license_expiry" class="form-label">{{__trans('license_expiry_date')}}</label>
                            <input type="text" name="license_expiry" class="form-control datetime" placeholder="{{__trans('license_expiry')}}" value="{{$companydocument->license_expiry}}">
                        </div>
                    </div>



                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="added_date" class="form-label">{{__trans('added_date')}}</label>
                            <input type="text" name="added_date" class="form-control datetime" placeholder="{{__trans('added_date')}}" value="{{$companydocument->added_date}}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="mol_code" class="form-label">{{__trans('mol_code')}}</label>
                            <input type="text" name="mol_code" class="form-control" placeholder="{{__trans('mol_code')}}" value="{{$companydocument->mol_code}}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="employer_reference" class="form-label">{{__trans('employer_reference')}}</label>
                            <input type="text" name="employer_reference" class="form-control" placeholder="{{__trans('employer_reference')}}" value="{{$companydocument->employer_reference}}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="routing_number" class="form-label">{{__trans('routing_number')}}</label>
                            <input type="text" name="routing_number" class="form-control" placeholder="{{__trans('routing_number')}}" value="{{$companydocument->routing_number}}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <h6>{{__trans('Uploaded Documents')}}</h6>
                        </div>
                        <div class="col-md-6 mt-6">


                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ asset('uploads/companydocument/'. $companydocument->document) }}"
                                    target="_blank">
                                    {{ $companydocument->document }}
                                </a>
                            </li>


                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __trans('logo') }}</label>
                            <input type="file" name="logo" class="form-control">
                            @if($companydocument->logo)
                            <img src="{{ asset('uploads/companydocument/' . $companydocument->logo) }}" class="img-thumbnail mt-2" style="max-height: 100px;">
                            @endif
                        </div>
                    </div>

                    <!-- Small Logo Upload -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __trans('small_logo') }}</label>
                            <input type="file" name="small_logo" class="form-control">
                            @if($companydocument->small_logo)
                            <img src="{{ asset('uploads/companydocument/' . $companydocument->small_logo) }}" class="img-thumbnail mt-2" style="max-height: 100px;">
                            @endif
                        </div>
                    </div>

                    <!-- Sign Upload -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __trans('sign') }}</label>
                            <input type="file" name="sign" class="form-control">
                            @if($companydocument->sign)
                            <img src="{{ asset('uploads/companydocument/' . $companydocument->sign) }}" class="img-thumbnail mt-2" style="max-height: 100px;">
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __trans('header') }}</label>
                            <input type="file" name="header" class="form-control">
                            @if($companydocument->header)
                            <img src="{{ asset('uploads/companydocument/' . $companydocument->header) }}" class="img-thumbnail mt-2" style="max-height: 100px;">
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __trans('footer') }}</label>
                            <input type="file" name="footer" class="form-control">
                            @if($companydocument->footer)
                            <img src="{{ asset('uploads/companydocument/' . $companydocument->footer) }}" class="img-thumbnail mt-2" style="max-height: 100px;">
                            @endif
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
    // initselect2search();
    // initTextEditor(['edit_body']);
    flatpickr("input.datetime", {
        enableTime: true,
        // minDate: "today",
        dateFormat: "Y-m-d",
    });
</script>
