<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPromotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'old_designation_id',
        'new_designation_id',
        'promotion_date',
        'remarks',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function oldDesignation()
    {
        return $this->belongsTo(Designation::class, 'old_designation_id');
    }

    public function newDesignation()
    {
        return $this->belongsTo(Designation::class, 'new_designation_id');
    }
}
