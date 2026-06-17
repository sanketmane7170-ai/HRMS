<?php

return [
    'name' => 'IndianPayroll',

    /*
    |--------------------------------------------------------------------------
    | Statutory constants that are NOT rate/slab data (those live in DB tables
    | so they can be revised without a deploy — see ip_pf_settings, ip_esi_settings,
    | ip_pt_slabs, ip_lwf_rules, ip_income_tax_slabs, ip_gratuity_settings).
    |--------------------------------------------------------------------------
    */
    'leave_encashment' => [
        // Section 10(10AA) exemption ceiling for non-government employees (lifetime).
        'exemption_ceiling' => 2500000,
    ],

    'tds' => [
        'cess_rate' => 0.04, // Health & Education Cess, applied on (tax + surcharge)
        'rebate_87a' => [
            'new_regime' => ['income_limit' => 700000, 'max_rebate' => 25000],
            'old_regime' => ['income_limit' => 500000, 'max_rebate' => 12500],
        ],
        'standard_deduction' => [
            'new_regime' => 75000,
            'old_regime' => 50000,
        ],
    ],

    'investment_sections' => [
        '80C' => ['label' => 'Section 80C (PF, ELSS, LIC, PPF, etc.)', 'cap' => 150000],
        '80CCD1B' => ['label' => 'Section 80CCD(1B) - NPS', 'cap' => 50000],
        '80D' => ['label' => 'Section 80D - Medical Insurance', 'cap' => 100000],
        '80E' => ['label' => 'Section 80E - Education Loan Interest', 'cap' => null],
        '80G' => ['label' => 'Section 80G - Donations', 'cap' => null],
        '80TTA' => ['label' => 'Section 80TTA - Savings Interest', 'cap' => 10000],
        '24B' => ['label' => 'Section 24(b) - Home Loan Interest', 'cap' => 200000],
    ],

    // Storage disk for investment proof uploads.
    // Use 'local' for single-server setups (files go to storage/app/).
    // Set INDIANPAYROLL_DOCUMENT_DISK=s3 in .env for multi-server / cloud deployments.
    'document_disk' => env('INDIANPAYROLL_DOCUMENT_DISK', 'local'),
    'document_path' => env('INDIANPAYROLL_DOCUMENT_PATH', 'indianpayroll/investment-proofs'),
];
