<?php

namespace Modules\Resignation\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class ResignationAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'resignation_id',
        'action_by',
        'action_role',
        'action_type',
        'comments',
        'action_date'
    ];

    protected $casts = [
        'action_date' => 'datetime',
    ];

    public function resignation()
    {
        return $this->belongsTo(Resignation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
