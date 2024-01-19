<?php

use App\Http\Controllers\FlamingoGridController;
use App\Http\Controllers\FlamingoViewGridController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::agGrid('/flamingos/rows', FlamingoGridController::class)->name('flamingos');
Route::agGrid('view/flamingos', FlamingoViewGridController::class)->name('view.flamingos');
