<?php

namespace App\Console\Commands;

use Exception;
use App\Models\Catalog;
use App\Models\CadastralParcel;
use Faker\Core\Number;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EstimateByCatalog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sisteco2:estimate_by_catalog 
                            {id : id of the catalog that must be used to estimate}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command loop on all cadastral particles and compute the estimated value on a specific catalog identified by id';

    /**
     * Execute the console command.
     * 
     * @return int
     */
    public function handle()
    {
        // Find Catalog
        $c = Catalog::find($this->argument('id'));
        if (empty($c)) {
            throw new Exception("Catalog with ID {$this->argument('id')} does not exist..", 1);
        }
        $types = $c->catalogTypes()->pluck('cod_int', 'id')->toArray();
        $prices = $c->catalogTypes()->pluck('prices', 'cod_int')->toArray();

        $this->info('Processing');
        $ids = collect(DB::select('select distinct cadastral_parcel_id as id from cadastral_parcel_owner;'))->pluck('id')->toArray();

        $parcels = CadastralParcel::whereIn('id', $ids)->get();
        $tot_p = $parcels->count();
        $count_p = 1;
        //get the VAT value from sisteco config file
        $vat = config('sisteco.vat.value');
        // Loop on particles
        foreach ($parcels as $p) {
            $this->info("($count_p/$tot_p)Processing cadastral parcel {$p->id}");
            $count_p++;
            $results = DB::select("
                        SELECT 
                            catalog_type_id, 
                            SUM(ST_AREA(ST_Intersection(a.geometry,p.geometry))) as area 
                        FROM 
                           cadastral_parcels as p, 
                           catalog_areas as a
                        WHERE 
                           a.catalog_id={$this->argument('id')} AND 
                           p.id = {$p->id} 
                           AND ST_Intersects(a.geometry,p.geometry)
                           
                        GROUP BY
                           catalog_type_id
                           ");
            // SLOPE AND DISTANCE
            $parcel_code = $p->computeSlopeClass() . '.' . $p->computeTransportClass();
            $total_price = 0;
            //define json structure
            $interventions = [];
            $maintenance = [];
            $general = [];
            $json = [];

            $intervention_area = 0;
            $intervention_price = 0;
            $items = [];
            if (count($results) > 0) {
                $count = count($results);
                $this->info("Found $count intersections");
                foreach ($results as $item) {
                    $cod_int = $types[$item->catalog_type_id];
                    $unit_price = $prices[$cod_int][$parcel_code];
                    $price = ($item->area / 1000) * $unit_price * (1 + $vat / 100); //adding the VAT to the price
                    $total_price += $price;
                    $intervention_area += $item->area / 1000;
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
                $supervision_price = $intervention_price * (1 + config('sisteco.supervision.value') / 100);
                $overhead_price = $intervention_price * (1 + config('sisteco.overhead.value') / 100);
                $business_profit_price = $intervention_price * (1 + config('sisteco.business_profit.value') / 100);
                $intervention_certification = config('sisteco.intervention_certification.value');
                $total_intervention_certificated_price = $intervention_price + $supervision_price + $overhead_price + $business_profit_price + $intervention_certification;
                $team_price = config('sisteco.team.value');
                $platform_maintenance_price = $total_intervention_certificated_price * config('sisteco.platform_maintenance.value');
                $total_intervention_gross_price = $total_intervention_certificated_price + $team_price + $platform_maintenance_price;
                $total_intervention_net_price = $total_intervention_gross_price - ($total_intervention_gross_price * $vat / 100);
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
                    'maintenance_gross_price_per_area' => number_format($total_maintenance_gross_price / $intervention_area, 2, ',', '.'),
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
                    'total_gross_price_per_area' => number_format($total_general_gross_price / $intervention_area, 2, ',', '.'),
                ];

                //assign the fields to json
                $json['interventions'] = $interventions;
                $json['maintenance'] = $maintenance;
                $json['general'] = $general;

                //formatting the estimated value
                $estimated_value = floatval(number_format($total_general_gross_price, 2, '.', ''));

                $p->catalog_estimate = $json;
                $p->estimated_value = $estimated_value;
                $this->info(json_encode($json));
                $p->save();
            } else {
                $this->info("No intersection Found");
            }
            $this->info('---');
        }
        return $this->info('Done!');
    }
}
