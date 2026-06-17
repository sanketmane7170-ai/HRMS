<div>
    <div class="card h-100">
        <div class="card-header border-0 pb-0">
            <div class="row align-items-center">
                <div class="col">
                    <div class="d-flex align-items-center">
                         <div class="icon-shape-premium me-3">
                            <i class="fas fa-plane-departure"></i>
                         </div>
                        <h5 class="card-title mb-0">
                            {{__trans('upcoming_air_tickets')}}
                        </h5>
                    </div>
                </div>
                <div class="col-auto">
                    @can('Manage Airticket')
                    <a href="{{route('backend.analytic.airticket.list')}}" class="btn-right btn btn-sm btn-outline-primary">
                        {{__trans('view_all')}}
                    </a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-stripped table-hover custom-table-premium">
                    <thead>
                        <tr>
                            <th>{{__trans('name')}}</th>
                            <th>{{__trans('department_name')}}</th>
                            <th>{{__trans('date')}}</th>
                            <th class="text-end">{{__trans('amount')}}</th>
                            <th class="text-center">{{__trans('quantity')}}</th>
                            <th class="text-end">{{__trans('total_amount')}}</th>
                            <th class="text-center">{{__trans('action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($airticketlist as $airticket)
                        <tr>
                            <td class="font-weight-600">{{$airticket->user->name}}</td>
                            <td><span class="badge bg-primary-soft text-primary">{{$airticket->user->department?->name??"NA"}}</span></td>
                            <td>{{formatDate($airticket->date,'birth_date_format')}}</td>
                            <td class="text-end font-weight-bold">{{$airticket->amount}}</td>
                            <td class="text-center">{{$airticket->quantity}}</td>
                            <td class="text-end"><span class="text-primary font-weight-bold">{{$airticket->totalAmount}}</span></td>
                            <td class="text-center">
                                <a href="{{ route('backend.airTicketReport') }}" class="btn-icon-view">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-ticket-alt opacity-20 mb-2 fa-2x d-block"></i>
                                {{__trans('no_upcoming_air_ticket')}}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-table-premium thead th {
        background: transparent !important;
        border-bottom: 2px solid var(--border) !important;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
        color: var(--text-muted);
    }
    .custom-table-premium tbody td {
        vertical-align: middle;
        padding: 1rem 0.75rem !important;
    }
    .btn-icon-view {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: var(--bg);
        color: var(--text-secondary);
        transition: all 0.2s;
    }
    .btn-icon-view:hover {
        background: var(--wp-primary);
        color: white;
        transform: translateX(2px);
    }
    .font-weight-600 { font-weight: 600; }
</style>
