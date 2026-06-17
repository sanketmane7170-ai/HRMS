<?php

namespace Modules\FileManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Department;
class FileDownloadLink extends Model
{
    use HasFactory;

    protected $guarded = [];
}