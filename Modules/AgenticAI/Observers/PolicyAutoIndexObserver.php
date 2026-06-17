<?php

namespace Modules\AgenticAI\Observers;

use Illuminate\Support\Facades\Log;
use Modules\PolicySetting\Entities\PolicySettings;
use Modules\AgenticAI\Jobs\IndexCompanyPolicies;
use Illuminate\Support\Facades\DB;

//Sanket v2.0 - auto-indexes policies into the AI vector store whenever they are created, updated, or deleted
class PolicyAutoIndexObserver
{
    //Sanket v2.0 - when a policy is created or updated, re-index it in the vector store
    public function saved(PolicySettings $policy): void
    {
        try {
            Log::info("PolicyAutoIndexObserver: Policy saved, queuing re-index", [
                'policy_id' => $policy->id,
                'policy_name' => $policy->name,
            ]);

            //Sanket v2.0 - dispatch async job to index this specific policy
            IndexSinglePolicyJob::dispatch($policy->id);
        } catch (\Exception $e) {
            Log::error("PolicyAutoIndexObserver: Failed to queue index", [
                'error' => $e->getMessage(),
                'policy_id' => $policy->id,
            ]);
        }
    }

    //Sanket v2.0 - when a policy is deleted, remove its chunks from the vector store
    public function deleted(PolicySettings $policy): void
    {
        try {
            DB::table('ai_documents')
                ->where('source_type', PolicySettings::class)
                ->where('source_id', $policy->id)
                ->delete();

            Log::info("PolicyAutoIndexObserver: Removed indexed chunks for deleted policy", [
                'policy_id' => $policy->id,
            ]);
        } catch (\Exception $e) {
            Log::error("PolicyAutoIndexObserver: Failed to remove index", [
                'error' => $e->getMessage(),
                'policy_id' => $policy->id,
            ]);
        }
    }
}
