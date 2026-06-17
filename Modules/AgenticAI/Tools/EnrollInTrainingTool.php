<?php
namespace Modules\AgenticAI\Tools;
use Illuminate\Support\Facades\Auth;

class EnrollInTrainingTool extends BaseTool
{
    public function name(): string { return 'enroll_in_training'; }

    public function isSensitive(): bool
    {
        return true;
    }
    public function description(): string { return 'View training program details. Use when user wants to access training.'; }
    
    public function schema(): array
    {
        return ['type' => 'object', 'properties' => ['training_id' => ['type' => 'integer', 'description' => 'Training program ID']], 'required' => ['training_id']];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $id = $args['training_id'] ?? null;
        if (!$id) return ['error' => 'Missing ID', 'message' => 'Provide training ID.'];
        
        try {
            $training = \DB::table('trainings')->where('id', $id)->first();
            if (!$training) return ['error' => 'Not found', 'message' => 'Training program not found.'];
            
            return [
                'success' => true,
                'message' => "Training program: {$training->title}. This system provides direct access to training videos - no enrollment needed. You can view the training materials anytime.",
                'training' => [
                    'id' => $training->id,
                    'title' => $training->title,
                    'description' => $training->description,
                    'video_available' => !empty($training->video_path)
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('EnrollInTrainingTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
