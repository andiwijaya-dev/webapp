<?php

namespace Andiwijaya\WebApp\Http\Middleware;

use Andiwijaya\WebApp\Facades\Auth;
use Andiwijaya\WebApp\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class AuthMiddleware{

  public function handle(Request $request, Closure $next){
    
    if(!in_array($request->path(), [ 'login' ])){

      Auth::load();

      if(!Auth::user() || Auth::user()->status < User::STATUS_ACTIVE)
      {
        Auth::logout();
        Session::put('after_login', $request->path());
        return $request->ajax() ? htmlresponse()->redirect('/login') : redirect('/login');
      }

      if(!in_array($request->path(), [ 'login', 'logout' ])){

        if(!Auth::user()->pathAllowed($request->path()))
          return $request->ajax() ? htmlresponse()->redirect($request->header('referer', '/')) :
            redirect($request->header('referer', '/'));

        Auth::user()->last_url = str_replace('//', '/', '/' . $request->path());
        Auth::user()->save();
      }

      View::share('user', Auth::user());
    }

    return $next($request);
  }
}