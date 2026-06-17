<?php
namespace Modules\AgenticAI\Tools;
use Illuminate\Support\Facades\Auth;

class CompleteTrainingTool extends BaseTool
{
    public function name(): string { return 'complete_training'; }
    public function description(): string { return 'Mark training as viewed. Use when user finishes watching training.'; }
    
    public function schema(): array
    {
        return ['type' => 'object', 'properties' => ['training_id' => ['type' => 'integer', 'description' => 'Training ID']], 'required' => ['training_id']];
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
                'message' => "Great! Training completion is tracked automatically when you watch the videos. Keep up the good learning!",
                'training' => [
                    'id' => $training->id,
                    'title' => $training->title
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('CompleteTrainingTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
