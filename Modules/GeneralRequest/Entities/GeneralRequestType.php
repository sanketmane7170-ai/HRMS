<?php

namespace Modules\GeneralRequest\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\GeneralRequest\Database\factories\GeneralRequestTypeFactory;

class GeneralRequestType extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

}
