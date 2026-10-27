<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\MailSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BlogController::class, 'index'])->name('home');
Route::post('/blogs', [BlogController::class, 'store'])->name('blogs.store');
Route::post('/blogs/{blog}/check', [BlogController::class, 'check'])->name('blogs.check');
Route::patch('/blogs/{blog}/toggle', [BlogController::class, 'toggle'])->name('blogs.toggle');
Route::delete('/blogs/{blog}', [BlogController::class, 'destroy'])->name('blogs.destroy');

Route::post('/settings/mail', [MailSettingsController::class, 'update'])->name('settings.mail.update');
Route::post('/settings/mail/test', [MailSettingsController::class, 'test'])->name('settings.mail.test');
Route::get('/settings/mail/test/{id}', [MailSettingsController::class, 'status'])->name('settings.mail.status');
