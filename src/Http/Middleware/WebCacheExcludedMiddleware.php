<?php

namespace Andiwijaya\WebApp\Http\Middleware;

class WebCacheExcludedMiddleware{

  public function handle($request, $next){

    return $next($request);

  }

}