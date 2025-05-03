<?php

use App\Http\Controllers\FacePlusController;
use App\Http\Controllers\ImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\FaceSetController;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\AuthController;

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
Route::post('/auth/google', [GoogleLoginController::class, 'handleGoogleCallback']);
Route::post('image/search', [ImageController::class, 'search']);
Route::get('image/search-results/{correlationId}', [ImageController::class, 'getSearchResults']);

Route::middleware(('auth:sanctum'))->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::resource('person', PersonController::class);
    Route::post('face/search', [FacePlusController::class, 'facePlusSearch']);
    Route::post('face/detect', [FacePlusController::class, 'facePlusDetect']);
    Route::post('face/fullsearch', [FacePlusController::class, 'detectAndSearchFaces']);

    Route::post('image/sync', [ImageController::class, 'syncImage']);

    // Route to getImagesFromMongo
    Route::get('image/getImages', [ImageController::class, 'getImagesFromMongo']);

    // Grouping all routes related to facesets under the /api/facesets path
    Route::prefix('faceset')->group(function () {
        // Create a new faceset
        Route::post('/', [FaceSetController::class, 'create'])->name('faceset.create');

        // List all facesets
        Route::get('/', [FaceSetController::class, 'index'])->name('faceset.index');

        // Get details of a specific faceset by outer_id
        Route::get('/{outer_id}', [FaceSetController::class, 'show'])->name('faceset.show');

        // Update a faceset by outer_id
        Route::put('/{outer_id}', [FaceSetController::class, 'update'])->name('faceset.update');

        // Delete a faceset by faceset_id
        Route::delete('/{faceset_id}', [FaceSetController::class, 'destroy'])->name('faceset.destroy');

        // Add faces to a faceset by outer_id
        Route::post('/{outer_id}/add-face', [FaceSetController::class, 'addFace'])->name('faceset.addFace');
        
        Route::post('/sync-face', [FaceSetController::class, 'syncFace'])->name('faceset.syncFace');

        // Remove faces from a faceset by outer_id
        Route::post('/{outer_id}/remove-face', [FaceSetController::class, 'removeFace'])->name('faceset.removeFace');

        // Remove all faces from a faceset by faceset_id
        Route::delete('/{faceset_id}/remove-all-faces', [FaceSetController::class, 'removeAllFaces'])->name('faceset.removeAllFaces');
    });
});

