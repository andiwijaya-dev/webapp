<?php

namespace Andiwijaya\WebApp\Http\Middleware;

use Andiwijaya\WebApp\Facades\WebCache;

class WebCacheMiddleware{

  public function handle($request, $next){

    $response = $next($request);

    if(config('webcache.enabled', 1) &&
      (count(config('webcache.hosts', [])) <= 0 || in_array($request->getHttpHost(), config('webcache.hosts', []))) &&
      $request->method() == 'GET' &&
      isset(($route = $request->route())->action['middleware']) && is_array($route->action['middleware']) &&
      !in_array('web-cache-excluded', $route->action['middleware'])){

      WebCache::store($request, $response);
    }

    return $response;
  }

}