<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Payroll\Entities\AdvanceRequest;

class AdvanceRequestHistory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function advanceRequest()
    {
        return $this->belongsTo(AdvanceRequest::class, 'advance_request_id');
    }
}
