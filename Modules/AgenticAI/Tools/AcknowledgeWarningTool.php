<?php
namespace Modules\AgenticAI\Tools;
use Illuminate\Support\Facades\Auth;
use Modules\Warning\Entities\UserWarning;

class AcknowledgeWarningTool extends BaseTool
{
    public function name(): string { return 'acknowledge_warning'; }
    public function description(): string { return 'Acknowledge a warning. Use when user acknowledges disciplinary warning.'; }
    public function schema(): array { return ['type' => 'object', 'properties' => ['warning_id' => ['type' => 'integer']], 'required' => ['warning_id']]; }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $id = $args['warning_id'] ?? null;
        if (!$id) return ['error' => 'Missing ID', 'message' => 'Provide warning ID.'];
        
        try {
            $warning = UserWarning::where('id', $id)->where('user_id', $user->id)->first();
            if (!$warning) return ['error' => 'Not found', 'message' => 'Warning not found.'];
            $warning->update(['ack_status' => true, 'ack_datetime' => now()]);
            return ['success' => true, 'message' => "Warning {$id} acknowledged."];
        } catch (\Exception $e) {
            \Log::error('AcknowledgeWarningTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
