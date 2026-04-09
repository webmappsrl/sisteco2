<?php

return [

    /*
    | SISTECO_VAT: aliquota IVA (solo numero, es. 22 per il 22%).
    | Usata per passare da imponibile a lordo (es. prezzo unitario × area × (1 + IVA/100))
    | e per ricavare il netto dai totali lordi nella manutenzione e nei riepiloghi.
    */
    'vat' => [
        'label' => 'IVA',
        'unit' => '%',
        'value' => (float) env('SISTECO_VAT', 22),
    ],
    /*
    | SISTECO_SUPERVISION: percentuale di direzione lavori sul costo base dell’intervento
    | (somma aree × prezzi da catalogo, già con IVA sulle singole voci dove previsto nel flusso).
    */
    'supervision' => [
        'label' => 'Direzione Lavori',
        'unit' => '%',
        'value' => (float) env('SISTECO_SUPERVISION', 10),
    ],
    /*
    | SISTECO_OVERHEADS: percentuale spese generali applicata sullo stesso basamento
    | del costo intervento usato per supervisione e utile.
    */
    'overheads' => [
        'label' => 'Spese Generali',
        'unit' => '%',
        'value' => (float) env('SISTECO_OVERHEADS', 16),
    ],
    /*
    | SISTECO_BUSINESS_PROFIT: percentuale utile d’impresa sul costo base intervento.
    */
    'business_profit' => [
        'label' => 'Utile di Impresa',
        'unit' => '%',
        'value' => (float) env('SISTECO_BUSINESS_PROFIT', 10),
    ],
    /*
    | SISTECO_INTERVENTION_CERTIFICATION: costo fisso in euro per la certificazione
    | dell’intervento (sommato al totale certificato dove il modello di calcolo lo prevede).
    */
    'intervention_certification' => [
        'label' => 'Costo certificazione intervento',
        'unit' => '€',
        'value' => (float) env('SISTECO_INTERVENTION_CERTIFICATION', 1100),
    ],
    /*
    | SISTECO_TEAM_MANAGEMENT: costi amministrativi / team.
    | Attenzione: in stima su CatalogArea è trattato come importo fisso in € (se c’è intervento);
    | in stima su CadastralParcel come percentuale sul totale certificato intervento.
    */
    'team_management' => [
        'label' => 'Costi amministrativi',
        'unit' => '€',
        'value' => (float) env('SISTECO_TEAM_MANAGEMENT', 3000),
    ],
    /*
    | SISTECO_PLATFORM_MAINTENANCE: percentuale per mantenimento piattaforma.
    | Su CatalogArea è applicata sul costo intervento; su manutenzione anche su base annua + sentieri.
    | Su CadastralParcel è applicata sul totale certificato intervento.
    */
    'platform_maintenance' => [
        'label' => 'Perc. Mantenimento piattaforma',
        'unit' => '%',
        'value' => (float) env('SISTECO_PLATFORM_MAINTENANCE', 10),
    ],
    /*
    | SISTECO_MAINTENANCE: costo annuo di manutenzione in € per ettaro (area intervento).
    | Moltiplicato per l’area per ottenere la voce annua; nella stima pluriennale viene ripetuto per gli anni previsti.
    */
    'maintenance' => [
        'label' => 'Costo manutenzione annua per ettaro',
        'unit' => '€/ettaro',
        'value' => (float) env('SISTECO_MAINTENANCE', 1250),
    ],
    /*
    | SISTECO_MAINTENANCE_CERTIFICATION: costo fisso in euro per ogni certificazione
    | di manutenzione (es. voci annuali nel riepilogo manutenzione).
    */
    'maintenance_certification' => [
        'label' => 'Costo certificazione manutenzione',
        'unit' => '€',
        'value' => (float) env('SISTECO_MAINTENANCE_CERTIFICATION', 850),
    ],
    /*
    | SISTECO_HIKING_ROUTES_COST_PER_KM: tariffa in € per km di sentiero.
    | La lunghezza è in metri nel modello: il codice divide per 1000 per ottenere il costo lineare.
    */
    'hiking_routes_cost_per_km' => [
        'label' => 'Costo per km di sentiero',
        'unit' => '€/km',
        'value' => (float) env('SISTECO_HIKING_ROUTES_COST_PER_KM', 3643.30),
    ],

    'areaStyle' => [
        'cadastral' => [
            'strokeColor' => 'rgba(255, 0, 0)',
            'fillColor' => 'rgba(255, 0, 0, 0)',
        ],
        // Nessun intervento
        '0' => [
            'strokeColor' => 'rgba(125, 125, 125, 1)',
            'fillColor' => 'rgba(125, 125, 125, 0.5)',
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
