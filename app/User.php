<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

    use Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     *
     * @var array auto cast attributes
     */
    protected $casts = [
        'size_played' => 'array'
    ];

    public function gamesWon()
    {
        return $this->hasMany(Game::class, 'winner_id');
    }

    public function gamesLost()
    {
        return $this->hasMany(Game::class, 'looser_id');
    }

    public function getGamesAttribute()
    {
        return $this->games()->get();
    }

    public function games()
    {
        return Game::where('winner_id', $this->id)->orWhere('looser_id', $this->id)->orderBy('id', 'desc')->take(5);
    }

}