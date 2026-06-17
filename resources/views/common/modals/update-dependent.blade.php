<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title light">{{__trans('edit_dependent')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{$route}}" datatable="true" method="POST" class="ajax-form-submit reset" {{$action ??''}}>
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('first_name')}}</label>
                            <input type="text" name="first_name" class="form-control" id="first_name"
                                placeholder="{{__trans('first_name')}}" value="{{$userDependent->first_name}}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('middle_name')}}</label>
                            <input type="text" name="middle_name" class="form-control" id="middle_name"
                                placeholder="{{__trans('middle_name')}}" value="{{$userDependent->middle_name}}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('last_name')}}</label>
                            <input type="text" name="last_name" class="form-control" id="last_name"
                                placeholder="{{__trans('last_name')}}" value="{{$userDependent->last_name}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('contact_details')}}</label>
                            <input type="text" name="contact" class="form-control" id="contact"
                                placeholder="{{__trans('contact')}}" value="{{$userDependent->contact}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('address')}}</label>
                            <input type="text" name="address" class="form-control" id="address"
                                placeholder="{{__trans('address')}}" value="{{$userDependent->address}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('date_of_birth')}}</label>
                            <input type="date" name="date_of_birth" class="form-control" id="date_of_birth"
                                placeholder="{{__trans('date_of_birth')}}" value="{{$userDependent->date_of_birth}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('nationality')}}</label>
                            <select name="nationality" id="nationality"
                                class="select-search">common.modals.update-dependent
                                <option value="">{{__trans('select_option')}}</option>
                                @foreach (config('default.nationalities') as $nationality)
                                <option value="{{$nationality}}" @if($userDependent->nationality == $nationality)
                                    selected @endif>{{$nationality}}</option>
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
                                <option value="{{$relation->value}}" @if($userDependent->relation->value ==
                                    $relation->value) selected @endif>{{$relation->name}}</option>
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
                                <option value="{{$gender->value}}" @if($userDependent->gender == $gender->value)
                                    selected @endif>{{$gender->name}}</option>
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
                                            <!-- <input type="text" id="document_name_0" name="document_name[]"
                                                class="form-control" placeholder="{{__trans('document_name')}}"> -->


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
                    <div class="col-md-12">
                        <div class="mb-3">
                            <h6>{{__trans('Uploaded Dependent Documents')}}</h6>
                        </div>
                        <div class="col-md-12 mt-12">
                            @php
                            $dependentDocuments = App\Models\UserDependentDocument::where('user_dependent_id',
                            $userDependent->id)->get();
                            @endphp
                            <div class="row">
                                @foreach($dependentDocuments as $dependentDocument)
                                <div id="{{ $dependentDocument->id }}"
                                    class="col-md-2 mb-2 text-center {{ $dependentDocument->id }}">
                                    @php
                                    $fileExtension = pathinfo($dependentDocument->document, PATHINFO_EXTENSION);
                                    $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif']);
                                    @endphp

                                    @if ($isImage)
                                    <!-- Display image if the file is an image -->
                                    <a href="{{ asset('uploads/user_dependent_document/' . $userDependent->user_id . '/' . $dependentDocument->document) }}"
                                        target="_blank">
                                        <img src="{{ asset('uploads/user_dependent_document/' . $userDependent->user_id . '/' . $dependentDocument->document) }}"
                                            alt="{{ $dependentDocument->document_name }}" class="img-fluid rounded"
                                            style="max-height: 100px; height:50px;">
                                    </a>
                                    @else
                                    <!-- Display PDF icon for non-image files -->
                                    <a href="{{ asset('uploads/user_dependent_document/' . $userDependent->user_id . '/' . $dependentDocument->document) }}"
                                        target="_blank">
                                        <i class="fa fa-file-pdf text-danger"
                                            style="font-size: 50px;max-height: 100px; height:50px;"></i>
                                    </a>
                                    @endif

                                    <div class="mt-2">
                                        <small>{{ __trans($dependentDocument->document_name) }}</small>
                                    </div>

                                    <!-- Delete Button -->
                                    <button type="button" class="btn btn-danger btn-sm mt-2"
                                        onclick="deleteDocument({{ $dependentDocument->id }}, {{ $userDependent->user_id }})">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                                @endforeach
                            </div>


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
function deleteDocument(documentId, user_id) {
    if (confirm('Are you sure you want to delete this document?')) {
        fetch(`/user-dependent-document/${documentId}/${user_id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // location.reload();
                    $('.' + documentId).remove();
                } else {
                    alert('Failed to delete document');
                }
            });
    }
}
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
