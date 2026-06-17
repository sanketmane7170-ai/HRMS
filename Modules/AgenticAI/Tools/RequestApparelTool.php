<?php
namespace Modules\AgenticAI\Tools;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestApparelTool extends BaseTool
{
    public function name(): string { return 'request_apparel'; }

    public function isSensitive(): bool
    {
        return true;
    }
    public function description(): string { return 'Request uniform/apparel. Use when user needs uniform or clothing.'; }
    
    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'item_type' => ['type' => 'string', 'description' => 'Type of apparel (uniform, shirt, etc)'],
                'quantity' => ['type' => 'integer', 'description' => 'Number of items needed']
            ],
            'required' => ['item_type']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        if (empty($args['item_type'])) return ['error' => 'Missing type', 'message' => 'Provide item type.'];
        
        try {
            // Find apparel by name or use first available
            $apparel = DB::table('apparels')
                ->where('name', 'LIKE', "%{$args['item_type']}%")
                ->first();
            
            if (!$apparel) {
                // Try to find "Uniform" as default
                $apparel = DB::table('apparels')->where('name', 'LIKE', '%Uniform%')->first();
            }
            
            if (!$apparel) {
                $apparel = DB::table('apparels')->first();
            }
            
            if (!$apparel) {
                return ['error' => 'No apparel', 'message' => 'Apparel items not configured. Contact admin.'];
            }
            
            $id = DB::table('apparel_requests')->insertGetId([
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'user_id' => $user->id,
                'apparel_id' => $apparel->id,
                'number_of_apparel' => $args['quantity'] ?? 1,
                'status' => 0, // Pending
                'is_remove' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return [
                'success' => true,
                'message' => "Apparel request created. ID: {$id}",
                'request' => [
                    'id' => $id,
                    'item' => $apparel->name,
                    'quantity' => $args['quantity'] ?? 1
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('RequestApparelTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
