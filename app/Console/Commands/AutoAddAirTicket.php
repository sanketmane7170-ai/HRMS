<?php
namespace App\Console\Commands;

use App\Models\AirTicketDetail;
use App\Models\AirTicketRequest;
use App\Models\EMPAirTicket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\AirTicketSetting\Entities\AirTicketSetting;

class AutoAddAirTicket extends Command
{
    protected $signature   = 'app:auto-add-air-ticket';
    protected $description = 'Automatically add eligible user air tickets based on policy and joining date.';

    public function handle()
    {
        $this->info('[' . now() . '] AutoAddAirTicket: started.');
        Log::info("[AutoAddAirTicket] ========= Started: " . now() . " =========");

        $users = User::query()
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })
            ->where('status', User::STATUS_ACTIVE)
            ->get();
        Log::info("[AutoAddAirTicket] Total active users: " . $users->count());

        foreach ($users as $user) {
            Log::info("[AutoAddAirTicket] ----------------------------------------------------");
            Log::info("[AutoAddAirTicket] Processing user {$user->id} ({$user->name})");
            try {

                $workdetails    = $user->workDetail()->first();
                $profiledetails = $user->profile()->first();

                if (! $workdetails || ! $workdetails->joining_date) {
                    Log::warning("[AutoAddAirTicket] Skipping user {$user->id} ({$user->name}) — no work details or joining date.");
                    continue;
                }
                Log::info("[AutoAddAirTicket] Joining Date: {$workdetails->joining_date}");

                $date     = Carbon::now()->toDateString();
                $joindate = Carbon::parse(substr($workdetails->joining_date, 0, 10));
                $country  = $profiledetails->country_id ?? 0;
                Log::info("[AutoAddAirTicket] Fetching policies for country: $country");

                $airtickeSetting        = AirTicketSetting::where('country', 0)->first();
                $airtickeCountrySetting = AirTicketSetting::where('country', $country)->first();
                Log::info("[AutoAddAirTicket] Global Policy: " . json_encode($airtickeSetting));
                Log::info("[AutoAddAirTicket] Country Policy: " . json_encode($airtickeCountrySetting));
                // --- Check if both policies are missing ---
                if (! $airtickeSetting && ! $airtickeCountrySetting) {
                    Log::warning("[AutoAddAirTicket] Skipping user {$user->id} — No Global Policy & No Country Policy found.");
                    continue;
                }

                $air_ticket_count = $workdetails->air_ticket_count;
                $quantity         = $air_ticket_count > 0
                    ? $air_ticket_count
                    : ($airtickeCountrySetting->request_limit_per_cycle ?? $airtickeSetting->request_limit_per_cycle ?? 0);
                Log::info("[AutoAddAirTicket] Calculated Ticket Quantity: $quantity");

                $policymonth = match ($workdetails->renewal_air_ticket) {
                    '1_year' => 12,
                    '2_year' => 24,
                    default  => $airtickeCountrySetting->request_after_months ?? $airtickeSetting->request_after_months ?? 0,
                };
                Log::info("[AutoAddAirTicket] Policymonth = $policymonth");

                // $eligibleDate = $joindate->copy();
                $eligibleDate = $joindate->copy()->addMonths($policymonth);

                Log::info("[AutoAddAirTicket] Initial Eligible Date: " . $eligibleDate->toDateString());

                while ($eligibleDate->lt(Carbon::today())) {
                    if ($eligibleDate->year == Carbon::now()->year) {
                        break;
                    }

                    Log::info("[AutoAddAirTicket] Adding +$policymonth months to eligible date");

                    $eligibleDate->addMonths($policymonth);
                    Log::info("[AutoAddAirTicket] Updated Eligible Date: " . $eligibleDate->toDateString());
                }

                $allowanceAmount = $airtickeCountrySetting?->allowance_amount ?? $airtickeSetting->allowance_amount ?? 0;
                Log::info("[AutoAddAirTicket] Allowance Amount = $allowanceAmount");

                Log::info("[AutoAddAirTicket] Fetching previous ticket breakdown");
                // Gather previous ticket breakdowns (if any)
                $detailsGrouped = AirTicketDetail::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->groupBy('user_id');

                $airTicketDetails = $detailsGrouped[$user->id] ?? collect();
                Log::info("[AutoAddAirTicket] Previous Detail Count: " . $airTicketDetails->count());
                $totalAmount = $allowanceAmount;
                $detailsStr  = $airTicketDetails->map(function ($d) use (&$totalAmount, $allowanceAmount) {
                    $calculatedAmount  = round($allowanceAmount * $d->percentage / 100 * $d->qty, 2);
                    $totalAmount      += $calculatedAmount;
                    return "{$d->title} (Qty-{$d->qty}, Per-{$d->percentage}%, Amount-$calculatedAmount)";
                })->implode(', ');
                $userPolicyid = $workdetails->air_ticket_setting_id;
                if (! $userPolicyid == null) {
                    $getPolicy = AirTicketSetting::where([['status', 1], ['id', $userPolicyid]])->first();
                } else {
                    $getPolicy = AirTicketSetting::where([['status', 1], ['country', $country]])->first();
                }
                $isPolicyApply = false;
                if ($getPolicy) {
                    if ($getPolicy->allow_encashment == 1) {
                        $isPolicyApply = true;
                    } else {
                        $isPolicyApply = false;
                    }
                }
                // ✅ Create ticket if eligible
                if ($getPolicy && $isPolicyApply == true) {
                    if ($policymonth && $quantity > 0 && $allowanceAmount > 0 && $eligibleDate->isToday()) {
                        $latestTicket = EMPAirTicket::where('user_id', $user->id)
                            ->orderBy('id', 'desc')
                            ->where('status', 'Approved')
                            ->first();

                        $shouldCreate = false;
                        if (! $latestTicket) {
                            $shouldCreate = true;
                        } elseif (Carbon::parse($latestTicket->date)->diffInMonths(Carbon::now()) >= $policymonth) {
                            $shouldCreate = true;
                        }
                        // check if ticket already exists for this eligible date
                        $isaddrequest = AirTicketRequest::where('user_id', $user->id)->where('status', '!=', 'rejected')->latest()->first();
                        if ($isaddrequest) {
                            $currentDate      = Carbon::now()->format('Y-m-d');
                            $lastRequestDate  = Carbon::parse($isaddrequest->journey_date);
                            $nextEligibleDate = $lastRequestDate->copy()->addMonths($policymonth)->format('Y-m-d');

                            $addedRequest = AirTicketRequest::where('user_id', $user->id)
                                ->whereBetween('journey_date', [$lastRequestDate, $nextEligibleDate])
                                ->count();
                            $requestLimit     = $getPolicy->request_limit_per_cycle;
                            $userRequestLimit = $user->workDetail?->air_ticket_count;
                            if ($userRequestLimit > 0) {
                                $requestLimit = $userRequestLimit;
                            }
                            if ($addedRequest >= $requestLimit) {
                                $shouldCreate = false;
                            }
                            if ($currentDate < $nextEligibleDate) {
                                $shouldCreate = false;
                            }
                        }
                        if ($shouldCreate) {
                            EMPAirTicket::create([
                                'user_id'      => $user->id,
                                'date'         => $eligibleDate->toDateString(),
                                'amount'       => $allowanceAmount,
                                'quantity'     => $quantity,
                                'total_amount' => round($totalAmount, 2),
                                'details'      => $detailsStr,
                                'status'       => 'Pending', // ✅ default
                            ]);

                            Log::info("[AutoAddAirTicket] Air ticket created for user {$user->id} ({$user->name}). Date: {$eligibleDate->toDateString()}, Amount: {$totalAmount}");
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("[AutoAddAirTicket] Error processing user {$user->id} ({$user->name}): " . $e->getMessage());
                Log::error($e->getTraceAsString());
                continue;
            }
        }

        $this->info('[' . now() . '] AutoAddAirTicket: completed.');
        Log::info('[AutoAddAirTicket] Completed at ' . now());
    }
}
