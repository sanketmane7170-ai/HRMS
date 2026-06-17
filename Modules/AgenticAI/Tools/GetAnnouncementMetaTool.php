<?php

namespace Modules\AgenticAI\Tools;

use Modules\Announcement\Entities\AnnouncementType;
use App\Models\Department;

class GetAnnouncementMetaTool extends BaseTool
{
    public function name(): string
    {
        return 'get_announcement_meta';
    }

    public function description(): string
    {
        //Sanket v2.0 - AI calls this before create/update to discover valid types and departments
        return 'Returns the list of available announcement types and departments. '
            . 'Call this before create_announcement or update_announcement when you need to pick a type name '
            . 'or a department_id. This avoids guessing.';
    }

    public function schema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [],
            'required'   => []
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - return announcement types and departments for AI context
            $types = AnnouncementType::select('id', 'name')->orderBy('name')->get()->toArray();
            $departments = Department::select('id', 'name')->orderBy('name')->get()->toArray();

            return [
                'announcement_types' => $types ?: [['id' => null, 'name' => 'No types found — "General" will be auto-created']],
                'departments'        => $departments ?: [['id' => null, 'name' => 'No departments found']],
                'note'               => 'Use the name for announcement_type and the id for department_id when calling create_announcement or update_announcement.',
            ];
        } catch (\Exception $e) {
            \Log::error('GetAnnouncementMetaTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to fetch metadata', 'message' => $e->getMessage()];
        }
    }
}
