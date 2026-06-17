<?php

namespace Modules\Attendance\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Enums\CheckinType;

trait HasAttendance
{

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class);
    }

    public function checkinsTypeIn(): HasMany
    {
        return $this->hasMany(Checkin::class)
            ->where('type', CheckinType::IN);
    }

    public function checkinsTypeOut(): HasMany
    {
        return $this->hasMany(Checkin::class)
            ->where('type', CheckinType::OUT);
    }
}
