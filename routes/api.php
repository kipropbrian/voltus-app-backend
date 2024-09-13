<?php

use App\Http\Controllers\FacePlusController;
use App\Http\Controllers\ImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::resource('person', PersonController::class);
Route::post('face/search', [FacePlusController::class, 'facePlusSearch']); 
Route::post('face/detect', [FacePlusController::class, 'facePlusDetect']); 

Route::post('image/sync', [ImageController::class, 'syncImage']);
