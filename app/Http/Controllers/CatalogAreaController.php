<?php

namespace App\Http\Controllers;

use App\Models\CatalogArea;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreCatalogAreaRequest;
use App\Http\Requests\UpdateCatalogAreaRequest;

class CatalogAreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCatalogAreaRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        $catalogArea = CatalogArea::findOrFail($id);
        $area = DB::table('catalog_areas')
            ->select(DB::raw('ST_Area(geometry) as area'))
            ->where('id', $catalogArea->id)
            ->value('area');
        $sisteco = config('sisteco');

        $interventionPrice = $catalogArea->catalog_estimate['interventions']['info']['intervention_price'];
        $interventionPrice = str_replace(".", "", $interventionPrice);
        $interventionPrice = str_replace(",", ".", $interventionPrice);
        $interventionPrice = floatval($interventionPrice);

        $interventionCertification = $catalogArea->catalog_estimate['interventions']['info']['intervention_certification'];
        $interventionCertification = str_replace(".", "", $interventionCertification);
        $interventionCertification = str_replace(",", ".", $interventionCertification);
        $interventionCertification = floatval($interventionCertification);

        $hikingRoutesTotalCost = $catalogArea->hiking_routes_length * $sisteco['hiking_routes_cost_per_km']['value'];

        $forestalInterventionPercentageValue =
            $sisteco['overheads']['value'] +  $sisteco['business_profit']['value'] + $sisteco['supervision']['value'];

        $forestalInterventionPrice = round(($interventionPrice + $hikingRoutesTotalCost) * $forestalInterventionPercentageValue / 100, 2);


        // Hiking Routes details
        $hiking_routes_details_string = '-';
        if (!is_null($catalogArea->hiking_routes_details) && count($catalogArea->hiking_routes_details) > 0) {
            $hiking_routes_details_string = '';
            foreach ($catalogArea->hiking_routes_details as $ref => $length) {
                $ls = number_format($length, 0);
                $hiking_routes_details_string .= "$ref($ls m) ";
            }
        }


        return view(
            'catalog-area',
            [
                'catalogArea' => $catalogArea,
                'sisteco' => $sisteco,
                'area' => $area,
                'hiking_routes_details_string' => $hiking_routes_details_string,
                'forestalInterventionPrice' => $forestalInterventionPrice,
                'hikingRoutesTotalCost' => $hikingRoutesTotalCost,
                'interventionPrice' => $interventionPrice,
                'interventionCertification' => $interventionCertification,
            ]
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CatalogArea $catalogArea)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCatalogAreaRequest $request, CatalogArea $catalogArea)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CatalogArea $catalogArea)
    {
        //
    }
}
