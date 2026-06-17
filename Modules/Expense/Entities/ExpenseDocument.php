<?php

namespace Modules\Expense\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExpenseDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_name',
        'document',
        'expense_id',
    ];
    
     // Define relationship with the Expense model
     public function expense()
     {
         return $this->belongsTo(Expense::class);
     }
}
