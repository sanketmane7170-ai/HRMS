<?php

namespace Modules\FileManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Department;
class FileManager extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    protected static function newFactory()
    {
        return \Modules\FileManager\Database\factories\FileManagerFactory::new();
    }

    public function employee()
    {
        return $this->belongsTo(User::class,'employee_id','id');
    } 

    public function department()
    {
        return $this->belongsTo(Department::class,'department_id','id');
    }
}
