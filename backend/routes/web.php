<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CaseController;

Route::post('/api/cases', [CaseController::class, 'store']);
Route::get('/api/cases/{id}', [CaseController::class, 'show']);

// Note: copy these into a real Laravel project's routes/web.php or routes/api.php
