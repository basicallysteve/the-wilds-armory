<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Api\ArmorController;

Route::get('/', function () {
   return to_route('armory.index');
})->name('home');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::group(["prefix" => "armory"], function () {
    Route::get('/', [ArmorController::class, 'armory'])->name('armory.index');

    // Route::get('profile', function () {
    //     return Inertia::render('Profile');
    // })->middleware(['auth', 'verified'])->name('profile');
});
require __DIR__.'/api.php';
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
