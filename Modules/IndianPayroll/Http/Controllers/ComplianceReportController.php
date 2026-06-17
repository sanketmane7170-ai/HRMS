<?php

namespace Modules\IndianPayroll\Http\Controllers;

use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\IndianPayroll\Entities\PayrollRun;
use Modules\IndianPayroll\Exports\EsiRegisterExport;
use Modules\IndianPayroll\Exports\GratuityRegisterExport;
use Modules\IndianPayroll\Exports\LwfRegisterExport;
use Modules\IndianPayroll\Exports\PfRegisterExport;
use Modules\IndianPayroll\Exports\PtRegisterExport;

class ComplianceReportController extends Controller
{
    public function pfRegister(PayrollRun $run)
    {
        canPerform('Export Compliance Reports');

        return Excel::download(new PfRegisterExport($run->id), "pf_register_{$run->month}_{$run->year}.xlsx");
    }

    public function esiRegister(PayrollRun $run)
    {
        canPerform('Export Compliance Reports');

        return Excel::download(new EsiRegisterExport($run->id), "esi_register_{$run->month}_{$run->year}.xlsx");
    }

    public function ptRegister(PayrollRun $run)
    {
        canPerform('Export Compliance Reports');

        return Excel::download(new PtRegisterExport($run->id), "pt_register_{$run->month}_{$run->year}.xlsx");
    }

    public function lwfRegister(PayrollRun $run)
    {
        canPerform('Export Compliance Reports');

        return Excel::download(new LwfRegisterExport($run->id), "lwf_register_{$run->month}_{$run->year}.xlsx");
    }

    public function gratuityRegister()
    {
        canPerform('Export Compliance Reports');

        return Excel::download(new GratuityRegisterExport, 'gratuity_register.xlsx');
    }
}
