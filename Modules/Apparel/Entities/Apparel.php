<?php

namespace Modules\Apparel\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Apparel\Database\factories\ApparelFactory;

class Apparel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'number_of_given',
    ];
    
    protected static function newFactory(): ApparelFactory
    {
        //return ApparelFactory::new();
    }
}
