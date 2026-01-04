<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key
     *
     * @param string $key
     * @param mixed $value
     * @return \App\Models\Setting
     */
    public static function set($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get multiple settings at once
     *
     * @param array $keys
     * @return array
     */
    public static function getMultiple(array $keys)
    {
        $settings = self::whereIn('key', $keys)->pluck('value', 'key')->toArray();
        
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $settings[$key] ?? null;
        }
        
        return $result;
    }

    /**
     * Delete a setting by key
     *
     * @param string $key
     * @return bool
     */
    public static function remove($key)
    {
        return self::where('key', $key)->delete();
    }

    /**
     * Get all settings as key-value array
     *
     * @return array
     */
    public static function getAllSettings()
    {
        return self::pluck('value', 'key')->toArray();
    }

    /**
     * Check if a setting exists
     *
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return self::where('key', $key)->exists();
    }
}