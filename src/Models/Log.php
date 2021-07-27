<?php

namespace Andiwijaya\WebApp\Models;

use Andiwijaya\WebApp\Models\Traits\FilterableTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class Log extends Model{

  use FilterableTrait;

  protected $table = 'log';

  protected $fillable = [
    'loggable_type', 'loggable_id', 'type', 'data', 'user_agent', 'remote_ip', 'user_id', 'session_id'
  ];

  protected $casts = [
    'data'=>'array'
  ];

  const TYPE_REMOVE = -1;
  const TYPE_CREATE = 1;
  const TYPE_UPDATE = 2;
  const TYPE_IMPORT = 3;
  const TYPE_EXPORT = 4;
  const TYPE_REORDER = 5;


  public function __construct(array $attributes = []){

    if(!app()->runningInConsole()){
      if(!isset($attributes['user_agent'])) $attributes['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
      if(!isset($attributes['remote_ip'])) $attributes['remote_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
      if(!isset($attributes['session_id'])) $attributes['session_id'] = Session::getId();
    }

    parent::__construct($attributes);

  }

  public function loggable(){

    return $this->morphTo();

  }

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }


  public function getTypeTextAttribute(){

    if(method_exists($this->loggable, 'getLogTypeText'))
      return $this->loggable::getLogTypeText($this);

    return __('models.log-type-' . $this->type);

  }

}