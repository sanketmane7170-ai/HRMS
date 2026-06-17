<?php

namespace App\Imports;

use App\Models\Department;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DepartmentImport implements ToModel, WithValidation, WithStartRow
{
    use Importable;
    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Department([
            'name' => $row[1],
            'code' => $row[2],
        ]);
    }

    public function rules(): array
    {
        return [
            '1' => 'required|unique:departments,name',
            '2' => 'required|unique:departments,code',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            '1.unique' => __trans('File contains duplicate date for department name field at') . ' :attribute',
            '2.unique' => __trans('File contains duplicate date for department code field at') . ' :attribute',
        ];
    }
}
