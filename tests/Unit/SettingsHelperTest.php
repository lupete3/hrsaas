<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

beforeEach(function () {
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
        $table->text('value');
    });
    $this->originalStoragePath = storage_path();
    $this->testStoragePath = sys_get_temp_dir().'/hrm-settings-'.bin2hex(random_bytes(8));
    mkdir($this->testStoragePath);
    touch($this->testStoragePath.'/installed');
    $this->app->useStoragePath($this->testStoragePath);
    $this->app->instance('request', Request::create('/'));
});

afterEach(function () {
    $this->app->useStoragePath($this->originalStoragePath);
    unlink($this->testStoragePath.'/installed');
    rmdir($this->testStoragePath);
});

test('missing settings owner returns an array compatible with currency defaults', function (bool $saas) {
    config(['app.is_saas' => $saas]);
    // Importing only the other account type must not break guest pages.
    DB::table('users')->insert(['type' => $saas ? 'company' : 'superadmin']);
    expect(settings())->toBe([]);
    expect(array_merge(settings(), ['currencySymbol' => '$']))->toBe(['currencySymbol' => '$']);
})->with([true, false]);

test('settings preserves configured values for the matching owner', function (bool $saas) {
    config(['app.is_saas' => $saas]);
    DB::table('users')->insert(['id' => 1, 'type' => $saas ? 'superadmin' : 'company']);
    DB::table('settings')->insert(['user_id' => 1, 'key' => 'defaultCurrency', 'value' => 'CDF']);
    expect(settings())->toBe(['defaultCurrency' => 'CDF']);
})->with([true, false]);

test('installation and an explicitly missing owner return empty arrays', function () {
    expect(settings(0))->toBe([]);
    $this->app->instance('request', Request::create('/install/database'));
    expect(settings())->toBe([]);
});
