<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/auth/cancel', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('auth.cancel');

Route::get('/two-factor-challenge',  [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
Route::post('/two-factor-challenge', [TwoFactorController::class, 'verify'])->name('two-factor.verify')->middleware('throttle:10,1');

Route::middleware('auth')->group(function () {
    Route::get('/',                       [CalendarController::class, 'index'])->name('calendar');
    Route::get('/calendar/{year}/{month}',[CalendarController::class, 'show'])->name('calendar.month');

    Route::get('/day/{date}',             [PostController::class, 'day'])->name('day');
    Route::get('/posts/create',           [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts',                 [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}',           [PostController::class, 'show'])->name('posts.show');
    Route::get('/posts/{post}/edit',      [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}',           [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}',        [PostController::class, 'destroy'])->name('posts.destroy');

    Route::delete('/media/{media}',       [MediaController::class, 'destroy'])->name('media.destroy');

    Route::get('/report',                 [ReportController::class, 'index'])->name('report');
    Route::get('/report/export',          [ReportController::class, 'export'])->name('report.export');

    Route::get('/log',                    [LogController::class, 'index'])->name('log');
    Route::delete('/log',                 [LogController::class, 'destroy'])->name('log.destroy');

    Route::get('/two-factor/setup',       [TwoFactorController::class, 'setup'])->name('two-factor.setup');
    Route::post('/two-factor/enable',     [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::delete('/two-factor/disable',  [TwoFactorController::class, 'disable'])->name('two-factor.disable');

    Route::get('/users',                              [UserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/email',               [UserController::class, 'updateEmail'])->name('users.update-email');
    Route::post('/users/{user}/reset-two-factor',     [UserController::class, 'resetTwoFactor'])->name('users.reset-2fa');
});
