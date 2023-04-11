<?php

use App\Exports\OwnersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OwnersExportController;
use App\Http\Controllers\CadastralParcelController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
//create link to download excel file
Route::get('/owners/export', [OwnersExportController::class, 'export']);

//create route to view cadastral parcel data and catalog estimate
Route::get('/cadastral-parcels/{id}', 'CadastralParcelController@show')->name('cadastral-parcel');
