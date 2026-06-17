<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FailedRowsExport implements FromCollection, WithHeadings
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

    public function headings(): array
    {
        return [
             __trans('first_name'),
            __trans('last_name'),
            __trans('phone'),
            __trans('email'),
            __trans('role'),
            __trans('designation'),
            __trans('branch'),
            __trans('department'),
            __trans('date_of_birth'),
            __trans('personal_email'),
            __trans('personal_phone'),
            __trans('marital_status'),
            __trans('country_id'),
            __trans('gender'),
            __trans('address'),
            __trans('joining_date'),
            __trans('probation_end_date'),
            __trans('company_name'),
            __trans('work_week'),
            __trans('location'),
            __trans('bank_name'),
            __trans('bank_account_no'),
            __trans('iban_no'),
            __trans('swift_code'),
            __trans('basic_salary'),
            __trans('passport_no'),
            __trans('place_of_issue'),
            __trans('country'),
            __trans('issue_date'),
            __trans('expiry_date'),
            __trans('visa_no'),
            __trans('place_of_issue'),
            __trans('country'),
            __trans('issue_date'),
            __trans('expiry_date'),
            __trans('labor_card_no'),
            __trans('personal_no'),
            __trans('issue_date'),
            __trans('expiry_date'),
            __trans('emirates_id'),
            __trans('issue_date'),
            __trans('expiry_date'),
            __trans('company_document'),
            __trans('visa_category'),
            __trans('entity'),
            __trans('error'),
        ];
    }

}
