<?php

use App\Enums\Gender;
use App\Models\Country;
use App\Models\Department;
use App\Models\Feature;
use App\Models\User;
use App\Models\UserDocument;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Modules\AirTicketSetting\Entities\AirTicketSetting;
use Modules\FileManager\Entities\FileManager;

if (! function_exists('getCountryList')) {
    function getCountryList()
    {
        $result = Cache::remember("countries", 86400, function () {
            return Country::get(['id', 'name'])->keyBy('id');
        });

        return $result;
    }
}

if (! function_exists('getCountryListwithFlag')) {
    function getCountryListwithFlag()
    {
        $countries = Country::get()->keyBy('id');
        return $countries->map(function ($country) {
            return [
                'id'       => $country->id,
                'name'     => $country->name,
                'code'     => $country->code,
                'flag_url' => "https://flagcdn.com/w40/" . strtolower($country->code) . ".png",
            ];
        });
    }
}

if (! function_exists('canPerform')) {
    function canPerform($permission)
    {
        if (! hasPermission($permission)) {
            abort(403, __trans('permission_denied'));
        }
    }
}

if (! function_exists('hasPermission')) {
    function hasPermission($permission)
    {
        if (! auth()->check()) {
            return false;
        }

        return auth()->user()->can($permission);
    }
}

if (! function_exists('formatDate')) {
    function formatDate($date, $type = "date_format")
    {
        return now()->parse($date)->format(config("project.$type"));
    }
}

if (! function_exists('employeeCount')) {
    function employeeCount()
    {
        $count = User::where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                return $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })->count();
        return $count;
    }
}

if (! function_exists('femaleEmployeeCount')) {
    function femaleEmployeeCount()
    {
        $count = User::where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                return $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })->whereHas('profile', function ($query) {
            $query->where('gender', Gender::Female);
        })->count();

        return $count;
    }
}

if (! function_exists('maleEmployeeCount')) {
    function maleEmployeeCount()
    {
        $count = User::where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                return $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })->whereHas('profile', function ($query) {
            $query->where('gender', Gender::Male);
        })->count();

        return $count;
    }
}

if (! function_exists('getCountryUserList')) {
    function getCountryUserList($array = false)
    {
        $list = Country::select('name')->withCount('users')->whereHas('users')
            ->orderByDesc('users_count')->take(10)->get();

        if ($array) {
            $array = [];
            foreach ($list as $key => $item) {
                $array[] = "$item->name , $item->users_count";
            }

            return $array;
        }
        return $list;
    }
}

if (! function_exists('getUpcomingBirthdayQuery')) {
    function getUpcomingBirthdayQuery($department = null)
    {
        $query = User::where('status', User::STATUS_ACTIVE)
            ->withWhereHas('profile', function ($query) {
                return $query->select('date_of_birth', 'user_id')->birthdayThisMonth();
            })
            ->when($department, function ($query) use ($department) {
                return $query->where('department_id', $department);
            })
            ->orderByRaw('(SELECT DATE_FORMAT(date_of_birth,"%d") FROM user_profiles WHERE users.id = user_profiles.user_id)');

        return $query;
    }
}

// if (! function_exists('getUpcomingWorkAnniversariesQuery')) {
//     function getUpcomingWorkAnniversariesQuery($department = null)
//     {
//         $query = User::where('status', User::STATUS_ACTIVE)
//             ->withWhereHas('workDetail', function ($query) {
//                 return $query->select('joining_date', 'user_id')->anniversaryThisMonth();
//             })
//             ->when($department, function ($query) use ($department) {
//                 return $query->where('department_id', $department);
//             })
//             ->orderByRaw('(SELECT DATE_FORMAT(joining_date,"%d") FROM user_work_details WHERE users.id = user_work_details.user_id)');

//         return $query;
//     }
// }

