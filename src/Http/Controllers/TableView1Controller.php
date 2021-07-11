<?php

namespace Andiwijaya\WebApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class TableView1Controller extends ActionableController
{
  protected $extends;
  protected $title;
  protected $model;
  protected $id;
  protected $columns = [
    [ 'text'=>'', 'name'=>'options', 'width'=>50 ],
    [ 'text'=>'Name', 'name'=>'name', 'width'=>260, 'filterable'=>[ 'type'=>'string' ], 'sortable'=>true ],
    [ 'text'=>'Price', 'name'=>'price', 'width'=>100, 'datatype'=>'number', 'align'=>'left', 'sortable'=>true ],
    [ 'text'=>'Date Created', 'name'=>'created_at', 'width'=>120, 'datatype'=>'datetime', 'class'=>"font-size-2", 'sortable'=>true ],
  ];

  protected $filters = [
    [ 'name'=>'name', 'type'=>'string' ],
    [ 'name'=>'price', 'type'=>'number' ],
    [ 'name'=>'created_at', 'type'=>'date' ],
  ];

  protected $searchable = true;

  protected $view = 'andiwijaya::tableview1';
  protected $items_per_page = 12;

  public function view(Request $request)
  {
    View::share([
      'extends'=>$this->extends,
      'title'=>$this->title,
      'id'=>$this->id,
      'column_html'=>$this->renderHeader()
    ]);
    
    return view_content($this->view);
  }

  public function load(Request $request)
  {
    list($data, $page, $next, $builder) = $this->loadData($request);

    $html = [];
    foreach($data as $obj){
      
      $html[] = $this->renderItem($obj);
    }
    $html = implode('', $html);

    $response = htmlresponse();

    if($page <= 1)
      $response->value("#{$this->id}", $html, [ 'next_page'=>$next ])
        ->html("#{$this->id} .table-foot", $this->renderFooter($builder));
    else
      $response->append("#{$this->id}", $html, [ 'next_page'=>$next ]);

    return $response;
  }

  protected function getBuilder(Request $request)
  {
    return $this->model::whereRaw('1=1');
  }

  protected function loadData(Request $request)
  {
    $page = explode('|', $request->input('action'))[1] ?? 1;

    $sorts = $request->input('sorts', []);

    $filters = $request->input('filters', []);
    foreach($filters as $idx=>$filter)
      if(is_string($filter))
        $filters[$idx] = json_decode($filter, 1);

    $builder = $this->getBuilder($request);

    $this->applyFilters($builder, $filters);

    if($request->has('search') && $this->searchable)
      $builder->search($request->input('search'));

    foreach($sorts as $sort){
      list($sort_name, $sort_type) = explode('|', $sort);
      $builder->orderBy($sort_name, $sort_type);
    }

    $offset = ($page - 1) * $this->items_per_page;
    $data = $builder->limit($this->items_per_page + 1)->offset($offset)->get();
    $next = count($data) > $this->items_per_page ? $page + 1 : -1;
    $data = $data->splice(0, $this->items_per_page);

    return [ $data, $page, $next, $builder ];
  }

  protected function applyFilters($builder, array $filters)
  {
    foreach($filters as $filter){
      $name = $filter['name'];
      $filters = $filter['filters'];

      $base_operand = null;
      $builder->where(function($query) use($name, $filters, &$base_operand){
        foreach($filters as $exp){
          list($operand, $operator, $value) = explode('|', $exp);

          if(!$base_operand) $base_operand = $operand;

          switch($operator){

            case '=':
            case '<':
            case '<=':
            case '>':
            case '>=':
              if($base_operand == 'or')
                $query->orWhere($name, $operator, $value);
              else
                $query->where($name, $operator, $value);
              break;

            case 'contains':
              if($base_operand == 'or')
                $query->orWhere($name, 'like', "%{$value}%");
              else
                $query->where($name, 'like', "%{$value}%");
              break;

            case 'starts-with':
              if($base_operand == 'or')
                $query->orWhere($name, 'like', "{$value}%");
              else
                $query->where($name, 'like', "{$value}%");
              break;

            case 'ends-with':
              if($base_operand == 'or')
                $query->orWhere($name, 'like', "%{$value}");
              else
                $query->where($name, 'like', "%{$value}");
              break;

          }
        }
      });
    }
  }

  protected function sort(Request $request)
  {
    $sorts = $request->input('sorts', []);

    $name = explode('|', $request->input('action'))[1] ?? null;

    if(count($sorts) == 0)
      $sorts = [ $name . '|asc' ];
    else{

      $exists_and_inverted = false;
      foreach($sorts as $idx=>$sort){
        list($sort_name, $sort_type) = explode('|', $sort);
        if($sort_name == $name){
          $sort_type = $sort_type == 'desc' ? 'asc' : 'desc';
          $sorts[$idx] = $name . '|' . $sort_type;
          $exists_and_inverted = true;
        }
      }

      if(!$exists_and_inverted)
        $sorts = [ $name . '|asc' ];
    }

    $request->merge([
      'sorts'=>$sorts,
      'action'=>'load'
    ]);

    $response = $this->load($request);
    $response->remove("input[name='sorts[]']");
    foreach($sorts as $sort)
      $response->append("th[name='$name']", "<input type='hidden' name='sorts[]' value=\"{$sort}\" />");
    return $response;
  }

