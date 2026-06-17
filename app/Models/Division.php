<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function branch()
    {
        return $this->belongsTo(Department::class);
    }

    public function users()
    {
        return $this->hasMany(User::class)->where('status', User::STATUS_ACTIVE);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id')->withDefault([
            'name' => 'Not Assigned'
        ]);
    }
}
