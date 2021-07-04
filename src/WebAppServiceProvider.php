<?php

namespace Andiwijaya\WebApp;


use Andiwijaya\WebApp\Console\Commands\WebCacheClear;
use Andiwijaya\WebApp\Console\Commands\WebCacheLoad;
use Andiwijaya\WebApp\Facades\WebCache;
use Andiwijaya\WebApp\Http\Middleware\WebCacheExcludedMiddleware;
use Andiwijaya\WebApp\Http\Middleware\WebCacheMiddleware;
use Andiwijaya\WebApp\Services\WebCacheService;
use Andiwijaya\WebApp\Console\Commands\Ping;
use Andiwijaya\WebApp\Console\Commands\ScheduledTaskRun;
use Andiwijaya\WebApp\Console\Commands\TestEmail;
use Andiwijaya\WebApp\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Maatwebsite\Excel\Sheet;

class WebAppServiceProvider extends ServiceProvider
{
  public function register()
  {
    $this->commands([
      TestEmail::class,
      Ping::class,
      ScheduledTaskRun::class,
      WebCacheClear::class,
      WebCacheLoad::class
    ]);

    $this->app->singleton(
      ExceptionHandler::class,
      Handler::class
    );

    $this->app->singleton('WebCache', function () {
      return new WebCacheService();
    });
  }

  public function provides()
  {
    return [ 'WebCache' ];
  }

  public function boot(Request $request){

    $this->loadViewsFrom(__DIR__ . '/views', 'andiwijaya');

    $this->app['router']->aliasMiddleware('web-cache-excluded', WebCacheExcludedMiddleware::class);
    $this->app['router']->pushMiddlewareToGroup('web', WebCacheMiddleware::class);

    $this->publishes(
      [
        __DIR__.'/database/' => database_path(),
        __DIR__.'/public/' => public_path(),
      ]
    );

    $this->handle($request);

    Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
      $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
    });
  }

  public function handle(Request $request){
    
    if(config('webcache.enabled', 1) &&
      (count(config('webcache.hosts', [])) <= 0 || in_array($request->getHttpHost(), config('webcache.hosts', []))) &&
      !$this->app->runningInConsole() &&
      $request->method() == 'GET' &&
      !$request->has('web-cache-reload') &&
      !env('APP_DEBUG')){
      
      if(Cache::has(WebCache::getKey($request))){

        global $kernel;

        $params = Cache::get(WebCache::getKey($this->app->request));
        $response = Response::create($params['content'], 200, $params['headers'] ?? []);

        $response->send();

        $kernel->terminate($request, $response);

        exit();
      }
    }
  }

}