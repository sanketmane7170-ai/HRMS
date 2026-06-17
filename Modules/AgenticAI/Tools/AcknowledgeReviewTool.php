<?php
namespace Modules\AgenticAI\Tools;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AcknowledgeReviewTool extends BaseTool
{
    public function name(): string { return 'acknowledge_review'; }
    public function description(): string { return 'Acknowledge performance review. Use when user acknowledges their review.'; }
    public function schema(): array { return ['type' => 'object', 'properties' => ['review_id' => ['type' => 'integer']], 'required' => ['review_id']]; }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $id = $args['review_id'] ?? null;
        if (!$id) return ['error' => 'Missing ID', 'message' => 'Provide review ID.'];
        
        try {
            // Check if record exists in the pivot table for this user and review
            $exists = DB::table('performance_review_user')
                ->where('performance_review_id', $id)
                ->where('user_id', $user->id)
                ->exists();

            if (!$exists) {
                return [
                    'error' => 'Review not found',
                    'message' => "Performance review ID {$id} not found for your account."
                ];
            }

            // Update the pivot table
            DB::table('performance_review_user')
                ->where('performance_review_id', $id)
                ->where('user_id', $user->id)
                ->update([
                    'employee_response' => 'Accepted',
                    'responded_at' => now(),
                    'updated_at' => now()
                ]);

            return [
                'success' => true,
                'message' => "Performance review ID {$id} has been acknowledged/accepted successfully."
            ];
        } catch (\Exception $e) {
            \Log::error('AcknowledgeReviewTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'review_id' => $id
            ]);
            return [
                'error' => 'Failed to acknowledge review',
                'message' => $e->getMessage()
            ];
        }
    }
}
