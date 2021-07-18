<?php

namespace Andiwijaya\WebApp\Services;


use Andiwijaya\WebApp\Models\WebCache;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Jenssegers\Agent\Agent;
use Symfony\Component\Process\Process;

class WebCacheService{

  protected $path;

  protected $excluded;

  protected $tags = [];

  protected $agent;

  public function __construct()
  {
    $this->agent = new Agent();

    $this->excluded = false;
  }

  public function getKey(Request $request){

    $device = $this->agent->isMobile() ? 'm' : ($this->agent->isTablet() ? 't' : 'd');

    $fullUrl = $request->fullUrl();

    // If full url has query string, do some manipulation, otherwise just use fullUrl()
    // due to:
    // - Issue for web url that append query parameter to not getting cached.
    //   (kliknss.co.id/beat key become kliknss.co.id/beat?id=7)
    if(strpos($fullUrl, '?') !== false){

      $query = $request->query();
      unset($query['webcache-reload']);
      $query = http_build_query($query);

      $fullUrl = $request->url();
      if(strlen($query) > 0){

        if($request->path() == '/' && substr($fullUrl, strlen($fullUrl) - 1, 1) != '/')
          $fullUrl .= '/';

        $fullUrl .= '?' . $query;

      }
    }

    return implode(' ', [
      $request->method(),
      $fullUrl,
      $request->wantsJson() ? 'json' : ($request->ajax() ? 'x' : 'n'),
      $device
    ]);

  }

  public function tag($tag){

    $this->tags[] = $tag;

  }

  public function store(Request $request, $response){

    if($request->method() !== 'GET') return;

    if($this->excluded) return;

    array_unshift($this->tags, 'path:' . $request->path());

    if(($response instanceof Response || $response instanceof JsonResponse) &&
      !$request->has('_')){

      $key = $this->getKey($request);

      if(strlen($key) <= 1000){

        Cache::forever($key, [
          'headers'=>[
            'Content-Type'=>$response->headers->get('content-type'),
            'X-Cache-Key'=>$key
          ],
          'content'=>$response->content()
        ]);

        if(Schema::hasTable('web_cache')){

          WebCache::updateOrCreate(
            [ 'key'=>$key ],
            [
              'tag'=>implode(' ', $this->tags)
            ]
          );

        }
        else{

          Log::warning("Table 'web_cache' doesn't exists, clear by key featured is not available.");

        }

      }

    }
    else{

      //Log::warning("Unable to create cache from response type of " . get_class($response));

    }

  }

  public function setExcluded($excluded){

    $this->excluded = $excluded;

  }


  public function clearAll($recache = false)
  {
    Artisan::call('cache:clear');

    if(Schema::hasTable('web_cache')){

      if($recache)
      {
        WebCache::select('id')
          ->orderBy('id')
          ->chunk(1000, function($caches){

            foreach($caches as $cache)
            {
              $process = new Process("php artisan web-cache:load --id={$cache->id} > /dev/null 2>&1 &", base_path());
              $process->setTimeout(10);
              $process->run();
            }

          });
      }

    }
  }

  public function clearByTag($tag, $recache = false){

    $count = 0;

    if(Schema::hasTable('web_cache')){

      WebCache::search($tag)
        ->chunkById(1000, function($caches) use(&$count, $recache){

          foreach($caches as $cache){

            Cache::forget($cache->key);

            if($recache)
            {
              $process = new Process("php artisan web-cache:load --id={$cache->id} > /dev/null 2>&1 &", base_path());
              $process->setTimeout(10);
              $process->run();
            }

            $count++;

          }

        });

    }
  }

  public function clearByKey($key, $recache = false){

    $count = 0;

    if(Schema::hasTable('web_cache')){

      WebCache::where('key', $key)
        ->chunkById(1000, function($caches) use(&$count, $recache){

          foreach($caches as $cache){

            Cache::forget($item->key);

            if($recache)
            {
              $process = new Process("php artisan web-cache:load --id={$cache->id} > /dev/null 2>&1 &", base_path());
              $process->setTimeout(10);
              $process->run();
            }

            $count++;

          }

        });

    }
  }

}