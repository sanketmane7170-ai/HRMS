<?php

namespace Modules\Recruitment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ApplicationLog extends Model
{
    protected $table = 'recruitment_application_logs';

    // Mass assignable attributes updated by Sanket to fix timeline date issues
    protected $fillable = [
        'application_id',
        'previous_stage',
        'new_stage',
        'changed_by',
        'action',
        'description',
        'metadata',
        'created_by',
        'updated_by',
        'created_at', // Timestamp fix by Sanket
        'updated_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime', // Timestamp cast by Sanket
        'updated_at' => 'datetime'
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
