<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalDetails extends Model
{
    use HasFactory;

    protected $fillable = [
           'name','base_url','unique_code'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d'
    ];
}
