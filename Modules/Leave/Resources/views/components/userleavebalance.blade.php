<div class="row">
    @foreach ($types as $type)
    @php
    $background =$loop->iteration;
    $available = (float) ($calculatePendingLeave($type) ?? 0);
    $percentage = $progressBarPercentage($available, $type);
    $urlparams = $getURLParamsForLeave($type);
    @endphp



    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card top-stat-box top-stat-box-5">
            <div class="card-body bg-{{$background}}">
                <div class="dash-widget-header">
                    <div class="dash-count">
                        <div class="dash-counts">
                            <h3>{{$available }}</h3>
                        </div>
                        <div class="dash-top-text">
                            <p><img src="{{asset('assets/backend/img/icon-arrow-white.svg')}}" class="img-fluid" /> {{$percentage}}% {{__trans('available')}}</p>
                        </div>
                        <div class="dash-title">
                            <p>{{$type->name}}</p>
                        </div>
                    </div>
                    @can('Edit Update Leave Balance EditUpdateLeave')
                    <a href="{{route('backend.leave-balance.edit',[$urlparams['user_id'],$urlparams['leave_type_id']])}}" style="position: absolute; right: 10px;bottom: 9px;
    padding: 5px 9px;
    font-size: 15px;
" class="btn btn-primary me-1 edit-button">
                        <i class="fas fa-edit"></i>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>


    {{--  <!-- <div class="col-xl-3 col-sm-6 col-12 ">
        <div class="card ">
            <div class="card-body bg-{{$background}}">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon">
                        <i class="fas fa-file-alt"></i>
                    </span>
                    <div class="dash-count">
                        <div class="dash-title">{{$type->name}}</div>
                        <div class="dash-counts">
                            <p>{{$available }}</p>
                        </div>
                    </div>
                </div>
                <div class="progress progress-sm mt-3">
                    <div class="progress-bar" role="progressbar"
                        style="width:{{$percentage}}%;background:linear-gradient(to right, #f53e61, #f99f25)"
                        aria-valuenow="{{$type->days}}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p class="text-muted mt-3 mb-0  p-1">
                    <span><i class="fas fa-arrow-up me-1"></i>{{$percentage}}%</span>
                    {{__trans('available')}}
                </p>
            </div>
        </div>
    </div> -->  --}}


    @endforeach
</div>