<?php

namespace Modules\IndianPayroll\Entities\Traits;

use Modules\IndianPayroll\Entities\StatutoryAuditLog;

/**
 * Attach to any statutory Eloquent model to get automatic audit logging.
 * Every create, update, and soft-delete is written to ip_statutory_audit_logs
 * with the before/after diff and the currently authenticated user.
 *
 * Usage: add `use LogsStatutoryChanges;` to the entity class.
 */
trait LogsStatutoryChanges
{
    public static function bootLogsStatutoryChanges(): void
    {
        static::created(function (self $model) {
            StatutoryAuditLog::create([
                'entity_type' => class_basename($model),
                'entity_id' => $model->getKey(),
                'action' => 'created',
                'old_values' => null,
                'new_values' => $model->getAttributes(),
                'changed_by' => auth()->id(),
            ]);
        });

        static::updated(function (self $model) {
            $dirty = $model->getDirty();
            if (empty($dirty)) {
                return;
            }

            $old = array_intersect_key($model->getOriginal(), $dirty);

            StatutoryAuditLog::create([
                'entity_type' => class_basename($model),
                'entity_id' => $model->getKey(),
                'action' => 'updated',
                'old_values' => $old,
                'new_values' => $dirty,
                'changed_by' => auth()->id(),
            ]);
        });

        static::deleted(function (self $model) {
            StatutoryAuditLog::create([
                'entity_type' => class_basename($model),
                'entity_id' => $model->getKey(),
                'action' => 'deleted',
                'old_values' => $model->getAttributes(),
                'new_values' => null,
                'changed_by' => auth()->id(),
            ]);
        });
    }
}
