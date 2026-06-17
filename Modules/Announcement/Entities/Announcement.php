<?php

namespace Modules\Announcement\Entities;

use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'announcement_type_id', 'start_at', 'end_at', 'file', 'body','user_id','department_id','is_added'
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(AnnouncementType::class, 'announcement_type_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function getFileAttribute($value)
    {
        if ($value) {
            // public_path('uploads/users/'.$this->user_id.'/announcement/')
            return url('uploads/users/' . $this->user_id . '/announcement/' . $value);
            // or asset('storage/announcements/' . $value) if stored in storage/app/public
        }
        return null;
    }
}
