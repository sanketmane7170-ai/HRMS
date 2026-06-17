<?php

namespace Modules\Api\Http\Controllers\Expense;

use Modules\Expense\Entities\ExpenseType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ExpenseTypeController extends Controller
{
    public function __construct() {}

    // List all expense types
    public function index()
    {

        $expenseTypes = ExpenseType::all();
        if (!$expenseTypes) {
            return response()->json([
                'message' => 'Expense type not found',
                'status' => false,
            ], 400);
        }
        return response()->json([
            'message' => 'Expense type fetched successfully.',
            'status' => 'success',
            'data' => $expenseTypes,
            'base_url' => url('/'),

        ], 200);
    }

    // Create a new expense type
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        $expenseType = ExpenseType::create($validated);
        return response()->json([
            'message' => 'Expense created successfully.',
            'status' => 'success',
            'data' => $expenseType,
            'base_url' => url('/'),

        ], 201);
    }

    // Show a single expense type
    public function show(Request $request, $id)
    {
        $id = $request->route('id');
        $expenseType = ExpenseType::find($id);

        if (!$expenseType) {
            return response()->json([
                'message' => 'Expense type not found',
                'status' => false,
            ], 400);
        }
        return response()->json([
            'message' => 'Expense type fetched successfully.',
            'status' => 'success',
            'data' => $expenseType,
            'base_url' => url('/'),

        ], 200);
    }

    // Update an existing expense type
    public function update(Request $request, $id)
    {
        $id = $request->route('id');
        $expenseType = ExpenseType::find($id);

        if (!$expenseType) {
            return response()->json([
                'message' => 'Expense type not found',
                'status' => false,
            ], 400);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'status' => 'boolean',
        ]);

        $expenseType->update($validated);
        return response()->json([
            'message' => 'Expense type updated successfully.',
            'status' => 'success',
            'data' => $expenseType,
            'base_url' => url('/'),

        ], 200);
    }

    // Delete an expense type
    public function destroy(Request $request, $id)
    {
        $id = $request->route('id');
        $expenseType = ExpenseType::find($id);

        if (!$expenseType) {
            return response()->json([
                'message' => 'Expense type not found',
                'status' => false,
            ], 400);
        }

        $expenseType->delete();
        return response()->json([
            'message' => 'Expense type deleted successfully',
            'status' => 'success',
            'data' => $expenseType,
            'base_url' => url('/'),

        ], 200);
    }
}
