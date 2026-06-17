<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value'
    ];


    /**
     * Clear route,config,route,view  cache
     * @return void
     */
    public static function clear(): void
    {
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');
    }

    /**
     * Add or update the setting model
     * @param string $key
     * @param string $value
     * @param nullable $file
     *
     * @return \App\Models\Setting $setting
     */
    public static function addOrUpdate($key, $value, $file = null)
    {
        $setting = Setting::firstOrNew(['key' => $key]);
        $setting->key = $key;
        if ($file) {
            @unlink(public_path($setting->value));
        }
        $setting->value = $value;

        return $setting->save();
    }
}
