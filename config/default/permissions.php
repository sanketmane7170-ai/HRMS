<?php

return [
    'Dashboard'              => [
        'View',
    ],
    'User'                   => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
        'Import',
        'Export',
        'Create Salary',
        'Edit Salary',
        'View Salary',
        'End of Service',
        'Increments',
        'Dependent Details',
        'Assets Details',
        'Issued Documents',
        'Documents',
        'Leave',
        'Service History',
        'Teams',
        'Hierarchy',
        'Hierarchy1',

    ],
    'Role'                   => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
    ],
    'Department'             => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
        'Assign Manager',
    ],
    'Designation'            => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
    ],
    'Dependent'              => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
    ],
    'User Document'          => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
    ],
    'Asset Type'             => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
    ],
    'Asset Manufacturer'     => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
    ],
    'Asset'                  => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
        'Assign',
    ],
    'Announcement Type'      => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
    ],
    'Announcement'           => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
    ],
    'Attendance'             => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
        'Export',
    ],
    'Leave Type'             => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
    ],
    'Leave'                  => [
        'Dashboard',
        'Manage',
        'Create',
        'Edit',
        'Delete',
        'View Report',
        'Approve',
        'Reject',
        'Previous Year Report',
        'Planner',
    ],
    'Apparel'                => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
    ],
    'General Request'        => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
    ],
    'Holiday'                => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
    ],
    'Document Type'          => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
    ],
    'Document Request'       => [
        'Manage',
        'View',
        'Generate',
    ],
    'Warning'                => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
    ],
    'Settings'               => [
        'General',
        'Smtp',
        'Advance',
        'Clear Cache',
    ],
    'Payroll'                => [
        'Set Salary and PayRoll',
        'Generate SIF',
        'Export',
    ],
    'Shift'                  => [
        'Manage Shift',
        'Manage Scheduling',
    ],
    'Roster'                 => [
        'Manage Roster',
    ],
    'FileManager'            => [
        'Manage',
        'Download',
        'Create',
        'Edit',
        'Delete',
    ],
    'EditUpdateLeave'        => [
        'Edit Update Leave Balance',
        'View Leave Update Logs',
    ],
    'Expense Type'           => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
    ],
    'Expense'                => [
        'Permission',
        'Manage',
        'Create',
        'Edit',
        'Delete',
    ],
    'Advance Salary Request' => [
        'Manage',
    ],
    'Reports'                => [
        'Leave',
        'Attendance',
        'Late Comers',
        'Early Comers',
        'Salary Increments',
        'Expense',
        'Gratuity',
    ],
    'Daily Email'            => [
        'Leave',
        'Attendance',
        'Late Comers',
        'Early Comers',
        'Expense',
    ],
    'Monthly Email'          => [
        'Leave',
        'Attendance',
        'Late Comers',
        'Early Comers',
        'Expense',
        'Gratuity Accrual',
        'Medical Insurance Accrual',
        'Air Ticket Accrual',
        'Leave Salary Accrual',
        'Accrual',
        'PH Leave',
        'Leave Balance',
    ],
    'EditUpdateLeave'        => [
        'Edit Update Leave Balance',
        'View Leave Update Logs',
    ],
    'Over Time Request'      => [
        'Manage',
        'Create',
        'Edit',
        'View',
    ],
    'Manager Access'         => [
        'Leave Request',
        'Attendance Report',
        'Document Request',
        'General Request',
    ],
    'Division'               => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
        // 'Assign Manager'
    ],
    'Recruitment'            => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
        'View',
    ],
    'Document'               => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
    ],
    'Performance'            => [
        'Manage',
        'Create',
        'Edit',
        'Delete',
    ],
    // Modules below are gated in the sidebar by a single "Manage X" permission
    // (module-sidebar.blade.php). They were missing from this picker, so a role
    // could never be granted/denied them. Keys must stay exactly like this so
    // "$permission $module" matches the permission names the sidebar checks
    // (e.g. 'Task' => 'Manage Task', not 'Task Management').
    'Company Document'       => [
        'Manage',
    ],
    'Training'               => [
        'Manage',
    ],
    'Performance Review'     => [
        'Manage',
    ],
    'Task'                   => [
        'Manage',
    ],
    'Indian Payroll'         => [
        'Manage',
    ],
    'International Payroll'   => [
        'Manage',
    ],
    'Company Policy'         => [
        'Manage',
    ],
    'Resignation'            => [
        'Manage',
    ],
    'Onboarding'             => [
        'Manage',
    ],
];
