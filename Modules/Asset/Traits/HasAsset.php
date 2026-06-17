<?php

namespace Modules\Asset\Traits;

use Modules\Asset\Entities\AssetAssignment;

trait HasAsset
{

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class);
    }
}
