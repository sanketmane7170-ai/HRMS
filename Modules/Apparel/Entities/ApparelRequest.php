<?php

namespace Modules\Apparel\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Apparel\Database\factories\ApparelRequestFactory;
use App\Models\User;

class ApparelRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','apparel_id','number_of_apparel','status'
    ];
    
    public function apparel()
    {
        return $this->belongsTo(Apparel::class, 'apparel_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
