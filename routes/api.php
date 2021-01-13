<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/activity', 'BotController@updatedActivity');

Route::get('/interests', 'BotController@interestsDebug');

Route::get('/setwebhook', 'BotController@setWebhook');
Route::get('/removewebhook', 'BotController@removeWebhook');

Route::post('/webhook', 'BotController@handleRequest');


//////////////////////HELLO//////////////////
///////////////BIG PROBLEM:
//If there is no user in the db, the whole bot stops working. Its pbbly some new feature (the 100% or 80% match pbbly) which is not controller if users dont exist. Make it so that at the start of the code the user (if exists in db) is saved in a variable. Then in the other methods, if the user is set, continue, if not, dont. This is better than calling to the db on every method and checking if the answer is [].
