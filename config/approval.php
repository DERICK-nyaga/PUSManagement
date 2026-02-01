<?php

return [
    'flows' => [
        'default' => [
            'levels' => [
                1 => [
                    'role' => 'supervisor',
                    'required' => true,
                    'deadline_hours' => 24,
                    'escalate_to' => 'department_head',
                ],
                2 => [
                    'role' => 'department_head',
                    'required' => false,
                    'deadline_hours' => 48,
                    'escalate_to' => 'hr_manager',
                ],
            ],
        ],

        'salary_change' => [
            'levels' => [
                1 => [
                    'role' => 'supervisor',
                    'required' => true,
                    'deadline_hours' => 24,
                    'escalate_to' => 'department_head',
                ],
                2 => [
                    'role' => 'department_head',
                    'required' => true,
                    'deadline_hours' => 48,
                    'escalate_to' => 'hr_manager',
                ],
                3 => [
                    'role' => 'finance_manager',
                    'required' => true,
                    'deadline_hours' => 72,
                    'escalate_to' => 'ceo',
                ],
            ],
        ],

        'termination' => [
            'levels' => [
                1 => [
                    'role' => 'hr_manager',
                    'required' => true,
                    'deadline_hours' => 24,
                    'escalate_to' => 'ceo',
                ],
                2 => [
                    'role' => 'ceo',
                    'required' => true,
                    'deadline_hours' => 48,
                ],
            ],
        ],
    ],

    'escalation_rules' => [
        'first_reminder' => 0.5, // 50% of deadline elapsed
        'escalation' => 0.8, // 80% of deadline elapsed
        'final_reminder' => 0.9, // 90% of deadline elapsed
    ],

    'email_notifications' => [
        'enabled' => true,
        'reminders_enabled' => true,
        'escalations_enabled' => true,
        'send_to_requester' => true,
    ],
];
