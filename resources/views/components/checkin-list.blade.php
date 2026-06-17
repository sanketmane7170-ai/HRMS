<div>
    <div class="card h-100">
        <div class="card-body">
            <div class="d-flex align-items-center mb-4">
                <div class="icon-shape-premium me-3">
                    <i class="fas fa-clock"></i>
                </div>
                <h5 class="card-title mb-0">{{__trans('today_check_ins')}}</h5>
            </div>
            
            <div class="wp-gauge-dashboard">
                <div class="gauge-stats">
                    <span class="text-secondary">{{__trans('completion_rate')}}</span>
                    <span class="text-primary font-weight-bold">{{$percentage}}%</span>
                </div>
                
                <div class="gauge-wrap">
                    <div class="gauge-fill" style="width: {{$percentage}}%"></div>
                </div>
                
                <div class="d-flex justify-content-between align-items-end mt-3">
                    <div>
                        <div class="h3 mb-0 font-weight-bold">{{$checkInCount}} <span class="text-muted" style="font-size: 0.9rem; font-weight: 400;">/ {{$userCount}}</span></div>
                        <p class="text-muted mb-0" style="font-size: 0.8rem;">{{__trans('employees_checked_in')}}</p>
                    </div>
                    
                    @if(isModuleEnabled('Attendance'))
                    <a href="{{route('backend.attendances.index')}}" class="btn btn-sm btn-outline-primary">
                        {{__trans('view_all')}} <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
