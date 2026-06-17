<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Setting;
use Carbon\Carbon;
use App\Models\EMPAirTicket;
use Modules\AirTicketSetting\Entities\AirTicketSetting;
use Modules\Payroll\Entities\UserPaySlip;
use Modules\Payroll\Entities\UserSalaryAllowance;

class AutoAddAirTicket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-add-air-ticket';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto add user air ticket';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('[' . now() . '] User Air-ticket started .');

        $users = User::query()
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })->where('status', User::STATUS_ACTIVE)
            ->get();
        $year = date('Y');
        $month = date('m');

        foreach ($users as $user) {
            $this->info('[' . now() . '] user: ' . json_encode($user));


            $date        = Carbon::now()->toDateString();
            $workdetails = $user->workDetail()->first();
            $profiledetails = $user->profile()->first();

            if ($workdetails) {

                $joindate = Carbon::parse($workdetails->joining_date);
                // try {
                //     $joindate = Carbon::createFromFormat('Y-m-d', $workdetails->joining_date);
                // } catch (\Exception $e) {
                //     dd($e->getMessage(), $workdetails->joining_date);
                //     continue; // skip invalid date like -0001-11-30
                // }
                $dateString = trim($workdetails->joining_date);

                // Keep only date part (first 10 chars if format is Y-m-d)
                if (preg_match('/^\d{4}-\d{2}-\d{2}/', $dateString, $matches)) {
                    $joindate = Carbon::parse($matches[0]); // only the date portion
                } else {
                    continue; // skip if not a valid date
                }






                $country = $profiledetails->country_id;
                $air_ticket_count = $workdetails->air_ticket_count;
                //
                $airtickeSetting = AirTicketSetting::where('country', 0)->first();
                $airtickeCountrySetting = AirTicketSetting::where('country', $country)->first();

                $policymonth = 0;
                $quantity = 0;
                if ($air_ticket_count > 0) {
                    $quantity = $air_ticket_count;
                } else {
                    $quantity = $airtickeCountrySetting->request_limit_per_cycle
                        ?? $airtickeSetting->request_limit_per_cycle
                        ?? 0;
                }
                if ($workdetails->renewal_air_ticket === '1_year') {
                    $policymonth = 12;
                } elseif ($workdetails->renewal_air_ticket === '2_year') {
                    $policymonth = 24;
                } else {
                    $policymonth = $airtickeCountrySetting->request_after_months
                        ?? $airtickeSetting->request_after_months
                        ?? 0;
                }
                $eligibleDate = $joindate->copy();


                // Keep adding policy months until we reach the next eligible date
                while ($eligibleDate->lt(Carbon::today())) {
                    if ($eligibleDate->year == Carbon::now()->year) {
                        break;
                    }
                    $eligibleDate->addMonths($policymonth);
                }

                // if ($user->id == 9) {
                //     dd($eligibleDate->toDateString());
                // }
                $amount = $airtickeCountrySetting?->allowance_amount ?? $airtickeSetting->allowance_amount ?? 0;

                if ($policymonth && $quantity > 0 && $amount > 0 && $eligibleDate->toDateString() == Carbon::today()->toDateString()) {
                    // if ($user->id == 48) {
                    //     dd($eligibleDate, $eligibleDate->toDateString(), $policymonth, $quantity, $amount);
                    // }

                    // if ($joindate->diffInMonths(Carbon::now()) == $policymonth && $eligibleDate==Carbon::today()) 
                    //{

                    $getTicket = EMPAirTicket::where('user_id', $user->id)
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($getTicket) {

                        $ticketdate = Carbon::parse($getTicket->date);
                        if ($ticketdate->diffInMonths(Carbon::now()) > $policymonth) {

                            $payroll = UserPaySlip::where([
                                'month_code' => $month,
                                'year' => $year,
                                'user_id' => $user->id,
                            ])->first();
                            if ($payroll) {
                                if ($payroll->is_close == 1) {
                                    $addTicket = EMPAirTicket::create([
                                        'user_id' => $user->id,
                                        'date' => $date,
                                        'amount' => $amount * $quantity,
                                        'quantity' => $quantity,
                                    ]);
                                    // add in allowance
                                    $nextMonthTimestamp = strtotime('+1 month');
                                    $allowance = UserSalaryAllowance::create([
                                        'title' => 'Air ticket allowance',
                                        'amount' => $amount * $quantity,
                                        'user_id' => $user->id,
                                        'allowance_type' => 'fixed',
                                        'salary_id' => 0, //$salaryid->id,
                                        'percentage_amount' => 0.00,
                                        'date' => now()->toDateString(),
                                        'month_code' => date('m', strtotime('+1 month')),
                                        'year' => date('Y', $nextMonthTimestamp),
                                        'is_fixed_for_current_month' => 1,
                                    ]);
                                } else {
                                    $addTicket = EMPAirTicket::create([
                                        'user_id' => $user->id,
                                        'date' => $date,
                                        'amount' => $amount * $quantity,
                                        'quantity' => $quantity,
                                    ]);
                                    // add in allowance
                                    $allowance = UserSalaryAllowance::create([
                                        'title' => 'Air ticket allowance',
                                        'amount' => $amount * $quantity,
                                        'user_id' => $user->id,
                                        'allowance_type' => 'fixed',
                                        'salary_id' => $payroll->id,
                                        'percentage_amount' => 0.00,
                                        'date' => now()->toDateString(),
                                        'month_code' => date('m'),
                                        'year' => date('Y'),
                                        'is_fixed_for_current_month' => 1,
                                    ]);
                                }
                            } else {
                                $addTicket = EMPAirTicket::create([
                                    'user_id' => $user->id,
                                    'date' => $date,
                                    'amount' => $amount * $quantity,
                                    'quantity' => $quantity,
                                ]);
                                // add in allowance
                                $allowance = UserSalaryAllowance::create([
                                    'title' => 'Air ticket allowance',
                                    'amount' => $amount * $quantity,
                                    'user_id' => $user->id,
                                    'allowance_type' => 'fixed',
                                    'salary_id' => 0, //$payroll->id,
                                    'percentage_amount' => 0.00,
                                    'date' => now()->toDateString(),
                                    'month_code' => date('m'),
                                    'year' => date('Y'),
                                    'is_fixed_for_current_month' => 1,
                                ]);
                            }
                        }
                    } else {


                        $payroll = UserPaySlip::where([
                            'month_code' => $month,
                            'year' => $year,
                            'user_id' => $user->id,
                        ])->first();

                        if ($payroll) {

                            if ($payroll->is_close == 1) {
                                $addTicket = EMPAirTicket::create([
                                    'user_id' => $user->id,
                                    'date' => $date,
                                    'amount' => $amount * $quantity,
                                    'quantity' => $quantity,
                                ]);
                                // add in allowance
                                $nextMonthTimestamp = strtotime('+1 month');

                                $allowance = UserSalaryAllowance::create([
                                    'title' => 'Air ticket allowance',
                                    'amount' => $amount * $quantity,
                                    'user_id' => $user->id,
                                    'allowance_type' => 'fixed',
                                    'salary_id' => 0, //$salaryid->id,
                                    'percentage_amount' => 0.00,
                                    'date' => now()->toDateString(),
                                    'month_code' => date('m', strtotime('+1 month')),
                                    'year' => date('Y', $nextMonthTimestamp),
                                    'is_fixed_for_current_month' => 1,
                                ]);
                            } else {
                                $addTicket = EMPAirTicket::create([
                                    'user_id' => $user->id,
                                    'date' => $date,
                                    'amount' => $amount * $quantity,
                                    'quantity' => $quantity,
                                ]);
                                // add in allowance
                                $allowance = UserSalaryAllowance::create([
                                    'title' => 'Air ticket allowance',
                                    'amount' => $amount * $quantity,
                                    'user_id' => $user->id,
                                    'allowance_type' => 'fixed',
                                    'salary_id' => $payroll->id,
                                    'percentage_amount' => 0.00,
                                    'date' => now()->toDateString(),
                                    'month_code' => date('m'),
                                    'year' => date('Y'),
                                    'is_fixed_for_current_month' => 1,
                                ]);
                            }
                        } else {

                            $addTicket = EMPAirTicket::create([
                                'user_id' => $user->id,
                                'date' => $eligibleDate->toDateString(),
                                'amount' => $amount * $quantity,
                                'quantity' => $quantity,
                            ]);
                            // add in allowance
                            $allowance = UserSalaryAllowance::create([
                                'title' => 'Air ticket allowance',
                                'amount' => $amount * $quantity,
                                'user_id' => $user->id,
                                'allowance_type' => 'fixed',
                                'salary_id' => 0, //$payroll->id,
                                'percentage_amount' => 0.00,
                                'date' => $eligibleDate->toDateString(),
                                'month_code' => date('m'),
                                'year' => date('Y'),
                                'is_fixed_for_current_month' => 1,
                            ]);
                        }
                    }
                    //}
                }
            }
        }
        //end
        $this->info('[' . now() . '] User Air-ticket successfully added.');
    }
}
