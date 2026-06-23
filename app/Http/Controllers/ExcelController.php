<?php

namespace App\Http\Controllers;

use App\Exports\ClanExport;
use App\Exports\ClanImportTemplate;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    public function downloadTemplate()
    {
        return Excel::download(new ClanImportTemplate(), 'sablon_import_clanova.xlsx');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['status', 'kategorija_id']);
        return Excel::download(new ClanExport($filters), 'clanovi.xlsx');
    }
}
