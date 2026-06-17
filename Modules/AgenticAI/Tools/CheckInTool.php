<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;
use Modules\Attendance\Services\CheckinService;
use Illuminate\Support\Facades\Log;

class CheckInTool extends BaseTool
{
    protected $checkinService;

    public function __construct()
    {
        $this->checkinService = new CheckinService();
    }

    public function name(): string
    {
        return 'check_in';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Perform a Check-In or Check-Out. It automatically detects the current state and toggles it. Use this when the user says "Clock in", "Check in", "Mark attendance", or "Clock out".';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => (object)[], 
            'required' => [],
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $checkin = $this->checkinService->performCheckInCheckOut();
            
            $type = strtoupper($checkin->type->value ?? $checkin->type);
            $time = $checkin->time;
            
            return [
                'success' => true,
                'message' => "Successfully recorded {$type} at {$time}.",
                'data' => [
                    'date' => $checkin->date,
                    'time' => $time,
                    'type' => $type
                ]
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Failed to record attendance',
                'message' => $e->getMessage()
            ];
        }
    }
}
