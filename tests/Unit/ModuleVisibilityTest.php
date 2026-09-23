<?php

use App\Models\Setting;
use App\Models\User;
use App\Support\ModuleVisibility;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

beforeEach(function () {
    // An isolated database: these tests never touch the installed application's data.
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    DB::purge('sqlite');
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('type');
    });
    Schema::create('settings', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('key');
        $table->text('value')->nullable();
        $table->timestamps();
        $table->unique(['user_id', 'key']);
    });
    DB::table('users')->insert([['id' => 1, 'type' => 'superadmin'], ['id' => 2, 'type' => 'company'], ['id' => 3, 'type' => 'employee']]);
    $this->withoutMiddleware();
});

test('all modules are visible by default including the five requested modules', function () {
    $values = ModuleVisibility::values();
    foreach (['calendar', 'organization-chart', 'holidays', 'announcements', 'award-types'] as $key) {
        expect($values[$key])->toBeTrue();
    }
    expect(array_unique(array_values($values)))->toBe([true]);
});

test('only the superadmin can view or change visibility', function () {
    foreach ([2, 3] as $id) {
        $this->actingAs(User::find($id))->get('/settings/modules')->assertForbidden();
        $this->put('/settings/modules', ['visibility' => ModuleVisibility::values()])->assertForbidden();
    }
    expect(Setting::count())->toBe(0);
});

test('superadmin can view the settings and persist and restore modules', function () {
    $this->actingAs(User::find(1));
    $this->get('/settings/modules', ['X-Inertia' => 'true'])->assertOk()
        ->assertJsonPath('component', 'settings/module-visibility')
        ->assertJsonPath('props.visibility.calendar', true);
    $values = ModuleVisibility::values();
    foreach (['calendar', 'organization-chart', 'holidays', 'announcements', 'award-types'] as $key) {
        $values[$key] = false;
    }
    $this->put('/settings/modules', ['visibility' => $values])->assertRedirect();
    expect(ModuleVisibility::values())->toBe($values);
    $this->actingAs(User::find(2));
    expect(ModuleVisibility::values()['announcements'])->toBeFalse();
    $this->actingAs(User::find(1));
    $values = array_fill_keys(array_keys($values), true);
    $this->put('/settings/modules', ['visibility' => $values])->assertRedirect();
    expect(ModuleVisibility::values())->toBe($values);
    expect(Setting::count())->toBe(1);
});

test('invalid incomplete and unknown settings are rejected without saving', function () {
    $this->actingAs(User::find(1));
    $values = ModuleVisibility::values();
    $this->putJson('/settings/modules', ['visibility' => ['calendar' => false]])->assertUnprocessable();
    $this->putJson('/settings/modules', ['visibility' => [...$values, 'unknown' => false]])->assertUnprocessable();
    $values['calendar'] = 'invalid';
    $this->putJson('/settings/modules', ['visibility' => $values])->assertUnprocessable();
    expect(Setting::count())->toBe(0);
});

test('company settings cannot override the superadmin visibility', function () {
    Setting::create(['user_id' => 1, 'key' => ModuleVisibility::KEY, 'value' => '{"calendar":false}']);
    Setting::create(['user_id' => 2, 'key' => ModuleVisibility::KEY, 'value' => '{"calendar":true}']);
    $this->actingAs(User::find(2));
    expect(ModuleVisibility::values()['calendar'])->toBeFalse();
});
