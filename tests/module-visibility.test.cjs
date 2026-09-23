const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const ts = require('typescript');

function load(file, imports = {}) {
    const { outputText } = ts.transpileModule(fs.readFileSync(file, 'utf8'), {
        compilerOptions: { module: ts.ModuleKind.CommonJS },
    });
    const exports = {};
    vm.runInNewContext(outputText, { exports, require: name => imports[name] });
    return exports;
}

function visibility(values) {
    return load('resources/js/hooks/use-module-visibility.ts', {
        '@inertiajs/react': { usePage: () => ({ props: { moduleVisibility: values } }) },
        '@/utils/authorization': load('resources/js/utils/authorization.ts'),
    }).useModuleVisibility();
}

test('missing settings preserve existing permission behavior', () => {
    const { canShow } = visibility(undefined);
    assert.equal(canShow(['manage-calendar'], 'manage-calendar'), true);
    assert.equal(canShow([], 'manage-calendar'), false);
});

test('hiding a module hides both employee and manager presentation', () => {
    for (const key of ['calendar', 'organization-chart', 'holidays', 'announcements', 'award-types']) {
        const { canShow, isVisible } = visibility({ [key]: false });
        assert.equal(isVisible(key), false);
        assert.equal(canShow([`manage-${key}`], `manage-${key}`), false);
        assert.equal(canShow([`view-${key}`], `view-${key}`), false);
        assert.equal(canShow(['manage-employees'], 'manage-employees'), true);
    }
});

test('showing a module never grants missing access permissions', () => {
    const { canShow } = visibility({ announcements: true });
    assert.equal(canShow([], 'view-announcements'), false);
    assert.equal(canShow(['view-announcements'], 'view-announcements'), true);
});

test('attendance visibility also controls the employee clock widget', () => {
    assert.equal(visibility({ 'attendance-records': false }).canShow(['clock-in-out'], 'clock-in-out'), false);
    assert.equal(visibility({ 'attendance-records': true }).canShow(['clock-in-out'], 'clock-in-out'), true);
});
