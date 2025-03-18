<?php

return [
    'label' => 'Question',
    'label_plural' => 'Questions',
    'form' => [
        'text' => [
            'label' => 'Question text',
        ],
        'answer' => [
            'label' => 'Answer text',
        ],
    ],
    'table' => [
        'text' => [
            'label' => 'Question',
        ],
        'answer' => [
            'label' => 'Answer',
        ],
    ],
    'relations' => [
        'products' => [
            'title_plural' => 'Products',
            'actions' => [
                'attach' => [
                    'label' => 'Associate product',
                    'field' => 'Product name',
                ]
            ],
        ],
        'variants' => [
            'title_plural' => 'Variants',
        ],
    ],
];
