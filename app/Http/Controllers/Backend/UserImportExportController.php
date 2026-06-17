<?php
namespace App\Http\Controllers\Backend;

use App\Exports\EmployeeWorkingDaySheet;
use App\Exports\ExcelExport;
use App\Exports\FailedAllowanceRowsExport;
use App\Exports\FailedDeductionRowsExport;
use App\Exports\FailedRowsExport;
use App\Exports\FailedRowsUpdateExport;
use App\Exports\MasterSheetExport;
use App\Exports\SettlementListExport;
use App\Exports\UserAllowanceSampleExport;
use App\Exports\UserDeductionSampleExport;
use App\Exports\UserEditSampleExport;
use App\Exports\UserExport;
use App\Exports\UserSalaryEntityExport;
use App\Exports\UserSampleExport;
use App\Http\Controllers\Controller;
use App\Imports\AllowanceImport;
use App\Imports\DeductionImport;
use App\Imports\MedicalpremiumImport;
use App\Imports\SalaryEntityImport;
use App\Imports\UserImport;
use App\Imports\UserUpdateImport;
use App\Imports\WorkingDayImport;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class UserImportExportController extends Controller
{
    /**
     * Return Excel File with User details
     */
    public function exportToExcel()
    {

        canPerform('Export User');
        return Excel::download(new UserExport, 'employee_' . time() . '.xlsx');
    }

    /**
     * Return Pdf File with User details
     */
    public function exportToPdf()
    {
        canPerform('Export User');
        return Excel::download(new UserExport, 'employee_' . time() . '.pdf', \Maatwebsite\Excel\Excel::MPDF);
    }

    public function exportMasterSheet()
    {
        ob_end_clean();
        ob_start();
        return Excel::download(new MasterSheetExport, 'master_sheet_' . time() . '.xlsx');
    }

    /**
     * Import Users List from excel to database
     */
    public function importFromExcel(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx',
        ], [
            'file.required' => __trans('Please upload file to import users'),
        ]);
        $response = getErrorResponse();
        try {
            $import = new UserImport();
            $import->import($request->file);

            $failedRows = $import->getFailedRows(); //this will return failed rows

            if (! empty($failedRows)) {
                $filePath = 'uploads/failedexport/employee_import_failed.xlsx';
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                try {
                    Excel::store(new FailedRowsExport($failedRows), $filePath, 'real_public');
                } catch (\Exception $e) {
                    Log::error('Error storing Excel file: ' . $e->getMessage());
                    print_r($e->getMessage());
                    die();
                }
                $response                 = getSuccessResponse(createFlashMessage('File', 'imported partially'));
                $response['download_url'] = asset($filePath);
            } else {
                $response = getSuccessResponse(createFlashMessage('File', 'imported'));
            }
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Return Sample(Columns) in Excel File
     */
    public function exportSampleToExcel()
    {
        // return Excel::download(new UserSampleExport, 'sample_' . time() . '.xlsx');
        $clientName = getSetting('site_title');

        $safeName = strtolower(str_replace(' ', '_', $clientName));

        $fileName = $safeName . '_users_for_import_' . time() . '.xlsx';

        return Excel::download(new UserSampleExport, $fileName);
    }

    public function editEmpExportSampleToExcel()
    {
        $clientName = getSetting('site_title');

        // $safeName = str_replace(' ', '_', $clientName);
        $safeName = strtolower(str_replace(' ', '_', $clientName));

        $fileName = $safeName . '_users_for_update_' . time() . '.xlsx';

        return Excel::download(new UserEditSampleExport, $fileName);
        // return Excel::download(new UserEditSampleExport, 'sample_for_update' . time() . '.xlsx');
    }

    public function updateEmpToExcel(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx',
        ], [
            'file.required' => __trans('Please upload file to import users'),
        ]);
        $response = getErrorResponse();
        try {
            $import = new UserUpdateImport();
            $import->import($request->file);

            $failedRows = $import->getFailedRows(); //this will return failed rows

            if (! empty($failedRows)) {
                $filePath = 'uploads/failedexport/employee_update_import_failed.xlsx';
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                try {
                    Excel::store(new FailedRowsUpdateExport($failedRows), $filePath, 'real_public');
                } catch (\Exception $e) {
                    Log::error('Error storing Excel file: ' . $e->getMessage());
                    print_r($e->getMessage());
                    die();
                }
                $response                 = getSuccessResponse(createFlashMessage('File', 'imported partially'));
                $response['download_url'] = asset($filePath);
            } else {
                $response = getSuccessResponse(createFlashMessage('File', 'imported'));
            }
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }
    /**
     * Return Excel File with User details with Basic Salary
     * BS => Basic Salary
     */
    public function exportBSSampleToExcel()
    {
        return Excel::download(new UserSalaryEntityExport, 'employee_' . time() . '.xlsx');
    }

    /**
     * Import Users List from excel to database
     */
    public function importBSFromExcel(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx',
        ], [
            'file.required' => __trans('Please upload file to import basic salary'),
        ]);
        $response = getErrorResponse();
        try {
            $import = new SalaryEntityImport();
            $import->import($request->file);
            $response = getSuccessResponse(createFlashMessage('File', 'imported'));
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function exportSettlementList()
    {
        return Excel::download(new SettlementListExport, 'settlement_list_' . time() . '.xlsx');
    }

    public function exportWorkingDayToExcel($month, $year)
    {
        return Excel::download(new EmployeeWorkingDaySheet($month, $year), 'working_day_sheet_' . strtoupper(date('M', mktime(0, 0, 0, $month, 1))) . $year . '.xlsx');
    }

    public function importWorkingDayFromExcel(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx',
        ], [
            'file.required' => __trans('Please upload file to import basic salary'),
        ]);
        $response = getErrorResponse();
        try {
            $import = new WorkingDayImport($request->month, $request->year);
            $import->import($request->file);
            $response = getSuccessResponse(createFlashMessage('File', 'imported'));
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }

    // public function exportAllowanceSampleToExcel(Request $request)
    // {
    //     return Excel::download(new UserAllowanceSampleExport, 'Allowance_' . time() . '.xlsx');
    // }
    public function exportAllowanceSampleToExcel(Request $request)
    {
        $month = $request->month;
        $year  = $request->year;

        return Excel::download(
            new UserAllowanceSampleExport($month, $year),
            'Allowance_' . $month . '_' . $year . '.xlsx'
        );
    }

    // public function importAllowanceFromExcel(Request $request): JsonResponse
    // {
    //     $request->validate([
    //         'file' => 'required|mimes:xlsx',
    //     ], [
    //         'file.required' => __trans('Please upload file to import allowance'),
    //     ]);
    //     $response = getErrorResponse();
    //     try {
    //         $import = new AllowanceImport();
    //         $import->import($request->file);
    //         $response = getSuccessResponse(createFlashMessage('File', 'imported'));
    //     } catch (Exception $e) {
    //         $response['message'] = $e->getMessage();
    //     }

    //     return response()->json($response);
    // }
    public function importAllowanceFromExcel(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx',
        ], [
            'file.required' => __trans('Please upload file to import allowance'),
        ]);

        $response = getErrorResponse();

        try {

            $import = new AllowanceImport();
            $import->import($request->file);

            $failedRows = $import->getFailedRows();

            if (! empty($failedRows)) {

                $filePath = 'uploads/failedexport/allowance_import_failed.xlsx';

                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                Excel::store(
                    new FailedAllowanceRowsExport($failedRows),
                    $filePath,
                    'real_public'
                );

                $response                 = getSuccessResponse(createFlashMessage('File', 'imported partially'));
                $response['download_url'] = asset($filePath);

            } else {

                $response = getSuccessResponse(createFlashMessage('File', 'imported'));
            }

        } catch (\Exception $e) {

            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }

    // public function exportDeductionSampleToExcel()
    // {
    //     return Excel::download(new UserDeductionSampleExport, 'Deduction_' . time() . '.xlsx');
    // }

    public function exportDeductionSampleToExcel(Request $request)
    {
        $month = $request->month;
        $year  = $request->year;

        return Excel::download(
            new UserDeductionSampleExport($month, $year),
            'Deduction_' . $month . '_' . $year . '.xlsx'
        );
    }

    public function importDeductionFromExcel(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx',
        ], [
            'file.required' => __trans('Please upload file to import allowance'),
        ]);
        $response = getErrorResponse();
        try {
            $import = new DeductionImport();
            $import->import($request->file);
            // $response = getSuccessResponse(createFlashMessage('File', 'imported'));
             $failedRows = $import->getFailedRows();

            if (! empty($failedRows)) {

                $filePath = 'uploads/failedexport/deduction_import_failed.xlsx';

                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                Excel::store(
                    new FailedDeductionRowsExport($failedRows),
                    $filePath,
                    'real_public'
                );

                $response                 = getSuccessResponse(createFlashMessage('File', 'imported partially'));
                $response['download_url'] = asset($filePath);

            } else {

                $response = getSuccessResponse(createFlashMessage('File', 'imported'));
            }

        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function importimportmedicalpremiumFromExcel(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx',
        ], [
            'file.required' => __trans('Please upload file to import users'),
        ]);
        $response = getErrorResponse();
        try {
            $import = new MedicalpremiumImport();

            $import->import($request->file);
            $response = getSuccessResponse(createFlashMessage('File', 'imported'));
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function exportSampleMedicalpremiumToExcel()
    {

        $query = User::with('workDetail')->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        });
        $employees_data = [];
        $employees      = $query->get();
        foreach ($employees as $row => $employee) {

            $employees_data[$row]['id']          = $employee->id;
            $employees_data[$row]['employee_id'] = $employee->employee_id;
            $employees_data[$row]['name']        = $employee->name;

            if ($employee->workDetail) {
                $employees_data[$row]['medical_insurance_provided'] = $employee->workDetail->medical_insurance_provided == 1 ? "Yes" : "No";
                $employees_data[$row]['annual_premium']             = $employee->workDetail->annual_premium;
            }
        }

        $exportExcel = [];
        $headers     = [
            __trans('ID'),
            __trans('Emp ID'),
            __trans('Employee Name'),
            __trans('medical_insurance_provided'),
            __trans('annual_premium'),
        ];
        $export = new ExcelExport($employees_data, $headers);
        return Excel::download($export, 'samplemedicalpremium_' . time() . '.xlsx');
    }
}
