<?php
namespace App\Events;

use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UserLogoutEvent implements ShouldBroadcast
{

    use SerializesModels;
    /**
     *
     * @var User
     */
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function broadCastWith()
    {
        return [
            'id' => $this->user->id,
            'name' => $this->user->name
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('user-activity');
    }
}