<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/get-presigned-url', [FileController::class, 'getPresignedUrl']);
    Route::post('/store-file-metadata', [FileController::class, 'storeFileMetadata']);
    // Route to display the file upload form (for the frontend view)
    Route::get('/file-upload', function () {
        return view('file-upload'); // Return the Blade view
    });

});

require __DIR__.'/auth.php';
