<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Announcement\Entities\Announcement;

class GetAnnouncementsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_announcements';
    }

    public function description(): string
    {
        //Sanket v2.0 - richer description with smart filter guidance
        return 'Fetch company announcements. Supports smart filtering: '
            . 'status = "active" (currently visible), "upcoming" (not started yet), "past" (expired), "all" (default). '
            . 'Can filter by department_id or announcement type name. '
            . 'Use when user asks about announcements, company news, notices, or what\'s new.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'status' => [
                    'type'        => 'string',
                    'description' => 'Filter by status: "active" (currently running, start_at <= today <= end_at), "upcoming" (start_at > today), "past" (end_at < today), "all" (no filter). Default: "all".',
                    'enum'        => ['active', 'upcoming', 'past', 'all']
                ],
                'limit' => [
                    'type'        => 'integer',
                    'description' => 'Number of announcements to return (default 10, max 30).'
                ],
                'department_id' => [
                    'type'        => 'integer',
                    'description' => 'Filter by department ID. Omit for all departments including company-wide.'
                ],
                'type' => [
                    'type'        => 'string',
                    'description' => 'Filter by announcement type name, e.g. "HR", "General", "Holiday".'
                ],
                'search' => [
                    'type'        => 'string',
                    'description' => 'Keyword to search inside announcement body.'
                ]
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        $limit  = min($args['limit'] ?? 10, 30);
        $status = strtolower($args['status'] ?? 'all');
        $now    = now();

        try {
            $query = Announcement::query()->with(['type', 'user']);

            //Sanket v2.0 - smart status filter using actual DB columns
            if ($status === 'active') {
                $query->where('start_at', '<=', $now)->where('end_at', '>=', $now);
            } elseif ($status === 'upcoming') {
                $query->where('start_at', '>', $now);
            } elseif ($status === 'past') {
                $query->where('end_at', '<', $now);
            }

            //Sanket v2.0 - optional department filter (include company-wide when filtering dept)
            if (!empty($args['department_id'])) {
                $deptId = (int) $args['department_id'];
                $query->where(function ($q) use ($deptId) {
                    $q->where('department_id', $deptId)->orWhereNull('department_id');
                });
            }

            //Sanket v2.0 - filter by type name
            if (!empty($args['type'])) {
                $typeName = $args['type'];
                $query->whereHas('type', function ($q) use ($typeName) {
                    $q->whereRaw('LOWER(name) = ?', [strtolower($typeName)]);
                });
            }

            //Sanket v2.0 - keyword search in body
            if (!empty($args['search'])) {
                $keyword = $args['search'];
                $query->where('body', 'LIKE', "%{$keyword}%");
            }

            $announcements = $query->latest('created_at')->limit($limit)->get();

            if ($announcements->isEmpty()) {
                return ['message' => "No {$status} announcements found.", 'announcements' => []];
            }

            return [
                'total'         => $announcements->count(),
                'filter_status' => $status,
                'announcements' => $announcements->map(function ($a) use ($now) {
                    //Sanket v2.0 - derive status dynamically for each row
                    $rowStatus = 'active';
                    if ($a->start_at && \Carbon\Carbon::parse($a->start_at)->gt($now)) {
                        $rowStatus = 'upcoming';
                    } elseif ($a->end_at && \Carbon\Carbon::parse($a->end_at)->lt($now)) {
                        $rowStatus = 'past';
                    }

                    return [
                        'id'          => $a->id,
                        'type'        => $a->type->name ?? 'General',
                        'status'      => $rowStatus,
                        'body'        => strip_tags($a->body),
                        'posted_by'   => $a->user->name ?? 'HR Team',
                        'department'  => $a->department_id ? 'Dept #' . $a->department_id : 'Company-wide',
                        'start_date'  => $a->start_at  ? \Carbon\Carbon::parse($a->start_at)->format('M d, Y')  : null,
                        'end_date'    => $a->end_at    ? \Carbon\Carbon::parse($a->end_at)->format('M d, Y')    : null,
                        'created_at'  => $a->created_at->format('M d, Y'),
                        'time_ago'    => $a->created_at->diffForHumans(),
                    ];
                })->toArray()
            ];
        } catch (\Exception $e) {
            \Log::error('GetAnnouncementsTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to fetch announcements', 'message' => $e->getMessage()];
        }
    }
}
