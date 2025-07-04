<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Violation statistics for the educator dashboard
Route::get('/violation-stats', 'App\Http\Controllers\ViolationController@getViolationStatsByPeriod');
Route::get('/violation-stats-by-batch', 'App\Http\Controllers\BehaviorDataController@getViolationStatsByBatch');

// Add route for fetching violation types by category
Route::get('/violation-types/{categoryId}', 'App\Http\Controllers\ViolationController@getViolationTypesByCategory');

