<?php

namespace Andiwijaya\WebApp\Models\Traits;

trait SearchableTrait{

  /*protected $searchable = [ 'tag' ];*/

  /*protected $lookup_rules = [
    'city_id'=>'id',
    'city_code'=>'code',
    'city_name'=>'name',
    'city'=>'code,name',
  ];*/

  /**
   * Replaces spaces with full text search wildcards
   *
   * @param string $term
   * @return string
   */
  protected function fullTextWildcards($term)
  {
    // removing symbols used by MySQL
    $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~'];
    $term = str_replace($reservedSymbols, ' ', $term);

    $words = explode(' ', $term);

    foreach($words as $key => $word){
      /*
       * applying + operator (required word) only big words
       * because smaller ones are not indexed by mysql
       */
      if(strlen($word) >= 3) {
        $words[$key] = '+' . $word . '*';
      }
    }

    $searchTerm = implode( ' ', $words);

    return $searchTerm;
  }

  /**
   * Scope a query that matches a full text search of term.
   *
   * @param \Illuminate\Database\Eloquent\Builder $query
   * @param string $term
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeSearch($query, $term, $callback = null)
  {
    if(isset($this->searchable) && is_array($this->searchable) && count($this->searchable) > 0){

      $columns = implode(',',$this->searchable);

      $filters = [];
      $term = $this->extractFilterFromTerm($term, $filters);

      if(strlen($term) > 0)
        $query->whereRaw("MATCH ({$columns}) AGAINST (? IN BOOLEAN MODE)", $this->fullTextWildcards($term));

      if(count($filters) > 0 && is_callable($callback))
        call_user_func_array($callback, [ $filters ]);

      return $query;

    }
  }



  function scopeLookup($model, array $obj){

    if(isset($this->lookup_rules) && is_array($this->lookup_rules)){

      $has_lookup = false;

      foreach($this->lookup_rules as $key=>$rules){

        if(!isset($obj[$key])) continue;

        if(strpos($rules, '+') !== false){

          $rules = explode('+', $rules);

          foreach($rules as $idx=>$rule)
            $rules[$idx] = "COALESCE(`{$rule}`, '')";

          $model->orWhereRaw("CONCAT(`" . implode("`, ' ', `", $rules) . "`) = '$obj[$key]'");

        }
        else{

          $rules = explode(',', $rules);

          $model->orWhere(function($query) use($key, $rules, $obj){
            foreach($rules as $rule)
              $query->orWhere($rule, 'like', isset($obj[$key]) ? $obj[$key] : '');
          });

        }

        $has_lookup = true;

      }

      if(!$has_lookup)
        $model = $model->whereRaw('1 = 0');

    }

  }

  function extractFilterFromTerm($term, &$filters){

    preg_match_all('/(\w+)([\:\>\<\~]+)((\".*?(?=\")\")|(\'.*?(?=\')\')|(\w+))/', $term, $matches);

    if(isset($matches[0][0])){
      foreach($matches[0] as $idx=>$text){

        $key = $matches[1][$idx];
        $operator = $matches[2][$idx];
        $value = str_replace([ '"', "'" ], '', $matches[3][$idx]);

        $filters[$key] = [ 'key'=>$key, 'operator'=>$operator, 'value'=>$value ];

        $term = str_replace($text, '', $term);
      }
    }

    $term = trim($term);

    return $term;
  }

}