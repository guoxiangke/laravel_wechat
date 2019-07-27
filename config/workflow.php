<?php

return [
    'straight'   => [
        'type'          => 'workflow', // or 'state_machine'
        'marking_store' => [
            'type'      => 'multiple_state',
            'arguments' => ['currentPlace'],
        ],
        'supports'      => ['App\Models\Post'],
        'places'        => ['draft', 'rejected', 'published'],
        'transitions'   => [
            'publish' => [
                'from' => ['draft', 'rejected'],
                'to'   => 'published',
            ],
            'reject' => [
                'from' => 'draft',
                'to'   => 'rejected',
            ],
        ],
    ],
];
