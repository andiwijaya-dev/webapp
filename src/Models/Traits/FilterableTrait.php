<?php

namespace Andiwijaya\WebApp\Models\Traits;


use App\Models\Session;
use Carbon\Carbon;

trait FilterableTrait{

  /*protected $filter_searchable = [
    'id:=',
    'name:like'
  ];*/

  public function scopeFilter($model, array $params, $callback = null){

    // Handle search parameter
    if(isset($params['search']) && $params['search']){

      if(isset($this->filter_searchable) && is_array($this->filter_searchable)) {

        $model->where(function ($query) use ($params) {

          foreach ($this->filter_searchable as $expr) {

            list($key, $operator) = explode(':', $expr);

            switch ($operator) {

              case '=':
                $query->orWhere($key, '=', "{$params['search']}");
                break;

              case 'like':
                $query->orWhere($key, 'like', "%{$params['search']}%");
                break;

            }

          }

        });

      }

    }

    // Handle filter parameter
    if(isset($params['filters']) && is_array($params['filters'])){

      foreach($params['filters'] as $filter){

        $model->where(function($query) use($filter){

          $name = $filter['name'];

          foreach($filter['values'] as $idx=>$item){

            if(!isset($item['operand'])) $item['operand'] = 'and';

            switch($item['operator']){

              case '=':
                $item['operand'] == 'or' ? $query->orWhere($name, '=', $item['value']) :
                  $query->where($name, '=', $item['value']);
                break;

              case 'contains':
                if(is_array($item['value']) && count($item['value']) > 0){
                  $item['operand'] == 'or' ? $query->orWhereIn($name, $item['value']) :
                    $query->whereIn($name, $item['value']);
                }
                break;

              case 'begins_with':
                $item['operand'] == 'or' ? $query->orWhere($name, 'like', "{$item['value']}%") :
                  $query->where($name, 'like', "{$item['value']}%");
                break;

              case 'ends_with':
                $item['operand'] == 'or' ? $query->orWhere($name, 'like', "%{$item['value']}") :
                  $query->where($name, 'like', "%{$item['value']}");
                break;


            }

          }

        });

      }

    }

    // Handle generic filter parameter
    foreach($params as $key=>$value){

      if(is_null($value)) continue;
      if(in_array($key, [ 'columns', 'filters', 'search' ])) continue;
      if(!in_array($key, array_merge($this->getFillable(), $this->getHidden(), $this->getGuarded(), [ 'created_at', 'updated_at' ]))){

        if(strpos($key, '_created_at') !== false) $key = str_replace('_created_at', '.created_at', $key);
        else if(strpos($key, '_updated_at') !== false) $key = str_replace('_updated_at', '.updated_at', $key);
        else continue;
      }

      if(isset($value['date_range'])){

        switch($value['date_range']){

          case 'today': $model->whereRaw("DATE($key) = ?", [ Carbon::now()->format('Y-m-d') ]); break;
          case 'yesterday': $model->whereRaw("DATE($key) = ?", [ Carbon::now()->addDays(-1)->format('Y-m-d') ]); break;
          case 'tomorrow': $model->whereRaw("DATE($key) = ?", [ Carbon::now()->addDays(1)->format('Y-m-d') ]); break;

          case 'this-week':
            $model->whereRaw("DATE($key) BETWEEN ? AND ?", [
              Carbon::now()->startOfWeek()->format('Y-m-d'),
              Carbon::now()->endOfWeek()->format('Y-m-d'),
            ]);
            break;

          case 'this-month':
            $model->whereRaw("DATE({$key}) BETWEEN ? AND ?", [
              Carbon::now()->startOfMonth()->format('Y-m-d'),
              Carbon::now()->endOfMonth()->format('Y-m-d'),
            ]);
            break;

          case 'this-quarter':
            $model->whereRaw("DATE($key) BETWEEN ? AND ?", [
              Carbon::now()->startOfQuarter()->format('Y-m-d'),
              Carbon::now()->endOfQuarter()->format('Y-m-d'),
            ]);
            break;

          case 'this-year':
            $model->whereRaw("DATE($key) BETWEEN ? AND ?", [
              Carbon::now()->startOfYear()->format('Y-m-d'),
              Carbon::now()->endOfYear()->format('Y-m-d'),
            ]);
            break;

          case 'this-decade':
            $model->whereRaw("DATE($key) BETWEEN ? AND ?", [
              Carbon::now()->startOfDecade()->format('Y-m-d'),
              Carbon::now()->endOfDecade()->format('Y-m-d'),
            ]);
            break;

          case 'custom':
            $custom_from = date('Y-m-d', strtotime($value['date_range_from']));
            $custom_to = date('Y-m-d', strtotime($value['date_range_to']));

            $model->whereRaw("DATE($key) BETWEEN ? AND ?", [
              $custom_from,
              $custom_to,
            ]);
            break;

        }

      }
      elseif(isset($value['number_range'])){

        $number_from = str_replace(',', '', $value['number_range_from']);
        $number_to = str_replace(',', '', $value['number_range_to']);

        switch($value['number_range']){

          case '=':
          case '>=':
          case '>':
          case '<=':
          case '<':
          case '<>':
            $model->where($key, $value['number_range'], $number_from);
            break;

          case 'between':
            $model->whereBetween($key, [ $number_from, $number_to ]);
            break;

        }

      }
      if(isset($value['operator']) && strlen($value['operator']) > 0 && isset($value['value']))
        $model->where($key, $value['operator'], $value['value']);
      else if(is_array($value) && isset($value[0]))
        $model->whereIn($key, $value);
      else if(is_scalar($value))
        $model->where($key, '=', $value);

    }

    if(method_exists($this, 'customFilter'))
      $this->customFilter($model, $params);

    if(is_callable($callback))
      $callback($model);

    return $model;

  }

}