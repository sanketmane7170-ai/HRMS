<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\PerformanceReview\Entities\EmployeeKpiAssignment;
use Modules\PerformanceReview\Entities\EmployeeKpiItem;
use Modules\PerformanceReview\Entities\KeyPerformanceIndicator;
use Modules\PerformanceReview\Entities\ReviewDuration;
use Carbon\Carbon;
use Modules\PerformanceReview\Entities\EmployeeKpiItemReview;
use Modules\PerformanceReview\Entities\KpiScoreLevel;

class AssignKpisToEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:assign-kpis-to-employees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign KPIs to employees based on completed duration.';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::query()
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })
            ->where('status', User::STATUS_ACTIVE)
            ->whereHas('workDetail', function ($query) {
                $query->whereNotNull('joining_date');
            })
            ->with('workDetail')
            ->get();

        foreach ($users as $user) {
            $joiningDate = $user->workDetail?->joining_date;
            if ($joiningDate) {
                $months_completed = Carbon::parse($joiningDate)->diffInMonths(now());

                $eligibleDuration = ReviewDuration::where('months', $months_completed)->first();

                if (!$eligibleDuration) continue;

                $alreadyAssigned = EmployeeKpiAssignment::where('user_id', $user->id)
                    ->where('duration_id', $eligibleDuration->id)
                    ->exists();

                if (! $alreadyAssigned) {
                    DB::beginTransaction();
                    try {
                        $assignment = EmployeeKpiAssignment::create([
                            'user_id' => $user->id,
                            'duration_id' => $eligibleDuration->id,
                            'due_date' => now(),
                            'status' => 'pending'
                        ]);

                        $kpis = KeyPerformanceIndicator::where('duration_id', $eligibleDuration->id)->get();

                        foreach ($kpis as $kpi) {
                            $item = EmployeeKpiItem::create([
                                'employee_kpi_assignment_id' => $assignment->id,
                                'key_performance_indicator_id' => $kpi->id
                            ]);

                            // // Get all review levels for this user's role
                            // $role = $user->roles()->first();
                            // if ($role) {
                            //     $levels = KpiScoreLevel::where('role_id', $role->id)
                            //         ->orderBy('step_number')
                            //         ->get();

                            //     foreach ($levels as $level) {
                            //         EmployeeKpiItemReview::create([
                            //             'employee_kpi_item_id' => $item->id,
                            //             'step_number' => $level->step_number,
                            //             'reviewer_id' => null, // To be filled later when reviewer evaluates
                            //             'reviewer_score' => 0, // Default score (can be null if needed)
                            //             'reviewer_remarks' => null,
                            //         ]);
                            //     }
                            // }
                        }


                        // Notify employee here (optional)

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("KPI assignment failed: " . $e->getMessage());
                    }
                }
            }
        }
    }
}
