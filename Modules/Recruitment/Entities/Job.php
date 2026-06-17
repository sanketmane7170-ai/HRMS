<?php

namespace Modules\Recruitment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Recruitment\Database\factories\JobFactory;
use App\Models\User;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

/**
 * @property int|null $hiring_manager_id
 * @property string $status
 * @property string|null $application_deadline
 * @property bool $is_open
 */
class Job extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'recruitment_jobs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'department_id',
        'role_id',
        'hiring_type',
        'job_type',
        'experience_level',
        'location',
        'description',
        'requirements',
        'responsibilities',
        'skills',
        'benefits',
        'min_salary',
        'max_salary',
        'remote_work',
        'positions_available',
        'application_deadline',
        'is_featured',
        'status',
        'hiring_manager_id',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'requirements' => 'array',
        'skills' => 'array',
        'remote_work' => 'boolean',
        'is_featured' => 'boolean',
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'positions_available' => 'integer',
        'application_deadline' => 'date',
        'hiring_manager_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Boot method to set default values.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($job) {
            if (auth()->check()) {
                $job->created_by = auth()->id();
            }
        });

        static::updating(function ($job) {
            if (auth()->check()) {
                $job->updated_by = auth()->id();
            }
        });
    }

    // Relationships
    
    /**
     * Get the department that owns the job.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the role associated with the job.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the user who created the job.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the job.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all applications for this job.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    // Scopes
    
    /**
     * Scope for active jobs.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for internal jobs.
     */
    public function scopeInternal($query)
    {
        return $query->whereIn('hiring_type', ['internal', 'internal_external']);
    }

    /**
     * Scope for external jobs.
     */
    public function scopeExternal($query)
    {
        return $query->whereIn('hiring_type', ['external', 'internal_external']);
    }

    // Accessors
    
    /**
     * Get formatted status.
     */
    public function getFormattedStatusAttribute(): string
    {
        return ucwords(str_replace('-', ' ', $this->status));
    }

    /**
     * Get formatted hiring type.
     */
    public function getFormattedHiringTypeAttribute(): string
    {
        return ucwords(str_replace('_', ' + ', $this->hiring_type));
    }

    /**
     * Get total applications count.
     */
    public function getApplicationsCountAttribute(): int
    {
        return $this->applications()->count();
    }

    /**
     * Check if job accepts internal applications.
     */
    public function getAcceptsInternalAttribute(): bool
    {
        return in_array($this->hiring_type, ['internal', 'internal_external']);
    }

    /**
     * Check if job accepts external applications.
     */
    public function getAcceptsExternalAttribute(): bool
    {
        return in_array($this->hiring_type, ['external', 'internal_external']);
    }
    
    /**
     * Determine if the job is still open for applications.
     * Sanket
     */
    public function getIsOpenAttribute(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->application_deadline && \Carbon\Carbon::parse($this->application_deadline)->isPast()) {
            return false;
        }

        return true;
    }
}
