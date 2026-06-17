<?php

namespace Modules\GeneralRequest\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\GeneralRequest\Database\factories\GeneralRequestFactory;
use App\Models\User;

class GeneralRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','type_id','amount','note','status','date'
    ];
    
    public function type()
    {
        return $this->belongsTo(GeneralRequestType::class, 'type_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
