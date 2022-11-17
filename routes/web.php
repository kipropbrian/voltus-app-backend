<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\FacePlusController;
use App\Http\Controllers\ImageController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource('person', PersonController::class)->middleware(['auth', 'verified']);

Route::get('faceplus/{image}', [FacePlusController::class, 'getFaceTokenAddFacesetSetUserID'])->name('faceplus.connect');

Route::resource('image', ImageController::class)->middleware(['auth', 'verified']);

require __DIR__.'/auth.php';
