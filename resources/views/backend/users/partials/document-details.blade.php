<div class="row align-items-center  mb-3">
    <div class="col"></div>
    <div class="col-auto">

        @can('Create Document')

        <a href="{{route('backend.user-document.create',$user)}}"
            class="btn btn-sm btn-success edit-button">{{__trans('add_document')}}</a>
        @endcan
    </div>
</div>
<div class="row">
    @forelse ($user->documents as $document)

    <div class="col-12 col-md-6 col-lg-4 d-flex">
        <div class="card flex-fill bg-white">
            @if(pathinfo($document->path, PATHINFO_EXTENSION) == "pdf")
            <img alt="Card Image" src="{{ asset('assets/backend/img/icon-pdf.svg') }}" class="card-img-top"
                style="height:150px;">
            @else
            <img alt="Card Image" src="{{ asset($document->path) }}" class="card-img-top" style="height:150px;">
            @endif

            <div class="card-header ">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title mb-0">

                            {{ucwords($document->type->name)}}
                            <span class="badge @if($document->status == 'verified') badge-success @elseif($document->status == 'rejected') badge-danger @else badge-warning @endif">
                                {{ucfirst($document->status ?? 'pending')}}
                            </span>
                        </h5>
                    </div>
                    <div class="col-auto">
                        @if($document->status == 'pending' || $document->status == '')
                        <button onclick="changeStatus({{$document->id}}, 'verified')" class="btn btn-sm btn-success" title="Verify">
                            <i class="fa fa-check"></i>
                        </button>
                        <button onclick="changeStatus({{$document->id}}, 'rejected')" class="btn btn-sm btn-danger" title="Reject">
                            <i class="fa fa-times"></i>
                        </button>
                        @endif
                        @can('Manage Document')

                        <a class="btn btn-success btn-sm " href="{{route('backend.user-document.download',$document)}}"
                            target="_blank">
                            <i class="fa fa-download"></i>
                        </a>
                        @endcan

                        @can('Edit Document')

                        <a href="{{route('backend.user-document.edit',$document->id)}}"
                            class="btn btn-sm btn-warning edit-button">
                            <i class="fa fa-edit"></i>
                        </a>
                        @endcan
                        @can('Delete Document')
                        <a class="btn btn-danger btn-sm action-button"
                            href="{{route('backend.user-document.destroy',$document)}}" html="#document-details">
                            <i class="fa fa-trash"></i>
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="card-text">
                    <a class="card-link light" href="#">

                        @if(ucwords($document->type->name)=="Passport")
                        {{__trans('Passport Number')}}
                        @elseif(ucwords($document->type->name)=="LaborCard")
                        {{__trans('Labor Card Number')}}
                        @elseif(ucwords($document->type->name)=="Visa")
                        {{__trans('File Number')}}
                        @elseif(ucwords($document->type->name)=="EmiratesID")
                        {{__trans('Emirates ID Number')}}
                        @else
                        {{__trans('serial_number')}}
                        @endif

                        @if($document->serial_number){{$document->serial_number}} @else Not Added @endif</a></br>

                    @if(ucwords($document->type->name)=="LaborCard")
                    <a class="card-link light" href="#">
                        {{__trans('Ministry of Labor Personal No')}}
                        @if($document->ministry_of_labor_personal_no){{$document->ministry_of_labor_personal_no}} @else
                        Not Added @endif</a></br>
                    @endif


                    <a class="card-link light" href="#">
                        @if(ucwords($document->type->name)=="Passport")
                        {{__trans('Passport Issue Date')}}

                        @else
                        {{__trans('issue_date')}}
                        @endif


                        @if($document->issue_date == '0000-00-00') Not Added @else {{$document->issue_date}}
                        @endif</a></br>
                    <a class="card-link light" href="#">
                        @if(ucwords($document->type->name)=="Passport")
                        {{__trans(' Passport Expiry Date')}}
                        @else
                        {{__trans('expiry_date')}}
                        @endif

                        @if($document->expiry_date == '0000-00-00') Not Added @else {{$document->expiry_date}}
                        @endif</a></br>
                    <a class="card-link light" href="#">

                        @if(ucwords($document->type->name)=="LaborCard")
                        {{__trans('Company Trade License Name')}}
                        @elseif(ucwords($document->type->name)=="Visa")
                        {{__trans('UID number')}}
                        @else
                        {{__trans('place_of_issue')}}
                        @endif
                        @if($document->place_of_issue){{$document->place_of_issue}} @else Not Added @endif</a></br>
                    <a class="card-link light" href="#">{{__trans('country_name')}}
                        @if($document->country_name){{$document->country_name}} @else Not Added @endif</a></br>
                    <!-- @if($document->expiry_date)
                    <a class="card-link light" href="#">{{__trans('expiry_date')}} {{$document->expiry_date}}</a>
                    @endif
                    <br>
                    @if($document->serial_number)
                    <a class="card-link light" href="#">{{__trans('serial_number')}} {{$document->serial_number}}</a>
                    @endif -->

                </div>
            </div>
        </div>
    </div>
    @empty

    @endforelse
</div>


<script>
    function changeStatus(documentId, status) {
        if(confirm('Are you sure you want to ' + status + ' this document?')) {
            fetch(`/user-document/${documentId}/status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({status: status})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $('#document-details').html(data.html);
                } else {
                    alert('Failed to update status');
                }
            });
        }
    }
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
