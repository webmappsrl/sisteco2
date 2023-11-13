<?php

return [

    'vat' => [
        'label' => 'IVA',
        'unit' => '%',
        'value' => 22,
    ],
    'supervision' => [
        'label' => 'Direzione Lavori',
        'unit' => '%',
        'value' => 10,
    ],
    'overheads' => [
        'label' => 'Spese Generali',
        'unit' => '%',
        'value' => 16,
    ],
    'business_profit' => [
        'label' => 'Utile di Impresa',
        'unit' => '%',
        'value' => 10,
    ],
    'intervention_certification' => [
        'label' => 'Costo certificazione intervento',
        'unit' => '€',
        'value' => 1100,
    ],
    'team_management' => [
        'label' => 'Costi amministrativi',
        'unit' => '€',
        'value' => 3000,
    ],
    'platform_maintenance' => [
        'label' => 'Perc. Mantenimento piattaforma',
        'unit' => '%',
        'value' => 2,
    ],
    'maintenance' => [
        'label' => 'Costo manutenzione annua per ettaro',
        'unit' => '€/ettaro',
        'value' => 1250,
    ],
    'maintenance_certification' => [
        'label' => 'Costo certificazione manutenzione',
        'unit' => '€',
        'value' => 850,
    ],
    'hiking_routes_cost_per_km' => [
        'label' => 'Costo per km di sentiero',
        'unit' => '€/km',
        'value' => 3072.06,
    ],

    'areaStyle' => [
        'cadastral' => [
            'strokeColor' => 'rgba(255, 0, 0)',
            'fillColor' => 'rgba(255, 0, 0, 0)',
        ],
        // Nessun intervento
        '0' => [
            'strokeColor' => 'rgba(191, 191, 191, 1)',
            'fillColor' => 'rgba(208, 208, 208, 0.4)',
        ],
        // Diradamento
        '1' => [
            'strokeColor' => 'rgba(255, 221, 1, 1)',
            'fillColor' => 'rgba(255, 221, 1, 0.4)',
        ],
        // Avviamento alto fusto 
        '2' => [
            'strokeColor' => 'rgba(128, 86, 52, 1)',
            'fillColor' => 'rgba(128, 86, 52, 0.4)',
        ],
        // Taglio ceduo
        '3' => [
            'strokeColor' => 'rgba(255, 1, 14, 1)',
            'fillColor' => 'rgba(255, 1, 14, 0.4)',
        ],
        // Recupero post-incendio
        '4' => [
            'strokeColor' => 'rgba(219, 30, 210, 1)',
            'fillColor' => 'rgba(219, 30, 210, 0.4)',
        ],
        // Selvicultura ad albero
        '5' => [
            'strokeColor' => 'rgba(128, 255, 0, 1)',
            'fillColor' => 'rgba(128, 255, 0, 0.4)',
        ],
    ]
];
