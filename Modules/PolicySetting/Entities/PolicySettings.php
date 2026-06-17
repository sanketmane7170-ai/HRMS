<?php

namespace Modules\PolicySetting\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\PolicySetting\Database\factories\PolicySettingsFactory;

class PolicySettings extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'type', 
        'name', 
        'status', 
        'policy', 
        'description', 
    ];
    
   
}
