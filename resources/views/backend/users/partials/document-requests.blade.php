
<!-- /Page Header -->
<div class="row">
    <div class="col-sm-12">
        <div class="card card-table">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table text-center table-hover" id="dataTable">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>{{__trans('requested_on')}}</th>
                                <th>{{__trans('document_type')}}</th>
                                <th>{{__trans('status')}}</th>
                                <th>{{__trans('action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($user->documentrequests as $document)
                            <tr>
                                <td>{{$document->id}}</td>
                                <td>{{Illuminate\Support\Carbon::parse($document->created_at)->toDateString()}}</td>
                                <td>{{$document->type->name}}</td>
                                <td>
                                    @php
                                        $status = 'btn-success';
                                        if ($document->status == Modules\Document\Enums\DocumentRequestStatus::Pending) {
                                            $status = 'btn-warning';
                                        } elseif ($document->status == Modules\Document\Enums\DocumentRequestStatus::Rejected) {
                                            $status = 'btn-danger';
                                        }
                                    @endphp
                                    <span class="badge {{$status}} ucfirst">{{$document->status->name}}</span>
                                </td>
                                <td>
                                    <a href="{{route('backend.document-requests.show', $document->id)}}" class="btn btn-sm inline-block me-2  btn-warning"><i class="fa fa-eye"></i>{{__trans('view')}}</a>
                                </td>
                            </tr>
                            @endforeach
                            @foreach ($user->warning as $warning)

                            @php
                                switch ($warning->type->name) {
                                    default:
                                    case ('VERBAL_WARNING'):
                                        $class = 'warning';
                                        break;
                                    case ('FIRST_WARNING'):
                                        $class = 'secondary';
                                        break;
                                    case ('SECOND_WARNING'):
                                        $class = 'info';
                                        break;
                                    case ('THIRD_WARNING'):
                                        $class = 'danger';
                                        break;
                                }
                            @endphp
                                <tr>
                                    <td>{{$warning->id}}</td>
                                    <td>{{Illuminate\Support\Carbon::parse($warning->created_at)->toDateString()}}</td>
                                    <td>{{__trans('warning')}}</td>
                                    <td>
                                        <span class="badge btn-{{$class}}">{{$warning->type->getName()}}</span></td>
                                    <td>
                                        <a href="{{route('backend.user-warnings.show', $warning->id)}}" class="btn btn-sm inline-block me-2  btn-warning"><i class="fa fa-eye"></i>{{__trans('view')}}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
