<div>
    <div class="card" style="min-height: 185px;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="icon-shape-premium me-3">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <h5 class="card-title mb-0">{{__trans('company_wide_announcements')}}</h5>
            </div>
            <div class="col-auto">
                <a href="#" class="btn-right btn btn-sm btn-outline-primary">
                    {{__trans('view_all')}}
                </a>
            </div>
        </div>
        <div class="card-body" style="max-height: 500px; overflow-y: auto;">
            <div class="feed-container">
                @php
                    if(auth()->user()->hasRole(App\Models\User::ROLE_ADMIN)){
                        $Announcementdata = Modules\Announcement\Entities\Announcement::with('type')
                            ->where('start_at', '<=', now()->toDateTimeString())
                            ->where('end_at', '>=', now()->toDateTimeString())
                             ->orderBy('created_at', 'desc')
                            ->get();
                    } else {
                        $Announcementdata = Modules\Announcement\Entities\Announcement::with('type')
                            ->where('start_at', '<=', now()->toDateTimeString())
                            ->where('end_at', '>=', now()->toDateTimeString())
                             ->orderBy('created_at', 'desc')
                            ->where(function ($q) {
                                $q->where('user_id', auth()->user()->id)->orwhereNull('user_id');
                            })
                            ->where(function ($q) {
                                $q->where('department_id', auth()->user()->department_id)->orwhereNull('department_id');
                            })
                            ->get();
                    }
                @endphp
                @foreach($Announcementdata as $announcement)
                @php
                    $color = $announcement->type->color ?? '#4F46E5';
                @endphp
                <div class="feed-item" style="border-left-color: {{$color}};">
                    <div class="d-flex w-100 flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                             <span class="badge" style="background: {{$color}}15; color: {{$color}}; font-size: 0.7rem; border: 1px solid {{$color}}30;">
                                {{ $announcement->type->name ?? 'Update' }}
                             </span>
                             <span class="text-muted" style="font-size: 0.75rem;">{{ $announcement->created_at->diffForHumans() }}</span>
                        </div>
                        
                        <div class="feed-content text-secondary" style="font-size: 0.9rem; line-height: 1.6;">
                            {!! $announcement->body !!}
                        </div>
                        
                        @if($announcement->file)
                        <div class="mt-3">
                            <a href="{{ $announcement->file }}" target="_blank" class="btn btn-xs btn-outline-info">
                                <i class="fas fa-paperclip me-1"></i> View Attachment
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
                
                @if($Announcementdata->isEmpty())
                <div class="text-center py-5">
                    <div class="text-muted mb-2">
                        <i class="fas fa-comment-slash fa-2x opacity-20"></i>
                    </div>
                    <p class="text-muted small">{{__trans('no_announcement_found')}}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
