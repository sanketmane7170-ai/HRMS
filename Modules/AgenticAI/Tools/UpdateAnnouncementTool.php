<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Announcement\Entities\Announcement;
use Modules\Announcement\Entities\AnnouncementType;

class UpdateAnnouncementTool extends BaseTool
{
    public function name(): string
    {
        return 'update_announcement';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        //Sanket v2.0 - admin/hr can update any announcement; others only their own
        return 'Update an existing announcement\'s content, dates, type, or department. '
            . 'Admin and HR can update any announcement. Other roles can only update their own. '
            . 'Use the current date from system context to resolve relative date expressions.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'announcement_id' => [
                    'type'        => 'integer',
                    'description' => 'ID of the announcement to update. Call get_announcements first if you don\'t know the ID.'
                ],
                'body' => [
                    'type'        => 'string',
                    'description' => 'New body/content for the announcement.'
                ],
                'title' => [
                    'type'        => 'string',
                    'description' => 'New title — will replace the existing bold heading in the body.'
                ],
                'start_at' => [
                    'type'        => 'string',
                    'description' => 'New start date (YYYY-MM-DD). Compute from context if user gives a relative expression.'
                ],
                'end_at' => [
                    'type'        => 'string',
                    'description' => 'New end date (YYYY-MM-DD). Compute from context if user gives a relative expression.'
                ],
                'announcement_type' => [
                    'type'        => 'string',
                    'description' => 'New type name e.g. "HR", "Holiday", "General". Resolved to ID internally.'
                ],
                'department_id' => [
                    'type'        => 'integer',
                    'description' => 'New department target. Set 0 or omit to make company-wide.'
                ]
            ],
            'required' => ['announcement_id']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $id   = $args['announcement_id'] ?? null;

        if (!$user) {
            return ['error' => 'Authentication required'];
        }
        if (!$id) {
            return ['error' => 'Missing ID', 'message' => 'Provide the announcement_id to update.'];
        }

        try {
            //Sanket v2.0 - admin/hr can update any announcement; others only their own
            $isPrivileged = $user->hasAnyRole(['admin', 'hr', 'super-admin']);
            $announcement = $isPrivileged
                ? Announcement::find($id)
                : Announcement::where('id', $id)->where('user_id', $user->id)->first();

            if (!$announcement) {
                return ['error' => 'Not found', 'message' => $isPrivileged
                    ? "Announcement #{$id} not found."
                    : "Announcement #{$id} not found or you don't have permission to edit it."];
            }

            $updates = [];

            //Sanket v2.0 - body update; optionally prepend new title
            if (isset($args['body'])) {
                $newTitle = trim($args['title'] ?? '');
                $updates['body'] = $newTitle
                    ? "<strong>{$newTitle}</strong><br>" . trim($args['body'])
                    : trim($args['body']);
            } elseif (isset($args['title'])) {
                // Title-only change: strip old <strong> heading and replace
                $existingBody = preg_replace('/^<strong>[^<]*<\/strong><br>/i', '', $announcement->body);
                $updates['body'] = "<strong>" . trim($args['title']) . "</strong><br>" . $existingBody;
            }

            //Sanket v2.0 - date updates with same safe Carbon parsing as create
            if (isset($args['start_at'])) {
                $updates['start_at'] = $this->resolveDate($args['start_at'], now()->format('Y-m-d'))->format('Y-m-d H:i:s');
            }
            if (isset($args['end_at'])) {
                $base = $updates['start_at'] ?? $announcement->start_at;
                $updates['end_at'] = $this->resolveDate($args['end_at'], \Carbon\Carbon::parse($base)->addDays(7)->format('Y-m-d'))->format('Y-m-d H:i:s');
            }

            //Sanket v2.0 - ensure end_at is never before start_at
            $effectiveStart = isset($updates['start_at']) ? \Carbon\Carbon::parse($updates['start_at']) : \Carbon\Carbon::parse($announcement->start_at);
            $effectiveEnd   = isset($updates['end_at'])   ? \Carbon\Carbon::parse($updates['end_at'])   : \Carbon\Carbon::parse($announcement->end_at);
            if ($effectiveEnd->lt($effectiveStart)) {
                $updates['end_at'] = $effectiveStart->copy()->addDays(7)->format('Y-m-d H:i:s');
            }

            //Sanket v2.0 - announcement type by name
            if (!empty($args['announcement_type'])) {
                $type = AnnouncementType::whereRaw('LOWER(name) = ?', [strtolower($args['announcement_type'])])->first();
                if ($type) {
                    $updates['announcement_type_id'] = $type->id;
                }
            }

            //Sanket v2.0 - department targeting; 0 = company-wide
            if (array_key_exists('department_id', $args)) {
                $updates['department_id'] = ($args['department_id'] === 0 || $args['department_id'] === null) ? null : (int) $args['department_id'];
            }

            if (empty($updates)) {
                return ['error' => 'No updates', 'message' => 'Provide at least one field to update (body, title, start_at, end_at, type, department_id).'];
            }

            $announcement->update($updates);

            return [
                'success'  => true,
                'message'  => "Announcement #{$id} updated successfully.",
                'updated_fields' => array_keys($updates),
                'announcement' => [
                    'id'         => $announcement->id,
                    'type'       => $announcement->type->name ?? 'General',
                    'start_date' => \Carbon\Carbon::parse($announcement->start_at)->format('M d, Y'),
                    'end_date'   => \Carbon\Carbon::parse($announcement->end_at)->format('M d, Y'),
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('UpdateAnnouncementTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }

    //Sanket v2.0 - safe Carbon date resolver shared with create
    private function resolveDate(?string $input, string $fallback): \Carbon\Carbon
    {
        if (!$input) {
            return \Carbon\Carbon::parse($fallback);
        }
        try {
            $parsed = \Carbon\Carbon::parse($input);
            if ($parsed->year < 2000 || $parsed->year > 2100) {
                return \Carbon\Carbon::parse($fallback);
            }
            return $parsed;
        } catch (\Exception $e) {
            return \Carbon\Carbon::parse($fallback);
        }
    }
}
