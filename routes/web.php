<?php

use App\Http\Controllers\BlogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BlogController::class, 'index'])->name('home');
Route::post('/blogs', [BlogController::class, 'store'])->name('blogs.store');
Route::post('/blogs/{blog}/check', [BlogController::class, 'check'])->name('blogs.check');
Route::patch('/blogs/{blog}/toggle', [BlogController::class, 'toggle'])->name('blogs.toggle');
Route::delete('/blogs/{blog}', [BlogController::class, 'destroy'])->name('blogs.destroy');
