<?php

use App\Models\Catalog;
use App\Models\CatalogArea;
use App\Models\CatalogType;
use Illuminate\Http\Request;
use App\Models\CadastralParcel;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\CatalogTypeAreaResource;
use App\Http\Resources\GeomCatalogAreaResource;
use App\Http\Resources\GeomCadastralParcelResource;
use App\Http\Resources\GeomToPointCadastralParcelResource;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('v1/geomtopoint/cadastralparcels', function () {
        return CadastralParcel::all()->pluck('updated_at', 'id')->toArray();
});
Route::get('v1/geomtopoint/cadastralparcel/{id}', function (string $id) {
        return new GeomToPointCadastralParcelResource(CadastralParcel::findOrFail($id));
});
Route::get('v1/geom/cadastralparcel/{id}', function (string $id) {
        return new GeomCadastralParcelResource(CadastralParcel::findOrFail($id));
});

Route::get('v1/geom/catalogarea/{id}', function (string $id) {
        return new GeomCatalogAreaResource(CatalogArea::findOrFail($id));
});

Route::get('v1/catalog/{catalog_id}/area/{type_id}.geojson', function (string $id, string $type_id) {
        $catalog = Catalog::findOrFail($id);
        $catalog_type = CatalogType::findOrFail($type_id);
        if ($catalog->id == $catalog_type->catalog_id) {
                return new CatalogTypeAreaResource($catalog_type);
        } else {
                return response()->json(['error' => 'Not Found'], 404);
        }
});
