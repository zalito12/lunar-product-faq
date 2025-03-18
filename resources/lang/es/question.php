<?php

return [
    'label' => 'Preguntas',
    'label_plural' => 'Preguntas',
    'form' => [
        'text' => [
            'label' => 'Pregunta',
        ],
        'answer' => [
            'label' => 'Respuesta',
        ],
    ],
    'table' => [
        'text' => [
            'label' => 'Pregunta',
        ],
        'answer' => [
            'label' => 'Respuesta',
        ],
    ],
    'relations' => [
        'products' => [
            'title_plural' => 'Productos',
            'actions' => [
                'attach' => [
                    'label' => 'Associar producto',
                    'field' => 'Nombre de producto',
                ]
            ],
        ],
        'variants' => [
            'title_plural' => 'Variaciones',
        ],
    ],
];
