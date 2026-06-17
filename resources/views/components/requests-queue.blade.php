<div>
    <div class="card h-100 shadow-premium">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="icon-shape-premium bg-warning-soft me-3">
                    <i class="fas fa-tasks text-warning"></i>
                </div>
                <h5 class="card-title mb-0">{{__trans('Pending_Requests_In_Queue')}}</h5>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-stripped table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>{{__trans('name')}}</th>
                            <th>{{__trans('type')}}</th>
                            <th>{{__trans('created_at')}}</th>
                            <th>{{__trans('action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leavePending as $request)
                            <tr>
                                <td>{{ $request->user->name }}</td>
                                <td>{{ 'Leave Request' }}</td>
                                <td>{{ formatDate($request->created_at ?? null, 'birth_date_format') }}</td>
                                <td><a href="{{ $urls['leave'] }}" class="btn btn-sm inline-block me-2  btn-success view-button"><i class="fa fa-arrow-right"></i></a></td>
                            </tr>
                        @endforeach
                        @foreach ($apparelRequests as $request)
                            <tr>
                                <td>{{ $request->user->name }}</td>
                                <td>{{ 'Apparel Request' }}</td>
                                <td>{{ formatDate($request->created_at ?? null, 'birth_date_format') }}</td>
                                <td><a href="{{ $urls['apparel'] }}" class="btn btn-sm inline-block me-2  btn-success view-button"><i class="fa fa-arrow-right"></i></a></td>
                            </tr>
                        @endforeach
                        @foreach ($generalRequests as $request)
                            <tr>
                                <td>{{ $request->user->name }}</td>
                                <td>{{ 'General Request' }}</td>
                                <td>{{ formatDate($request->created_at ?? null, 'birth_date_format') }}</td>
                                <td><a href="{{ $urls['general'] }}" class="btn btn-sm inline-block me-2  btn-success view-button"><i class="fa fa-arrow-right"></i></a></td>
                            </tr>
                        @endforeach
                        @foreach ($documentRequest as $request)
                            <tr>
                                <td>{{ $request->user->name }}</td>
                                <td>{{ 'Document Request' }}</td>
                                <td>{{ formatDate($request->created_at ?? null, 'birth_date_format') }}</td>
                                <td><a href="{{ $urls['document'] }}" class="btn btn-sm inline-block me-2  btn-success view-button"><i class="fa fa-arrow-right"></i></a></td>
                            </tr>
                        @endforeach
                        @foreach ($advanceRequest as $request)
                            <tr>
                                <td>{{ $request->user->name }}</td>
                                <td>{{ $request->type . ' Request'}}</td>
                                <td>{{ formatDate($request->created_at ?? null, 'birth_date_format') }}</td>
                                <td><a href="{{ $urls['advance'] }}" class="btn btn-sm inline-block me-2  btn-success view-button"><i class="fa fa-arrow-right"></i></a></td>
                            </tr>
                        @endforeach
                        @foreach ($expenseRequest as $request)
                            <tr>
                                <td>{{ $request->user->name }}</td>
                                <td>{{ 'Expense Request'}}</td>
                                <td>{{ formatDate($request->created_at ?? null, 'birth_date_format') }}</td>
                                <td><a href="{{ $urls['expense'] }}" class="btn btn-sm inline-block me-2  btn-success view-button"><i class="fa fa-arrow-right"></i></a></td>
                            </tr>
                        @endforeach
                        {{--  @empty
                            <tr>
                                <td colspan="4">{{ __trans('no_data_found') }}</td>
                            </tr>
                        @endforelse  --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
