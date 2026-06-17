<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiErrorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_name',
        'response',
        'status',
        'user_id'
    ];
}
