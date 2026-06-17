<div>
    <div class="card h-100 shadow-premium">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">
                <i class="fas fa-magic text-primary me-2"></i>
                Latest Features
            </h5>
            <div class="col-auto">
                @if (isModuleEnabled('Analytic'))
                <a href="{{route('backend.analytic.feature.list')}}" class="btn btn-sm btn-outline-primary">
                    {{__trans('view_all')}}
                </a>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-stripped table-hover">
                    <thead class="thead-light">
                        <tr>
                            <!-- <th>{{__trans('version')}}</th> -->
                            <!-- <th>{{__trans('feature')}}</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($features as $feature)
                        <tr>
                            <!-- <td>{{$feature->version}}</td> -->
                            <td>
                            <a href="{{$feature->url}}"><span>{{$feature->feature}} ({{$feature->date}})</span></a>

                           </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2">{{__trans('no_features_this_month')}}</td>
                        </tr>
                        @endforelse
                </table>
            </div>
        </div>
    </div>
</div>
