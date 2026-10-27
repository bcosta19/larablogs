<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\MailSettingsController;
use App\Http\Controllers\ReaderController;
use App\Http\Middleware\RequirePrivateAccount;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.store');
});

Route::middleware(['auth', RequirePrivateAccount::class])->group(function () {
    Route::get('/account/password', [AuthController::class, 'password'])->name('password.edit');
    Route::put('/account/password', [AuthController::class, 'updatePassword'])->name('password.update');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [BlogController::class, 'index'])->name('home');
    Route::get('/blogs/{blog}', [ReaderController::class, 'blog'])->name('blogs.show');
    Route::get('/posts/{post}/read', [ReaderController::class, 'post'])->name('posts.read');
    Route::post('/blogs', [BlogController::class, 'store'])->name('blogs.store');
    Route::post('/blogs/{blog}/check', [BlogController::class, 'check'])->name('blogs.check');
    Route::patch('/blogs/{blog}/toggle', [BlogController::class, 'toggle'])->name('blogs.toggle');
    Route::delete('/blogs/{blog}', [BlogController::class, 'destroy'])->name('blogs.destroy');

    Route::post('/settings/mail', [MailSettingsController::class, 'update'])->name('settings.mail.update');
    Route::post('/settings/mail/test', [MailSettingsController::class, 'test'])->name('settings.mail.test');
    Route::get('/settings/mail/test/{id}', [MailSettingsController::class, 'status'])->name('settings.mail.status');
});
