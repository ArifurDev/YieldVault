<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

//Route::get('/dashboard', function () {
//    return view('backend.dashboard');
//})->middleware(['auth','admin', 'verified'])->name('dashboard');

//Route::middleware('auth')->group(function () {
//    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
//});


Route::get('/media/{path}', function ($path) {

    $path = str_replace('..', '', $path);
    $disk = config('app.file_system');

    if (!Storage::disk($disk)->exists($path)) {
        abort(404);
    }

    $fullPath = Storage::disk($disk)->path($path);
    $mimeType = mime_content_type($fullPath);

    return response()->stream(function () use ($fullPath) {
        readfile($fullPath);
    }, 200, [
        'Content-Type'        => $mimeType,
        'Content-Disposition' => 'inline',
        'Accept-Ranges'       => 'bytes',
    ]);

})->where('path', '.*')->name('media');

require __DIR__.'/auth.php';
