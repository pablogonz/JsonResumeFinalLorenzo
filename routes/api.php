<?php

use App\Http\Controllers\ResumeController;
use App\Http\Middleware\SimpleAuth;
use Illuminate\Support\Facades\Route;

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
// We've implemented the cachable with etag through a middleware applicalble on all the routes
Route::middleware('cache.headers:public;max_age=2628000;etag')->group(function () {
    Route::get('Resume', [ResumeController::class, 'showFullResume']);
    Route::get('Resume/{section}/{subsection?}', [ResumeController::class, 'showSpecific']);
    Route::middleware(SimpleAuth::class)->group(function () {
        /** You need to be authenticated to be able to access those routes */
        Route::post('Resume', [ResumeController::class,'store']);
        Route::put('Resume', [ResumeController::class,'update']);
        Route::patch('Resume/{section}/{subsection?}', [ResumeController::class,'updateSpecific']);
        Route::delete('Resume', [ResumeController::class,'destroy']);
    });
});


