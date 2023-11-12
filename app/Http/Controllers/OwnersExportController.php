<?php

namespace App\Http\Controllers;

use App\Exports\OwnersExport;
use Maatwebsite\Excel\Facades\Excel;

class OwnersExportController extends Controller
{
    public function export()
    {
        return Excel::download(new OwnersExport(), 'owners.xlsx');
    }
}
