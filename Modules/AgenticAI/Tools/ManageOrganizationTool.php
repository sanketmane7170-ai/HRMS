<?php

namespace Modules\AgenticAI\Tools;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Division;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

class ManageOrganizationTool extends BaseTool
{
    public function name(): string
    {
        return 'manage_organization';
    }

    public function description(): string
    {
        return 'Create or update organizational units: Branches (Departments), Divisions, and Designations. Use this for onboarding new branches or setting up company structure.';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'type' => [
                    'type' => 'string',
                    'enum' => ['branch', 'division', 'designation'],
                    'description' => 'The type of entity to manage.'
                ],
                'action' => [
                    'type' => 'string',
                    'enum' => ['create', 'update'],
                    'description' => 'Action to perform: create or update.'
                ],
                'id' => [
                    'type' => 'integer',
                    'description' => 'Required for update: The ID of the branch, division, or designation.'
                ],
                'data' => [
                    'type' => 'object',
                    'description' => 'Key-value pairs for the entity data.',
                    'properties' => [
                        'name' => ['type' => 'string', 'description' => 'Name of the branch, division, or designation.'],
                        'code' => ['type' => 'string', 'description' => 'Short code (required for branches, divisions, and designations).'],
                        'address' => ['type' => 'string', 'description' => 'Branch address (Required for branches). AI will attempt to geocode this.'],
                        'login_radius' => ['type' => 'string', 'description' => 'Radius for attendance (e.g., 500, 1000).'],
                        'branch_id' => ['type' => 'integer', 'description' => 'Required for divisions and designations: The parent Branch (Department) ID.'],
                        'grade' => ['type' => 'string', 'description' => 'Grade (Optional for designations).'],
                        'short_name' => ['type' => 'string', 'description' => 'Short name (Optional for branches).'],
                    ],
                    'required' => ['name', 'code']
                ]
            ],
            'required' => ['type', 'action', 'data']
        ];
    }

    public function execute(array $args): mixed
    {
        $type = $args['type'];
        $action = $args['action'];
        $data = $args['data'];
        $id = $args['id'] ?? null;

        try {
            switch ($type) {
                case 'branch':
                    return $this->handleBranch($action, $id, $data);
                case 'division':
                    return $this->handleDivision($action, $id, $data);
                case 'designation':
                    return $this->handleDesignation($action, $id, $data);
                default:
                    return ['error' => 'Invalid entity type.'];
            }
        } catch (Exception $e) {
            return [
                'error' => 'Operation failed',
                'message' => $e->getMessage()
            ];
        }
    }

    private function handleBranch($action, $id, $data)
    {
        if ($action === 'create') {
            if (empty($data['address'])) {
                throw new Exception('Branch address is required for creation.');
            }

            $geocodedData = $this->geocodeAddress($data['address']);
            $data = array_merge($data, $geocodedData);
            $data['slug'] = $data['name'];
            
            $branch = Department::create($data);
            return [
                'success' => true,
                'message' => "Branch '{$branch->name}' created successfully with geocoding.",
                'branch' => $branch->toArray()
            ];
        }

        if ($action === 'update') {
            if (!$id) throw new Exception('ID is required for update.');
            $branch = Department::findOrFail($id);
            
            if (!empty($data['address']) && $data['address'] !== $branch->address) {
                $geocodedData = $this->geocodeAddress($data['address']);
                $data = array_merge($data, $geocodedData);
            }
            
            if (!empty($data['name'])) {
                $data['slug'] = $data['name'];
            }

            $branch->update($data);
            return [
                'success' => true,
                'message' => "Branch '{$branch->name}' updated successfully.",
                'branch' => $branch->toArray()
            ];
        }
    }

    private function handleDivision($action, $id, $data)
    {
        if (empty($data['branch_id'])) {
            throw new Exception('Branch ID (branch_id) is required for division management.');
        }

        if ($action === 'create') {
            $division = Division::create($data);
            return [
                'success' => true,
                'message' => "Division '{$division->name}' created successfully.",
                'division' => $division->toArray()
            ];
        }

        if ($action === 'update') {
            if (!$id) throw new Exception('ID is required for update.');
            $division = Division::findOrFail($id);
            $division->update($data);
            return [
                'success' => true,
                'message' => "Division '{$division->name}' updated successfully.",
                'division' => $division->toArray()
            ];
        }
    }

    private function handleDesignation($action, $id, $data)
    {
        if (empty($data['branch_id'])) {
            throw new Exception('Branch ID (branch_id) is required for designation management.');
        }
        
        // Match model fields (department_id in DB, but we call it branch_id in AI for consistency)
        $data['department_id'] = $data['branch_id'];
        unset($data['branch_id']);

        if ($action === 'create') {
            $designation = Designation::create($data);
            return [
                'success' => true,
                'message' => "Designation '{$designation->name}' created successfully.",
                'designation' => $designation->toArray()
            ];
        }

        if ($action === 'update') {
            if (!$id) throw new Exception('ID is required for update.');
            $designation = Designation::findOrFail($id);
            $designation->update($data);
            return [
                'success' => true,
                'message' => "Designation '{$designation->name}' updated successfully.",
                'designation' => $designation->toArray()
            ];
        }
    }

    private function geocodeAddress(string $address): array
    {
        $apiKey = env('GOOGLE_MAPS_API_KEY');
        if (empty($apiKey)) {
            return ['latitude' => null, 'longitude' => null];
        }

        try {
            $client = new Client();
            $response = $client->get("https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=$apiKey");
            $result = json_decode($response->getBody());

            if ($result->status === 'OK') {
                return [
                    'latitude' => $result->results[0]->geometry->location->lat,
                    'longitude' => $result->results[0]->geometry->location->lng
                ];
            }
        } catch (Exception $e) {
            \Log::warning('Geocoding failed for ManageOrganizationTool', ['error' => $e->getMessage()]);
        }

        return ['latitude' => null, 'longitude' => null];
    }
}
