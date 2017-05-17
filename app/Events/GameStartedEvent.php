<?php
namespace App\Events;

use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class GameStartedEvent implements ShouldBroadcast
{

    use Dispatchable,
        InteractsWithSockets,
        SerializesModels;
    /**
     * @var User
     */
    private $to_user;

    /**
     * @var User
     */
    private $from_user;

    /**
     * @var bool
     */
    private $starts_game;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $to_user, $from_user, $starts_game = false)
    {
        $this->to_user = $to_user;
        $this->from_user = $from_user;
        $this->starts_game = $starts_game;
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
            'user' => [
                'id' => $this->from_user->id,
                'name' => $this->from_user->name
            ],
            'starts_game' => $this->starts_game
        ];
    }
}