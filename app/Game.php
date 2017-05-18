<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = ['winner', 'looser', 'extra'];
    protected $casts = [
        'extra' => 'array'
    ];

}