<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\Training\Entities\Training;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'start_number',
        'slug',
        'code',
        'address',
        'login_radius',
        'latitude',
        'longitude',
        'budget',
        'logo',
        'small_logo',
        'sign',
        'header',
        'footer',
        'cancel_off_credit',
        'cancel_off_amount',
        'over_time',
    ];

    /**
     * Get the user's first name.
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => Str::slug($value),
        );
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class)->where('status', User::STATUS_ACTIVE);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id')->withDefault([
            'name' => 'Not Assigned',
        ]);
    }

    public function user_count()
    {
        return $this->hasMany('App\Models\User', 'department_id', 'id')
            ->where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            });
    }
    public function trainings()
    {
        return $this->hasMany(Training::class);
    }
    public function allowances()
    {
        return $this->hasMany(DepartmentAllowance::class);
    }

    public function recruitmentJobs(): HasMany
    {
        return $this->hasMany(\Modules\Recruitment\Entities\Job::class, 'department_id');
    }
}