  protected function renderHeader()
  {
    $html = [];
    $columns = $this->columns;
    foreach($columns as $column){

      $name = $column['name'] ?? '';
      $width = $column['width'] ?? 100;
      $text = $column['text'] ?? ($column['name'] ?? '');
      $datatype = $column['datatype'] ?? 'text';
      $align = $column['align'] ?? '';
      $sortable = $column['sortable'] ?? false;

      switch($datatype){

        case 'bool':
        case 'boolean':
          if(!$align) $align = 'align-center';
          break;

        case 'number':
          if(!$align) $align = 'align-right';
          break;
      }

      $html[] = "<th class='{$align}' width=\"{$width}px\" name=\"{$name}\">";
      if($sortable)
        $html[] = "<button name='action' value=\"sort|{$name}\">{$text}</button>";
      else
        $html[] = $text;
      $html[] = "<div class=\"table-resize\"></div>";
      $html[] = "</th>";
    }

    return implode('', $html);
  }
  
  protected function renderItem($obj){

    $id = $obj['id'] ?? '';
    $tag = "<tr data-id=\"{$id}\">";
    foreach($this->columns as $column){

      $name = $column['name'] ?? '';
      $text = $obj[$name] ?? '';
      $datatype = $column['datatype'] ?? 'text';
      $align = $column['align'] ?? '';
      $class = $column['class'] ?? '';

      switch($datatype){

        case 'bool':
        case 'boolean':
          if(!$align) $align = 'align-center';
          break;

        case 'number':
          $text = number_format(doubleval($text));
          if(!$align) $align = 'align-right';
          break;

        case 'date':
          $text = date('j M Y', strtotime($text));
          break;

        case 'datetime':
          $text = date('j M Y H:i', strtotime($text));
          break;
      }

      $tag .= "<td class='{$align}'>";
      switch($datatype){

        case 'bool':
        case 'boolean':
          if($text)
            $tag .= "<label class='ellipsis'><span class='fa fa-check-circle cl-green'></span></label>";
          else
            $tag .= "<label class='ellipsis'><span class='fa fa-minus-circle cl-gray-500'></span></label>";
          break;

        default:
          if(method_exists($this, ($method = 'column' . ucwords(Str::camel($name))))){
            $tag .= $this->$method($obj, $column);
          }
          else{
            $tag .= "<label class=\"ellipsis {$class}\">{$text}</label>";
          }
      }
      $tag .= "</td>";
    }
    $tag .= "</tr>";
    
    return $tag;
  }

  protected function renderFooter($builder)
  {
    return '';
  }

  protected function openFilters(Request $request)
  {
    $id = explode('|', $request->input('action'))[1] ?? null;

    Session::put('tableview1', $request->all());

    if($id){
      $filters = $request->input('filters', []);
      foreach($filters as $idx=>$filter)
        if(is_string($filter))
          $filters[$idx] = json_decode($filter, true);

      $value = collect($filters)->where('id', $id)->first();

      if($value){
        foreach($value['filters'] as $idx=>$exp){

          list($operand, $operator, $val) = explode('|', $exp);

          $value['filters'][$idx] = [
            'operand'=>$operand,
            'operator'=>$operator,
            'value'=>$val
          ];
        }
      }
    }

    return htmlresponse()
      ->modal(
        'tableview1-filter',
        view('andiwijaya::sections.tableview1-filter', compact('value'))->render(),
        [
          'width'=>600
        ]
      );
  }

  protected function addFilter(Request $request)
  {
    $id = $request->input('id');
    $name = $request->input('name');
    $params = $request->input('params', []);
    $type = collect($this->filters)->where('name', $name)->first()['type'] ?? 'string';

    $filters = [];
    for($i = 0 ; $i < count($params) / 3 ; $i++){

      $operand = $params[$i * 3]['operand'];
      $operator = $params[($i * 3) + 1]['operator'];
      $value = $params[($i * 3) + 2]['value'] ?? '';

      if($type == 'date') $value = date('Y-m-d', strtotime($value));

      if($value !== ''){
        $filters[] = $operand . '|' . $operator . '|' . $value;
      }
    }

    if(count($filters) <= 0)
      exc(__('models.tableview1-no-filter-value'));

    $filter = [
      'id'=>$id ?? uniqid(),
      'name'=>$name,
      'text'=>collect($this->filters)->where('name', $name)->first()['text'] ?? $name,
      'filters'=>$filters
    ];

    $current = Session::get('tableview1');
    unset($current['action']);

    if($id){
      foreach($current['filters'] as $idx=>$item){

        if(is_string($item)){
          $item = json_decode($item, true);
          $current['filters'][$idx] = $item;
        }

        if($item['id'] == $id)
          $current['filters'][$idx] = $filter;
      }
    }
    else
      $current['filters'][] = $filter;

    $request->offsetUnset('params');
    $request->offsetUnset('name');
    $request->offsetUnset('id');
    $request->offsetUnset('action');
    $request->merge($current);
    $response = $this->load($request);

    if($id){
      $response->replace('#filter-item-' . $id, view('andiwijaya::components.tableview1-filter-item', compact('filter'))->render());
    }
    else
      $response->append('.filter-area', view('andiwijaya::components.tableview1-filter-item', compact('filter'))->render());

    $response->script("ui('#tableview1-filter').modal_close()");

    return $response;
    // { name: "or|contains|123", "|between|2012-04-01,2021-04-22" }}

  }

  public function __construct()
  {
    if(!$this->id) $this->id = 'tableview1';

    parent::__construct();
  }
}