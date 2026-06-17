<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserPromotionLetter;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserPromotionDefault extends Command implements ShouldQueue
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:promotion-default';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert default Service for users who do not have any record yet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('[' . now() . '] user:promotion-default started.');
        Log::info('user:promotion-default started');

        DB::beginTransaction();

        try {
            // Fetch users who do not have a promotion letter yet
            $users = User::whereNotIn('id', function ($query) {
                $query->select('user_id')->from('user_promotion_letters');
            })
                ->get();

            if ($users->isEmpty()) {
                $this->info('All users already have promotion letters.');
                Log::info('user:promotion-default', ['message' => 'All users already have promotion letters.']);
                return;
            }

            foreach ($users as $user) {
                UserPromotionLetter::create([
                    'user_id'                        => $user->id,
                    'letter_type_id'                 => 1, // set default if applicable
                    'date'                           => $user->workDetail?->joining_date ?? $user->created_at,
                    'old_designation_id'             => null,
                    'old_department_id'             => null,
                    'new_designation_id'             => $user->designation_id,
                    'new_department_id'             => $user->department_id,
                    'new_position'                   => optional($user->designation)->name,
                    'user_basic_salary'              =>  $user->salary ? $user->salary->basic : 0,
                    'user_transportation_allowances' => $user->transportation_allowances ?? 0,
                    'user_housing_allowances'        => $user->housing_allowances ?? 0,
                    'user_other_allowances'          => $user->other_allowances ?? 0,
                    'user_gross_salary'              => $user->gross_salary ?? 0,
                    'remarks'              => "Initial appointment",
                    'reason'              => "Hiring"
                ]);

                Log::info("Inserted default promotion letter for user ID: {$user->id}");
                $this->info("Inserted default promotion letter for user: {$user->name}");
            }

            DB::commit();
            $this->info('Default promotion letters inserted successfully.');
            Log::info('user:promotion-default completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('user:promotion-default failed', ['error' => $e->getMessage()]);
            $this->error('Error: ' . $e->getMessage());
        }

        $this->info('[' . now() . '] user:promotion-default ended.');
    }
}
