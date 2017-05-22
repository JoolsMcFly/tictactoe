<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = ['winner_id', 'looser_id', 'first_player', 'extra'];
    protected $casts = [
        'extra' => 'array'
    ];

    public function winner()
    {
        return $this->hasOne(User::class, 'id', 'winner_id');
    }

    public function looser()
    {
        return $this->hasOne(User::class, 'id', 'looser_id');
    }
}