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

        return view(
            'catalog-area',
            [
                'catalogArea' => $catalogArea,
                'sisteco' => $sisteco,
                'area' => $area
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
