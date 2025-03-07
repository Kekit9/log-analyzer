<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/logs/filter', [LogController::class, 'filter']);
