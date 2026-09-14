<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::post('/upload', [FileController::class, 'store'])
    ->middleware('throttle:files')
    ->name('files.store');
