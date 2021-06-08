<?php

namespace Andiwijaya\WebApp\Events;

use Andiwijaya\WebApp\Models\ChatDiscussion;
use Andiwijaya\WebApp\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ModelEvent
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public $type;
  public $class;
  public $id;

  const TYPE_CREATE = 1;
  const TYPE_UPDATE = 2;
  const TYPE_REMOVE = -1;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct($type, $class, $id)
  {
    $this->type = $type;
    $this->class = $class;
    $this->id = $id;
  }

}
