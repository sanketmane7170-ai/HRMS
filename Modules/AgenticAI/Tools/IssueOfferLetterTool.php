<?php

namespace Modules\AgenticAI\Tools;

use Modules\Recruitment\Entities\Offer;
use Modules\Recruitment\Entities\Application;
use Exception;

class IssueOfferLetterTool extends BaseTool
{
    public function name(): string
    {
        return 'issue_offer_letter';
    }

    public function description(): string
    {
        return 'Generate and record a job offer for a successful candidate. The position and department are automatically inherited from the candidate application ID.';
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
                'application_id' => ['type' => 'integer', 'description' => 'ID of the candidate application.'],
                'salary' => ['type' => 'number'],
                'currency' => ['type' => 'string', 'default' => 'USD'],
                'start_date' => ['type' => 'string', 'description' => 'Format: YYYY-MM-DD'],
                'joining_date' => ['type' => 'string', 'description' => 'Format: YYYY-MM-DD'],
                'benefits' => ['type' => 'string', 'description' => 'Bonus, insurance, etc.'],
                'notes' => ['type' => 'string'],
            ],
            'required' => ['application_id', 'salary', 'start_date']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $application = Application::findOrFail($args['application_id']);
            
            $offer = Offer::create([
                'application_id' => $args['application_id'],
                'salary' => $args['salary'],
                'currency' => $args['currency'] ?? 'USD',
                'start_date' => $args['start_date'],
                'joining_date' => $args['joining_date'] ?? $args['start_date'],
                'status' => 'pending',
                'benefits' => $args['benefits'] ?? '',
                'notes' => $args['notes'] ?? '',
                'offer_date' => now(),
                'response_deadline' => now()->addDays(7),
                'created_by' => auth()->id(),
            ]);

            // Update application status
            $application->update(['status' => 'offered']);

            return [
                'success' => true,
                'message' => "Offer issued successfully to '{$application->name}' for position '{$args['position']}'. Status: Pending.",
                'offer_id' => $offer->id
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