if (! function_exists('getUpcomingWorkAnniversariesQuery')) {
    function getUpcomingWorkAnniversariesQuery($department = null)
    {
        $query = User::where('status', User::STATUS_ACTIVE)
            ->withWhereHas('workDetail', function ($query) {
                $query->select('joining_date', 'user_id')
                    ->whereMonth('joining_date', now()->month)
                    ->whereYear('joining_date', '<', now()->year); // exclude current year joiners
            })
            ->when($department, function ($query) use ($department) {
                $query->where('department_id', $department);
            })
            ->orderByRaw('(SELECT DATE_FORMAT(joining_date,"%d") FROM user_work_details WHERE users.id = user_work_details.user_id)');

        return $query;
    }
}

if (! function_exists('getProbationEndQuery')) {
    function getProbationEndQuery($days = 40)
    {
        $query = User::where('status', User::STATUS_ACTIVE)
            ->withWhereHas('workDetail', function ($query) use ($days) {
                $query->probationEndsIn($days);
            })
            ->orderBy(function ($query) {
                $query->select('probation_end_date')
                    ->from('user_work_details')
                    ->whereColumn('users.id', 'user_work_details.user_id')
                    ->limit(1);
            });

        return $query;
    }
}

if (! function_exists('getUserDocumentExpiredQuery')) {
    function getUserDocumentExpiredQuery()
    {
        // $query = UserDocument::with('user')
        //     ->expired()->orderBy('expiry_date');

        // return $query;
        $now  = Carbon::now();
        $days = 90;
        if (! empty(getSetting('document_expiry_days'))) {
            $days = getSetting('document_expiry_days');
        }
        // $nextThirtyDays = Carbon::now()->addDays( $days);
        // $query = UserDocument::with(['user' => function ($query) {
        //     $query->where('status', User::STATUS_ACTIVE);
        // }])
        // ->whereHas('user', function ($query) {
        //     $query->where('status', User::STATUS_ACTIVE);
        // })
        // ->whereBetween('expiry_date', [$now, $nextThirtyDays])
        // ->orderBy('expiry_date', 'asc');

        $nextThirtyDays = Carbon::now()->addDays($days);
        $now            = Carbon::now();

        $query = UserDocument::with(['user' => function ($query) {
            $query->where('status', User::STATUS_ACTIVE);
        }])
            ->whereHas('user', function ($query) {
                $query->where('status', User::STATUS_ACTIVE);
            })
            ->where(function ($query) use ($now, $nextThirtyDays) {
                $query->whereBetween('expiry_date', [$now, $nextThirtyDays]) // Documents expiring in 30 days
                    ->orWhere('expiry_date', '<', $now);                         // Expired documents
            })
            ->orderBy('expiry_date', 'asc');

        return $query;
    }
}

if (! function_exists('getRoleUsers')) {
    function getRoleUsers(array $roles)
    {
        $users = User::role($roles)->get();

        return $users;
    }
}

if (! function_exists('getalldepartments')) {
    function getalldepartments()
    {
        $data = Department::with('user_count')->select('name', 'id', 'logo')->get();

        return $data;
    }
}

if (! function_exists('getlatestFeatureList')) {
    function getFeatureQuery()
    {
        $query = Feature::where('status', 1)
            ->orderByRaw('id desc');

        return $query;
    }
}

if (! function_exists('getFilemanagerDocumentExpiredQuery')) {
    function getFilemanagerDocumentExpiredQuery()
    {
        $query = FileManager::select('id', 'title', 'expiry_days', 'expiry_date', 'file_type', 'employee_id', 'department_id')
            ->with(['employee', 'department'])
            ->whereNotNull('expiry_date')
            ->whereNotNull('expiry_days')
            ->whereDate('expiry_date', '>=', Carbon::today())
            ->get();

        $query = $query->filter(function ($data) {
            $expiryDate = Carbon::parse($data->expiry_date);

            $newExpiryDate = $expiryDate->subDays($data->expiry_days)->toDateString();
            // dd($newExpiryDate);
            return $newExpiryDate <= now()->toDateString();
        });
        // dd($query);

        // $now = Carbon::now();
        // $nextThirtyDays = Carbon::now()->addDays(90);
        // $query = UserDocument::with(['user' => function ($query) {
        //     $query->where('status', User::STATUS_ACTIVE);
        // }])
        // ->whereHas('user', function ($query) {
        //     $query->where('status', User::STATUS_ACTIVE);
        // })
        // ->whereBetween('expiry_date', [$now, $nextThirtyDays])
        // ->orderBy('expiry_date', 'asc');

        return $query;
    }

}
if (! function_exists('getAirTicketSettingsList')) {
    function getAirTicketSettingsList()
    {
        $query = AirTicketSetting::where('status', 1)->get();

        return $query;
    }
}

