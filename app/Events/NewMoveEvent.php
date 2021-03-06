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

class NewMoveEvent implements ShouldBroadcast
{

    use Dispatchable,
        InteractsWithSockets,
        SerializesModels;
    /**
     * @var User
     */
    private $to_user;

    /**
     * @var Array
     */
    public $move;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $to_user, $move)
    {
        $this->to_user = $to_user;
        $this->move = $move;
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
}