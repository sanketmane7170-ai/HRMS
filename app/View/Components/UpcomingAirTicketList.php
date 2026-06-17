<?php

namespace App\View\Components;

use App\Models\EMPAirTicket;
use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Carbon\Carbon;
use Modules\AirTicketSetting\Entities\AirTicketSetting;

class UpcomingAirTicketList extends Component
{
    public $airticketlist;

    public function __construct()
    {
        $today = Carbon::today();

        $this->airticketlist = collect();

        // $endOfMonth = Carbon::today()->endOfMonth();
        $endOfMonth = Carbon::today()->endOfMonth();




        $users = User::query()
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })
            ->where('status', User::STATUS_ACTIVE)
            ->with('department') // eager load
            // ->limit(5)
            ->get();
        $userIds = $users->pluck('id')->toArray();
        $detailsGrouped = \App\Models\AirTicketDetail::whereIn('user_id', $userIds)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('user_id');
        foreach ($users as $user) {
            $workdetails    = $user->workDetail()->first();
            $profiledetails = $user->profile()->first();

            if (!$workdetails || !$profiledetails) {
                continue;
            }

            $joindate   = Carbon::parse($workdetails->joining_date);
            $country    = $profiledetails->country_id;
            $air_ticket_count = $workdetails->air_ticket_count;

            // $airtickeCountrySetting = AirTicketSetting::where('country', $country)->first();
            // $airtickeSetting        = AirTicketSetting::where('country', 0)->first();
            $userPolicyid = $workdetails->air_ticket_setting_id;
            if(!$userPolicyid == null){
                $getPolicy = AirTicketSetting::where([['status', 1],['id',$userPolicyid]])->first();
            } else {
                $getPolicy = AirTicketSetting::where([['status', 1],['country',$country]])->first();
                if(!$getPolicy){
                    $getPolicy = AirTicketSetting::where([['status', 1],['country',0]])->first();
                }
            }

            $policymonth = 0;
            if ($workdetails->renewal_air_ticket === '1_year') {
                $policymonth = 12;
            } elseif ($workdetails->renewal_air_ticket === '2_year') {
                $policymonth = 24;
            } else {
                $policymonth = $getPolicy->request_after_months
                    ?? $getPolicy->request_after_months
                    ?? 0;
            }

            $quantity = $air_ticket_count > 0
                ? $air_ticket_count
                : ($getPolicy?->request_limit_per_cycle ?? $getPolicy->request_limit_per_cycle ?? 0);


            if (!$getPolicy && !$getPolicy) {
                continue;
            }

            // $eligibleDate = $joindate->copy()->addMonths($policymonth);
            $eligibleDate = $joindate->copy();

            // Keep adding policy months until we reach the next eligible date
            while ($eligibleDate->lt($today)) {
                $eligibleDate->addMonths($policymonth);
            }
            if ($joindate->format('Y-m') == $today->format('Y-m')) {
                continue;
            }
            // if($user->id == 9)
            // dd($eligibleDate);

            // Only consider if upcoming date falls within this month
            if ($eligibleDate->between($today, $endOfMonth)) {
                $airTicketDetails = $detailsGrouped[$user->id] ?? collect();
                $allowanceAmount = $getPolicy?->allowance_amount ?? $getPolicy->allowance_amount ?? 0;
                

                $totalAmount = $allowanceAmount;
                $detailsStr = $airTicketDetails->map(function ($d) use (&$totalAmount, $allowanceAmount) {
                    $calculatedAmount = round($allowanceAmount * $d->percentage / 100 * $d->qty, 2);
                    $totalAmount += $calculatedAmount;
                    return "{$d->title} (Qty-{$d->qty}, Per-{$d->percentage}%, Amount-$calculatedAmount)";
                })->implode(', ');

                $this->airticketlist->push((object)[
                    "user"     => $user,
                    "date"     => $eligibleDate->toDateString(),
                    "amount"   => $allowanceAmount,
                    "quantity" => $quantity,
                    "totalAmount" => round($totalAmount, 2),
                ]);
            }
        }
        $this->airticketlist = $this->airticketlist->sortBy('date')->take(5);
    }

    public function render(): View|Closure|string
    {
        return view('components.air-ticket-list', [
            'airticketlist' => $this->airticketlist
        ]);
    }
}
