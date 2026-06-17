<?php
namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Division;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\CompanyDocument\Entities\CompanyDocument;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Select2Controller extends Controller
{

    /**
     * return permission list
     */
    public function getUsers(Request $request): JsonResponse
    {
        $response = [];
        $query    = User::query()
            ->select('id', 'name', 'email', 'employee_id', 'department_id')
            ->where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [
                    User::ROLE_ADMIN,
                    User::ROLE_SUPER_ADMIN,
                ]);
            })
            ->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'Like', "%$request->search%")
                    ->orWhere('email', 'Like', "%$request->search%")
                    ->orWhereHas('department', function ($query) use ($request) {
                        return $query->where('name', 'Like', "%$request->search%");
                    });
            });
        $list = $query->get();

        foreach ($list as $key => $data) {
            $response[] = [
                "id"   => $data->id,
                "text" => "{$data->employee_id} - $data->name ",
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $response,
        ]);
    }
    public function getUsersWithAll(Request $request): JsonResponse
    {
        $response = [];
        $query    = User::query()
            ->select('id', 'name', 'email', 'employee_id', 'department_id')
            ->where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [
                    User::ROLE_ADMIN,
                    User::ROLE_SUPER_ADMIN,
                ]);
            })

            ->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'Like', "%$request->search%")
                    ->orWhere('email', 'Like', "%$request->search%")
                    ->orWhereHas('department', function ($query) use ($request) {
                        return $query->where('name', 'Like', "%$request->search%");
                    });
            });
        $list       = $query->get();
        $response[] = [
            "id"   => 0,
            "text" => "All ",
        ];

        foreach ($list as $key => $data) {
            $response[$key + 1] = [
                "id"   => $data->id,
                "text" => "{$data->employee_id} - $data->name ",
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $response,
        ]);
    }

    public function getUsersWithSelect(Request $request): JsonResponse
    {
        $response = [];
        $query    = User::query()
            ->select('id', 'name', 'email', 'employee_id', 'department_id')
            ->where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [
                    User::ROLE_ADMIN,
                    User::ROLE_SUPER_ADMIN,
                ]);
            })

            ->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'Like', "%$request->search%")
                    ->orWhere('email', 'Like', "%$request->search%")
                    ->orWhereHas('department', function ($query) use ($request) {
                        return $query->where('name', 'Like', "%$request->search%");
                    });
            });
        $list       = $query->get();
        $response[] = [
            "id"   => 0,
            "text" => "Select",
        ];

        foreach ($list as $key => $data) {
            $response[$key + 1] = [
                "id"   => $data->id,
                "text" => "{$data->employee_id} - $data->name ",
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $response,
        ]);
    }
    /**
     * return permission list
     */
    public function getPermissions(Request $request): JsonResponse
    {
        $response = [];
        $query    = Permission::query()
            ->select('id', 'name')
            ->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'Like', "%$request->search%");
            });
        $list = $query->get();
        foreach ($list as $data) {
            $response[] = [
                "id"   => $data->id,
                "text" => ucwords($data->name),
            ];
        }
        return response()->json([
            'success' => true,
            'data'    => $response,
        ]);
    }

    /**
     * return roles list
     */
    // public function getRoles(Request $request): JsonResponse
    // {

    //     $issuperadmin = auth()->user()->hasRole(User::ROLE_SUPER_ADMIN);
    //     dd($issuperadmin);

    //     $response = [];
    //     $query    = Role::query()
    //         ->select('id', 'name')
    //         ->whereNotIn('name', [
    //             // User::ROLE_ADMIN,
    //             User::ROLE_SUPER_ADMIN,
    //         ])
    //         ->when($request->search, function ($query) use ($request) {
    //             return $query->where('name', 'Like', "%$request->search%");
    //         });
    //     $list = $query->get();
    //     foreach ($list as $data) {
    //         $response[] = [
    //             "id"   => $data->id,
    //             "text" => ucwords($data->name),
    //         ];
    //     }
    //     return response()->json([
    //         'success' => true,
    //         'data'    => $response,
    //     ]);
    // }
    public function getRoles(Request $request): JsonResponse
    {
        $user = auth()->user();

        $isSuperAdmin = $user->hasRole(User::ROLE_SUPER_ADMIN);

        $query = Role::query()->select('id', 'name');

        if ($isSuperAdmin) {
            // Super Admin → hide only Super Admin
            $query->whereNotIn('name', [
                User::ROLE_SUPER_ADMIN,
            ]);
        } else {
            // Other users → hide Super Admin AND Admin
            $query->whereNotIn('name', [
                User::ROLE_SUPER_ADMIN,
                User::ROLE_ADMIN,
            ]);
        }

        // Search filter
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $roles = $query->get();

        $response = $roles->map(function ($role) {
            return [
                "id"   => $role->id,
                "text" => ucwords($role->name),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $response,
        ]);
    }

    /**
     * return department  list
     */
    public function getDepartments(Request $request): JsonResponse
    {
        $response = [];
        $query    = Department::query()
            ->select('id', 'name')
            ->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'Like', "%$request->search%");
            });
        $list = $query->get();

        foreach ($list as $key => $data) {
            $response[] = [
                "id"   => $data->id,
                "text" => $data->name,
            ];
        }
        return response()->json([
            'success' => true,
            'data'    => $response,
        ]);
    }
    public function getDepartmentsWithAll(Request $request): JsonResponse
    {
        $response = [];
        $query    = Department::query()
            ->select('id', 'name')
            ->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'Like', "%$request->search%");
            });
        $list       = $query->get();
        $response[] = [
            "id"   => 0,
            "text" => "All ",
        ];
        foreach ($list as $key => $data) {
            $response[$key + 1] = [
                "id"   => $data->id,
                "text" => $data->name,
            ];
        }
        return response()->json([
            'success' => true,
            'data'    => $response,
        ]);
    }

    public function getDepartmentsWithSelect(Request $request): JsonResponse
    {
        $response = [];
        $query    = Department::query()
            ->select('id', 'name')
            ->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'Like', "%$request->search%");
            });
        $list       = $query->get();
        $response[] = [
            "id"   => 0,
            "text" => "Select ",
        ];
        foreach ($list as $key => $data) {
            $response[$key + 1] = [
                "id"   => $data->id,
                "text" => $data->name,
            ];
        }
        return response()->json([
            'success' => true,
            'data'    => $response,
        ]);
    }

    /**
     * return department designation list
     */
    public function getDesignations(Request $request): JsonResponse
    {
        $response = [];
        $query    = Designation::query()
            ->select('id', 'name')
            ->when($request->department_id, function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    $q->where('department_id', $request->department_id)
                        ->orWhere('department_id', 0);
                });
            })
            ->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'Like', "%$request->search%");
            });
        $list = $query->get();
        foreach ($list as $data) {
            $response[] = [
                "id"   => $data->id,
                "text" => $data->name,
            ];
        }
        return response()->json([
            'success' => true,
            'data'    => $response,
        ]);
    }

    // public function fetchUsersByRoleId($roleId)
    // {
    //     $role  = Role::findOrFail($roleId);
    //     $users = User::role($role->name)->get(['id', 'name', 'email']);

    //     return response()->json(['users' => $users]);
    // }
    public function fetchUsersByRoleId(Request $request, $roleId)
    {
        $role = Role::findOrFail($roleId);

        $users = $role->users()
            ->where('status', User::STATUS_ACTIVE)
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            })
            ->select('id', 'name', 'email')
            ->limit(20)
            ->get();

        return response()->json([
            'users' => $users,
        ]);
    }

    public function getCompanyDocument(Request $request): JsonResponse
    {
        $response = [];
        $query    = CompanyDocument::query()
            ->select('id', 'legal_trade_name', 'license_number')
            ->when($request->search, function ($query) use ($request) {
                return $query->where('legal_trade_name', 'Like', "%$request->search%");
            });
        $list = $query->get();

        foreach ($list as $key => $data) {
            $response[] = [
                "id"   => $data->id,
                "text" => $data->legal_trade_name,
            ];
        }
        return response()->json([
            'success' => true,
            'data'    => $response,
        ]);
    }

    public function getCurrency(Request $request): JsonResponse
    {
        $response = [];
        $query    = Currency::query()
            ->select('id', 'country_name', 'currency_name', 'currency_code', 'symbol', 'exchange_rate')
            ->when($request->search, function ($query) use ($request) {
                return $query->where('currency_code', 'Like', "%$request->search%");
            });
        $list = $query->get();
        foreach ($list as $data) {
            $response[] = [
                "id"   => ucwords($data->currency_code) . '-' . ucwords($data->symbol),
                "text" => ucwords($data->currency_code) . '-' . ucwords($data->symbol),
            ];
        }
        return response()->json([
            'success' => true,
            'data'    => $response,
        ]);
    }

    // public function getUsersWithSelectByMonth(Request $request): JsonResponse
    // {
    //     $response = [];

    //     $query = User::query()
    //         ->select('users.id', 'users.name', 'users.email', 'users.employee_id', 'users.department_id')
    //         ->whereDoesntHave('roles', function ($query) {
    //             return $query->whereNotIn('name', [
    //     User::ROLE_ADMIN,
    //     User::ROLE_SUPER_ADMIN,
    // ]);
    //         })
    //         ->leftJoin('user_work_details', 'users.id', '=', 'user_work_details.user_id')
    //         ->when($request->search, function ($query) use ($request) {
    //             return $query->where(function ($subQuery) use ($request) {
    //                 $subQuery->where('users.name', 'like', "%{$request->search}%")
    //                     ->orWhere('users.email', 'like', "%{$request->search}%")
    //                     ->orWhereHas('department', function ($q) use ($request) {
    //                         $q->where('name', 'like', "%{$request->search}%");
    //                     });
    //             });
    //         })
    //         // ->when($request->filled('months'), function ($query) use ($request) {
    //         //     $cutoffDate = now()->subMonths((int) $request->months)->format('Y-m-d');
    //         //     $query->whereDate('user_work_details.joining_date', '>=', $cutoffDate);
    //         // });
    //         // ->when($request->filled('months'), function ($query) use ($request) {
    //         //     $months = (int) $request->months;

    //         //     $startDate = now()->subDays(($months + 1) * 30)->format('Y-m-d');
    //         //     $endDate = now()->subDays($months * 30)->format('Y-m-d');

    //         //     $query->whereBetween('user_work_details.joining_date', [$startDate, $endDate]);
    //         // });
    //         // ->when($request->filled('months') && $request->filled('label'), function ($query) use ($request) {
    //         //     $unit = strtolower($request->label);
    //         //     $value = (int) $request->months;

    //         //     if ($unit === 'year') {
    //         //         $startDate = now()->subDays(($value + 1) * 365)->format('Y-m-d'); // e.g. 2 years ago
    //         //         $endDate = now()->subDays($value * 365)->format('Y-m-d');         // e.g. 1 year ago
    //         //     } else {
    //         //         $startDate = now()->subDays(($value + 1) * 30)->format('Y-m-d');  // e.g. 60 days ago
    //         //         $endDate = now()->subDays($value * 30)->format('Y-m-d');          // e.g. 30 days ago
    //         //     }

    //         //     $query->whereBetween('user_work_details.joining_date', [$startDate, $endDate]);
    //         // });
    //         ->when($request->filled('months') && $request->filled('label'), function ($query) use ($request) {
    //             $unit = strtolower($request->label);
    //             $value = (int) $request->months;

    //             $query->whereRaw("
    //                 CASE
    //                     WHEN ? = 'year' THEN TIMESTAMPDIFF(YEAR, user_work_details.joining_date, CURDATE()) = ?
    //                     WHEN ? = 'month' THEN TIMESTAMPDIFF(MONTH, user_work_details.joining_date, CURDATE()) = ?
    //                     ELSE FALSE
    //                 END
    //             ", [$unit, $value, $unit, $value]);
    //         });

    //     $list = $query->get();

    //     $response[] = [
    //         "id" => 0,
    //         "text" => "Select"
    //     ];

    //     foreach ($list as $key => $data) {
    //         $response[] = [
    //             "id" => $data->id,
    //             "text" => "{$data->employee_id} - {$data->name}"
    //         ];
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'data' => $response
    //     ]);
    // }
    public function getUsersWithSelectByMonth(Request $request): JsonResponse
    {
        $response = [];

        $query = User::query()
            ->select('users.id', 'users.name', 'users.email', 'users.employee_id', 'users.department_id')
            ->where('users.status', 'active')
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [
                    User::ROLE_ADMIN,
                    User::ROLE_SUPER_ADMIN,
                ]);
            })

            ->leftJoin('user_work_details', 'users.id', '=', 'user_work_details.user_id')
            ->when($request->search, function ($query) use ($request) {
                return $query->where(function ($subQuery) use ($request) {
                    $subQuery->where('users.name', 'like', "%{$request->search}%")
                        ->orWhere('users.email', 'like', "%{$request->search}%")
                        ->orWhereHas('department', function ($q) use ($request) {
                            $q->where('name', 'like', "%{$request->search}%");
                        });
                });
            })
            ->when($request->filled('months') && $request->filled('label'), function ($query) use ($request) {
                $unit   = strtolower($request->label);
                $months = (int) $request->months;

                if ($unit === 'year') {
                    $minMonths = $months;
                    $maxMonths = $months + 12;
                } else {
                    $minMonths = $months;
                    $maxMonths = $months + 1;
                }

                $query->whereRaw("
                    TIMESTAMPDIFF(MONTH, user_work_details.joining_date, CURDATE()) >= ?
                    AND TIMESTAMPDIFF(MONTH, user_work_details.joining_date, CURDATE()) < ?
                ", [$minMonths, $maxMonths]);
            });

        $list = $query->get();

        $response[] = [
            "id"   => 0,
            "text" => "Select",
        ];

        foreach ($list as $data) {
            $response[] = [
                "id"   => $data->id,
                "text" => "{$data->employee_id} - {$data->name}",
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $response,
        ]);
    }
    public function getEmployeesByGrade(Request $request)
    {
        $criteriaId = $request->input('grade'); // This is actually score_criteria_id

        // Get grade title from ScoreCriterion
        $criteria = \Modules\PerformanceReview\Entities\ScoreCriterion::find($criteriaId);

        if (! $criteria) {
            return response()->json(['data' => []]); // Or return error
        }

        $grade = $criteria->title; // e.g., "G", "E"

        $users = \Modules\PerformanceReview\Entities\EmployeeKpiAssignment::with('user')
            ->where('grade', $grade)
            ->where('status', 'completed')
            ->get()
            ->pluck('user')
            ->filter() // remove null users
            ->unique('id')
            ->map(function ($user) {
                return ['id' => $user->id, 'text' => $user->name];
            })
            ->values();

        return response()->json(['data' => $users]);
    }

    /**
     * Get designation grade by designation ID
     */
    public function getDesignationGrade(Request $request): JsonResponse
    {
        $designationId = $request->input('designation_id');

        if (! $designationId) {
            return response()->json([
                'success' => false,
                'grade'   => '',
            ]);
        }

        $designation = Designation::find($designationId);

        return response()->json([
            'success' => true,
            'grade'   => $designation ? $designation->grade : '',
        ]);
    }

    public function getDivisions(Request $request): JsonResponse
    {
        $response = [];
        $query    = Division::query()
            ->select('id', 'name')
            ->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'Like', "%$request->search%");
            })
            ->when($request->department_id, function ($query) use ($request) {
                return $query->where('branch_id', $request->department_id)->orWhere('branch_id', 0);
            });
        $list = $query->get();

        foreach ($list as $key => $data) {
            $response[] = [
                "id"   => $data->id,
                "text" => $data->name,
            ];
        }
        return response()->json([
            'success' => true,
            'data'    => $response,
        ]);
    }
}
