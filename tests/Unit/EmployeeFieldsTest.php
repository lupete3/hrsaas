<?php

use App\Models\Employee;
use App\Models\Setting;
use App\Models\User;
use App\Support\EmployeeFields;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

uses(Tests\TestCase::class);

beforeEach(function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'app.is_saas' => true]);
    DB::purge('sqlite');
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        foreach (['name', 'email', 'password', 'type', 'avatar', 'lang', 'slug'] as $key) $table->string($key)->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->timestamps();
    });
    Schema::create('settings', function (Blueprint $table) {
        $table->id(); $table->unsignedBigInteger('user_id'); $table->string('key'); $table->text('value')->nullable(); $table->timestamps();
        $table->unique(['user_id', 'key']);
    });
    Schema::create('employees', function (Blueprint $table) {
        $table->id(); $table->unsignedBigInteger('user_id'); $table->unsignedBigInteger('created_by');
        $table->string('employee_id')->unique();
        $table->string('employee_status')->default('active');
        foreach (config('employee-fields') as $key => $field) {
            if (($field['locked'] ?? false) || in_array($key, ['documents', 'profile_image', 'employee_id', 'employee_status'])) continue;
            $table->string($field['column'] ?? $key)->nullable();
        }
        $table->timestamps();
    });
    Schema::create('roles', function (Blueprint $table) {
        $table->id(); $table->string('name'); $table->string('guard_name'); $table->unsignedBigInteger('created_by')->nullable();
    });
    Schema::create('permissions', function (Blueprint $table) {
        $table->id(); $table->string('name'); $table->string('guard_name');
    });
    Schema::create('model_has_roles', function (Blueprint $table) {
        $table->unsignedBigInteger('role_id'); $table->string('model_type'); $table->unsignedBigInteger('model_id');
    });
    DB::table('users')->insert([
        ['id' => 1, 'name' => 'Admin', 'type' => 'superadmin', 'created_by' => null],
        ['id' => 2, 'name' => 'Company', 'type' => 'company', 'created_by' => 1],
        ['id' => 3, 'name' => 'Employee', 'type' => 'employee', 'created_by' => 2],
    ]);
    $this->withoutMiddleware();
});

function saveEmployeeFieldConfiguration(array $configuration): void
{
    Setting::updateOrCreate(['user_id' => 1, 'key' => EmployeeFields::KEY], ['value' => json_encode($configuration)]);
}

function hiddenEmployeeFieldConfiguration(): array
{
    $values = EmployeeFields::values();
    foreach ($values as $key => &$value) $value['visible'] = config("employee-fields.$key.locked", false);
    return $values;
}

test('all fields start visible and only superadmin may configure them', function () {
    expect(array_values(array_unique(EmployeeFields::visibility())))->toBe([true]);
    foreach ([2, 3] as $id) {
        $this->actingAs(User::find($id))->get('/settings/employee-fields')->assertForbidden();
        $this->put('/settings/employee-fields', ['configuration' => EmployeeFields::values()])->assertForbidden();
    }
    expect(Setting::count())->toBe(0);
    $this->actingAs(User::find(1))->get('/settings/employee-fields', ['X-Inertia' => 'true'])->assertOk()
        ->assertJsonPath('component', 'settings/employee-fields');
});

