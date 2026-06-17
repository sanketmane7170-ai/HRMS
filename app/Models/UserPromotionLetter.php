<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPromotionLetter extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type()
    {
        return $this->belongsTo(UserPromotionType::class);
    }


    public function oldPosition()
    {
        return $this->belongsTo(Designation::class, 'old_designation_id');
    }

    public function newPosition()
    {
        return $this->belongsTo(Designation::class, 'new_designation_id');
    }
    public function oldDepartment()
    {
        return $this->belongsTo(Department::class, 'old_department_id');
    }

    public function newDepartment()
    {
        return $this->belongsTo(Department::class, 'new_department_id');
    }
}
