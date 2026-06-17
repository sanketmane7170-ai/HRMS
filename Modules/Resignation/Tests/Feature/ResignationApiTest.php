<?php

namespace Modules\Resignation\Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Modules\Resignation\Entities\Resignation;
use Modules\Resignation\Entities\EmployeeNoticePeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class ResignationApiTest extends TestCase
{
    // usage of RefreshDatabase might wipe entire DB, let's use caution or Transactions.
    // Given the environment, DatabaseTransactions trait is safer if existing data matters.
    // However, for a clean test of new features, we often create factory data.
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    protected $employee;
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create Role if not exists (for testing purposes)
        if(!Role::where('name', 'employee')->exists()) Role::create(['name' => 'employee']);
        if(!Role::where('name', 'manager')->exists()) Role::create(['name' => 'manager']);

        // Create Users
        $this->manager = User::factory()->create();
        $this->manager->assignRole('manager');

        $this->employee = User::factory()->create([
            'report_to_id' => $this->manager->id // Assuming this field exists or we mocked the service logic
        ]);
        $this->employee->assignRole('employee');
        
        // Mock 'report_to_id' behavior if it depends on UserWorkDetail which might be complex to factory
        // For simplicity, we assume ResignationService logic uses $user->report_to_id directly as we saw earlier,
        // OR we manually set it if it's on the user table or relation. 
        // We'll update the Service logic or User factory if needed. 
    }

    public function test_employee_can_apply_for_resignation()
    {
        $response = $this->actingAs($this->employee)->postJson('/api/v1/resignation/apply', [
            'reason' => 'Better opportunity',
            'preferred_last_working_date' => now()->addDays(30)->toDateString(),
            'notice_period_days' => 30,
            'comments' => 'Thanks for everything.'
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('resignations', [
            'employee_id' => $this->employee->id,
            'reason' => 'Better opportunity'
        ]);
    }

    public function test_manager_can_approve_resignation()
    {
        // 1. Create Resignation
        $resignation = Resignation::create([
            'employee_id' => $this->employee->id,
            'manager_id' => $this->manager->id,
            'applied_date' => now(),
            'notice_period_days' => 30,
            'preferred_last_working_date' => now()->addDays(30),
            'reason' => 'Test',
            'status' => 'pending'
        ]);

        // 2. Approve Request
        $response = $this->actingAs($this->manager)->postJson("/api/v1/resignation/{$resignation->id}/action", [
            'action_type' => 'approve',
            'comments' => 'Good luck!',
            'approved_last_working_date' => now()->addDays(30)->toDateString()
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 'approved');

        // 3. Verify Notice Period Creation
        $this->assertDatabaseHas('employee_notice_periods', [
            'resignation_id' => $resignation->id,
            'employee_id' => $this->employee->id,
            'status' => 'active'
        ]);
    }

    public function test_employee_cannot_apply_twice()
    {
        // Create one pending
        Resignation::create([
            'employee_id' => $this->employee->id,
            'manager_id' => $this->manager->id,
            'applied_date' => now(),
            'reason' => 'Test',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->employee)->postJson('/api/v1/resignation/apply', [
            'reason' => 'Another one',
            'preferred_last_working_date' => now()->addDays(30)->toDateString()
        ]);

        $response->assertStatus(400); 
    }
}
