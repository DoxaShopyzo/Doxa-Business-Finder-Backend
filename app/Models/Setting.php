<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model {
    protected $fillable = ['tenant_id', 'group', 'key', 'value'];
    protected $casts = ['value' => 'array'];
    public static function get($key, $group = 'general', $tenantId = null) {
        $setting = self::where('key', $key)->where('group', $group)->where('tenant_id', $tenantId)->first();
        return $setting ? $setting->value : null;
    }
    public static function set($key, $value, $group = 'general', $tenantId = null) {
        return self::updateOrCreate(
            ['key' => $key, 'group' => $group, 'tenant_id' => $tenantId],
            ['value' => $value]
        );
    }
}
