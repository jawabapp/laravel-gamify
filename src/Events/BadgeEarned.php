<?php

namespace Jawabapp\Gamify\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Jawabapp\Gamify\Badge;

class BadgeEarned
{
    use Dispatchable, SerializesModels;

    /**
     * @var Model
     */
    public $user;

    /**
     * @var Badge
     */
    public $badge;

    /**
     * @var bool
     */
    public $increment;

    /**
     * Create a new event instance.
     *
     * @param $user
     * @param $point integer
     * @param $increment
     */
    public function __construct(Model $user, Badge $badge)
    {
        $this->user = $user;
        $this->badge = $badge;
    }
}
