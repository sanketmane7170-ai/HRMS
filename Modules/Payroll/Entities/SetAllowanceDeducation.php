<?php

namespace Modules\Payroll\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Payroll\Database\factories\SetAllowanceDeducationFactory;

class SetAllowanceDeducation extends Model
{
    use HasFactory;

    protected $fillable = [
        'type','name', 'amount','document_request_id'
    ];
    
    // protected static function newFactory(): SetAllowanceDeducationFactory
    // {
    //     //return SetAllowanceDeducationFactory::new();
    // }
}