test('settings save defaults and reject invalid or unsafe configurations', function () {
    $this->actingAs(User::find(1));
    $values = hiddenEmployeeFieldConfiguration();
    $values['country']['default'] = 'RDC';
    $this->put('/settings/employee-fields', ['configuration' => $values])->assertSessionHasNoErrors();
    expect(EmployeeFields::values()['country'])->toBe(['visible' => false, 'default' => 'RDC']);
    foreach ([['employee_status', null], ['salary', '-1'], ['branch_id', '2'], ['date_of_birth', '2099-01-01'], ['phone', str_repeat('1', 21)]] as [$key, $invalid]) {
        $bad = $values; $bad[$key]['default'] = $invalid;
        $this->put('/settings/employee-fields', ['configuration' => $bad])->assertSessionHasErrors("configuration.$key.default");
    }
    $bad = $values; $bad['name']['visible'] = false;
    $this->put('/settings/employee-fields', ['configuration' => $bad])->assertSessionHasErrors('configuration.name.visible');
    $bad = $values; $bad['department_id']['visible'] = true;
    $this->put('/settings/employee-fields', ['configuration' => $bad])->assertSessionHasErrors('configuration.department_id.visible');
    $bad = $values; $bad['created_by'] = ['visible' => false, 'default' => 9];
    $this->put('/settings/employee-fields', ['configuration' => $bad])->assertSessionHasErrors('configuration');
    expect(EmployeeFields::values()['country']['default'])->toBe('RDC');
});

test('hidden inputs and cached uploads cannot override defaults', function () {
    $values = hiddenEmployeeFieldConfiguration();
    $values['country']['default'] = 'RDC';
    saveEmployeeFieldConfiguration($values);
    $request = Request::create('/', 'POST', ['name' => 'Visible', 'country' => 'Forged', 'salary' => 999, 'documents' => [['document_type_id' => 1]]], [], [
        'profile_image' => UploadedFile::fake()->create('photo.jpg', 1),
        'documents' => [['file' => UploadedFile::fake()->create('document.pdf', 1)]],
    ]);
    $request->allFiles();
    [$prepared, $policy] = EmployeeFields::prepare($request);
    expect($prepared->country)->toBe('RDC')->and($prepared->salary)->toBeNull()
        ->and($prepared->employee_status)->toBe('active')->and($prepared->name)->toBe('Visible')
        ->and($prepared->hasFile('profile_image'))->toBeFalse()->and($prepared->documents)->toBe([]);
    $rules = EmployeeFields::rules(['name' => 'required', 'phone' => 'required|string', 'profile_image' => 'required|image', 'documents.*.file' => 'required|file'], $policy);
    expect(Validator::make($prepared->all(), $rules)->passes())->toBeTrue();
});

test('hidden values including nulls are preserved on edit and visible values still validate', function () {
    $values = hiddenEmployeeFieldConfiguration();
    $values['phone']['default'] = '000';
    saveEmployeeFieldConfiguration($values);
    $employee = new Employee;
    $employee->setRawAttributes(['phone' => null, 'bank_name' => 'Existing bank', 'base_salary' => '123.45', 'employee_status' => 'probation'], true);
    [$request, $policy] = EmployeeFields::prepare(Request::create('/', 'PUT', ['phone' => 'forged', 'bank_name' => 'forged', 'salary' => 999]), $employee);
    expect($request->phone)->toBeNull()->and($request->bank_name)->toBe('Existing bank')
        ->and($request->salary)->toBe('123.45')->and($request->employee_status)->toBe('probation');
    $rules = EmployeeFields::rules(['name' => 'required', 'phone' => 'required|string'], $policy, true);
    expect(Validator::make($request->all(), $rules)->errors()->keys())->toBe(['name']);
});

test('visible fields keep their existing values and validation rules', function () {
    $values = EmployeeFields::values();
    $values['country']['default'] = 'RDC';
    saveEmployeeFieldConfiguration($values);
    [$request, $policy] = EmployeeFields::prepare(Request::create('/', 'POST', ['country' => 'Other country']));
    $rules = ['phone' => 'required|string', 'documents.*.file' => 'required|file'];
    expect($request->country)->toBe('Other country')->and(EmployeeFields::rules($rules, $policy))->toBe($rules);
    expect(Validator::make($request->all(), $rules)->fails())->toBeTrue();
});

