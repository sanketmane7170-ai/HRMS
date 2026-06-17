<?php
namespace Modules\Payroll\Entities;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'deduction_type',
        'amount',
        'salary_id',
        'percentage_amount',
        'date',
        'month_code',
        'year',
        'is_fixed_for_current_month',
        'document_request_id',
        'branch_id',
        'remark',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
