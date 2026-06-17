<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="icon-shape-premium bg-success-soft me-3">
                    <i class="fas fa-id-badge text-success"></i>
                </div>
                <h5 class="card-title mb-0">{{__trans('PIC-Certified Employees')}}</h5>
            </div>
            <a href="{{route('backend.analytic.PICCertificationExpiry.list')}}" class="btn-right btn btn-sm btn-outline-primary">
                {{__trans('view_all')}}
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-stripped table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>{{__trans('name')}}</th>
                            <th>{{__trans('branch')}}</th>
                            <th>{{__trans('expiry_date')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $picExpiryList = App\Models\UserDocument::with('user.department')
                            ->where('type', 'pic_certification')
                            ->whereHas('user', function ($q) {
                                $q->where('status', 'active');
                            })
                            ->limit(5)
                            ->get();
                        @endphp
                        @forelse ($picExpiryList as $list)
                        <tr>
                            <td>{{$list->user?->name}}</td>
                            <td>{{$list->user?->department?->name}}</td>
                            <td>{{formatDate($list->expiry_date,'date_format')}}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3">{{__trans('no_records_found')}}</td>
                        </tr>
                        @endforelse
                </table>
            </div>
        </div>
    </div>
</div>
