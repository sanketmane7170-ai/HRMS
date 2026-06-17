<?php

namespace Modules\Asset\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Asset\Enums\AssetStatus;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_type_id', 'asset_manufacturer_id',
        'unique_id', 'model',
        'description',
        'purchase_date'
    ];

    protected static function newFactory()
    {
        return \Modules\Asset\Database\factories\AssetFactory::new();
    }

    protected $casts = [
        'status' => AssetStatus::class
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(AssetType::class, 'asset_type_id');
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(AssetManufacturer::class, 'asset_manufacturer_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(AssetAssignment::class)->orderByDesc('id')->whereNotNull('issue_date')
            ->WhereNull('return_date');
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $this->manufacturer->name . " " . $this->type->name . " [ M:" . $attributes['model'] . ", S:" . $attributes['unique_id'] . " ] "
        );
    }
}
