<?php

namespace Modules\Onboarding\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class OperationalReadiness extends Model
{
    use HasFactory;

    protected $table = 'operational_readiness';

    protected $fillable = [
        'user_id',
        'branch_notification_sent_at',
        'it_login_created',
        'email_created',
        'uniform_status',
        'induction_completed',
        'asset_id',
        'apparel_id',
    ];

    public function asset()
    {
        return $this->belongsTo(\Modules\Asset\Entities\Asset::class, 'asset_id');
    }

    public function apparel()
    {
        return $this->belongsTo(\Modules\Apparel\Entities\Apparel::class, 'apparel_id');
    }

    protected $casts = [
        'branch_notification_sent_at' => 'datetime',
        'it_login_created' => 'boolean',
        'email_created' => 'boolean',
        'induction_completed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