if (! function_exists('getRoleBasedTree')) {
    function getRoleBasedTree()
    {
        // Fetch all roles with their users
        $roles = \Spatie\Permission\Models\Role::with(['users' => function ($query) {
            $query->with(['designation', 'profile']);
        }])->orderBy('priority', 'asc')->get();

        $tree = [];
        foreach ($roles as $role) {
            // Create a root node for each role
            $roleNode = [
                'id'    => $role->id,
                'name'  => $role->name,
                'users' => [],
            ];

            // Add each user under the role
            foreach ($role->users as $user) {
                $designation         = $user->designation ? $user->designation->name : 'No Designation';
                $roleNode['users'][] = [
                    'id'            => $user->id,
                    'name'          => $user->name,
                    'designation'   => $designation,
                    'profile_image' => $user->profile_image ? $user->profile_image : $user->profile_image_url,
                ];
            }

            $tree[] = $roleNode;
        }

        return $tree;
    }
}

if (! function_exists('getUpcomingDocumentExpiringQuery')) {
    function getUpcomingDocumentExpiringQuery($department = null)
    {
        $today      = now();
        $next30Days = now()->addDays(30);

        $today      = Carbon::today();
        $next30Days = Carbon::today()->addDays(30);

        $query = getUserDocumentExpiredQuery()
            ->with(['user.department'])
            ->where(function ($q) use ($today, $next30Days) {
                $q->whereDate('expiry_date', '<', $today)               // expired
                    ->orWhereBetween('expiry_date', [$today, $next30Days]); // upcoming 30 days
            })
            ->orderBy('expiry_date', 'asc');

        return $query;
    }
}

if (! function_exists('getProbationEndQuery')) {
    function getProbationEndQuery($days = 40)
    {
        // Adjust status constant if needed (e.g., 'active' or 1)
        $query = \App\Models\User::where('status', 'active')
            ->withWhereHas('workDetail', function ($query) use ($days) {
                // Ensure the 'ProbationEndsIn' scope is added to UserWorkDetail model
                $query->probationEndsIn($days);
            })
            ->orderBy(function ($query) {
                $query->select('probation_end_date')
                    ->from('user_work_details')
                    ->whereColumn('users.id', 'user_work_details.user_id')
                    ->limit(1);
            });
        return $query;
    }
}

if (! function_exists('getNoticePeriodQuery')) {
    function getNoticePeriodQuery()
    {
        // Only works if Resignation module is enabled
        if (! isModuleEnabled('Resignation')) {
            return collect([]);
        }

        // Returns approved resignations ordered by last working date
        return \Modules\Resignation\Entities\Resignation::where('status', 'approved')
            ->with(['employee'])
            ->orderBy('approved_last_working_date', 'asc');
    }
}
if (! function_exists('getUpcomingDocumentExpiringQuery')) {
    function getUpcomingDocumentExpiringQuery($department = null)
    {
        $today      = now();
        $next30Days = now()->addDays(30);

        $today      = Carbon::today();
        $next30Days = Carbon::today()->addDays(30);

        $query = getUserDocumentExpiredQuery()
            ->with(['user.department'])
            ->where(function ($q) use ($today, $next30Days) {
                $q->whereDate('expiry_date', '<', $today)               // expired
                    ->orWhereBetween('expiry_date', [$today, $next30Days]); // upcoming 30 days
            })
            ->orderBy('expiry_date', 'asc');

        return $query;
    }
}
