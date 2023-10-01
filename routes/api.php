<?php

use App\Http\Controllers\AddNewBrightSpotController;
use App\Http\Controllers\AddVoteController;
use App\Http\Controllers\NasaDataController;
use App\Models\BrightSpot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/actual-position', [NasaDataController::class, 'getActualData']);
Route::post('/test', [NasaDataController::class, 'test']);
Route::post('/bright-spots/{brightSpot}/vote', AddVoteController::class);
Route::post('/bright-spots', AddNewBrightSpotController::class);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
