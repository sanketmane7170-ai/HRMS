<?php

namespace Modules\Recruitment\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class BulkOperationLog extends Model
{
    protected $table = 'bulk_operation_logs';

    protected $fillable = [
        'action',
        'application_ids',
        'user_id',
        'metadata',
        'status',
        'message'
    ];

    protected $casts = [
        'application_ids' => 'array',
        'metadata' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
