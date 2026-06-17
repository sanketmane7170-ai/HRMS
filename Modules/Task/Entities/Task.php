<?php
namespace Modules\Task\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'assigned_to',
        'assigned_by',
        'priority',
        'status',
        'start_date',
        'end_date',
    ];

    public function assigned_to_user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    public function assigned_by_user()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
   
    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }
}
