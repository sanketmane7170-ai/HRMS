<?php

namespace Modules\IndianPayroll\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\IndianPayroll\Database\Seeders\IndianPayrollPermissionsSeeder;
use Modules\IndianPayroll\Entities\EmployeeTaxDeclaration;
use Modules\IndianPayroll\Entities\InvestmentDeclaration;
use Modules\IndianPayroll\Http\Controllers\Employee\MyTaxDeclarationController;
use Modules\IndianPayroll\Http\Controllers\TaxDeclarationController;
use Tests\TestCase;

/**
 * Previously HR could see a "proof uploaded" icon with no way to open the file —
 * verification decisions were being made blind. This proves the full path: employee
 * uploads -> file lands on disk -> HR (and only HR) can actually retrieve it.
 */
class InvestmentProofDownloadTest extends TestCase
{
    use DatabaseTransactions;

    public function test_uploaded_proof_can_be_downloaded_by_an_authorized_hr_user(): void
    {
        $employee = User::factory()->create();
        $hr = User::factory()->create();
        $this->seed(IndianPayrollPermissionsSeeder::class);
        $hr->givePermissionTo('Verify Tax Declarations');

        $this->actingAs($employee);
        $file = UploadedFile::fake()->create('proof.pdf', 50, 'application/pdf');
        $request = Request::create('', 'POST', ['section_code' => '80C', 'declared_amount' => 50000]);
        $request->files->set('proof', $file);

        app(MyTaxDeclarationController::class)->storeInvestment($request);

        $declaration = EmployeeTaxDeclaration::where('user_id', $employee->id)->first();
        $investment = InvestmentDeclaration::where('declaration_id', $declaration->id)->where('section_code', '80C')->first();

        $this->assertNotNull($investment->proof_path);
        $this->assertTrue(Storage::disk(config('indianpayroll.document_disk'))->exists($investment->proof_path));

        $this->actingAs($hr);
        $response = app(TaxDeclarationController::class)->downloadProof($investment);
        $this->assertEquals(200, $response->getStatusCode());

        Storage::disk(config('indianpayroll.document_disk'))->delete($investment->proof_path);
    }

    public function test_declaration_without_a_proof_returns_404(): void
    {
        $employee = User::factory()->create();
        $hr = User::factory()->create();
        $this->seed(IndianPayrollPermissionsSeeder::class);
        $hr->givePermissionTo('Verify Tax Declarations');

        $declaration = EmployeeTaxDeclaration::create([
            'user_id' => $employee->id,
            'financial_year' => '2025-2026',
            'regime_choice' => 'old',
        ]);
        $investment = InvestmentDeclaration::create([
            'declaration_id' => $declaration->id,
            'section_code' => '80D',
            'declared_amount' => 20000,
            'status' => 'pending',
        ]);

        $this->actingAs($hr);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        app(TaxDeclarationController::class)->downloadProof($investment);
    }
}
