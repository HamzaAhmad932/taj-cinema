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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::post('save-shares', 'ClosedShowController@storeShare')->name('save-shares');
Route::get('/fetch-closed-show/{id}', 'ClosedShowController@fetchShow')->name('share.edit');


Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
    /**
     * Custom routes that has its own views and controller
     */
    Route::get('/closed-shows', 'ShowController@index');
    Route::get('/journel-entry', 'AccountDetailController@index');
    Route::get('/ledgers', 'LedgerController@index');
    Route::get('/coa-add/{type}', 'AccountsController@coaAdd')->name('coa-add');

    /**
     * POST method routes for API calls
     */
    Route::post('save-coa', 'AccountsController@coaSave')->name('coa-save');
});
