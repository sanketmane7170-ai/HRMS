<?php

namespace App\Console\Commands;

use App\Models\UserPromotionLetter;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UserPromotionUpdate extends Command implements ShouldQueue
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:promotion-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update user designation based on promotion letters with today\'s date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('[' . now() . '] user:promotion-update started.');
        Log::info('user:promotion-update started');

        $today = now()->toDateString();

        // Fetch promotion letters effective today
        $promotions = UserPromotionLetter::whereDate('date', $today)
            ->with(['user'])
            ->get();

        if ($promotions->isEmpty()) {
            $this->info('No promotions found for today (' . $today . ').');
            Log::info('user:promotion-update', ['message' => 'No promotions found for today']);
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($promotions as $promotion) {
                $user = $promotion->user;
                if (!$user) {
                    Log::warning("Promotion letter #{$promotion->id} has no associated user.");
                    continue;
                }

                $oldDesignationId = $user->designation_id;
                $newDesignationId = $promotion->new_designation_id;

                $user->update([
                    'designation_id' => $newDesignationId
                ]);

                Log::info("Updated user {$user->name} ({$user->id}) designation from {$oldDesignationId} to {$newDesignationId}");

                $this->info("Updated user: {$user->name} → New Designation ID: {$newDesignationId}");
            }

            DB::commit();
            $this->info('User promotions updated successfully.');
            Log::info('user:promotion-update completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('user:promotion-update failed', ['error' => $e->getMessage()]);
            $this->error('Error: ' . $e->getMessage());
        }

        $this->info('[' . now() . '] user:promotion-update ended.');
    }
}
