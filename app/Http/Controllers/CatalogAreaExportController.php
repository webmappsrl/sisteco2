<?php

namespace App\Http\Controllers;

use App\Exports\CatalogAreaExport;
use Maatwebsite\Excel\Facades\Excel;

class CatalogAreaExportController extends Controller
{
    public function export()
    {
        $filename = now()->format('Ymd').'_sisteco_catalog_area.xlsx';

        return Excel::download(new CatalogAreaExport(), $filename);
    }
}
