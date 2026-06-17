<?php

namespace Modules\AgenticAI\Tools;

use Modules\Training\Entities\Training;
use Modules\Training\Entities\TrainingQuestion;
use Modules\Training\Entities\TrainingAnswer;
use Exception;

class TrainingQuizTool extends BaseTool
{
    public function name(): string
    {
        return 'training_quiz';
    }

    public function description(): string
    {
        return 'Manage training quizzes. List training programs, and create or list questions for a specific training.';
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
                    'enum' => ['list_trainings', 'list_questions', 'create_question'],
                    'description' => 'Action to perform.'
                ],
                'training_id' => ['type' => 'integer', 'description' => 'Required for list_questions/create_question.'],
                'question' => ['type' => 'string', 'description' => 'The question text.'],
                'options' => [
                    'type' => 'array', 
                    'items' => ['type' => 'string'],
                    'description' => 'Required for create_question. List of possible answers.'
                ],
                'correct_option' => ['type' => 'integer', 'description' => 'Index of the correct option (0-based).']
            ],
            'required' => ['action']
        ];
    }

    public function execute(array $args): mixed
    {
        $action = $args['action'];

        try {
            switch ($action) {
                case 'list_trainings':
                    $trainings = Training::all();
                    return ['trainings' => $trainings->toArray()];

                case 'list_questions':
                    if (empty($args['training_id'])) {
                        throw new Exception('training_id is required.');
                    }
                    $questions = TrainingQuestion::where('training_id', $args['training_id'])->get();
                    return ['questions' => $questions->toArray()];

                case 'create_question':
                    if (empty($args['training_id']) || empty($args['question']) || empty($args['options'])) {
                        throw new Exception('training_id, question, and options are required.');
                    }

                    $q = TrainingQuestion::create([
                        'training_id' => $args['training_id'],
                        'question' => $args['question'],
                    ]);

                    foreach ($args['options'] as $index => $option) {
                        TrainingAnswer::create([
                            'training_question_id' => $q->id,
                            'answer' => $option,
                            'is_correct' => ($index === ($args['correct_option'] ?? -1)) ? 1 : 0
                        ]);
                    }

                    return [
                        'success' => true,
                        'message' => "Question created successfully for training ID {$args['training_id']}.",
                        'question_id' => $q->id
                    ];

                default:
                    return ['error' => 'Invalid action.'];
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
