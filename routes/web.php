<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogAreaController;
use App\Http\Controllers\OwnersExportController;
use App\Http\Controllers\SupportProjectController;
use App\Http\Controllers\CatalogAreaExportController;

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

Route::get('/owners/export', [OwnersExportController::class, 'export']);

Route::get('/catalog-areas/export', [CatalogAreaExportController::class, 'export']);

Route::get('/catalog-areas/{id}', [CatalogAreaController::class, 'show'])->name('catalog-area');

Route::post('/support-project', [SupportProjectController::class, 'sendMail'])->name('support.project');
