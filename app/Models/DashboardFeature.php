<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DashboardFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'status',
    ];


    /**
     * Get the user's first name.
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Str::slug($value),
        );
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class)->where('status', User::STATUS_ACTIVE);
    }


    

    
}
