<?php

namespace Andiwijaya\WebApp\Exceptions;

use Andiwijaya\WebApp\Models\ScheduledTask;
use Andiwijaya\WebApp\Models\SysLog;
use Andiwijaya\WebApp\Notifications\SlackNotification;
use App\Notifications\LogToSlackNotification;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
      UserException::class,
      NotFoundHttpException::class,
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function report(Exception $exception)
    {
      $message = substr($exception->getMessage(), 0, 255);
      $traces = $exception->getTraceAsString();

      SysLog::create([
        'type'=>SysLog::TYPE_ERROR,
        'message'=>strlen($message) <= 0 ? get_class($exception) : $message,
        'data'=>[
          'console'=>app()->runningInConsole(),
          'session_id'=>Session::getId(),
          'method'=>Request::method(),
          'url'=>Request::fullUrl(),
          'data'=>Request::input(null),
          'traces'=>$traces,
          'session'=>Session::get(null),
          'user_agent'=>$_SERVER['HTTP_USER_AGENT'] ?? '',
          'remote_ip'=>$_SERVER['REMOTE_ADDR'] ?? '',
          'cookies'=>$_COOKIE ?? '',
        ]
      ]);

      if(strlen(env('LOG_SLACK_WEBHOOK_URL')) > 0){
        try{
          ScheduledTask::runOnce(function() use($message, $traces){
            try{
              Notification::route('slack', env('LOG_SLACK_WEBHOOK_URL'))
                ->notify(new SlackNotification($message, 'error', $traces));
            }
            catch(\Exception $ex){
              SysLog::create([
                'type'=>SysLog::TYPE_ERROR,
                'message'=>substr($ex->getMessage(), 0, 255),
                'data'=>[
                  'traces'=>$ex->getTraceAsString(),
                ]
              ]);
            }
          });
        }
        catch(\Exception $ex){
          SysLog::create([
            'type'=>SysLog::TYPE_ERROR,
            'message'=>substr($ex->getMessage(), 0, 255),
            'data'=>[
              'traces'=>$ex->getTraceAsString(),
            ]
          ]);
        }
      }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
      if($request->ajax()){

        if ($exception instanceof TokenMismatchException)
          return htmlresponse()->redirect($request->fullUrl());

        else if($exception->getMessage() == 'Login required')
          return response()->json([ 'script'=>"window.location = '/login';" ]);

        else if($exception instanceof MaintenanceModeException)
          return response()->json([ 'error'=>0, 'message'=>'Maintenance mode' ]);

        else{

          $traces = [];
          if(env('APP_DEBUG')){
            foreach($exception->getTrace() as $idx=>$trace){
              if(isset($trace['file']) && isset($trace['class'])){
                //if($idx > 0 && strpos($trace['file'], 'Illuminate') !== false) continue;

                $traces[] = implode(' - ', [
                  isset($trace['file']) && isset($trace['line']) ? basename($trace['file']) . ":" . $trace['line'] : '',
                  isset($trace['class']) && isset($trace['function']) && isset($trace['line']) ? $trace['class'] . "@" . $trace['function'] . ":" . $trace['line'] : '',
                  isset($trace['args']) ? json_encode($trace['args']) : ''
                ]);
              }
            }
          }

          if($exception instanceof PostTooLargeException)
            return response()->json([ 'error'=>1, 'title'=>"Post data too large, maximum upload is " . ini_get('post_max_size') ]);
          else{

            if(basename($exception->getFile()) == 'FilesystemManager.php')
              return response()->json([ 'error'=>1, 'title'=>__('errors.storage-not-found'), 'description'=>implode("\n", $traces) ]);
            else
              return response()->json([ 'error'=>1, 'title'=>$exception->getMessage(), 'description'=>implode("\n", $traces) ]);
          }

        }
      }
      else{
        if ($exception instanceof TokenMismatchException)
          return redirect($request->fullUrl())->with('warning', 'Session expired, please reload the page.');
      }

      return parent::render($request, $exception);
    }
}
