<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MedicamentController;

Route::get('/medicaments', [MedicamentController::class, 'index']);
Route::post('/medicaments', [MedicamentController::class, 'store']);
Route::delete('/medicaments/{id}', [MedicamentController::class, 'destroy']);
Route::put('/medicaments/{id}', [MedicamentController::class, 'update']);
Route::post('/ventes', [MedicamentController::class, 'vente']);