<?php

namespace Andiwijaya\WebApp\Models;

use Andiwijaya\WebApp\Models\Traits\FilterableTrait;
use Andiwijaya\WebApp\Models\Traits\LoggedTraitV3;
use Andiwijaya\WebApp\Providers\ScheduledTaskSecurityProvider;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializableClosure;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class ScheduledTask extends Model
{
  use LoggedTraitV3, FilterableTrait;

  protected $table = 'scheduled_task';

  protected $fillable = [ 'status', 'description', 'creator_id',
    'command', 'start', 'repeat', 'repeat_custom', 'count', 'error',
    'remove_after_completed' ];

  protected $filter_searchable = [
    'id:=',
    'description:like'
  ];

  const STATUS_DISABLED = -2;
  const STATUS_ERROR = -1;
  const STATUS_ACTIVE = 1;
  const STATUS_RUNNING = 2;
  const STATUS_COMPLETED = 3;

  const REPEAT_NONE = 0;
  const REPEAT_MINUTELY = 1;
  const REPEAT_EVERY_FIVE_MINUTE = 2;
  const REPEAT_EVERY_TEN_MINUTE = 3;
  const REPEAT_HOURLY = 4;
  const REPEAT_DAILY = 5;
  const REPEAT_WEEKLY = 6;
  const REPEAT_MONTHLY = 7;
  const REPEAT_CUSTOM = 21;

  protected $attributes = [
    'repeat'=>self::REPEAT_NONE,
    'status'=>self::STATUS_ACTIVE
  ];

  protected $casts = [
    'repeat_custom'=>'array', // { every:{ n:1, unit:"day" }, max_count:10, except:{ dates:[], day:[] } }
    'updated_at'=>'datetime',
    'remove_after_completed'=>0
  ];

  public function results(){

    return $this->hasMany('Andiwijaya\WebApp\Models\ScheduledTaskResult', 'task_id');
  }

  public function preSave()
  {
    if(is_string($this->command)){

      if(in_array(($name = explode(':', explode(' ', $this->command)[0])[0]), [
        'app',
        'migrate',
        'make',
        'event',
        'config',
        'data',
        'cache',
        'list',
        'optimize',
        'down',
        'up',
        'clear-compiled',
        'dump-server',
        'db',
        'queue',
        'redis',
        'route',
        'schedule',
        'vendor'
      ]))
        exc('Unable to use this command');
    }

  }

  public function calculate()
  {
    $model = DB::table('scheduled_task_result')->where('task_id', $this->id)
      ->select(DB::raw("COUNT(*) as `count`, SUM(case when `status` = " . ScheduledTask::STATUS_ERROR .
        " then 1 else 0 end) as `error`"));
    //exc($model->toSql());
    $res = $model->first();

    $this->count = $res->count ?? 0;
    $this->error = $res->error ?? 0;
    parent::save();
  }

  public function run(){

    if(in_array($this->status, [ self::STATUS_DISABLED, self::STATUS_ERROR, self::STATUS_RUNNING ])) return;

    if($this->status == self::STATUS_RUNNING){
      foreach($this->results->where('status', ScheduledTask::STATUS_RUNNING) as $result)
        exec("kill -9 {$result->pid}");
    }

    $t1 = microtime(1);

    $this->status = self::STATUS_RUNNING;
    $this->save();

    $result = $this->results()->create([
      'status'=>self::STATUS_RUNNING,
      'started_at'=>Carbon::now()->format('Y-m-d H:i:s'),
      'pid'=>getmypid()
    ]);

    if(strpos($this->command, 'SerializableClosure') !== false){

      if (null !== $securityProvider = SerializableClosure::getSecurityProvider()) {
        SerializableClosure::removeSecurityProvider();
      }

      $command = unserialize($this->command)->getClosure();

      if ($securityProvider !== null) {
        SerializableClosure::addSecurityProvider($securityProvider);
      }

      try{
        $output = call_user_func_array($command, [ $result ]);
        $exitCode = 0;
      }
      catch(\Exception $ex){
        $exitCode = 1;
        $output = $ex->getMessage() . "@" . $ex->getFile() . ":" . $ex->getLine() . PHP_EOL;
        report($ex);
      }
    }
    else{
      $exitCode = Artisan::call($this->command);
      $output = Artisan::output();
    }

    if($exitCode == 0 && $this->remove_after_completed)
      $this->delete();
    else{
      $result->status = $exitCode > 0 ? self::STATUS_ERROR : self::STATUS_COMPLETED;
      $result->verbose .= $output;
      $result->ellapsed = microtime(1) - $t1;
      $result->completed_at = Carbon::now()->format('Y-m-d H:i:s');
      $result->save();

      $this->status = $result->status;

      $this->save();
    }
  }

  public function runInBackground($delay = 0){

    chdir(base_path());
    exec("php artisan scheduled-task:run --id={$this->id} --delay={$delay} >> /dev/null 2>&1 &");
  }

  public static function check(Command $cmd = null){

    ScheduledTask::where('status', '>=', ScheduledTask::STATUS_ACTIVE)
      ->orderBy('id')
      ->chunk(1000, function($tasks) use($cmd){

        foreach($tasks as $task){

          switch($task->repeat){

            case self::REPEAT_NONE:
              if($task->count <= 0 && ($task->start == null || ($task->start != null && Carbon::now()->format('YmdHis') >= date('YmdHis', strtotime($task->start))))){
                if($cmd) $cmd->info("Run in background {$task->id}");
                $task->runInBackground();
              }
              break;

            case self::REPEAT_MINUTELY:
              if(strtotime($task->start) < time() && (Carbon::now()->format('YmdHi') - $task->updated_at->format('YmdHi')) >= 1){
                if($cmd) $cmd->info("Run in background {$task->id} (every minute)");

                $task->runInBackground();
              }
              break;

            case self::REPEAT_HOURLY:
              if(strtotime($task->start) < time() && (Carbon::now()->format('YmdH') - $task->updated_at->format('YmdH')) >= 1){
                if($cmd) $cmd->info("Run in background {$task->id} (every hour)");

                $task->runInBackground();
              }
              break;

            case self::REPEAT_DAILY:
              if(strtotime($task->start) < time() && (Carbon::now()->format('Ymd') - $task->updated_at->format('Ymd')) >= 1){
                if($cmd) $cmd->info("Run in background {$task->id} (every day)");

                $task->runInBackground();
              }
              break;

            case self::REPEAT_MONTHLY:
              if(strtotime($task->start) < time() && (Carbon::now()->format('Ym') - $task->updated_at->format('Ym')) >= 1){
                if($cmd) $cmd->info("Run in background {$task->id} (every month)");

                $task->runInBackground();
              }
              break;

          }

        }

      });
  }

  public static function runOnceThenRemove(array $params, Command $cmd = null){

    $task = new ScheduledTask($params);

    if(!$task->description) $task->description = 'Run ' . $params['command'] . ' once.';

    $task->fill([
      'repeat'=>self::REPEAT_NONE,
      'remove_after_completed'=>1
    ]);

    $task->save();

    $task->runInBackground();

    return $task;
  }

  public static function runOnce($command, $description = '', $delay = 0){

    if($command instanceof \Closure){

      if (null !== $securityProvider = SerializableClosure::getSecurityProvider()) {
        SerializableClosure::removeSecurityProvider();
      }

      $command = serialize(new SerializableClosure($command));

      if ($securityProvider !== null) {
        SerializableClosure::addSecurityProvider($securityProvider);
      }
    }

    if(!$description)
      $description = "Scheduled task - " . Str::random(5);

    $task = new ScheduledTask([
      'command'=>$command,
      'description'=>$description,
      'repeat'=>self::REPEAT_NONE,
      'remove_after_completed'=>1
    ]);

    $task->save();

    $task->runInBackground($delay);

    return $task;
  }

  public static function runAt($command, $at, $description){

    if($command instanceof \Closure){

      if (null !== $securityProvider = SerializableClosure::getSecurityProvider()) {
        SerializableClosure::removeSecurityProvider();
      }

      $command = serialize(new SerializableClosure($command));

      if ($securityProvider !== null) {
        SerializableClosure::addSecurityProvider($securityProvider);
      }
    }

    if(!$description)
      $description = "Scheduled task - " . Str::random(5);

    $task = new ScheduledTask([
      'command'=>$command,
      'description'=>$description,
      'start'=>$at,
      'repeat'=>self::REPEAT_NONE,
      'remove_after_completed'=>1
    ]);

    $task->save();

    if(!$at) $task->runInBackground();

    return $task;
  }


  public function getRepeatTextAttribute(){

    switch($this->repeat){

      case self::REPEAT_MINUTELY: return 'Every minute';
      case self::REPEAT_HOURLY: return 'Every hour';
      case self::REPEAT_DAILY: return 'Every day';
      case self::REPEAT_MONTHLY: return 'Every month';
      default: return '';

    }
  }

  public function getStatusTextAttribute(){

    switch($this->status){

      case self::STATUS_DISABLED: return 'Disabled';
      case self::STATUS_ACTIVE: return 'Active';
      case self::STATUS_RUNNING: return 'Running';
      case self::STATUS_COMPLETED: return 'Completed';
      case self::STATUS_ERROR: return 'Error';
    }
  }

  public function getStatusHtmlAttribute(){

    $html = [ "<div class='pad-1'>" ];

    switch($this->status){

      case self::STATUS_DISABLED: $html[] = "<span class='badge gray'><span>Inactive</span></span>"; break;
      case self::STATUS_ACTIVE: $html[] = "<span class='badge green'><span>Active</span></span>"; break;
      case self::STATUS_RUNNING: $html[] = "<span class='badge yellow'><span>Running</span></span>"; break;
      case self::STATUS_COMPLETED: $html[] = "<span class='badge blue'><span>Completed</span></span>"; break;
      case self::STATUS_ERROR: $html[] = "<span class='badge red'><span>Error</span></span>"; break;
    }

    $html[] = "</div>";

    return implode('', $html);
  }

}
