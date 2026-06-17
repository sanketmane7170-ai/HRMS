<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FailedRowsUpdateExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $failedRows;

    public function __construct($failedRows)
    {
        $this->failedRows = $failedRows;
    }

    public function collection()
    {
        return new Collection($this->failedRows);
    }

    // public function headings(): array
    // {
    //     return [
    //         __trans('first_name'),
    //         __trans('last_name'),
    //         __trans('phone'),
    //         __trans('email'),
    //         __trans('role'),
    //         __trans('designation'),
    //         __trans('branch'),
    //         __trans('department'),
    //         __trans('date_of_birth'),
    //         __trans('personal_email'),
    //         __trans('personal_phone'),
    //         __trans('marital_status'),
    //         __trans('country_id'),
    //         __trans('gender'),
    //         __trans('address'),
    //         __trans('joining_date'),
    //         __trans('probation_end_date'),
    //         __trans('company_name'),
    //         __trans('reporting_manager'),
    //         __trans('work_week'),
    //         __trans('location'),
    //         __trans('bank_name'),
    //         __trans('routing_number'),
    //         __trans('bank_account_no'),
    //         __trans('iban_no'),
    //         __trans('swift_code'),
    //         __trans('basic_salary'),

    //         __trans('passport_no'),
    //         __trans('place_of_issue'),
    //         __trans('country'),
    //         __trans('issue_date'),
    //         __trans('expiry_date'),

    //         __trans('visa_no'),
    //         __trans('place_of_issue'),
    //         __trans('country'),
    //         __trans('issue_date'),
    //         __trans('expiry_date'),
    //         __trans('visa_category'),

    //         __trans('labor_card_no'),
    //         __trans('ministry_of_labor_personal_no'),
    //         __trans('issue_date'),
    //         __trans('expiry_date'),

    //         __trans('emirates_id'),
    //         __trans('issue_date'),
    //         __trans('expiry_date'),

    //         __trans('employee_id'),
    //         __trans('company_document'),
    //         __trans('entity'),
    //         __trans('biometric_user_id'),
    //         __trans('id'),
    //         __trans('Error'),
    //     ];
    // }
    public function headings(): array
{
    return [
        'First Name', 'Last Name', 'Phone', 'Email', 'Role', 'Designation', 'Branch', 'Department',
        'Date of Birth', 'Personal Email', 'Personal Phone', 'Marital Status', 'Country', 'Gender', 'Address',
        'Joining Date', 'Probation End Date', 'Company Name', 'Reporting Manager', 'Work Week', 'Location',
        'Bank Name', 'Routing Number', 'Account Number', 'IBAN', 'Swift Code', 'Basic Salary',
        'Passport No', 'Place of Issue', 'Country', 'Issue Date', 'Expiry Date',
        'Visa No', 'Visa UID Number', 'Country', 'Issue Date', 'Expiry Date', 'Visa Category',
        'Labor Card No', 'MOL Personal No', 'Issue Date', 'Expiry Date',
        'Emirates ID', 'Issue Date', 'Expiry Date',
        'Visa Designation', 'Visa Category', 'Visa Type',
        'MOL Number', 'Last Working Day', 'Remarks',
        'Insurance Number', 'Insurance Expiry', 'Status',
        'Employee ID', 'Company Document', 'Entity','Biometric User ID', 'ID',

        'Error' // ✅ ADD THIS AT LAST
    ];
}
}
