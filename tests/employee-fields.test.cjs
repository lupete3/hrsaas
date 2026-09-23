const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const ts = require('typescript');

function fields(values) {
    const exports = {};
    const { outputText } = ts.transpileModule(fs.readFileSync('resources/js/hooks/use-employee-fields.ts', 'utf8'), { compilerOptions: { module: ts.ModuleKind.CommonJS } });
    vm.runInNewContext(outputText, { exports, require: () => ({ usePage: () => ({ props: { employeeFieldVisibility: values } }) }) });
    return exports.useEmployeeFields();
}

test('existing forms retain all five steps without configuration', () => {
    const form = fields();
    assert.equal(form.visible('phone'), true);
    assert.equal(form.availableSteps.join(','), '0,1,2,3,4');
});

test('hidden documents do not cause a validation error or an empty final step', () => {
    const form = fields({ documents: false });
    assert.equal(form.visible('documents.0.file'), false);
    assert.equal(form.lastStep, 3);
    assert.equal(Object.keys(form.visibleErrors({ 'documents.0.file': 'Required', email: 'Required' })).join(','), 'email');
});

test('navigation skips hidden sections and keeps optional visible fields reachable', () => {
    const visibility = Object.fromEntries(['branch_id', 'department_id', 'designation_id', 'shift_id', 'attendance_policy_id', 'date_of_joining', 'employment_type', 'employee_status'].map(key => [key, false]));
    const form = fields(visibility);
    assert.equal(form.nextStep(0), 2);
    assert.equal(form.previousStep(2), 0);
    visibility.shift_id = true;
    assert.equal(fields(visibility).nextStep(0), 1);
});

test('creation editing and candidate conversion apply the same controls', () => {
    for (const file of ['hr/employees/create', 'hr/employees/edit', 'hr/recruitment/candidates/convert-to-employee']) {
        const source = fs.readFileSync(`resources/js/pages/${file}.tsx`, 'utf8');
        assert.equal(ts.createSourceFile(file, source, ts.ScriptTarget.Latest, true, ts.ScriptKind.TSX).parseDiagnostics.length, 0);
        for (const key of ['phone', 'profile_image', 'salary', 'branch_id', 'documents']) assert.ok(source.includes(`visible('${key}')`));
        assert.ok(source.includes('visibleErrors(e)'));
        assert.ok(source.includes('nextStep(s)'));
        assert.ok(source.includes('previousStep(s)'));
    }
});
