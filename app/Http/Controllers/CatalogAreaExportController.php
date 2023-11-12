<?php

namespace App\Http\Controllers;

use App\Exports\CatalogAreaExport;
use Maatwebsite\Excel\Facades\Excel;

class CatalogAreaExportController extends Controller
{
    public function export()
    {
        return Excel::download(new CatalogAreaExport(), 'catalog-area.xlsx');
    }
}
