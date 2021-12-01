<?php

namespace Jawabapp\Gamify\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ReputationChanged
{
    use Dispatchable, SerializesModels;

    /**
     * @var Model
     */
    public $user;

    /**
     * @var int
     */
    public $point;

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
    public function __construct(Model $user, int $point, bool $increment)
    {
        $this->user = $user;
        $this->point = $point;
        $this->increment = $increment;
    }
}
