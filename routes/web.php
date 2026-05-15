<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ─── AUTH ─────────────────────────────────────────────────────────
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('notes.index');
    }
    return view('landing');
});
Route::get('/login',      [AuthController::class, 'loginForm'])->name('auth.login');
Route::post('/login',     [AuthController::class, 'login'])->name('auth.login.post');
Route::get('/register',   [AuthController::class, 'registerForm'])->name('auth.register');
Route::post('/register',  [AuthController::class, 'register'])->name('auth.register.post');
Route::get('/activate',   [AuthController::class, 'activate'])->name('auth.activate');
Route::get('/forgot',     [AuthController::class, 'forgotForm'])->name('auth.forgot');
Route::post('/forgot',    [AuthController::class, 'forgot'])->name('auth.forgot.send');
Route::get('/reset',      [AuthController::class, 'resetForm'])->name('auth.reset');
Route::post('/reset',     [AuthController::class, 'reset'])->name('auth.reset.process');
Route::post('/logout',    [AuthController::class, 'logout'])->name('auth.logout');

// ─── NOTES ────────────────────────────────────────────────────────
Route::middleware('auth')->prefix('notes')->name('notes.')->group(function () {
    Route::get('/',              [NoteController::class, 'index'])->name('index');
    Route::get('/shared',        [NoteController::class, 'sharedWithMe'])->name('shared');
    Route::get('/search',        [NoteController::class, 'search'])->name('search');
    Route::get('/{id}',          [NoteController::class, 'get'])->name('get');
    Route::post('/create',       [NoteController::class, 'create'])->name('create');
    Route::post('/save',         [NoteController::class, 'save'])->name('save');
    Route::post('/delete/{id}',  [NoteController::class, 'delete'])->name('delete');
    Route::post('/pin/{id}',     [NoteController::class, 'togglePin'])->name('pin');
    Route::post('/setLock',      [NoteController::class, 'setLock'])->name('setLock');
    Route::post('/removeLock',   [NoteController::class, 'removeLock'])->name('removeLock');
    Route::post('/verifyLock',   [NoteController::class, 'verifyLock'])->name('verifyLock');
    Route::post('/uploadImages', [NoteController::class, 'uploadImages'])->name('uploadImages');
    Route::post('/removeImage/{imageId}', [NoteController::class, 'removeImage'])->name('removeImage');
    Route::post('/share',        [NoteController::class, 'share'])->name('share');
    Route::post('/updatePermission', [NoteController::class, 'updatePermission'])->name('updatePermission');
    Route::post('/revokeShare/{id}', [NoteController::class, 'revokeShare'])->name('revokeShare');
    Route::post('/syncLabels',   [NoteController::class, 'syncLabels'])->name('syncLabels');
});

// ─── LABELS ───────────────────────────────────────────────────────
Route::middleware('auth')->prefix('labels')->name('labels.')->group(function () {
    Route::get('/',         [LabelController::class, 'index'])->name('index');
    Route::post('/create',  [LabelController::class, 'create'])->name('create');
    Route::post('/{id}',    [LabelController::class, 'update'])->name('update');
    Route::post('/delete/{id}', [LabelController::class, 'destroy'])->name('destroy');
});

// ─── PROFILE ──────────────────────────────────────────────────────
Route::middleware('auth')->prefix('profile')->name('profile.')->group(function () {
    Route::get('/',                    [ProfileController::class, 'index'])->name('index');
    Route::post('/update',             [ProfileController::class, 'update'])->name('update');
    Route::post('/change-password',    [ProfileController::class, 'changePassword'])->name('changePassword');
    Route::post('/preferences',        [ProfileController::class, 'updatePreferences'])->name('preferences');
});
