<?php

namespace Andiwijaya\WebApp\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class FormPostMiddleware
{
  public function handle(Request $request, \Closure $next)
  {
    if($request->method() == 'POST'){

      $merged = [];
      $unsets = [];
      foreach($request->keys() as $key){
        if(strpos($key, ':') !== false){

          $newKey = explode(':', $key)[0];
          $newValue = $this->parse($request->input($key), explode(':', $key)[1] ?? 'string');
          $merged[$newKey] = $newValue;
          $unsets[] = $key;
        }
        else if(is_array(($arr = $request->input($key)))){

          $newValue = $this->recurseArr($arr);
          $merged[$key] = $newValue;
        }
      }

      $this->extractFiles($request, $request->files->all(), $merged);

      $request->merge($merged);
      foreach($unsets as $key)
        $request->offsetUnset($key);
    }

    return $next($request);
  }

  protected function recurseArr($arr)
  {
    $unsets = [];
    foreach($arr as $key=>$value){
      if(strpos($key, ':') !== false){
        $newKey = explode(':', $key)[0];
        $newValue = $this->parse($value, explode(':', $key)[1] ?? 'string');
        $arr[$newKey] = $newValue;
        $unsets[] = $key;
      }
      else if(is_array($value)){
        $arr[$key] = $this->recurseArr($value);
      }
    }

    foreach($unsets as $key)
      unset($arr[$key]);

    return $arr;
  }

  protected function parse($value, $datatype){

    switch($datatype){

      case 'number':
        $value = floatval(str_replace([ ',', ' ' ], '', $value));
        break;
      case 'date':
        $value = date('Y-m-d', strtotime($value));
      case 'datetime':
        $value = date('Y-m-d H:i:s', strtotime($value));
        break;
    }

    return $value;
  }

  protected function extractFiles($request, $arr, &$files, $prefix = ''){

    foreach($arr as $key=>$value){

      $actualKey = ($prefix != '' ? $prefix . '.' : '') . $key;

      if(is_array($value)){
        $this->extractFiles($request, $value, $files, $actualKey);
      }
      else{

        $attr = array_slice(explode('.', $key), -1)[0] ?? '';
        $newKey = explode(':', $key)[0];
        $params = explode(':', $key)[1] ?? '';
        $disk = explode(',', $params)[1] ?? 'images';
        $dir = explode(',', $params)[2] ?? '';

        $path = save_image($request->file($actualKey), $disk, $dir);
        Arr::set($files, explode(':', $actualKey)[0], $path);
      }
    }
  }
}