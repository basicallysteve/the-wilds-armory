<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ArmorController;
Route::group(['prefix' => 'api/v1'], function () {
   
    // Define your API routes here
    Route::group(["prefix" => 'armors'], function () {
        Route::get('/', 'App\Http\Controllers\Api\ArmorController@index')->name('api.armors.index');
        Route::get('/{id}', [ArmorController::class, 'show'])->name('api.armors.show');
    });
});