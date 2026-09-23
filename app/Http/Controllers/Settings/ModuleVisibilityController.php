<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\ModuleVisibility;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ModuleVisibilityController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->type === 'superadmin', 403);

        return Inertia::render('settings/module-visibility', [
            'modules' => config('modules'),
            'visibility' => ModuleVisibility::values(),
        ]);
    }

    public function update(Request $request)
    {
        abort_unless($request->user()->type === 'superadmin', 403);
        $keys = array_keys(config('modules'));
        $rules = ['visibility' => ['required', 'array:'.implode(',', $keys)]];
        foreach ($keys as $key) {
            $rules['visibility.'.$key] = ['required', 'boolean'];
        }
        $data = $request->validate($rules);
        $values = array_map(fn ($value) => (bool) $value, $data['visibility']);

        Setting::updateOrCreate(
            ['user_id' => ModuleVisibility::ownerId(), 'key' => ModuleVisibility::KEY],
            ['value' => json_encode($values, JSON_THROW_ON_ERROR)]
        );

        return back()->with('success', 'La visibilité des modules a été enregistrée.');
    }
}
