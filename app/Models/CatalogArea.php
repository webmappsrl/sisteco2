<?php

namespace App\Models;

use App\Models\Catalog;
use App\Models\CatalogType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CatalogArea extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'geometry',
        'catalog_type_id',
        'catalog_id',
        'sisteco_legacy_id',
        'slope_min',
        'slope_max',
        'slope_avg',
        'work_start_date',
        'owners',
    ];

    protected $casts = [
        'catalog_estimate' => 'array',
        'hiking_routes_details' => 'array',
        'owners' => 'array',
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
     * Registering media collections for spaties media library
     * @return void
     *
     * @see https://spatie.be/docs/laravel-medialibrary/v10/working-with-media-collections/defining-media-collections
     */
    public function registerMediaCollections(): void
    {
        $acceptedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg','image/webp'];

        $this->addMediaCollection('featured-image')
            ->singleFile()
            ->acceptsMimeTypes($acceptedMimeTypes);
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
        $parcel_code = $area_slope_class . '.' .$this->computeTransportClass();  
        
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

        // Hiking Routes details
        $hiking_routes_details_string = '-';
        if (!is_null($this->hiking_routes_details) && count($this->hiking_routes_details) > 0) {
            $hiking_routes_details_string = '';
            foreach ($this->hiking_routes_details as $ref => $length) {
                $ls = number_format($length, 0);
                $hiking_routes_details_string .= "$ref($ls m) ";
            }
        }
        $hiking_routes_total_cost = $this->hiking_routes_length * config('sisteco.hiking_routes_cost_per_km.value') / 1000;

        $items[] = [
            'code' => $cod_int . '.' . $parcel_code,
            'area' => number_format($intervention_area, 4, ',', '.'),
            'unit_price' => number_format($unit_price, 2, ',', '.'),
            'price' => $price,
        ];
        $interventions['items'] = $items;

        //define the variables for the $intervention['info'] array
        $intervention_forestal_price = $items[0]['price'];
        $intervention_price = $intervention_forestal_price + $hiking_routes_total_cost;

        $supervision_price = $intervention_price *  ((config('sisteco.supervision.value') / 100));
        $overhead_price = $intervention_price * ((config('sisteco.overheads.value') / 100));
        $business_profit_price = $intervention_price * ((config('sisteco.business_profit.value') / 100));
        $intervention_company_price = $supervision_price + $overhead_price + $business_profit_price;
        $intervention_certification = $intervention_price > 0 ? config('sisteco.intervention_certification.value') : 0;
        $total_intervention_certificated_price = $intervention_price + $supervision_price + $overhead_price + $business_profit_price + $intervention_certification;
        $team_price = $intervention_price > 0 ? config('sisteco.team_management.value') : 0 ;
        $platform_maintenance_price = $intervention_price > 0 ? $intervention_price * ((config('sisteco.platform_maintenance.value') / 100)) : 0;
        $intervention_certification_and_management_price = $team_price + $platform_maintenance_price + $intervention_certification;
        $intervention_total_net_price = $intervention_certification_and_management_price + $intervention_price + $intervention_company_price;
        $intervention_total_vat_price = $intervention_total_net_price * config('sisteco.vat.value') / 100;
        $intervention_total_gross_price = $intervention_total_net_price + $intervention_total_vat_price;

        $intervention_total_net_price_per_area = $intervention_total_net_price / $intervention_area;
        $intervention_total_vat_price_per_area = $intervention_total_net_price_per_area * config('sisteco.vat.value') / 100;
        $intervention_total_gross_price_per_area = $intervention_total_net_price_per_area + $intervention_total_vat_price_per_area;

        $interventions['info'] = [
            'name' => $type->name,
            'excerpt' => $type->excerpt,
            'description' => $type->description,
            'intervention_area' => $intervention_area,
            'forestal_price' => $intervention_forestal_price,
            'price' => $intervention_price,
            'supervision_price' => $supervision_price,
            'overhead_price' => $overhead_price,
            'business_profit_price' => $business_profit_price,
            'company_price' => $intervention_company_price,
            'certification_and_management_price' => $intervention_certification_and_management_price,

            'intervention_certification' => number_format($intervention_certification, 2, ',', '.'),
            'total_intervention_certificated_price' => number_format($total_intervention_certificated_price, 2, ',', '.'),
            'team_price' => number_format($team_price, 2, ',', '.'),
            'platform_maintenance_price' => $platform_maintenance_price,
            'total_net_price' => $intervention_total_net_price,
            'total_vat_price' => $intervention_total_vat_price,
            'total_gross_price' => $intervention_total_gross_price,
            'total_net_price_per_area' => $intervention_total_net_price_per_area, 
            'total_vat_price_per_area' => $intervention_total_vat_price_per_area, 
            'total_gross_price_per_area' => $intervention_total_gross_price_per_area, 

            'hiking_routes_details' => $hiking_routes_details_string,
            'hiking_routes_total_cost' => $hiking_routes_total_cost,
        ];

        $maintenance_item_price = $intervention_area * config('sisteco.maintenance.value') * (1 + $vat / 100);

        // Per ciascun anno devono essere presi in considerazione: 
        // costi di manutenzioni per interventi forestali
        // costi di manutenzione della sentieristica
        // costi di certificazione (da mostrare solo nel totale)
        // costi di gestione della piattaforma (da mostrare solo nel totale)
        $maintenance['years'] = [];
        $maintenance_certification_total_price = 0;
        $maintenance_platform_total_price = 0;
        $maintenance_intervention_total_price = 0;
        $hr_price = $this->hiking_routes_length*config('sisteco.hiking_routes_cost_per_km.value')/1000;

        $price = $type->maintenance_price_fist_year;
        $maintenance_year = [
                'intervention_forest_price' => $intervention_area*$price,
                'intervention_hiking_route_price' => $hr_price,
                'intervention_total_price'=> $intervention_area*$price + $hr_price,
                'certification_price' => $price > 0 ? config('sisteco.maintenance_certification.value') : 0,
                'platform_price' => ((config('sisteco.platform_maintenance.value') / 100)) * ($intervention_area*$price + $hr_price)
        ];
        $maintenance_certification_total_price += $maintenance_year['certification_price'];
        $maintenance_platform_total_price += $maintenance_year['platform_price'];
        $maintenance_intervention_total_price += $maintenance_year['intervention_total_price'];
        $maintenance['years'][]=$maintenance_year;

        $price = $type->maintenance_price_second_year;
        $maintenance_year = [
            'intervention_forest_price' => $intervention_area*$price,
            'intervention_hiking_route_price' => $hr_price,
            'intervention_total_price'=> $intervention_area*$price + $hr_price,
            'certification_price' => $price > 0 ? config('sisteco.maintenance_certification.value') : 0,
            'platform_price' => ((config('sisteco.platform_maintenance.value') / 100)) * ($intervention_area*$price + $hr_price)
        ];
        $maintenance_certification_total_price += $maintenance_year['certification_price'];
        $maintenance_platform_total_price += $maintenance_year['platform_price'];
        $maintenance_intervention_total_price += $maintenance_year['intervention_total_price'];
        $maintenance['years'][]=$maintenance_year;

        $price = $type->maintenance_price_third_year;
        $maintenance_year = [
            'intervention_forest_price' => $intervention_area*$price,
            'intervention_hiking_route_price' => $hr_price,
            'intervention_total_price'=> $intervention_area*$price + $hr_price,
            'certification_price' => $price > 0 ? config('sisteco.maintenance_certification.value') : 0,
            'platform_price' => ((config('sisteco.platform_maintenance.value') / 100)) * ($intervention_area*$price + $hr_price)
        ];
        $maintenance_certification_total_price += $maintenance_year['certification_price'];
        $maintenance_platform_total_price += $maintenance_year['platform_price'];
        $maintenance_intervention_total_price += $maintenance_year['intervention_total_price'];
        $maintenance['years'][]=$maintenance_year;

        $price = $type->maintenance_price_fourth_year;
        $maintenance_year = [
            'intervention_forest_price' => $intervention_area*$price,
            'intervention_hiking_route_price' => $hr_price,
            'intervention_total_price'=> $intervention_area*$price + $hr_price,
            'certification_price' => $price > 0 ? config('sisteco.maintenance_certification.value') : 0,
            'platform_price' => ((config('sisteco.platform_maintenance.value') / 100)) * ($intervention_area*$price + $hr_price)
        ];
        $maintenance_certification_total_price += $maintenance_year['certification_price'];
        $maintenance_platform_total_price += $maintenance_year['platform_price'];
        $maintenance_intervention_total_price += $maintenance_year['intervention_total_price'];
        $maintenance['years'][]=$maintenance_year;

        $price = $type->maintenance_price_fifth_year;
        $maintenance_year = [
            'intervention_forest_price' => $intervention_area*$price,
            'intervention_hiking_route_price' => $hr_price,
            'intervention_total_price'=> $intervention_area*$price + $hr_price,
            'certification_price' => $price > 0 ? config('sisteco.maintenance_certification.value') : 0,
            'platform_price' => ((config('sisteco.platform_maintenance.value') / 100)) * ($intervention_area*$price + $hr_price)
        ];
        $maintenance_certification_total_price += $maintenance_year['certification_price'];
        $maintenance_platform_total_price += $maintenance_year['platform_price'];
        $maintenance_intervention_total_price += $maintenance_year['intervention_total_price'];
        $maintenance['years'][]=$maintenance_year;

        $maintenance_company_price = $maintenance_intervention_total_price *
            (
                config('sisteco.supervision.value')/100 + 
                config('sisteco.overheads.value')/100 + 
                config('sisteco.business_profit.value')/100  
            );
        $maintenance_certification_and_management_price = $maintenance_certification_total_price + $maintenance_platform_total_price;
        $total_maintenance_net_price = $maintenance_intervention_total_price + $maintenance_company_price +  $maintenance_certification_and_management_price;

        $maintenance['summary'] = [
            'intervention_total_price' => $maintenance_intervention_total_price,
            'company_price' => $maintenance_company_price,
            'platform_total_price' => $maintenance_platform_total_price,
            'certification_and_management_price' => $maintenance_certification_and_management_price,
            'total_net_price' => $total_maintenance_net_price,
            'total_vat' => $total_maintenance_net_price * $vat/100,
            'total_gross_price' => $total_maintenance_net_price * (1+$vat/100),
            'maintenance_gross_price_per_area' => 0,
        ];

        //defining $general array

        $total_net_price = $maintenance['summary']['total_net_price'] + $interventions['info']['total_net_price'];
        $total_vat_price = $total_net_price * config('sisteco.vat.value') / 100;
        $total_gross_price = $total_net_price + $total_vat_price;

        $total_net_price_per_area = $total_net_price / $intervention_area;
        $total_vat_price_per_area = $total_vat_price / $intervention_area;
        $total_gross_price_per_area = $total_gross_price / $intervention_area;


        $general = [
            'total_net_price' => $total_net_price,
            'total_vat_price' => $total_vat_price,
            'total_gross_price' => $total_gross_price,
            'platform_net_price' => $interventions['info']['platform_maintenance_price'] + $maintenance['summary']['platform_total_price'],
            'total_net_price_per_area' => $total_net_price_per_area, 
            'total_vat_price_per_area' => $total_vat_price_per_area, 
            'total_gross_price_per_area' => $total_gross_price_per_area, 
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

    /**
     * It returns the transport class
     * '1' -> d <= 500
     * '2' -> 500 < d <=1000
     * '3' -> 1000 >= 1000
     *
     * @return string
     */
    public function computeTransportClass(): string
    {
        if (min($this->hiking_routes_min_dist,$this->streets_min_dist) <= 500) return '1';
        if (min($this->hiking_routes_min_dist,$this->streets_min_dist) <= 1000) return '2';
        return '3';
    }

}
