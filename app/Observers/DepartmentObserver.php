<?php

namespace App\Observers;

use App\Models\Department;
use Illuminate\Support\Str;

class DepartmentObserver
{
    /**
     * Handle the Department "creating" event.
     */
    public function creating(Department $department): void
    {
        $department->slug = Str::slug($department->name);
    }

    /**
     * Handle the Department "updating" event.
     */
    public function updating(Department $department): void
    {
        $department->slug = Str::slug($department->name);
    }
}
