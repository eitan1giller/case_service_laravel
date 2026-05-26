<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/cases', [\App\Http\Controllers\Api\CaseController::class, 'store']);
Route::get('/cases/{case}', [\App\Http\Controllers\Api\CaseController::class, 'show']);
