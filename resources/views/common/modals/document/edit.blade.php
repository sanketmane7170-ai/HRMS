<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('update_document')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{$route}}" datatable="true" method="POST" class="ajax-form-submit reset" {{$action ?? ''}}>
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('document_type')}}</label>
                            <select name="type" id="type" class="select-search">
                                {{$userdocument->type->name}}
                                <option value="">{{__trans('select_option')}}</option>
                                @foreach (\App\Enums\Document::cases() as $type)
                                @if($userdocument->type->name == $type->name)
                                <option value="{{ $type->value }}" selected>{{ $type->name }}</option>
                                @else
                                <option value="{{$type->value}}">{{$type->name}}</option>
                                @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label id="lbl_serial_number" class="form-label">
                                @if(ucwords($userdocument->type->name)=="Passport")
                                {{__trans('Passport Number')}}
                                @elseif(ucwords($userdocument->type->name)=="LaborCard")
                                {{__trans('Labor Card Number')}}
                                @elseif(ucwords($userdocument->type->name)=="Visa")
                                {{__trans('File Number')}}
                                @elseif(ucwords($userdocument->type->name)=="EmiratesID")
                                {{__trans('Emirates ID Number')}}
                                @else
                                {{__trans('serial_number')}}
                                @endif


                            </label>
                            <input type="text" name="serial_number" id="serial_number"
                                value="{{ $userdocument->serial_number }}" class="form-control">
                        </div>
                    </div>
                    <div style={{ $userdocument->type->name === 'LaborCard' ? 'display:block;' : 'display:none;' }}"
                        id="div_ministry_of_labor_personal_no" class="col-md-6">
                        <div class="mb-3">
                            <label id="lbl_ministry_of_labor_personal_no"
                                class="form-label">{{__trans('Ministry of Labor Personal No')}}</label>
                            <input type="text" name="ministry_of_labor_personal_no" id="ministry_of_labor_personal_no"
                                value="{{ $userdocument->ministry_of_labor_personal_no }}" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label id="lbl_expiry_date" class="form-label">

                                @if(ucwords($userdocument->type->name)=="Passport")
                                {{__trans('Passport Issue Date')}}

                                @else
                                {{__trans('issue_date')}}
                                @endif
                            </label>
                            <input type="date" name="issue_date" id="issue_date" value="{{ $userdocument->issue_date }}"
                                class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label id="lbl_place_of_issue" class="form-label">
                                @if(ucwords($userdocument->type->name)=="Passport")
                                {{__trans(' Passport Expiry Date')}}
                                @else
                                {{__trans('expiry_date')}}
                                @endif
                            </label>
                            <input type="date" name="expiry_date" id="expiry_date"
                                value="{{ $userdocument->expiry_date }}" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">
                                @if(ucwords($userdocument->type->name)=="LaborCard")
                                {{__trans('Company Trade License Name')}}
                                @elseif(ucwords($userdocument->type->name)=="Visa")
                                {{__trans('UID number')}}
                                @else
                                {{__trans('place_of_issue')}}
                                @endif</label>
                            <input type="text" name="place_of_issue" id="place_of_issue"
                                value="{{ $userdocument->place_of_issue }}" class="form-control">

                            <!-- <select name="place_of_issue" id="place_of_issue" class="form-control" required>
                                <option value="">Select</option>
                                <option @if(!empty($userdocument) && $userdocument->place_of_issue == 'father') selected @endif  value="Abu Dhabi">Abu Dhabi</option>
                                <option @if(!empty($userdocument) && $userdocument->place_of_issue == 'Dubai') selected @endif value="Dubai">Dubai</option>
                                <option @if(!empty($userdocument) && $userdocument->place_of_issue == 'Sharjah') selected @endif value="Sharjah">Sharjah</option>
                                <option @if(!empty($userdocument) && $userdocument->place_of_issue == 'Ajman') selected @endif value="Ajman">Ajman</option>
                                <option @if(!empty($userdocument) && $userdocument->place_of_issue == 'Umm Al-Quwain') selected @endif value="Umm Al-Quwain">Umm Al-Quwain</option>
                                <option @if(!empty($userdocument) && $userdocument->place_of_issue == 'Ras Al Khaimah') selected @endif value="Ras Al Khaimah">Ras Al Khaimah</option>
                                <option @if(!empty($userdocument) && $userdocument->place_of_issue == 'Fujairah') selected @endif value="Fujairah">Fujairah</option>
                            </select> -->
                        </div>
                    </div>
                    <!-- <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('country_name')}}</label>
                            <input type="text" name="country_name" id="country_name"
                                value="{{ $userdocument->country_name }}" class="form-control">
                        </div>
                    </div> -->
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label>{{__trans('country_name')}}</label>
                            <select id="country_name" name="country_name"
                                class="form-control select-search flag_country">
                                <option value="">{{__trans('select_a_option')}}</option>
                                @foreach (getCountryListwithFlag() as $country)
                                <option data-flag="{{ $country['flag_url'] }}" @if(!empty($userdocument->country_name)
                                    && $userdocument->country_name == $country['name']) selected @endif
                                    value="{{$country['name']}}">
                                    {{$country['name']}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <input type="file" name="file" id="user-file" class="form-control"
                                onchange="previewImage('user-file','preview-user-file')"
                                accept="image/*,application/pdf">
                        </div>
                        <div id="preview-user-file" class="preview-image">
                            <div class="col-md-3 mt-2">
                                @if(pathinfo($userdocument->path, PATHINFO_EXTENSION) == "pdf")
                                <img width="50" src="{{ asset('assets/backend/img/icon-pdf.svg') }}">
                                @else
                                <img width="50" src="{{ asset($userdocument->path) }}">
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label id="lbl_note" class="form-label">{{__trans('note')}}</label>
                            <textarea name="note" id="note" class="form-control">{{ $userdocument->note }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect"
                    data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>
            </div>
        </form>
    </div>
</div>

<script>
initselect2search();
</script>
<script>
$(document).on('change', '#type', function() {
    const type = $(this).val();
    $('#lbl_serial_number').text("Serial Number"); // Update the label
    $('#lbl_issue_date').text("Issue Date"); // Update the label
    $('#lbl_expiry_date').text("Expiry Date"); // Update the label
    $('#lbl_place_of_issue').text("Place Of Issue"); // Update the label
    $('#div_ministry_of_labor_personal_no').hide();

    if (type == "passport") {
        $('#lbl_serial_number').text("Passport No."); // Update the label
        $('#lbl_issue_date').text("Passport Issue Date"); // Update the label
        $('#lbl_expiry_date').text("Passport Expiry Date"); // Update the label

    } else if (type == "emirates_id") {
        $('#lbl_serial_number').text("Emirates ID No"); // Update the label
    } else if (type == "labor_card_no") {
        $('#lbl_serial_number').text("Labor card No");
        $('#div_ministry_of_labor_personal_no').show();
        $('#lbl_place_of_issue').text("Company Trade License Name");
    } else if (type == "visa") {
        $('#lbl_serial_number').text("File Number");
        $('#lbl_place_of_issue').text("UID number");
    }

});
</script>

<script>
$(document).ready(function() {
    function formatCountry(country) {
        if (!country.id) {
            return country.text; // Return default text for placeholder
        }

        // Retrieve the flag URL from the data attribute
        const flagUrl = $(country.element).data('flag');
        const countryName = country.text;

        return $(
            `<span>
                    <img src="${flagUrl}" style="width: 20px; height: 15px; margin-right: 8px; vertical-align: middle;">
                    ${countryName}
                </span>`
        );
    }

    $('.flag_country').select2({
        templateResult: formatCountry, // Function for rendering options
        templateSelection: formatCountry, // Function for rendering the selected item
        placeholder: 'Select A Option',
        // allowClear: true
    });
});
</script>
