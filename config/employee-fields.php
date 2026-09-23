<?php

// Only fields explicitly reviewed against employee/user storage may be configured.
$text = fn ($label, $group, $max = 255) => [
    'label' => $label, 'group' => $group, 'type' => 'text',
    'rules' => "nullable|string|max:$max", 'default' => null,
];
$date = fn ($label) => ['label' => $label, 'group' => 'Employment', 'type' => 'date', 'rules' => 'nullable|date_format:Y-m-d', 'default' => null];
$none = fn ($label, $group) => ['label' => $label, 'group' => $group, 'type' => 'none', 'rules' => 'nullable', 'default' => null];

return [
    'name' => ['label' => 'Full Name', 'group' => 'Personal', 'locked' => true],
    'email' => ['label' => 'Email', 'group' => 'Personal', 'locked' => true],
    'password' => ['label' => 'Password', 'group' => 'Personal', 'locked' => true],
    'employee_id' => $none('Employee ID', 'Personal') + ['hint' => 'L’identifiant reste généré automatiquement.'],
    'biometric_emp_id' => $none('Employee Code', 'Personal') + ['hint' => 'Sans code biométrique à la création ; les codes existants sont conservés.'],
    'phone' => $text('Phone Number', 'Personal', 20),
    'date_of_birth' => array_replace($date('Date of Birth'), ['group' => 'Personal', 'rules' => 'nullable|date_format:Y-m-d|before_or_equal:today']),
    'gender' => ['label' => 'Gender', 'group' => 'Personal', 'type' => 'select', 'rules' => 'nullable|in:male,female,other', 'default' => null, 'options' => ['male' => 'Male', 'female' => 'Female', 'other' => 'Other']],
    'profile_image' => $none('Profile Image', 'Personal') + ['hint' => 'Aucune photo imposée à la création ; la photo existante est conservée.'],
    'branch_id' => $none('Branch', 'Employment') + ['hint' => 'Sans affectation à la création. Masquer le site masque aussi le département et le poste.'],
    'department_id' => $none('Department', 'Employment') + ['hint' => 'Sans affectation à la création. Masquer le département masque aussi le poste.'],
    'designation_id' => $none('Designation', 'Employment'),
    'shift_id' => $none('Shift', 'Employment'),
    'attendance_policy_id' => $none('Attendance Policy', 'Employment'),
    'date_of_joining' => $date('Date of Joining'),
    'employment_type' => ['label' => 'Employment Type', 'group' => 'Employment', 'type' => 'select', 'rules' => 'nullable|in:Full-time,Part-time,Contract,Internship,Temporary', 'default' => null, 'options' => ['Full-time' => 'Full-time', 'Part-time' => 'Part-time', 'Contract' => 'Contract', 'Internship' => 'Internship', 'Temporary' => 'Temporary']],
    'employee_status' => ['label' => 'Employee Status', 'group' => 'Employment', 'type' => 'select', 'required' => true, 'rules' => 'required|in:active,inactive,probation,terminated', 'default' => 'active', 'options' => ['active' => 'Active', 'inactive' => 'Inactive', 'probation' => 'Probation', 'terminated' => 'Terminated']],
    'address_line_1' => $text('Address Line 1', 'Contact'),
    'address_line_2' => $text('Address Line 2', 'Contact'),
    'city' => $text('City', 'Contact', 100),
    'state' => $text('State/Province', 'Contact', 100),
    'country' => $text('Country', 'Contact', 100),
    'postal_code' => $text('Postal/Zip Code', 'Contact', 20),
    'emergency_contact_name' => $text('Emergency Contact Name', 'Contact'),
    'emergency_contact_relationship' => $text('Relationship', 'Contact', 100),
    'emergency_contact_number' => $text('Emergency Contact Phone', 'Contact', 20),
    'bank_name' => $text('Bank Name', 'Banking'),
    'account_holder_name' => $text('Account Holder Name', 'Banking'),
    'account_number' => $text('Account Number', 'Banking', 50),
    'bank_identifier_code' => $text('Bank Identifier Code (BIC/SWIFT)', 'Banking', 50),
    'bank_branch' => $text('Bank Branch', 'Banking'),
    'tax_payer_id' => $text('Tax Payer ID', 'Banking', 50),
    'salary' => ['label' => 'Base Salary', 'group' => 'Banking', 'type' => 'number', 'rules' => 'nullable|numeric|min:0|max:99999999.99|decimal:0,2', 'default' => null, 'column' => 'base_salary'],
    'documents' => $none('Documents', 'Documents') + ['hint' => 'Aucun document demandé à la création, même pour les types obligatoires. Les documents existants sont conservés.'],
];
