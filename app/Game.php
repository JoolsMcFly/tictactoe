<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = ['winner', 'looser', 'first_player', 'extra'];
    protected $casts = [
        'extra' => 'array'
    ];

}