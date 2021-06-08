<?php

namespace Andiwijaya\WebApp\Http\Controllers;

use Andiwijaya\WebApp\Exceptions\KnownException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\View;
use function foo\func;

class ActionableController extends BaseController{

  protected $request;

  public function index(Request $request){

    $this->preload($request);

    $this->request = $request;

    $action = isset(($actions = explode('|', $request->input('action', 'view')))[0]) ? $actions[0] : '';
    $method = action2method($action);
    if(method_exists($this, $method))
      return call_user_func_array([ $this, $method ], func_get_args());
  }

  public function store(Request $request){

    $this->preload($request);

    $this->request = $request;

    $action = isset(($actions = explode('|', $request->input('action', 'save')))[0]) ? $actions[0] : '';
    $method = action2method($action);
    if(method_exists($this, $method))
      return call_user_func_array([ $this, $method ], func_get_args());
  }

  public function show(Request $request, $id){

    $this->preload($request);

    $this->request = $request;

    $action = isset(($actions = explode('|', $request->input('action', 'open')))[0]) ? $actions[0] : '';
    $method = action2method($action);
    if(method_exists($this, $method))
      return call_user_func_array([ $this, $method ], func_get_args());
  }

  public function patch(Request $request){

    $this->preload($request);

    $this->request = $request;

    $action = isset(($actions = explode('|', $request->input('action', 'patch')))[0]) ? $actions[0] : '';
    $method = action2method($action);
    if(method_exists($this, $method))
      return call_user_func_array([ $this, $method ], func_get_args());
  }

  public function onlyMethods($methods){

    $arr = is_scalar($methods) ? [ $methods ] : (!is_array($methods) ? [] : $methods);

    if(!in_array($this->request->getMethod(), $arr))
      throw new KnownException(__('Action not available for this method'));

    return true;
  }

  public function __construct()
  {
    View::share(get_object_vars($this));
  }

  public function preload(Request $request){}

  public function alertRequest(Request $request){

    return htmlresponse()->alert(json_encode($request->all(), JSON_PRETTY_PRINT));
  }
}