test('invalid imported defaults fall back safely and hidden parent fields hide their children', function () {
    saveEmployeeFieldConfiguration(['salary' => ['visible' => false, 'default' => -1], 'employee_status' => ['visible' => false, 'default' => 'invalid'], 'branch_id' => ['visible' => false, 'default' => 99], 'name' => ['visible' => false]]);
    $values = EmployeeFields::values();
    expect($values['salary']['default'])->toBeNull()->and($values['employee_status']['default'])->toBe('active')
        ->and($values['branch_id']['default'])->toBeNull()->and($values['department_id']['visible'])->toBeFalse()
        ->and($values['designation_id']['visible'])->toBeFalse()->and($values['name']['visible'])->toBeTrue();
});

test('an employee can be created and edited with all configurable fields hidden', function () {
    Gate::before(fn () => true);
    $this->actingAs(User::find(2));
    $values = hiddenEmployeeFieldConfiguration();
    $values['country']['default'] = 'RDC';
    saveEmployeeFieldConfiguration($values);
    $this->post(route('hr.employees.store'), ['name' => 'Test Employee', 'email' => 'employee@example.test', 'password' => 'Test-password-123', 'country' => 'forged'])
        ->assertSessionHasNoErrors()->assertSessionHas('success');
    $employee = Employee::firstOrFail();
    expect($employee->country)->toBe('RDC')->and($employee->employee_status)->toBe('active')
        ->and($employee->phone)->toBeNull()->and($employee->base_salary)->toBeNull()
        ->and($employee->employee_id)->toBe('EMP000001');
    $employee->update(['bank_name' => 'Existing bank', 'base_salary' => '150.50']);
    // Route bindings are deliberately disabled with the other middleware in this isolated suite.
    \Illuminate\Support\Facades\Route::put('/_test/employees/{id}', fn (Request $request, $id) => app(\App\Http\Controllers\EmployeeController::class)->update($request, Employee::findOrFail($id)));
    $this->put('/_test/employees/'.$employee->id, ['name' => 'Updated Employee', 'email' => 'employee@example.test', 'bank_name' => 'forged', 'salary' => 999])
        ->assertSessionHasNoErrors()->assertSessionHas('success');
    $employee->refresh();
    expect($employee->bank_name)->toBe('Existing bank')->and($employee->base_salary)->toBe('150.50')
        ->and($employee->user->name)->toBe('Updated Employee');

    Schema::create('branches', function (Blueprint $table) { $table->id(); });
    DB::table('branches')->insert([['id' => 1], ['id' => 2]]);
    $employee->update(['branch_id' => 1, 'department_id' => 9]);
    $values['branch_id']['visible'] = true;
    saveEmployeeFieldConfiguration($values);
    $this->put('/_test/employees/'.$employee->id, ['name' => 'Updated Employee', 'email' => 'employee@example.test', 'branch_id' => 2])
        ->assertSessionHasErrors('branch_id');
    expect($employee->fresh()->branch_id)->toBe('1');
});

test('both candidate conversion submission routes apply employee field defaults', function () {
    Gate::before(fn () => true);
    $this->actingAs(User::find(2));
    Schema::create('candidates', function (Blueprint $table) {
        $table->id(); $table->unsignedBigInteger('created_by'); $table->string('status');
        $table->boolean('is_employee')->default(false); $table->timestamps();
    });
    DB::table('candidates')->insert([['id' => 1, 'created_by' => 2, 'status' => 'Hired'], ['id' => 2, 'created_by' => 2, 'status' => 'Hired']]);
    $values = hiddenEmployeeFieldConfiguration();
    $values['salary']['default'] = '100.50';
    saveEmployeeFieldConfiguration($values);
    foreach (['hr.employees.store', 'hr.recruitment.candidates.store-employee'] as $index => $route) {
        $this->post(route($route), ['candidate_id' => $index + 1, 'name' => 'Converted Employee', 'email' => "candidate$index@example.test", 'password' => 'Test-password-123'])
            ->assertSessionHasNoErrors()->assertSessionHas('success');
        expect(DB::table('candidates')->where('id', $index + 1)->value('is_employee'))->toBe(1);
    }
    expect(Employee::count())->toBe(2)->and(Employee::pluck('base_salary')->all())->toBe(['100.50', '100.50']);
});
