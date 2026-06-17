<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Announcement\Entities\Announcement;
use Modules\Announcement\Entities\AnnouncementType;
use App\Models\Department;

class CreateAnnouncementTool extends BaseTool
{
    public function name(): string
    {
        return 'create_announcement';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        //Sanket v2.0 - rich description so AI understands date inference, targeting, and type selection
        return 'Create a company announcement or notice. Use when the user wants to post an announcement, broadcast a message, or share news. '
            . 'You already know today\'s date from the system context — use it to compute relative dates (e.g. "next Monday", "this Friday", "in 2 weeks"). '
            . 'Always infer start_at and end_at from what the user says; do NOT default to today unless explicitly told to start today. '
            . 'Call get_announcement_meta FIRST if you are unsure which announcement_type to use or which department exists. '
            . 'Only Admin, HR, Super-Admin, or Managers can create announcements — employees cannot.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'description' => 'Short subject/title for the announcement (e.g. "Office Closure", "New Leave Policy"). Will be prepended to the body in bold.'
                ],
                'body' => [
                    'type' => 'string',
                    'description' => 'The main content/body of the announcement. Write it as a clear, professional message.'
                ],
                'start_at' => [
                    'type' => 'string',
                    'description' => 'Visibility start date in YYYY-MM-DD format. Compute from user\'s words using today\'s date in system context. If the user says "starting next Monday" resolve it to the actual date. If no start mentioned, default to today.'
                ],
                'end_at' => [
                    'type' => 'string',
                    'description' => 'Visibility end date in YYYY-MM-DD format. Compute from user\'s words (e.g. "for 1 week", "until Friday", "expires on the 30th"). If no end date mentioned, default to 7 days after start_at.'
                ],
                'announcement_type' => [
                    'type' => 'string',
                    'description' => 'Category name such as "General", "HR", "Policy", "Holiday", "Event". If unsure, call get_announcement_meta first to see available types. Defaults to "General".'
                ],
                'department_id' => [
                    'type' => 'integer',
                    'description' => 'Optional. Target a specific department by its ID. Omit or set null for a company-wide announcement. Call get_announcement_meta to see department IDs.'
                ]
            ],
            'required' => ['body']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();

        //Sanket v2.0 - role-based permission check
        if (!$user) {
            return ['error' => 'Authentication required', 'message' => 'You must be logged in to create announcements.'];
        }
        if (!$user->hasAnyRole(['admin', 'hr', 'super-admin', 'manager'])) {
            return ['error' => 'Permission denied', 'message' => 'Only Admin, HR, or Managers can create announcements. Your current role does not have this permission.'];
        }

        $rawBody = trim($args['body'] ?? '');
        if (empty($rawBody)) {
            return ['error' => 'Missing content', 'message' => 'Please provide the announcement content.'];
        }

        //Sanket v2.0 - prepend bold title if provided so the body is self-contained
        $title = trim($args['title'] ?? '');
        $body  = $title ? "<strong>{$title}</strong><br>{$rawBody}" : $rawBody;

        try {
            //Sanket v2.0 - smart date parsing: accept YYYY-MM-DD or fall back to Carbon parse
            $startAt = $this->resolveDate($args['start_at'] ?? null, now()->format('Y-m-d'));
            $endAt   = $this->resolveDate($args['end_at']   ?? null, $startAt->copy()->addDays(7)->format('Y-m-d'));

            //Sanket v2.0 - validate date order; swap silently if reversed
            if ($endAt->lt($startAt)) {
                [$startAt, $endAt] = [$endAt, $startAt];
            }

            //Sanket v2.0 - resolve announcement type by name, fallback to first available or auto-create "General"
            $typeName = trim($args['announcement_type'] ?? 'General');
            $announcementType = AnnouncementType::whereRaw('LOWER(name) = ?', [strtolower($typeName)])->first()
                ?? AnnouncementType::first()
                ?? AnnouncementType::create(['name' => 'General']);

            //Sanket v2.0 - optional department targeting
            $departmentId = isset($args['department_id']) ? (int) $args['department_id'] : null;
            if ($departmentId && !Department::find($departmentId)) {
                return ['error' => 'Invalid department', 'message' => "Department ID {$departmentId} not found. Call get_announcement_meta to see valid department IDs."];
            }

            $announcement = Announcement::create([
                'announcement_type_id' => $announcementType->id,
                'body'                 => $body,
                'start_at'             => $startAt->format('Y-m-d H:i:s'),
                'end_at'               => $endAt->format('Y-m-d H:i:s'),
                'user_id'              => $user->id,
                'department_id'        => $departmentId,
            ]);

            $audience = $departmentId
                ? 'department #' . $departmentId
                : 'company-wide';

            return [
                'success'  => true,
                'message'  => "Announcement created successfully! [{$typeName}] visible {$audience} from "
                    . $startAt->format('M d, Y') . " to " . $endAt->format('M d, Y') . ".",
                'announcement' => [
                    'id'         => $announcement->id,
                    'title'      => $title ?: '(no title)',
                    'type'       => $announcementType->name,
                    'audience'   => $audience,
                    'start_date' => $startAt->format('M d, Y'),
                    'end_date'   => $endAt->format('M d, Y'),
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('CreateAnnouncementTool failed', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return ['error' => 'Failed to create announcement', 'message' => $e->getMessage()];
        }
    }

    //Sanket v2.0 - safely parse a date string into a Carbon instance, returning $fallback on failure
    private function resolveDate(?string $input, string $fallback): \Carbon\Carbon
    {
        if (!$input) {
            return \Carbon\Carbon::parse($fallback);
        }
        try {
            $parsed = \Carbon\Carbon::parse($input);
            // Reject obviously bogus years (e.g. year 0001 from a model hallucination)
            if ($parsed->year < 2000 || $parsed->year > 2100) {
                return \Carbon\Carbon::parse($fallback);
            }
            return $parsed;
        } catch (\Exception $e) {
            return \Carbon\Carbon::parse($fallback);
        }
    }
}
