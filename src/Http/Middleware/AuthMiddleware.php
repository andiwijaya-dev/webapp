<?php

namespace Andiwijaya\WebApp\Http\Middleware;

use Andiwijaya\WebApp\Facades\Auth;
use Andiwijaya\WebApp\Models\User;
use Closure;
use Illuminate\Support\Facades\Session;

class AuthMiddleware{

  public function handle($request, Closure $next){
    
    if(!in_array($request->path(), [ 'login' ])){

      Auth::load();

      if(!Auth::user() || Auth::user()->status < User::STATUS_ACTIVE)
      {
        Auth::logout();
        Session::put('after_login', $request->path());
        return $request->ajax() ? htmlresponse()->redirect('/login') : redirect('/login');
      }
    }

    return $next($request);
  }
}