<?php

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

// Home
Auth::routes();
Route::get('home', function () {
    return redirect('/');
});
Route::get('/', 'HomeController@index')->name('home');

// My profile
Route::get('my-profile', function () {
    return redirect('my-profile/overview');
});
Route::prefix('my-profile')->group(function () {

	// Get view
	Route::get('overview', 'MyProfileController@displayOverview')->name('my-profile/overview');
	Route::get('details', 'MyProfileController@displayDetails')->name('my-profile/details');
	Route::get('transaction', 'MyProfileController@displayTransaction')->name('my-profile/transaction');
	Route::get('category', 'MyProfileController@displayCategory')->name('my-profile/category');
	Route::get('account', 'MyProfileController@displayAccount')->name('my-profile/account');

	// Form request
	Route::put('add-transaction', 'TransactionController@addTransaction')->name('my-profile/add-transaction');
	Route::put('add-transfer', 'TransferController@addTransfer')->name('my-profile/add-transfer');

	Route::put('add-category', 'CategoryController@addCategory')->name('my-profile/add-category');
	Route::delete('remove-category', 'CategoryController@removeCategory')->name('my-profile/remove-category');

	Route::put('add-subcategory', 'SubCategoryController@addSubCategory')->name('my-profile/add-subcategory');
	Route::delete('remove-subcategory', 'SubCategoryController@removeSubCategory')->name('my-profile/remove-subcategory');

	Route::put('add-account', 'AccountController@addAccount')->name('my-profile/add-account');
	Route::delete('remove-account', 'AccountController@removeAccount')->name('my-profile/remove-account');
});
