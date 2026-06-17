<?php

namespace Modules\Announcement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnnouncementType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'color'
    ];
}
