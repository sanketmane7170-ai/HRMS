<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title light">{{__trans('add_dependent')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{$route}}" datatable="true" method="POST" class="ajax-form-submit reset" {{$action ?? ''}}>
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('first_name')}}</label>
                            <input type="text" name="first_name" class="form-control" id="first_name"
                                placeholder="{{__trans('first_name')}}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('middle_name')}}</label>
                            <input type="text" name="middle_name" class="form-control" id="middle_name"
                                placeholder="{{__trans('middle_name')}}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('last_name')}}</label>
                            <input type="text" name="last_name" class="form-control" id="last_name"
                                placeholder="{{__trans('last_name')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('contact_details')}}</label>
                            <input type="text" name="contact" class="form-control" id="contact"
                                placeholder="{{__trans('contact')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('address')}}</label>
                            <input type="text" name="address" class="form-control" id="address"
                                placeholder="{{__trans('address')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('date_of_birth')}}</label>
                            <input type="date" name="date_of_birth" class="form-control" id="date_of_birth"
                                placeholder="{{__trans('date_of_birth')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('nationality')}}</label>
                            <select name="nationality" id="nationality" class="select-search">
                                <option value="">{{__trans('select_option')}}</option>
                                @foreach (config('default.nationalities') as $nationality)
                                <option value="{{$nationality}}">{{$nationality}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('relation')}}</label>
                            <select name="relation" id="relation" class="select-search">
                                <option value="">{{__trans('select_option')}}</option>
                                @foreach (\App\Enums\Relation::cases() as $relation)
                                @if($relation->value == 'wife' || $relation->value == 'husband')
                                @else
                                <option value="{{$relation->value}}">{{$relation->name}}</option>
                                @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('gender')}}</label>
                            <select name="gender" id="gender" class="select-search">
                                <option value="">{{__trans('select_option')}}</option>
                                @foreach (\App\Enums\Gender::cases() as $gender)
                                <option value="{{$gender->value}}">{{$gender->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="user_depended_document_section">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <h6>{{__trans('Dependent Documents')}}</h6>
                            </div>
                        </div>
                        <div class=" table-responsive">
                            <table id="myTable" class="w-100 ">
                                <tbody id="section">

                                    <tr class="removeclass0" id="removeclass">
                                        <td style="min-width: auto;">
                                            <label for="document"
                                                class="form-label">{{__trans('document_name')}}</label>
                                            <!-- <input type="text" id="document_name_0" name="document_name[]" class="form-control" placeholder="{{__trans('document_name')}}"> -->

                                            <select name="document_name[]" id="document_name_0" class="select-search">
                                                <option value="">{{__trans('select_option')}}</option>
                                                @foreach (\App\Enums\Document::cases() as $type)
                                                <option value="{{$type->value}}">{{$type->name}}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td style="min-width: auto;">
                                            <label for="document" class="form-label">{{__trans('document')}}</label>
                                            <input type="file" id="document_0" name="document[]" id="document"
                                                class="form-control" placeholder="{{__trans('document')}}">
                                        </td>

                                        <td style="min-width: 20px;">
                                            <label for="increment_date" class="form-label">Add</label>
                                            <button type="button" class="form-control btn btn-primary"
                                                onclick="addFields();">+</button>
                                        </td>



                                    </tr>
                                </tbody>
                            </table>

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
    var room = 1;
    var srNo = 1;

    function documentSrNo() {
        srNo++;
    }

    function decrementSrNo() {
        srNo--;
    }

    function addFields() {
        documentSrNo();
        room++;

        var objTo = document.getElementById('section');
        var tr = document.createElement("tr");
        tr.setAttribute("class", "removeclass" + room);
        tr.setAttribute("id", "removeclass" + room);

        var rdiv = 'removeclass' + room;

        tr.innerHTML = `<td style="min-width: auto;">
                               

                               <select name="document_name[]" id="document_name_${srNo}" class="form-control select-search">
                                                <option value="">{{__trans('select_option')}}</option>
                                                @foreach (\App\Enums\Document::cases() as $type)
                                                <option value="{{$type->value}}">{{$type->name}}</option>
                                                @endforeach
                                            </select>
                                            
                            </td>

                            <td style="min-width: auto;">
                                <input required type="file" id="document_${srNo}" name="document[]" id="document" class="form-control" placeholder="{{__trans('document')}}" >
                            </td>
                        
                            
                            <td style="min-width: auto;">
                                <button type="button" class="btn btn-danger form-control text-white" onclick="removeSection(${srNo})">-</button>
                            </td>
                                    `;

        objTo.appendChild(tr);
    }

    function removeSection(rid) {
        var elementToRemove = document.querySelector('.removeclass' + rid);
        elementToRemove.parentNode.removeChild(elementToRemove);
    }
</script>
