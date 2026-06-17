<?php

namespace Modules\PerformanceReview\Http\Controllers;

use Illuminate\Routing\Controller;
use Exception;
use Illuminate\Http\Request;
use Modules\PerformanceReview\Entities\KpiScoreLevel;
use Spatie\Permission\Models\Role;

class KpiScoreLevelController extends Controller
{

    public function __construct()
    {
        view()->share('activeLink', 'KPI Score Level');
    }
    public function index()
    {
        $roles = Role::whereNotIn('name', ['Admin', 'Employee'])->pluck('name', 'id');

        $steps = KpiScoreLevel::all()->groupBy('role_id')->map(function ($items) {
            return $items->groupBy('step_number')->map(function ($stepItems) {
                return [
                    'approvers' => $stepItems->pluck('approvers')->flatten()->toArray(),
                ];
            });
        });
        $permission_roles = Role::whereNotIn('name', ['Admin'])->pluck('name', 'id');

        return view('performancereview::kpi_score_levels.index', compact('steps', 'roles', 'permission_roles'));
    }
    // public function store(Request $request)
    // {
    //     try {
    //         KpiScoreLevel::truncate();

    //         // If only CSRF token is submitted
    //         if (count($request->all()) <= 1) {
    //             return redirect()->back()->with('success', 'KPI Score Level saved successfully!');
    //         }

    //         // Validate the structure of 'steps'
    //         $validated = $request->validate([
    //             'steps' => 'required|array',
    //             'steps.*.*.approvers' => 'required|array',
    //         ]);

    //         foreach ($validated['steps'] as $roleId => $levels) {
    //             foreach ($levels as $levelIndex => $step) {
    //                 KpiScoreLevel::create([
    //                     'role_id' => $roleId,
    //                     'step_number' => $levelIndex + 1,
    //                     'approvers' => $step['approvers'], // already array, will be cast to JSON by model
    //                 ]);
    //             }
    //         }

    //         return redirect()->back()->with('success', 'KPI Score Level saved successfully!');
    //     } catch (Exception $e) {
    //         return redirect()->back()->with('error', 'Failed to save KPI Score Level: ' . $e->getMessage());
    //     }
    // }
    public function store(Request $request)
    {
        try {
            KpiScoreLevel::truncate();

            if (count($request->all()) <= 1) {
                return redirect()->back()->with('success', 'KPI Score Level saved successfully!');
            }

            // Validate request
            $validated = $request->validate([
                'steps' => 'required|array',
                'steps.*.*.approvers' => 'required|array',
            ]);

            if (!isset($validated['steps']) || !is_array($validated['steps'])) {
                return redirect()->back()->with('error', 'Invalid data format: Steps are missing.');
            }

            foreach ($validated['steps'] as $roleId => $levels) {
                $stepIndex = 1; // Start from 1 per role
                foreach ($levels as $step) {
                    KpiScoreLevel::create([
                        'role_id' => $roleId,
                        'step_number' => $stepIndex++,
                        'approvers' => $step['approvers'],
                    ]);
                }
            }


            return redirect()->back()->with('success', 'KPI Score Level saved successfully!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to save KPI Score Level: ' . $e->getMessage());
        }
    }
}
