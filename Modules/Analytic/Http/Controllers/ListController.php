<?php
namespace Modules\Analytic\Http\Controllers;

use App\Models\Country;
use App\Models\Feature;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AirTicketSetting\Entities\AirTicketSetting;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Enums\LeaveStatus;
use App\Models\Setting;
use App\Models\UserDocument;
use Yajra\DataTables\Facades\DataTables;

class ListController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'dashboard');
    }

    public function upcominAnniversaryList(Request $request): View | JsonResponse
    {
        if ($request->ajax()) {
            $data = getUpcomingWorkAnniversariesQuery()
                ->with('department');
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('workDetail.joining_date', function ($user) {
                    return $user->workDetail?->joining_date->format(config('project.date_format'));
                })
                ->make(true);
        }

        return view('analytic::list.anniversary');
    }

    public function upcomingBirthdayList(Request $request): View | JsonResponse
    {
        if ($request->ajax()) {
            $data = getUpcomingBirthdayQuery()
                ->with('department');
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('profile.date_of_birth', function ($user) {
                    return $user->profile->date_of_birth->format(config('project.date_format'));
                })
                ->make(true);
        }

        return view('analytic::list.birthday');
    }

    public function upcomingProbationList(Request $request): View | JsonResponse
    {
        abort_if(auth()->user()->hasRole('employee'), 404);

        if ($request->ajax()) {
            $data = getProbationEndQuery()
                ->with('department');
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('workDetail.probation_end_date', function ($user) {
                    return $user->workDetail->probation_end_date->format(config('project.date_format'));
                })
                ->make(true);
        }

        return view('analytic::list.probation-end');
    }

    public function expiredDocumentList(Request $request): View | JsonResponse
    {
        abort_if(auth()->user()->hasRole('employee'), 404);
        if ($request->ajax()) {
            $data = getUserDocumentExpiredQuery()
                ->with(['user' => ['department']])
                ->reorder('expiry_date', 'asc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('type', function ($data) {
                    try {
                        return $data->type ? $data->type->name : '';
                    } catch (\Exception $e) {
                        // Handle any other exceptions
                        return 'NO TYPE';
                    }
                })
            // ->editColumn('expiry_date', function ($data) {
            //     return formatDate($data->expiry_date);
            // })
                ->editColumn('expiry_date', function ($data) {
                    return $data->expiry_date ? \Carbon\Carbon::parse($data->expiry_date)->format('d-m-Y') : '';
                })

                ->addColumn('action', function ($data) {
                    return createActionButton(route('backend.users.show', [$data->user_id, 'document']), 'view', 'btn-primary', 'fa fa-eye');
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('analytic::list.document-expired');
    }

    public function countryUserList(Request $request): View | JsonResponse
    {

        if ($request->ajax()) {
            $data = Country::whereHas('users')->select('id', 'name', 'code')
                ->withCount(['female_users', 'male_users', 'users']);
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }

        return view('analytic::list.country-user');
    }

    public function latestFeatureList(Request $request): View | JsonResponse
    {
        if ($request->ajax()) {
            // $data = getlatestFeatureList()->with('user');;
            $data = Feature::where('status', 1)->orderBy('id', 'DESC')->get();
            // dd($data);
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }

        return view('analytic::list.feature');
    }

    public function expiredFilemanagerList(Request $request): View | JsonResponse
    {

        abort_if(auth()->user()->hasRole('employee'), 404);
        if ($request->ajax()) {
            $data = getFilemanagerDocumentExpiredQuery();
            // dd($data);
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('file_type', function ($data) {
                    try {
                        return $data->file_type ? $data->file_type : '';
                    } catch (\Exception $e) {
                        // Handle any other exceptions
                        return 'NO TYPE';
                    }
                })
                ->editColumn('expiry_date', function ($data) {
                    return formatDate($data->expiry_date);
                })
                ->addColumn('action', function ($data) {
                    return createActionButton(route('backend.filemanager.file', [$data->department_id]), 'view', 'btn-primary', 'fa fa-eye');
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('analytic::list.filemanager-expired');
    }

    public function upcominLeaveList(Request $request): View | JsonResponse
    {
        if ($request->ajax()) {
            $today = Carbon::today();
            $data  = Leave::where('status', LeaveStatus::Approved)
                ->where(function ($query) use ($today) {
                    $query->where(function ($q) use ($today) {
                        // Current leave
                        $q->whereDate('start_date', '<=', $today)
                            ->whereDate('end_date', '>=', $today);
                    })->orWhere(function ($q) use ($today) {
                        // Upcoming leave
                        $q->whereDate('start_date', '>', $today);
                    });
                })->with('user.department')
                ->orderBy('start_date', 'asc')
                ->get();
            return DataTables::of($data)
                ->addIndexColumn()

                ->make(true);
        }

        return view('analytic::list.leave');
    }

    // public function upcominAirTicketList (Request $request): View|JsonResponse
    // {
    //     if ($request->ajax()) {
    //         $today = Carbon::today();

    //         $next60Days = Carbon::today()->addDays(60);

    //         $data = EMPAirTicket::whereBetween('date', [$today, $next60Days])
    //             ->with('user.department')
    //             ->orderBy('date', 'asc')
    //             ->get();

    //         return DataTables::of($data)
    //             ->addIndexColumn()

    //             ->make(true);
    //     }

    //     return view('analytic::list.airticket');
    // }

    // public function upcominAirTicketList(Request $request): View|JsonResponse
    // {
    //     if ($request->ajax()) {
    //         $today = Carbon::today();
    //         // $endOfMonth = Carbon::today()->endOfMonth();
    //         $endOfMonth = Carbon::today()->endOfMonth();
    //         $startOfMonth =Carbon::today()->startOfMonth();

    //         $upcomingTickets = collect();

    //         $users = User::query()
    //             ->whereDoesntHave('roles', function ($query) {
    //                 $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
    //             })
    //             ->where('status', User::STATUS_ACTIVE)
    //             ->with('department') // eager load
    //             ->get();
    //         $userIds = $users->pluck('id')->toArray();
    //         $detailsGrouped = \App\Models\AirTicketDetail::whereIn('user_id', $userIds)
    //             ->orderBy('created_at', 'desc')
    //             ->get()
    //             ->groupBy('user_id');
    //         foreach ($users as $user) {

    //             $workdetails    = $user->workDetail()->first();
    //             $profiledetails = $user->profile()->first();

    //             if (!$workdetails || !$profiledetails) {
    //                 continue;
    //             }

    //             $joindate   = Carbon::parse($workdetails->joining_date);
    //             $country    = $profiledetails->country_id;
    //             $air_ticket_count = $workdetails->air_ticket_count;

    //             $airtickeSetting        = AirTicketSetting::where('country', 0)->first();
    //             $airtickeCountrySetting = AirTicketSetting::where('country', $country)->first();

    //             $policymonth = 0;
    //             if ($workdetails->renewal_air_ticket === '1_year') {
    //                 $policymonth = 12;
    //             } elseif ($workdetails->renewal_air_ticket === '2_year') {
    //                 $policymonth = 24;
    //             } else {
    //                 $policymonth = $airtickeCountrySetting->request_after_months
    //                     ?? $airtickeSetting->request_after_months
    //                     ?? 0;
    //             }

    //             $quantity = $air_ticket_count > 0
    //                 ? $air_ticket_count
    //                 : ($airtickeCountrySetting?->request_limit_per_cycle ?? $airtickeSetting->request_limit_per_cycle ?? 0);

    //             if (!$airtickeSetting && !$airtickeCountrySetting) {
    //                 continue;
    //             }

    //             // $eligibleDate = $joindate->copy()->addMonths($policymonth);
    //             $eligibleDate = $joindate->copy();

    //             // Keep adding policy months until we reach the next eligible date
    //             while ($eligibleDate->lt($today)) {
    //                 $eligibleDate->addMonths($policymonth);
    //             }
    //             // if($user->id == 9)
    //             // dd($eligibleDate);

    //             // Only consider if upcoming date falls within this month
    //             if ($eligibleDate->between($today, $endOfMonth)) {
    //                 $airTicketDetails = $detailsGrouped[$user->id] ?? collect();
    //                 $allowanceAmount = $airtickeCountrySetting?->allowance_amount ?? $airtickeSetting->allowance_amount ?? 0;

    //                 $totalAmount = $allowanceAmount;
    //                 $detailsStr = $airTicketDetails->map(function ($d) use (&$totalAmount, $allowanceAmount) {
    //                     $calculatedAmount = round($allowanceAmount * $d->percentage / 100 * $d->qty, 2);
    //                     $totalAmount += $calculatedAmount;
    //                     return "{$d->title} (Qty-{$d->qty}, Per-{$d->percentage}%, Amount-$calculatedAmount)";
    //                 })->implode(', ');

    //                 $upcomingTickets->push((object)[
    //                     "user"     => $user,
    //                     "date"     => $eligibleDate->toDateString(),
    //                     "amount"   => $airtickeCountrySetting?->allowance_amount ?? $airtickeSetting->allowance_amount ?? 0,
    //                     "quantity" => $quantity,
    //                     "totalAmount" => round($totalAmount, 2),
    //                     "details" => $detailsStr,
    //                 ]);
    //             }
    //         }
    //         $upcomingTickets = $upcomingTickets->sortBy('date');

    //         return DataTables::of($upcomingTickets)
    //             ->addIndexColumn()
    //             ->addColumn('name', fn($row) => $row->user->name)
    //             ->addColumn('department', fn($row) => $row->user->department->name ?? '-')
    //             ->addColumn('date', fn($row) => formatDate($row->date, 'birth_date_format'))
    //             ->addColumn('amount', fn($row) => $row->amount)
    //             ->addColumn('quantity', fn($row) => $row->quantity)
    //             ->addColumn('totalAmount', fn($row) => $row->totalAmount)
    //             ->addColumn('details', fn($row) => $row->details)
    //             ->rawColumns(['name', 'department'])
    //             ->make(true);
    //     }

    //     return view('analytic::list.airticket');
    // }
    public function upcominAirTicketList(Request $request): View | JsonResponse
    {
        if ($request->ajax()) {
            $today      = Carbon::today();
            $endOfMonth = Carbon::today()->endOfMonth();

            $upcomingTickets = collect();

            $users = User::query()
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                })
                ->where('status', User::STATUS_ACTIVE)
                ->with('department')
                ->get();

            $userIds = $users->pluck('id')->toArray();

            $detailsGrouped = \App\Models\AirTicketDetail::whereIn('user_id', $userIds)
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('user_id');

            // Preload all existing air ticket records for quick lookup
            $existingTickets = \App\Models\EMPAirTicket::whereIn('user_id', $userIds)
                ->get()
                ->groupBy(fn($t) => $t->user_id . '|' . $t->date);

            foreach ($users as $user) {

                $workdetails    = $user->workDetail()->first();
                $profiledetails = $user->profile()->first();

                if (! $workdetails || ! $profiledetails) {
                    continue;
                }

                $joindate         = Carbon::parse($workdetails->joining_date);
                $country          = $profiledetails->country_id;
                $air_ticket_count = $workdetails->air_ticket_count;

                // $airtickeSetting        = AirTicketSetting::where('country', 0)->first();
                // $airtickeCountrySetting = AirTicketSetting::where('country', $country)->first();
                $userPolicyid = $workdetails->air_ticket_setting_id;
                if(!$userPolicyid == null){
                    $getPolicy = AirTicketSetting::where([['status', 1],['id',$userPolicyid]])->first();
                } else {
                    $getPolicy = AirTicketSetting::where([['status', 1],['country',$country]])->first();
                    if(!$getPolicy){
                        $getPolicy = AirTicketSetting::where([['status', 1],['country',0]])->first();
                    }
                }

                $policymonth = match ($workdetails->renewal_air_ticket) {
                    '1_year' => 12,
                    '2_year' => 24,
                    default  => $getPolicy->request_after_months ?? $getPolicy->request_after_months ?? 0,
                };

                $quantity = $air_ticket_count > 0
                    ? $air_ticket_count
                    : ($getPolicy?->request_limit_per_cycle ?? $getPolicy->request_limit_per_cycle ?? 0);

                if (! $getPolicy && ! $getPolicy) {
                    continue;
                }

                $eligibleDate = $joindate->copy();
                while ($eligibleDate->lt($today)) {
                    $eligibleDate->addMonths($policymonth);
                }
                if ($joindate->format('Y-m') == $today->format('Y-m')) {
                    continue;
                }
                if ($eligibleDate->between($today, $endOfMonth)) {

                    // find existing ticket for user and date
                    $key            = $user->id . '|' . $eligibleDate->toDateString();
                    $existingTicket = $existingTickets->get($key)?->first();

                    $status = $existingTicket->status ?? 'Pending';

                    $airTicketDetails = $detailsGrouped[$user->id] ?? collect();
                    $allowanceAmount  = $getPolicy?->allowance_amount ?? $getPolicy->allowance_amount ?? 0;

                    $totalAmount = $allowanceAmount;
                    $detailsStr  = $airTicketDetails->map(function ($d) use (&$totalAmount, $allowanceAmount) {
                        $calculatedAmount  = round($allowanceAmount * $d->percentage / 100 * $d->qty, 2);
                        $totalAmount      += $calculatedAmount;
                        return "{$d->title} (Qty-{$d->qty}, Per-{$d->percentage}%, Amount-$calculatedAmount)";
                    })->implode(', ');

                    $upcomingTickets->push((object) [
                        "user"        => $user,
                        "date"        => $eligibleDate->toDateString(),
                        "amount"      => $allowanceAmount,
                        "quantity"    => $quantity,
                        "totalAmount" => round($totalAmount, 2),
                        "details"     => $detailsStr,
                        "status"      => $status,
                    ]);
                }
            }

            $upcomingTickets = $upcomingTickets->sortBy('date');

            return DataTables::of($upcomingTickets)
                ->addIndexColumn()
                ->addColumn('name', fn($row) => $row->user->name)
                ->addColumn('department', fn($row) => $row->user->department->name ?? '-')
                ->addColumn('date', fn($row) => formatDate($row->date, 'birth_date_format'))
                ->addColumn('amount', fn($row) => $row->amount)
                ->addColumn('quantity', fn($row) => $row->quantity)
                ->addColumn('totalAmount', fn($row) => $row->totalAmount)
                ->addColumn('details', fn($row) => $row->details)
                ->addColumn('status', fn($row) => $row->status)
                ->rawColumns(['name', 'department', 'status'])
                ->make(true);
        }

        return view('analytic::list.airticket');
    }

    public function picCertificationExpiryList(Request $request)
    {

        abort_if(auth()->user()->hasRole('employee'), 404);
        if ($request->ajax()) {
           
            $data = UserDocument::with('user.department')->where('type', 'pic_certification')->orderBy('expiry_date','desc')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('expiry_date', function ($data) {
                    return formatDate($data->expiry_date);
                })
                ->make(true);
        }


        return view('analytic::list.pic-expired');
    }
    public function documentexpiringList(Request $request): View | JsonResponse
    {
        abort_if(auth()->user()->hasRole('employee'), 404);

        if ($request->ajax()) {

            $today      = Carbon::today();
            $next30Days = Carbon::today()->addDays(30);

            $data = getUserDocumentExpiredQuery()
                ->with(['user.department'])
                ->where(function ($q) use ($today, $next30Days) {
                    $q->whereDate('expiry_date', '<', $today)               // expired
                        ->orWhereBetween('expiry_date', [$today, $next30Days]); // upcoming 30 days
                })
                ->orderBy('expiry_date', 'asc');

            return DataTables::of($data)
                ->addIndexColumn()

                ->addColumn('status', function ($row) use ($today) {
                    return $row->expiry_date < $today
                        ? '<span class="badge bg-danger">Expired</span>'
                        : '<span class="badge bg-warning text-dark">Expiring Soon</span>';
                })

                ->editColumn('type', function ($row) {
                    return optional($row->type)->name ?? 'NO TYPE';
                })

                ->editColumn('expiry_date', function ($row) {
                    return $row->expiry_date
                        ? Carbon::parse($row->expiry_date)->format('d-m-Y')
                        : '';
                })

                ->addColumn('status', function ($row) use ($today) {
                    return $row->expiry_date < $today
                        ? '<span class="badge bg-danger">Expired</span>'
                        : '<span class="badge bg-warning text-dark">Expiring Soon</span>';
                })
                ->addColumn('action', function ($row) {
                    return createActionButton(
                        route('backend.users.show', [$row->user_id, 'document']),
                        'View',
                        'btn-primary',
                        'fa fa-eye'
                    );
                })
                ->rawColumns(['status', 'action'])

                ->make(true);
        }

        return view('analytic::list.document-expiring');
    }
}
