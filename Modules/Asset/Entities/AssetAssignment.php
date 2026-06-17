<?php

namespace Modules\Asset\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Asset\Enums\AssetStatus;

class AssetAssignment extends Model
{
    use HasFactory;
    protected static function boot()
    {
        parent::boot();
        static::created(
            function ($model) {
                $asset = $model->asset;
                $asset->status = AssetStatus::Assigned;
                $asset->save();
            }
        );
        static::updating(
            function ($model) {
                $asset = $model->asset;
                $asset->status = AssetStatus::Available;
                $asset->save();
            }
        );
    }


    public $timestamps = false;

    protected $fillable = [
        'issue_date', 'return_date', 'asset_id', 'user_id'
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
