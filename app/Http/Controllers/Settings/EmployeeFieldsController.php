<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\EmployeeFields;
use App\Support\ModuleVisibility;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class EmployeeFieldsController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->type === 'superadmin', 403);
        return Inertia::render('settings/employee-fields', [
            'fields' => config('employee-fields'),
            'configuration' => EmployeeFields::values(),
        ]);
    }

    public function update(Request $request)
    {
        abort_unless($request->user()->type === 'superadmin', 403);
        $catalog = config('employee-fields');
        $rules = ['configuration' => ['required', 'array:'.implode(',', array_keys($catalog))]];
        foreach ($catalog as $key => $field) {
            $rules["configuration.$key"] = ['required', 'array:visible,default'];
            $rules["configuration.$key.visible"] = ['required', 'boolean'];
            $rules["configuration.$key.default"] = ($field['locked'] ?? false) || $field['type'] === 'none'
                ? ['present', 'prohibited'] : explode('|', $field['rules']);
        }
        $attributes = [];
        foreach ($catalog as $key => $field) {
            $attributes["configuration.$key.default"] = __($field['label']);
        }
        $data = $request->validate($rules, [
            'required' => 'Une valeur est obligatoire pour :attribute.',
            'in' => 'Choisissez une valeur proposée pour :attribute.',
            'max' => 'La valeur de :attribute dépasse la limite autorisée (:max).',
            'min' => 'La valeur de :attribute doit être au moins égale à :min.',
            'date_format' => 'Renseignez une date valide au format année-mois-jour pour :attribute.',
            'before_or_equal' => 'La date de naissance ne peut pas être dans le futur.',
            'numeric' => 'Renseignez un nombre pour :attribute.',
            'decimal' => 'Utilisez au maximum deux décimales pour :attribute.',
            'prohibited' => 'Ce champ ne peut pas recevoir de valeur par défaut.',
        ], $attributes)['configuration'];
        foreach ($catalog as $key => $field) {
            if (($field['locked'] ?? false) && !$data[$key]['visible']) {
                throw ValidationException::withMessages(["configuration.$key.visible" => 'Ce champ est nécessaire à l’identité ou à la connexion de l’employé.']);
            }
            $data[$key] = ['visible' => (bool) $data[$key]['visible'], 'default' => $data[$key]['default'] ?? null];
        }
        foreach (['department_id' => 'branch_id', 'designation_id' => 'department_id'] as $child => $parent) {
            if ($data[$child]['visible'] && !$data[$parent]['visible']) {
                throw ValidationException::withMessages(["configuration.$child.visible" => 'Affichez aussi le champ parent ou masquez les deux champs.']);
            }
        }
        $owner = ModuleVisibility::ownerId();
        abort_unless($owner, 422, 'Aucun super administrateur trouvé.');
        Setting::updateOrCreate(['user_id' => $owner, 'key' => EmployeeFields::KEY], ['value' => json_encode($data, JSON_THROW_ON_ERROR)]);
        return back()->with('success', 'Les champs du formulaire employé ont été configurés.');
    }
}
