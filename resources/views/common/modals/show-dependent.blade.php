<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('Uploaded Dependent Documents')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
            <div class="row">


                <div class="col-md-12 mt-12">
                    @php
                    $dependentDocuments = App\Models\UserDependentDocument::where('user_dependent_id',
                    $userDependent->id)->get();
                    @endphp
                    <div class="row">
                        @foreach($dependentDocuments as $dependentDocument)
                        <div id="{{ $dependentDocument->id }}" class="col-md-3 mb-3 text-center {{ $dependentDocument->id }}">
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
                                    style="max-height: 50px; height:50px;">
                            </a>
                            @else
                            <!-- Display PDF icon for non-image files -->
                            <a href="{{ asset('uploads/user_dependent_document/' . $userDependent->user_id . '/' . $dependentDocument->document) }}"
                                target="_blank">
                                <i class="fa fa-file-pdf text-danger"
                                    style="font-size: 50px;max-height: 50px; height:50px;"></i>
                            </a>
                            @endif

                            <div class="mt-2">
                                <small>{{ $dependentDocument->document_name }}</small>
                            </div>

                            <!-- Delete Button -->
                            <button type="button" class="btn btn-danger btn-sm mt-2"
                                onclick="deleteDocument({{ $dependentDocument->id }}, {{ $userDependent->user_id }})">
                                <i class="fa fa-trash"></i>
                            </button>
                            <a class="btn btn-success btn-sm mt-2" href="{{route('backend.user-dependent.download',[$dependentDocument,$userDependent->user_id])}}"
                                target="_blank">
                                <i class="fa fa-download"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>


                </div>
            </div>
        </div>

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
                               <input required type="text" id="document_name_${srNo}" name="document_name[]" class="form-control" placeholder="{{__trans('document_name')}}">
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
