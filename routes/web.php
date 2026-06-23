<?php

use App\Http\Controllers\ExcelController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Excel download rute (zaštićene auth-om)
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/excel/template', [ExcelController::class, 'downloadTemplate'])->name('excel.template');
    Route::get('/admin/excel/export', [ExcelController::class, 'export'])->name('excel.export');
});
