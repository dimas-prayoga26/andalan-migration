<?php

return [
    'hr_interview' => [
        'criteria' => [
            [
                'key' => 'communication',
                'label' => 'Kemampuan Komunikasi',
                'weight' => 20,
                'default_rating' => 0,
                'sort_order' => 1,
            ],
            [
                'key' => 'confidence',
                'label' => 'Kepercayaan Diri',
                'weight' => 10,
                'default_rating' => 0,
                'sort_order' => 2,
            ],
            [
                'key' => 'motivation',
                'label' => 'Motivasi Kerja',
                'weight' => 10,
                'default_rating' => 0,
                'sort_order' => 3,
            ],
            [
                'key' => 'experience_match',
                'label' => 'Kesesuaian Pengalaman',
                'weight' => 15,
                'default_rating' => 0,
                'sort_order' => 4,
            ],
            [
                'key' => 'position_understanding',
                'label' => 'Pemahaman Posisi',
                'weight' => 15,
                'default_rating' => 0,
                'sort_order' => 5,
            ],
            [
                'key' => 'discipline',
                'label' => 'Kedisiplinan & Profesionalisme',
                'weight' => 10,
                'default_rating' => 0,
                'sort_order' => 6,
            ],
            [
                'key' => 'culture_fit',
                'label' => 'Culture Fit & Problem Solving',
                'weight' => 20,
                'default_rating' => 0,
                'sort_order' => 7,
            ],
        ],
    ],
    'user_interview' => [
        'criteria' => [
            [
                'key' => 'technical_skill',
                'label' => 'Skill Teknis',
                'weight' => 25,
                'default_rating' => 0,
                'sort_order' => 1,
            ],
            [
                'key' => 'creativity',
                'label' => 'Kreativitas',
                'weight' => 20,
                'default_rating' => 0,
                'sort_order' => 2,
            ],
            [
                'key' => 'tools_usage',
                'label' => 'Penggunaan Tools',
                'weight' => 20,
                'default_rating' => 0,
                'sort_order' => 3,
            ],
            [
                'key' => 'experience',
                'label' => 'Pengalaman',
                'weight' => 20,
                'default_rating' => 0,
                'sort_order' => 4,
            ],
            [
                'key' => 'commitment',
                'label' => 'Kesanggupan',
                'weight' => 15,
                'default_rating' => 0,
                'sort_order' => 5,
            ],
        ],
    ],
];
