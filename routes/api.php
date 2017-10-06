<?php

use Illuminate\Http\Request;

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

Route::post('auth/register', 'UserController@register');
Route::post('auth/login', 'UserController@login');
Route::group(['middleware' => 'jwt.auth'], function () {
    Route::get('user', 'UserController@getAuthUser');
    Route::get('get-categories', 'APIController@getCategories');
	Route::get('get-subcategories/{parentCategory?}', 'APIController@getSubCategories');
	Route::get('get-accounts', 'APIController@getAccounts');
	Route::get('get-overview-infos/{start}/{end}', 'APIController@getOverviewInfos');
	Route::get('get-accounts-amount', 'APIController@getAccountsAmount');
	Route::put('add-transaction', 'APIController@addTransaction');
});

/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/
