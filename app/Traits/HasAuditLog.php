<?php
namespace App\Traits;
use App\Models\AuditLog;

trait HasAuditLog {
    protected static function bootHasAuditLog() {
        static::created(function ($model) { self::logAudit('created', $model); });
        static::updated(function ($model) { self::logAudit('updated', $model); });
        static::deleted(function ($model) { self::logAudit('deleted', $model); });
    }
    protected static function logAudit($action, $model) {
        $user = auth()->user();
        AuditLog::create([
            'tenant_id' => $user->tenant_id ?? null,
            'user_id' => $user->id ?? null,
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'old_values' => $action === 'updated' ? $model->getOriginal() : null,
            'new_values' => $action !== 'deleted' ? $model->getAttributes() : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}
