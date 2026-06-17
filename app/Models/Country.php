<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code'
    ];

    public function users()
    {
        return $this->hasMany(UserProfile::class)
            ->whereHas('user', function ($query) {
                $query->where('status', User::STATUS_ACTIVE);
            });
    }

    public function female_users()
    {
        return $this->hasMany(UserProfile::class)
            ->where('gender', Gender::Female)
            ->whereHas('user', function ($query) {
                $query->where('status', User::STATUS_ACTIVE);
            });
    }

    public function male_users()
    {
        return $this->hasMany(UserProfile::class)
            ->where('gender', Gender::Male)
            ->whereHas('user', function ($query) {
                $query->where('status', User::STATUS_ACTIVE);
            });
    }
}
