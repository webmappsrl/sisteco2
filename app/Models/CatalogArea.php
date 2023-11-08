<?php

namespace App\Models;

use App\Models\Catalog;
use App\Models\CatalogType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CatalogArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'geometry',
        'catalog_type_id',
        'catalog_id',
        'sisteco_legacy_id',
        'slope_min',
        'slope_max',
        'slope_avg',
    ];

    protected $casts = [
        'catalog_estimate' => 'array',
        'hiking_routes_details' => 'array',
    ];

    /**
     * Get the catalog type that owns the catalog area.
     */
    public function catalogType()
    {
        return $this->belongsTo(CatalogType::class);
    }

    /**
     * Get the catalog that owns the catalog area.
     */
    public function catalog()
    {
        return $this->belongsTo(Catalog::class);
    }

    /**
     * Calculate the estimated catalog value based on the catalog id
     * 
     */

    public function computeCatalogEstimate()
    {
        $catalog = $this->catalog;
        $type = $this->catalogType;
        $prices = $catalog->catalogTypes()->pluck('prices', 'cod_int')->toArray();
        $vat = config('sisteco.vat.value');
        $area_slope_class = $this->slope_class;
        if (!in_array($area_slope_class, ['A', 'B', 'C'])) {
            $area_slope_class = 'A';
        }
        $parcel_code = $area_slope_class . '.1';  //TODO get the parcel code from the cadastral parcel

        //define json structure
        $interventions = [];
        $maintenance = [];
        $general = [];
        $json = [];


        $intervention_area = DB::table('catalog_areas')
            ->select(DB::raw('ST_Area(geometry) as area'))
            ->where('id', $this->id)
            ->value('area');

        $intervention_price = 0;
        $cod_int = $type->cod_int;
        $unit_price = $prices[$cod_int][$parcel_code];
        $intervention_area = $intervention_area / 10000; //convert to hectares
        $price = $unit_price * $intervention_area;
        $intervention_price += $price;

        if ($cod_int != 0) {
            $items[] = [
                'code' => $cod_int . '.' . $parcel_code,
                'area' => number_format($intervention_area, 4, ',', '.'),
                'unit_price' => number_format($unit_price, 2, ',', '.'),
                'price' => number_format($price, 2, ',', '.')
            ];
            $interventions['items'] = $items;
        } else {
            $interventions['items'] = [];
        }

        //define the variables for the $intervention['info'] array
        $supervision_price = $intervention_price *  ((config('sisteco.supervision.value') / 100));
        $overhead_price = $intervention_price * ((config('sisteco.overheads.value') / 100));
        $business_profit_price = $intervention_price * ((config('sisteco.business_profit.value') / 100));
        $intervention_certification = config('sisteco.intervention_certification.value');
        $total_intervention_certificated_price = $intervention_price + $supervision_price + $overhead_price + $business_profit_price + $intervention_certification;
        $team_price = 3000;
        $platform_maintenance_price = $total_intervention_certificated_price * ((config('sisteco.platform_maintenance.value') / 100));
        $total_intervention_gross_price = $total_intervention_certificated_price + $team_price + $platform_maintenance_price;
        $total_intervention_net_price = $total_intervention_gross_price / (1 + ($vat / 100));
        $total_intervention_vat = $total_intervention_gross_price - $total_intervention_net_price;
        $intervention_gross_price_per_area = $intervention_area != 0 ? $total_intervention_gross_price / $intervention_area : $total_intervention_gross_price; //if intervention_area is 0 the default value is total_intervention_gross_price
        //create an array with all the variables above using number_format to format the numbers
        $interventions['info'] = [
            'intervention_area' => number_format($intervention_area, 4, ',', '.'),
            'intervention_price' => number_format($intervention_price, 2, ',', '.'),
            'supervision_price' => number_format($supervision_price, 2, ',', '.'),
            'overhead_price' => number_format($overhead_price, 2, ',', '.'),
            'business_profit_price' => number_format($business_profit_price, 2, ',', '.'),
            'intervention_certification' => number_format($intervention_certification, 2, ',', '.'),
            'total_intervention_certificated_price' => number_format($total_intervention_certificated_price, 2, ',', '.'),
            'team_price' => number_format($team_price, 2, ',', '.'),
            'platform_maintenance_price' => number_format($platform_maintenance_price, 2, ',', '.'),
            'total_intervention_gross_price' => number_format($total_intervention_gross_price, 2, ',', '.'),
            'total_intervention_net_price' => number_format($total_intervention_net_price, 2, ',', '.'),
            'total_intervention_vat' => number_format($total_intervention_vat, 2, ',', '.'),
            'intervention_gross_price_per_area' => number_format($intervention_gross_price_per_area, 2, ',', '.'),
        ];

        //defining $maintenance['items'] 
        // items maintenace (5 elements, one for year)
        // item.[].code this value indicates the year of the maintenance( year_1, year_2, year_3, year_4, year_5)
        // items.[].area data taken from the intervention area
        // items..[].unit_price: config('sisteco.maintenance.val')
        // items.[].price: product from area * unit_price * config('sisteco.vat.val')
        $maintenance_item_price = $intervention_area * config('sisteco.maintenance.value') * (1 + $vat / 100);
        $maintenance['items'] = [
            [
                'code' => 'year_1',
                'area' => number_format($intervention_area, 4, ',', '.'),
                'unit_price' => number_format(config('sisteco.maintenance.value'), 2, ',', '.'),
                'price' => number_format($maintenance_item_price, 2, ',', '.'),
            ],
            [
                'code' => 'year_2',
                'area' => number_format($intervention_area, 4, ',', '.'),
                'unit_price' => number_format(config('sisteco.maintenance.value'), 2, ',', '.'),
                'price' => number_format($maintenance_item_price, 2, ',', '.'),
            ],
            [
                'code' => 'year_3',
                'area' => number_format($intervention_area, 4, ',', '.'),
                'unit_price' => number_format(config('sisteco.maintenance.value'), 2, ',', '.'),
                'price' => number_format($maintenance_item_price, 2, ',', '.'),
            ],
            [
                'code' => 'year_4',
                'area' => number_format($intervention_area, 4, ',', '.'),
                'unit_price' => number_format(config('sisteco.maintenance.value'), 2, ',', '.'),
                'price' => number_format($maintenance_item_price, 2, ',', '.'),
            ],
            [
                'code' => 'year_5',
                'area' => number_format($intervention_area, 4, ',', '.'),
                'unit_price' => number_format(config('sisteco.maintenance.value'), 2, ',', '.'),
                'price' => number_format($maintenance_item_price, 2, ',', '.'),
            ],
        ];
        //defining $maintenance['certifications'] array
        //certifications code: 2 elements, year_2 and year_5
        //certifications price: config(sisteco.maintenance_certification.val)
        $maintenance['certifications'] = [
            [
                'code' => 'year_2',
                'price' => number_format(config('sisteco.maintenance_certification.value'), 2, ',', '.'),
            ],
            [
                'code' => 'year_5',
                'price' => number_format(config('sisteco.maintenance_certification.value'), 2, ',', '.'),
            ],
        ];
        //defining $maintenance['summary'] array
        //  total_maintenance_gross_price: calculated from the sum of all the price of the items in the maintenance['items'] array
        // total_maintenance_net_price: total_maintenance_gross_price divided by config(sisteco.vat.val)
        // total_maintenance_vat: diference gross - net
        // maintenance_gross_price_per_area: total_maintenance_gross_price / area
        $total_maintenance_gross_price = ($maintenance_item_price * 5) + (config('sisteco.maintenance_certification.value') * 2); //price of the 5 years of maintenance plus vat
        $total_maintenance_net_price = $total_maintenance_gross_price / (1 + $vat / 100); //price of the 5 years of maintenance without vat
        $maintenance['summary'] = [
            'total_maintenance_gross_price' => number_format($total_maintenance_gross_price, 2, ',', '.'),
            'total_maintenance_net_price' => number_format($total_maintenance_net_price, 2, ',', '.'),
            'total_maintenance_vat' => number_format($total_maintenance_gross_price - $total_maintenance_net_price, 2, ',', '.'), //vat
            'maintenance_gross_price_per_area' => $intervention_area != 0 ? number_format($total_maintenance_gross_price / ($intervention_area * 5), 2, ',', '.') : $total_maintenance_gross_price, //if intervention_area is 0 the default value is the total_maintenance_gross_price
        ];

        //defining $general array
        //total_gross_price: sum of total_intervention_gross_price and total_maintenance_gross_price
        //total_net_price: total_gross_price divided by config(sisteco.vat.val)
        //total_vat: diference gross - net
        //total_gross_price_per_area: total_gross_price / area
        $total_general_gross_price = $total_intervention_gross_price + $total_maintenance_gross_price;
        $total_net_price = $total_general_gross_price / (1 + $vat / 100);
        $general = [
            'total_gross_price' => number_format($total_general_gross_price, 2, ',', '.'),
            'total_net_price' => number_format($total_net_price, 2, ',', '.'),
            'total_vat' => number_format($total_general_gross_price - $total_net_price, 2, ',', '.'),
            'total_gross_price_per_area' => $intervention_area != 0 ? number_format($total_general_gross_price / $intervention_area, 2, ',', '.') : $total_general_gross_price, //if intervention_area is 0 the default value is the total_general_gross_price  
        ];

        //if $intervention is empty or if all 'code' fields inside $intervention['items'] are 0, then $json will be empty
        if (empty($interventions['items'])) {
            $json = [];
        } else {
            $json['interventions'] = $interventions;
            $json['maintenance'] = $maintenance;
            $json['general'] = $general;
        }
        return $json;
    }

    public function computeSlopeStats(): array
    {
        $stats = [
            'slope_min' => 0,
            'slope_max' => 0,
            'slope_avg' => 0,
        ];
        $id = $this->id;
        $sql = <<<EOF
WITH features AS (
    SELECT id, ST_Transform(geometry::geometry, 3035) AS geom
    FROM catalog_areas AS p
),

p_stats AS (
    SELECT id, (slope_stats).*
    FROM (
        -- Sottoquery che calcola le statistiche di pendenza per ciascun poligono
        SELECT id, ST_SummaryStats(ST_Slope(ST_Clip(rast, geom))) AS slope_stats
        FROM dem
        INNER JOIN features ON ST_Intersects(features.geom, rast)
    ) AS foo
)

SELECT id,
    MIN(min) AS slope_min,   -- Calcola il valore minimo di pendenza
    MAX(max) AS slope_max,   -- Calcola il valore massimo di pendenza
    SUM(mean * count) / SUM(count) AS slope_avg  -- Calcola la media ponderata delle elevazioni
FROM p_stats
GROUP BY id  -- Raggruppa per ID del poligono
HAVING id=$id;  -- Ordina per ID del poligono
EOF;

        try {
            $results = DB::select($sql);
            if (count($results) > 0) {
                $stats = [
                    'slope_min' => $results[0]->slope_min,
                    'slope_max' => $results[0]->slope_max,
                    'slope_avg' => $results[0]->slope_avg,
                ];
            }
            Log::warning("WARN: computeSlopeStats for catalogArea with id $id has no results");
        } catch (\Throwable $th) {
            //throw $th;
            Log::error("WARN: computeSlopeStats for catalogArea with id $id returns an error, probably the geometry is not correct");
        }
        return $stats;
    }

    /**
     * It returns the slope class:
     * 'A' -> s < 20 deg
     * 'B' -> 20 < s <=40 deg
     * 'C' -> s > 40
     *
     * @return string
     */
    public function computeSlopeClass(): string
    {
        if ($this->slope_avg <= 20) return 'A';
        if ($this->slope_avg <= 40) return 'B';
        return 'C';
    }

    public function computeHikingRoutes(): array
    {
        $hrs = [];
        $hr_id = $this->id;
        $sql = <<<EOF
    SELECT
        hr.ref AS ref,
        ST_Length(ST_Intersection(hr.geometry, ca.geometry)) AS length
    FROM
        hiking_routes AS hr
    JOIN
        catalog_areas AS ca
    ON
        ST_Intersects(hr.geometry, ca.geometry)
    WHERE
        ca.id = $hr_id;
EOF;
        $results = DB::select($sql);
        if (count($results) > 0) {
            foreach ($results as $data) {
                $hrs[$data->ref] = $data->length;
            }
        }
        return $hrs;
    }

    public function getHikingRouteMinDist(): float
    {
        $area_id = $this->id;
        $dist = 0;
        $sql = <<<EOF
    SELECT 
        MIN(ST_Distance(a.geometry, r.geometry)) AS dist
      FROM 
        catalog_areas a
      CROSS JOIN 
        hiking_routes r
      WHERE 
        a.id = $area_id;      
EOF;
        $dist = DB::select($sql)[0]->dist;
        return $dist;
    }

    public function getStreetsMinDist(): float
    {
        $area_id = $this->id;
        $dist = 0;
        $sql = <<<EOF
    SELECT 
        MIN(ST_Distance(ST_transform(a.geometry::geometry,3857), s.geom)) AS dist
      FROM 
        catalog_areas a
      CROSS JOIN 
        streets s
      WHERE 
        a.id = $area_id;      
EOF;
        $dist = DB::select($sql)[0]->dist;
        return $dist;
    }
}
