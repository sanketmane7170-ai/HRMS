<?php
namespace Modules\AgenticAI\Tools;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UpdateMyProfileTool extends BaseTool
{
    public function name(): string { return 'update_my_profile'; }

    public function isSensitive(): bool
    {
        return true;
    }
    public function description(): string { return 'Update user profile. Use when user wants to update their info.'; }
    public function schema(): array { return ['type' => 'object', 'properties' => ['phone' => ['type' => 'string'], 'email' => ['type' => 'string'], 'address' => ['type' => 'string']], 'required' => []]; }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        try {
            $updates = [];
            if (isset($args['phone'])) $updates['phone'] = $args['phone'];
            if (isset($args['email'])) $updates['email'] = $args['email'];
            if (isset($args['address'])) $updates['address'] = $args['address'];
            if (empty($updates)) return ['error' => 'No updates', 'message' => 'Provide at least one field.'];
            
            User::where('id', $user->id)->update($updates);
            return ['success' => true, 'message' => 'Profile updated.'];
        } catch (\Exception $e) {
            \Log::error('UpdateMyProfileTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
