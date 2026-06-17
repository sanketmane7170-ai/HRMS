<?php

namespace Modules\PerformanceReview\Entities;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class KpiScoreLevel extends Model
{
    protected $fillable = ['role_id','step_number', 'approvers','level'];

    protected $casts = [
        'approvers' => 'array',
    ];
}
