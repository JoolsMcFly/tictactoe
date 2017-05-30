<?php
use App\Events\GameRequestEvent;
use App\Events\GameOverEvent;
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
    if (Auth::guest()) {
        return view('welcome');
    }
    else {
        return response()->redirectTo('/home');
    }
});

Route::group(['middleware' => 'auth'], function () {

    Route::post('/game-save', function () {
        $input = Input::all();
        $game = \App\Game::create([
            'winner_id' => $input['winner'],
            'looser_id' => $input['looser'],
            'first_player' => $input['first_player'],
            'extra' => [
                'elapsed' => $input['elapsed'],
                'size' => $input['size'],
                'moves' => $input['moves']
            ]
        ]);
        foreach ([$input['winner'], $input['looser']] as $user_id) {
            $player = User::find($user_id);
            if (!$player) {
                continue;
            }
            if ($user_id == $input['winner']) {
                $player->wins++;
            } else {
                $player->losses++;
            }
            $player->total_moves += count($input['moves']);
            $player->total_time += $input['elapsed'];
            $size_played = $player->size_played;
            if (empty($size_played[$input['size']])) {
                $size_played[$input['size']] = 1;
            } else {
                $size_played[$input['size']] ++;
            }
            $player->size_played = $size_played;
            $player->save();
            broadcast(new GameOverEvent($player));
            return response()->json($game->load('winner', 'looser')->toArray());
        }
    });

    Route::post('/new-game-request/{to}', function (User $to) {
        $grid_width = Input::get('grid_width', 3);
        broadcast(new GameRequestEvent($to, $grid_width));
    });

    Route::post('/new-move/{to}', function (User $to) {
        broadcast(new \App\Events\NewMoveEvent($to, Input::all()));
    });

    Route::post('/new-game-accepted/{to}', function (User $to) {
        broadcast(new App\Events\GameStartedEvent($to, Auth::user(), true));
        broadcast(new App\Events\GameStartedEvent(Auth::user(), $to, false));
    });

    Route::post('/new-game-refused/{to}', function (User $to) {
        broadcast(new App\Events\GameRefusedEvent($to, Input::get('reason')));
    });


    Route::get('/home', 'HomeController@index')->name('home');

    Route::post('/player/{to}', function (User $to) {
        $a = $to->toArray();
        return response()->json($a);
    });
});
Auth::routes();
