<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;
use Modules\Training\Entities\Training;
use Illuminate\Support\Facades\Auth;

class GetTrainingProgramsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_training_programs';
    }

    public function description(): string
    {
        return 'Get a list of available training programs/courses.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'search' => [
                    'type' => 'string',
                    'description' => 'Optional keyword to search for programs (e.g., "Safety", "React").'
                ]
            ],
            'required' => [],
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $query = Training::query();
            
            if (isset($args['search'])) {
                $query->where('name', 'like', '%' . $args['search'] . '%')
                      ->orWhere('description', 'like', '%' . $args['search'] . '%');
            }

            $programs = $query->orderBy('created_at', 'desc')->get();

            return [
                'programs' => $programs->map(function($p) {
                    return [
                        'id' => $p->id,
                        'name' => $p->name,
                        'description' => $p->description,
                        'duration' => $p->duration,
                        'status' => $p->status
                    ];
                }),
                'count' => $programs->count()
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Failed to fetch training programs',
                'message' => $e->getMessage()
            ];
        }
    }
}
