<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChallengeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ChallengeController::class, 'home'])->name('home');
Route::get('/proposal', [ChallengeController::class, 'proposal'])->name('proposal');
Route::post('/teams', [ChallengeController::class, 'createTeam'])->middleware('throttle:5,1')->name('teams.create');
Route::get('/captain/{token}', [ChallengeController::class, 'captain'])->name('captain.show');
Route::post('/captain/{token}/invite', [ChallengeController::class, 'invite'])->middleware('throttle:10,1')->name('captain.invite');
Route::get('/join/{token}', [ChallengeController::class, 'join'])->name('join.show');
Route::post('/join/{token}', [ChallengeController::class, 'registerMember'])->middleware('throttle:10,1')->name('join.store');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->prefix('staff')->name('staff.')->group(function () {
    Route::get('/', [ChallengeController::class, 'staff'])->name('dashboard');
    Route::post('/members/{member}/replace', [ChallengeController::class, 'replaceMember'])->name('members.replace');
    Route::get('/export', [ChallengeController::class, 'export'])->name('export');
});
