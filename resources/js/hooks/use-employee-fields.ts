import { usePage } from '@inertiajs/react';

export function useEmployeeFields() {
    const { employeeFieldVisibility = {} } = usePage().props as { employeeFieldVisibility?: Record<string, boolean> };
    const visible = (field: string) => employeeFieldVisibility[field.split('.')[0]] !== false;
    const visibleErrors = (errors: Record<string, string>) => Object.fromEntries(Object.entries(errors).filter(([key]) => visible(key)));
    const groups = [
        ['name', 'email', 'password', 'employee_id', 'biometric_emp_id', 'phone', 'date_of_birth', 'gender', 'profile_image'],
        ['branch_id', 'department_id', 'designation_id', 'shift_id', 'attendance_policy_id', 'date_of_joining', 'employment_type', 'employee_status'],
        ['address_line_1', 'address_line_2', 'city', 'state', 'country', 'postal_code', 'emergency_contact_name', 'emergency_contact_relationship', 'emergency_contact_number'],
        ['bank_name', 'account_holder_name', 'account_number', 'bank_identifier_code', 'bank_branch', 'tax_payer_id', 'salary'],
        ['documents'],
    ];
    const availableSteps = groups.flatMap((fields, index) => fields.some(visible) ? [index] : []);
    const nextStep = (step: number) => availableSteps.find(index => index > step) ?? step;
    const previousStep = (step: number) => availableSteps.filter(index => index < step).at(-1) ?? 0;
    const lastStep = availableSteps.at(-1) ?? 0;
    return { visible, visibleErrors, availableSteps, nextStep, previousStep, lastStep };
}
