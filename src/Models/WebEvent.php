<?php

namespace Andiwijaya\WebApp\Models;

use Illuminate\Database\Eloquent\Model;

class WebEvent extends Model
{
  protected $table = 'web_event';

  protected $fillable = [ 'session_id', 'user_id', 'remote_addr', 'user_agent', 'event',
    'value1', 'value2', 'value3', 'value4', 'value5' ];
}
