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

Route::post('/game-save', function () {
    $input = Illuminate\Support\Facades\Input::all();
    file_put_contents('/tmp/debug', print_r($input, 1) . "\n\n");
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
