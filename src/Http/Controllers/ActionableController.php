<?php

namespace Andiwijaya\WebApp\Http\Controllers;

use Andiwijaya\WebApp\Exceptions\KnownException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\ValidationRuleParser;
use function foo\func;

class ActionableController extends BaseController{

  protected $request;

  protected $request_inputs = [];

  public function index(Request $request){

    $this->preload($request);

    $this->request = $request;

    $action = isset(($actions = explode('|', $request->input('action', 'view')))[0]) ? $actions[0] : '';
    $method = action2method($action);
    if(method_exists($this, $method) && $this->validateDocComment($request, $method))
      return call_user_func_array([ $this, $method ], func_get_args());
  }

  public function store(Request $request){

    $request = $this->prepareRequest($request);
    $this->preload($request);
    $this->request = $request;

    $action = isset(($actions = explode('|', $request->input('action', 'save')))[0]) ? $actions[0] : '';
    $method = action2method($action);
    if(method_exists($this, $method) && $this->validateDocComment($request, $method))
      return call_user_func_array([ $this, $method ], [ $request ]);
  }

  public function show(Request $request, $id){

    $this->preload($request);

    $this->request = $request;

    $action = isset(($actions = explode('|', $request->input('action', 'open')))[0]) ? $actions[0] : '';
    $method = action2method($action);
    if(method_exists($this, $method) && $this->validateDocComment($request, $method))
      return call_user_func_array([ $this, $method ], func_get_args());
  }

  public function update(Request $request){

    $this->preload($request);

    $this->request = $request;

    $action = isset(($actions = explode('|', $request->input('action', 'patch')))[0]) ? $actions[0] : '';
    $method = action2method($action);
    if(method_exists($this, $method) && $this->validateDocComment($request, $method))
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

  public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])
  {
    $validator = Validator::make($request->all(), $rules, $messages, $customAttributes);

    if($validator->fails())
      throw new ValidationException($validator);
  }

  public function validateDocComment(Request $request, $method)
  {
    $comment_string = (new \ReflectionClass($this))->getMethod($method)->getDocComment();

    $obj = [];
    preg_match_all('/@(\w+) (.*)/', $comment_string, $matches);
    if(isset($matches[1][0])){
      foreach($matches[1] as $idx=>$key){
        $obj[$key] = str_replace([ '\'', '"' ], '', $matches[2][$idx]);
      }
    }

    if(isset($obj['method']) && strtolower($obj['method']) != strtolower($request->getMethod()))
      return false;

    if(isset($obj['ajax'])){
      if($obj['ajax'] && !$request->ajax()) return false;
      if(!$obj['ajax'] && $request->ajax()) return false;
    }

    return true;
  }

  public function prepareRequest(Request $request)
  {

    if(is_array($this->request_inputs)){

      foreach($this->request_inputs as $key=>$specs){
        $params = ValidationRuleParser::parse($specs);

        switch(strtolower($params[0])){

          case 'image':
            $this->prepareImage($request, $key, $params);
            break;

          case 'date':
            $request->merge([ $key=>date('Y-m-d', strtotime($request->input($key))) ]);
            break;
            
          case 'datetime':
            $request->merge([ $key=>date('Y-m-d H:i:s', strtotime($request->input($key))) ]);
            break;

          case 'number':
          case 'numeric':
            $request->merge([ $key=>str_replace([ ',', ' ' ], '', $request->input($key)) ]);
            break;

        }
      }
    }

    return $request;
  }

  public function prepareImage(Request $request, $key, $params){

    if($request->hasFile($key)){

      foreach(($params[1] ?? []) as $param){
        $param = explode('=', $param);
        switch($param[0]){
          case 'ratio': $ratio = $param[1]; break;
          case 'text': $text = $param[1]; break;
          case 'target': $target = $param[1]; break;
          case 'max_width': $max_width = $param[1]; break;
          case 'max_height': $max_height = $param[1]; break;
          case 'min_width': $min_width = $param[1]; break;
          case 'min_height': $min_height = $param[1]; break;
          case 'max_size': $max_size = $param[1]; break;
          case 'as': $as = $param[1]; break;
        }
      }

      if($as ?? false){

        $target = explode('/', $target ?? '');
        $disk = $target[0] ?? '';
        $dir = $target[1] ?? '';
        list($width, $height, $type, $attr) = getimagesize($request->file($key));

        if(isset($ratio)){
          list($ratio1, $ratio2) = explode('/', $ratio);
          if($width / $height != $ratio1 / $ratio2)
            exc(__('validation.dimensions', [ 'attribute'=>$text ?? $key ]));
        }
        if(isset($max_width)){
          if($width > $max_width)
            exc(__('validation.dimensions', [ 'attribute'=>$text ?? $key ]));
        }
        if(isset($max_height)){
          if($height > $max_height)
            exc(__('validation.dimensions', [ 'attribute'=>$text ?? $key ]));
        }
        if(isset($min_width)){
          if($width < $min_width)
            exc(__('validation.dimensions', [ 'attribute'=>$text ?? $key ]));
        }
        if(isset($min_height)){
          if($height < $min_height)
            exc(__('validation.dimensions', [ 'attribute'=>$text ?? $key ]));
        }
        if(isset($max_size)){
          $kb = filesize($request->file($key)) / 1024;
          if($kb > $max_size)
            exc(__('validation.max.file', [ 'attribute'=>$text ?? $key, 'max'=>$max_size ]));
        }
        
        $path = save_image($request->file($key), $disk, $dir);
        $request->merge([ $as=>$path ]);
      }
    }
  }
}