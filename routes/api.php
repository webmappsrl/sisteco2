<?php

use App\Http\Resources\GeomCadastralParcelResource;
use App\Http\Resources\GeomToPointCadastralParcelResource;
use App\Models\CadastralParcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::get('cadastralparcels',function() {return [];});
Route::get('v1/geomtopoint/cadastralparcels',function() {
    return CadastralParcel::all()->pluck('updated_at','id')->toArray();
});
Route::get('v1/geomtopoint/cadastralparcel/{id}',function (string $id) {
        return new GeomToPointCadastralParcelResource(CadastralParcel::findOrFail($id));
});
Route::get('v1/geom/cadastralparcel/{id}',function (string $id) {
        return new GeomCadastralParcelResource(CadastralParcel::findOrFail($id));
});
