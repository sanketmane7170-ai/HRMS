<?php
namespace Modules\AgenticAI\Tools;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubmitSelfReviewTool extends BaseTool
{
    public function name(): string { return 'submit_self_review'; }

    public function isSensitive(): bool
    {
        return true;
    }
    public function description(): string { return 'Submit self-review for performance. Use when user submits self-assessment.'; }
    public function schema(): array { return ['type' => 'object', 'properties' => ['review_id' => ['type' => 'integer'], 'comments' => ['type' => 'string'], 'rating' => ['type' => 'integer']], 'required' => ['review_id', 'comments']]; }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        if (empty($args['review_id']) || empty($args['comments'])) return ['error' => 'Missing fields', 'message' => 'Provide review ID and comments.'];
        
        try {
            DB::table('performance_reviews')->where('id', $args['review_id'])->where('user_id', $user->id)->update([
                'self_review' => $args['comments'],
                'self_rating' => $args['rating'] ?? null,
                'self_submitted_at' => now(),
                'updated_at' => now()
            ]);
            return ['success' => true, 'message' => 'Self-review submitted.'];
        } catch (\Exception $e) {
            \Log::error('SubmitSelfReviewTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
