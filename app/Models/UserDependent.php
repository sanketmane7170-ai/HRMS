<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\Relation;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDependent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'relation',
        'nationality',
        'contact',
        'address',
        'gender',
        'date_of_birth'
    ];

    protected $casts = [
        'relation' => Relation::class,
    ];


    public function name(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return $attributes['first_name'] . " " . $attributes['middle_name'] . " " . $attributes['last_name'];
            },
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
}
