<?php

namespace Modules\AgenticAI\Tools;

use Modules\Attendance\Entities\Holiday;
use Exception;

class ManageHolidaysTool extends BaseTool
{
    public function name(): string
    {
        return 'manage_holidays';
    }

    public function description(): string
    {
        return 'Create, update, or list company holidays. Use this to maintain the holiday calendar. Recurring holidays (like New Year) should have is_recurring set to true.';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['create', 'update', 'delete', 'list'],
                    'description' => 'Action to perform.'
                ],
                'id' => [
                    'type' => 'integer',
                    'description' => 'Required for update/delete.'
                ],
                'data' => [
                    'type' => 'object',
                    'properties' => [
                        'start_date' => ['type' => 'string', 'description' => 'Format: YYYY-MM-DD'],
                        'end_date' => ['type' => 'string', 'description' => 'Format: YYYY-MM-DD'],
                        'detail' => ['type' => 'string', 'description' => 'Holiday name/description.'],
                        'is_recurring' => ['type' => 'boolean', 'description' => 'Whether it repeats annually.']
                    ]
                ]
            ],
            'required' => ['action']
        ];
    }

    public function execute(array $args): mixed
    {
        $action = $args['action'];
        $id = $args['id'] ?? null;
        $data = $args['data'] ?? [];

        try {
            switch ($action) {
                case 'create':
                    $holiday = Holiday::create([
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'] ?? $data['start_date'],
                        'detail' => $data['detail'],
                        'is_recurring' => ($data['is_recurring'] ?? false) ? Holiday::RECURRING : Holiday::NOT_RECURRING,
                        'created_by_id' => auth()->id(),
                    ]);
                    return ['success' => true, 'message' => "Holiday '{$holiday->detail}' created.", 'holiday' => $holiday->toArray()];

                case 'update':
                    if (!$id) throw new Exception('ID required for update.');
                    $holiday = Holiday::findOrFail($id);
                    if (isset($data['is_recurring'])) {
                        $data['is_recurring'] = $data['is_recurring'] ? Holiday::RECURRING : Holiday::NOT_RECURRING;
                    }
                    $holiday->update($data);
                    return ['success' => true, 'message' => "Holiday updated.", 'holiday' => $holiday->toArray()];

                case 'delete':
                    if (!$id) throw new Exception('ID required for delete.');
                    $holiday = Holiday::findOrFail($id);
                    $holiday->delete();
                    return ['success' => true, 'message' => "Holiday deleted."];

                case 'list':
                    $holidays = Holiday::orderBy('start_date', 'desc')->limit(20)->get();
                    return ['holidays' => $holidays->toArray()];

                default:
                    return ['error' => 'Invalid action.'];
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
