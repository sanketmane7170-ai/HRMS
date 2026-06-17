<?php

namespace Modules\AgenticAI\Tools;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Division;
use Spatie\Permission\Models\Role;
use Exception;

class ExploreOrganizationTool extends BaseTool
{
    public function name(): string
    {
        return 'explore_organization';
    }

    public function description(): string
    {
        return 'Lookup IDs and details for organizational entities: Branches (Departments), Divisions, Designations, and Security Roles. Use this to find the correct IDs before creating employees or updating branches.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'type' => [
                    'type' => 'string',
                    'enum' => ['branch', 'division', 'designation', 'role'],
                    'description' => 'The type of entity to list or search.'
                ],
                'query' => [
                    'type' => 'string',
                    'description' => 'Optional: Search by name or code.'
                ],
                'branch_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Filter divisions or designations by a specific branch ID.'
                ]
            ],
            'required' => ['type']
        ];
    }

    public function execute(array $args): mixed
    {
        $type = $args['type'];
        $query = $args['query'] ?? '';
        $branchId = $args['branch_id'] ?? null;

        try {
            switch ($type) {
                case 'branch':
                    $items = Department::query();
                    if ($query) $items->where('name', 'like', "%{$query}%")->orWhere('code', 'like', "%{$query}%");
                    return ['branches' => $items->get(['id', 'name', 'code', 'address'])->toArray()];

                case 'division':
                    $items = Division::query();
                    if ($branchId) $items->where('branch_id', $branchId);
                    if ($query) $items->where('name', 'like', "%{$query}%");
                    return ['divisions' => $items->get(['id', 'name', 'code', 'branch_id'])->toArray()];

                case 'designation':
                    $items = Designation::query();
                    if ($branchId) $items->where('department_id', $branchId);
                    if ($query) $items->where('name', 'like', "%{$query}%");
                    return ['designations' => $items->get(['id', 'name', 'code', 'grade', 'department_id'])->toArray()];

                case 'role':
                    $items = Role::query();
                    if ($query) $items->where('name', 'like', "%{$query}%");
                    return ['roles' => $items->get(['id', 'name'])->toArray()];

                default:
                    return ['error' => 'Invalid type.'];
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
