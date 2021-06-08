<?php

namespace Andiwijaya\WebApp\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledTaskResult extends Model{

  protected $table = 'scheduled_task_result';

  protected $fillable = [ 'task_id', 'status', 'verbose', 'started_at', 'completed_at',
    'ellapsed', 'pid' ];

  protected $casts = [
    'started_at'=>'datetime',
    'completed_at'=>'datetime',
  ];

  public function getStatusTextAttribute(){

    switch($this->status){

      case ScheduledTask::STATUS_DISABLED: return 'Disabled';
      case ScheduledTask::STATUS_ACTIVE: return 'Active';
      case ScheduledTask::STATUS_RUNNING: return 'Running';
      case ScheduledTask::STATUS_COMPLETED: return 'Completed';
      case ScheduledTask::STATUS_ERROR: return 'Error';
    }
  }

  public function info($text)
  {
    $this->verbose .= $text . PHP_EOL;

    $this->save();
  }

}