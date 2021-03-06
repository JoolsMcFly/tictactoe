<?php
namespace App\Events;

use App\Game;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameOverEvent implements ShouldBroadcast
{

    use Dispatchable,
        InteractsWithSockets,
        SerializesModels;
    /**
     * @var User
     */
    private $to_user;
    private $game_details;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $to_user, $game_details)
    {
        $this->to_user = $to_user;
        $this->game_details = $game_details;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('App.User.' . $this->to_user->id);
    }

    public function broadcastWith()
    {
        return [
            'user' => $this->to_user->toArray(),
            'game' => $this->game_details
        ];
    }
}