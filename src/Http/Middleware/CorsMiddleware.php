<?php

namespace Andiwijaya\WebApp\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Response;

class CorsMiddleware{

  public function handle($request, Closure $next)
  {
    $headers = [
      'Access-Control-Allow-Origin' => '*',
      'Access-Control-Allow-Methods'=> 'POST, GET, OPTIONS, PUT, DELETE',
      'Access-Control-Allow-Headers'=> 'Content-Type, X-Auth-Token, Origin'
    ];

    if($request->getMethod() == "OPTIONS") {
      return Response::make('OK', 200, $headers);
    }

    $response = $next($request);

    foreach($headers as $key => $value)
      $response->header($key, $value);

    return $response;
  }

}