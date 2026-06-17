<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DesignationImport implements ToModel, WithValidation, WithStartRow
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
    // public function model(array $row)
    // {
    //     return new Designation([
    //         'name' => $row[1],
    //         'code' => $row[2],
    //         'department_id' => $this->getDepartmentId($row[3])
    //     ]);
    // }
    public function model(array $row)
    {
        $departmentId = $this->getDepartmentId($row[3]);

        return Designation::updateOrCreate(
            [
                'department_id' => $departmentId,
                'code' => $row[2], // unique per department
            ],
            [
                'name' => $row[1], // update name if changed
            ]
        );
    }


    // public function rules(): array
    // {
    //     return [
    //         '1' => 'required|unique:designations,name',
    //         '2' => 'required|unique:designations,code',
    //         '3' => 'required'
    //     ];
    // }
    public function rules(): array
    {
        return [
            '1' => [
                'required',
                function ($attribute, $value, $fail) {
                    $departmentNameOrCode = request()->input('3'); // this will not work here
                    // Instead use $this->row (see below)
                }
            ],
            // '2' => 'required|unique:designations,code',
            '2' => [
                'required',
                function ($attribute, $value, $fail) {
                    $departmentNameOrCode = request()->input('3'); // this will not work here
                    // Instead use $this->row (see below)
                }
            ],
            '3' => 'required',

        ];
    }


    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            '1.unique' => __trans('File contains duplicate date for designation name field at') . ' :attribute',
            '2.unique' => __trans('File contains duplicate date for designation code field at') . ' :attribute',
            '3.requried' => __trans('Please provider department') . ' :attribute',
        ];
    }

    public function getDepartmentId($data)
    {
        $data = Department::where('name', $data)
            ->orWhere('code', $data)->first();

        return $data->id ?? null;
    }
}
