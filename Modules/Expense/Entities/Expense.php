<?php

namespace Modules\Expense\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Expense\Database\factories\ExpensesFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Expense\Enums\ExpenseStatus;
use App\Models\User;

class Expense extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'created_by',
        'date',
        'name',
        'payment_mode',
        'remark',
        'expense_type_id',
        'amount',
        'user_id',
        'hr_id',
        'hr_status',
        'hr_comments',
        'lm_id',
        'lm_status',
        'lm_comments'
    ];
    protected $casts = [
        'status' => ExpenseStatus::class
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ExpenseType::class, 'expense_type_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function documents()
    {
        return $this->hasMany(ExpenseDocument::class);
    }
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
