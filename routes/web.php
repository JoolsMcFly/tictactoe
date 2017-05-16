<?php
use App\Events\GameRequestEvent;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

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
    $input = Input::all();
    file_put_contents('/tmp/debug', print_r($input, 1) . "\n\n");
});

Route::post('/new-game-request/{to}', function (User $to) {
    broadcast(new GameRequestEvent($to));
});

Route::post('/new-move/{to}', function (User $to) {
    broadcast(new \App\Events\NewMoveEvent($to, Input::all()));
});

Route::post('/new-game-accepted/{to}', function (User $to) {
    broadcast(new App\Events\GameStartedEvent($to));
    broadcast(new App\Events\GameStartedEvent(Auth::user()));
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
