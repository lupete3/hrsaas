<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;

class ModuleVisibility
{
    public const KEY = 'module_visibility';

    public static function ownerId(): ?int
    {
        return User::where('type', 'superadmin')->orderBy('id')->value('id');
    }

    public static function values(): array
    {
        $defaults = array_fill_keys(array_keys(config('modules', [])), true);
        $ownerId = self::ownerId();
        if (!$ownerId) {
            return $defaults;
        }

        $saved = json_decode(Setting::where('user_id', $ownerId)->where('key', self::KEY)->value('value') ?? '{}', true);
        foreach (is_array($saved) ? $saved : [] as $key => $value) {
            if (array_key_exists($key, $defaults) && is_bool($value)) {
                $defaults[$key] = $value;
            }
        }

        return $defaults;
    }
}
