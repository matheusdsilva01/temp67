<?php

declare(strict_types=1);

use App\Http\Controllers\FileController;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get('/', fn (): Factory|View => view('home'))->name('home');

Route::get('/files/{file:public_id}', [FileController::class, 'show'])
    ->whereUuid('file')
    ->name('files.show');

Route::post('/upload', [FileController::class, 'store'])
    ->middleware('throttle:files')
    ->name('files.store');
