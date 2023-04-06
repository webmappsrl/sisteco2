<?php

namespace App\Models;

use Exception;
use App\Models\Catalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class CadastralParcel extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'municipality',
        'estimated_value',
        'average_slope',
        'meter_min_distance_road',
        'meter_min_distance_path',
        'square_meter_surface',
        'slope',
        'way',
        'catalog_estimate',
        'geometry',
        'sisteco_legacy_id',
    ];

    protected $casts = [
        'catalog_estimate' => 'array',

    ];


    /**
     * Get the owners that own the CadastralParcel.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function owners(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Owner::class);
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
        if ($this->average_slope <= 20) return 'A';
        if ($this->average_slope <= 40) return 'B';
        return 'C';
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
        if ($this->meter_min_distance_road <= 500) return '1';
        if ($this->meter_min_distance_road <= 1000) return '2';
        return '3';
    }

    /**
     * Calculate the estimated catalog value based on the catalod id
     * 
     * @param int $catalog_id
     * 
     * @return array catalog_estimate array
     */

    public function computeCatalogEstimate(int $catalog_id): array
    {
        $c = Catalog::find($catalog_id);
        if (!$c) {
            throw new Exception("Catalog with id $catalog_id not found");
        }

        $types = $c->catalogTypes()->pluck('id', 'cod_int')->toArray();
        $prices = $c->catalogTypes()->pluck('prices', 'cod_int')->toArray();
        $vat = config('sisteco.vat.value');
        $results = DB::select("
                        SELECT 
                            catalog_type_id, 
                            SUM(ST_AREA(ST_Intersection(a.geometry,p.geometry))) as area 
                        FROM 
                           cadastral_parcels as p, 
                           catalog_areas as a
                        WHERE 
                           a.catalog_id={$catalog_id} AND 
                           p.id = {$this->id} 
                           AND ST_Intersects(a.geometry,p.geometry)
                           
                        GROUP BY
                           catalog_type_id
                           ");

        // SLOPE AND DISTANCE
        $parcel_code = $this->computeSlopeClass() . '.' . $this->computeTransportClass();
        //define json structure
        $interventions = [];
        $maintenance = [];
        $general = [];
        $json = [];

        $intervention_area = 0;
        $intervention_price = 0;
        $items = [];
        if (count($results) > 0) {
            foreach ($results as $item) {
                $cod_int = $types[$item->catalog_type_id];
                $unit_price = $prices[$cod_int][$parcel_code];
                $intervention_area += ($item->area / 10000);
                $price = $intervention_area * $unit_price * (1 + $vat / 100); //adding the VAT to the price
                $intervention_price += $price;

                //if $cod_int is not equal to 0 then add the item to the interventions array
                if ($cod_int != 0) {
                    $items[] = [
                        'code' => $cod_int . '.' . $parcel_code,
                        'area' => number_format($item->area / 10000, 4, ',', '.'),
                        'unit_price' => number_format($unit_price, 2, ',', '.'),
                        'price' => number_format($price, 2, ',', '.'),
                    ];
                }
            }
            //defining $interventions['items']
            $interventions['items'] = $items;
            //define the variables for the $intervention['info'] array
            $supervision_price = $intervention_price *  (1 + (config('sisteco.supervision.value') / 100));
            $overhead_price = $intervention_price * (1 + (config('sisteco.overhead.value') / 100));
            $business_profit_price = $intervention_price * (1 + (config('sisteco.business_profit.value') / 100));
            $intervention_certification = config('sisteco.intervention_certification.value');
            $total_intervention_certificated_price = $intervention_price + $supervision_price + $overhead_price + $business_profit_price + $intervention_certification;
            $team_price = $total_intervention_certificated_price * (1 + (config('sisteco.team_management.value') / 100));
            $platform_maintenance_price = $total_intervention_certificated_price * (1 + (config('sisteco.platform_maintenance.value') / 100));
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
            $total_maintenance_gross_price = $maintenance_item_price * 5; //price of the 5 years of maintenance plus vat
            $total_maintenance_net_price = $total_maintenance_gross_price / (1 + $vat / 100); //price of the 5 years of maintenance without vat
            $maintenance['summary'] = [
                'total_maintenance_gross_price' => number_format($total_maintenance_gross_price, 2, ',', '.'),
                'total_maintenance_net_price' => number_format($total_maintenance_net_price, 2, ',', '.'),
                'total_maintenance_vat' => number_format($total_maintenance_gross_price - $total_maintenance_net_price, 2, ',', '.'), //vat
                'maintenance_gross_price_per_area' => $intervention_area != 0 ? number_format($total_maintenance_gross_price / $intervention_area, 2, ',', '.') : $total_maintenance_gross_price, //if intervention_area is 0 the default value is the total_maintenance_gross_price
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

            //assign the fields to json
            $json['interventions'] = $interventions;
            $json['maintenance'] = $maintenance;
            $json['general'] = $general;

            //formatting the estimated value
            $estimated_value = floatval(number_format($total_general_gross_price, 2, '.', ''));

            return $json;
        }
    }
}
