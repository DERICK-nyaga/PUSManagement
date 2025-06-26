<?php

return [
    'types' => [
        'salary' => 'Salary',
        'utility' => 'Utility Bill',
        'rent' => 'Rent',
        'maintenance' => 'Maintenance',
        'tax' => 'Tax',
        'other' => 'Other'
    ],

    // You can add more payment-related configurations here
    'statuses' => [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'paid' => 'Paid',
        'cancelled' => 'Cancelled'
    ],

    'recurrence' => [
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly'
    ]
];
