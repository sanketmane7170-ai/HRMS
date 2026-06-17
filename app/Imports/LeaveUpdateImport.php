<?php
namespace App\Imports;

use App\Models\User;
use App\Models\UserLeaveBalanceTransaction;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Modules\Leave\Entities\LeaveBalance;
use Modules\Leave\Entities\LeaveBalanceUpdateLog;
use Modules\Leave\Entities\LeaveType;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LeaveUpdateImport implements ToModel, WithStartRow
{
    use Importable;

    protected $failedRows = [];

    protected $dateFormats = [
        'Y-m-d', // Format used in the CSV file
    ];

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
    public function map($row): array
    {

    }

    // public function model(array $row)
    // {
    //     try{
    //         // Extract using regex
    //         preg_match('/\((.*?)\)/', $row[0], $empid);

    //         if (!empty($empid[1])) {
    //             $empId = $empid[1];
    //             $user = User::where('employee_id', $empId)->first();
    //             $types = LeaveType::get(['id', 'name', 'days']);

    //             foreach ($types as $i => $type) {
    //                 $i++;
    //                 $getbalance = LeaveBalance::where(
    //                     [
    //                         'user_id' => $user->id,
    //                         'year' => date('Y'),
    //                         'leave_type_id' => $type->id
    //                     ],
    //                 )->first();
    //                 $newValue = (float) $row[$i];
    //                 $oldvalue = (float) $getbalance->available;

    //                 $difference = abs($oldvalue - $newValue);
    //                 if ($newValue > $oldvalue) {
    //                     $is_less = 1;
    //                 } elseif ($newValue < $oldvalue) {
    //                     $is_less = 0;
    //                 } else {
    //                     $is_less = null;
    //                 }

    //                 // if ($newValue !== $oldvalue) {

    //                     // Log the update
    //                     LeaveBalanceUpdateLog::create([
    //                         'user_id' => $user->id,
    //                         'leave_type_id' => $type->id,
    //                         'previous_balance' => empty($oldvalue) ? 0 : $oldvalue,
    //                         'new_balance' =>  empty($row[$i]) ? 0 : $row[$i],
    //                         'diff_value' => $difference,
    //                         'is_less' => $is_less,
    //                         'updated_by' => auth()->user()->id,
    //                         'updated_at' => now(),
    //                         'description' => 'this updated by admin using import excel.'
    //                     ]);

    //                     $addtransaction = UserLeaveBalanceTransaction::create([
    //                         'user_id' => $user->id,
    //                         'leave_type_id' => $type->id,
    //                         'transaction_type' => 'remove',
    //                         'old_balance' => empty($oldvalue) ? 0 : $oldvalue,
    //                         'update_balance' => $difference,
    //                         'new_balance' => empty($row[$i]) ? 0 : $row[$i],
    //                         'transaction_date' => Carbon::now()->toDateString(),
    //                         'description' => 'this updated by admin using import excel.',
    //                     ]);
    //                     $checkLeaveBalance = LeaveBalance::where([
    //                         'user_id' => $user->id,
    //                         'leave_type_id' => $type->id,
    //                         'year' => date('Y')
    //                     ])->first();

    //                     $value = trim($row[$i]);

    //                     if ($value === '' || $value === null || $value === 0) {
    //                         $newValue = 0;
    //                     } else {
    //                         $newValue = (float) $value;
    //                     }

    //                     if($checkLeaveBalance){
    //                         $checkLeaveBalance->available = $newValue;
    //                         $checkLeaveBalance->monthwiseDay = $newValue;
    //                         $checkLeaveBalance->save();
    //                     }
    //                 // }
    //             }
    //         }
    //         return $user;
    //     } catch (\Exception $e) {
    //         $row['error'] = $e->getMessage();
    //         $this->failedRows[] = $row;
    //         return null;
    //     }
    // }
    public function model(array $row)
    {
        try {
            // Column A = Employee ID
            $empId = trim($row[0]);

            if (empty($empId)) {
                throw new \Exception('Employee ID is empty');
            }

            $user = User::where('employee_id', $empId)->first();

            if (! $user) {
                throw new \Exception("User not found for Employee ID: {$empId}");
            }

            $types = LeaveType::get(['id', 'name', 'days']);

            // Leave values start from column index 2
            $columnIndex = 2;

            foreach ($types as $type) {

                $getBalance = LeaveBalance::where([
                    'user_id'       => $user->id,
                    'year'          => date('Y'),
                    'leave_type_id' => $type->id,
                ])->first();

                if (! $getBalance) {
                    $getBalance = LeaveBalance::create([
                        'user_id'                => $user->id,
                        'leave_type_id'          => $type->id,
                        'year'                   => date('Y'),

                        // keep everything NULL intentionally
                        'available'              => 0,
                        'monthwiseDay'           => 0,
                        'initial_balance'        => 0,
                        'initial_balance_date'   => today(),
                        'thisYearAvailableLeave' => 0,
                        'isAddThisMonthLeave'    => 0,
                    ]);
                }

                $oldValue = (float) ($getBalance->available ?? 0);
                $value    = trim($row[$columnIndex] ?? '');

                $newValue   = ($value === '' || $value === null) ? 0 : (float) $value;
                $difference = abs($oldValue - $newValue);

                if ($newValue > $oldValue) {
                    $is_less = 1;
                } elseif ($newValue < $oldValue) {
                    $is_less = 0;
                } else {
                    $is_less = null;
                }

                // Log update
                LeaveBalanceUpdateLog::create([
                    'user_id'          => $user->id,
                    'leave_type_id'    => $type->id,
                    'previous_balance' => $oldValue,
                    'new_balance'      => $newValue,
                    'diff_value'       => $difference,
                    'is_less'          => $is_less,
                    'updated_by'       => auth()->user()->id,
                    'updated_at'       => now(),
                    'description'      => 'Updated by admin using Excel import',
                ]);

                // Transaction
                UserLeaveBalanceTransaction::create([
                    'user_id'          => $user->id,
                    'leave_type_id'    => $type->id,
                    'transaction_type' => 'adjust',
                    'old_balance'      => $oldValue,
                    'update_balance'   => $difference,
                    'new_balance'      => $newValue,
                    'transaction_date' => Carbon::now()->toDateString(),
                    'description'      => 'Updated by admin using Excel import',
                ]);

                if ($getBalance) {
                    $getBalance->available    = $newValue;
                    $getBalance->monthwiseDay = $newValue;
                    $getBalance->save();
                }

                $columnIndex++; // move to next leave column
            }

            return $user;

        } catch (\Exception $e) {
            $row['error']       = $e->getMessage();
            $this->failedRows[] = $row;
            return null;
        }
    }

    public function getFailedRows()
    {
        return $this->failedRows;
    }
}
