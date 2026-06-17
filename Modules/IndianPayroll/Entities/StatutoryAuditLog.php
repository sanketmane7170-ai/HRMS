<?php

namespace Modules\IndianPayroll\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class StatutoryAuditLog extends Model
{
    protected $table = 'ip_statutory_audit_logs';

    protected $fillable = [
        'entity_type', 'entity_id', 'action', 'old_values', 'new_values', 'changed_by',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
