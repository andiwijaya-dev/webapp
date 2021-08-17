<?php

namespace Andiwijaya\WebApp\Http\Controllers;

use Andiwijaya\WebApp\Exports\GenericExport;
use Andiwijaya\WebApp\Imports\GenericImport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DataImportController extends ActionableController
{
  protected $path;
  protected $title;
  protected $description = 'Masukkan file excel, csv atau zip ke dalam box dibawah ini, lalu tekan tombol lanjut.';
  protected $template_name;
  protected $files;
    
  protected $columns = [
    'name'=>[ 'text'=>'Name', 'required'=>true, 'mappings'=>[ 'Name', 'name' ], 'cast'=>'' ]
  ];

  protected $result_new;
  protected $result_updates;
  protected $result_removed;
  protected $result_logs = [];

  protected $use_importables = true;
  
  /**
   * @param Request $request
   * @return \Andiwijaya\WebApp\Responses\HTMLResponse
   * @throws \Throwable
   * @ajax true
   */
  public function view(Request $request)
  {
    return htmlresponse()
      ->modal(
        'import-dialog',
        view('andiwijaya::sections.import-dialog')->render(),
        [
          'width'=>600,
          'height'=>400
        ]
      );
  }

  /**
   * @param Request $request
   * @return \Andiwijaya\WebApp\Responses\HTMLResponse
   * @throws \Andiwijaya\WebApp\Exceptions\UserException
   * @throws \Throwable
   * @ajax true
   */
  public function analyse(Request $request)
  {
    if(!$request->hasFile('file'))
      exc(__('controllers.import-dialog-file-not-found'));

    $dir_path = Storage::disk($this->import_disk ?? 'imports')->path(Session::getId());
    exec("rm -Rf {$dir_path}");
    mkdir($dir_path);

    $file_path = null;
    $files = [];

    if(is_zip($request->file('file')->getMimeType()))
    {
      $za = new \ZipArchive();
      $za->open($request->file('file')->getRealPath());
      $za->extractTo(Storage::disk($this->import_disk ?? 'imports')->path(Session::getId()));

      $files = array_merge(
        rglob("{$dir_path}/*.xlsx"),
        rglob("{$dir_path}/*.xls"),
        rglob("{$dir_path}/*.csv")
      );

      $file_path = $files[0] ?? null;
      if(!$file_path)
        exc(__('controllers.import-dialog-no-csv-inside-zip'));

      foreach(rglob("{$dir_path}/*.*") as $path)
        $files[basename($path)] = $path;
    }
    else
    {
      if(!in_array($request->file('file')->getClientOriginalExtension(), [ 'csv', 'xls', 'xlsx' ]))
        exc(__('controllers.import-dialog-invalid-file'));

      $file_path = $dir_path . '/' . $request->file('file')->getClientOriginalName();
      file_put_contents($file_path, file_get_contents($request->file('file')->getRealPath()));
    }

    $ext = strtolower(explode('.', basename($file_path))[1] ?? '');

    if($ext == 'csv'){
      $sheets = array_map('str_getcsv', file($file_path));
      $rows = [ $sheets ];
    }
    else{
      $rows = Excel::toArray(new GenericImport, $file_path);
    }

    $columns = array_filter($rows[0][0] ?? []);

    Session::put('imports', [
      'columns'=>$columns,
      'file_path'=>$file_path,
      'files'=>$files
    ]);

    View::share([
      'columns'=>$this->columns,
      'data_columns'=>$columns,
      'path'=>$this->path
    ]);

    return htmlresponse()
      ->html('#import-dialog', view('andiwijaya::sections.import-dialog-column')->render())
      ->script("ui('#import-dialog').modal_resize()");
  }

  public function proceed(Request $request)
  {
    $validations = [];
    foreach($this->columns as $key=>$column){
      if($column['required'] ?? false)
        $validations[$key] = 'required';
    }
    $validator = Validator::make($request->only(array_keys($this->columns)), $validations);
    if($validator->fails())
      exc(implode("<br />\n", $validator->errors()->all()));

    $imports = Session::get('imports');

    $data_columns = $imports['columns'];
    $data_columns_idx = [];
    foreach($data_columns as $idx=>$column)
      $data_columns_idx[$column] = $idx;

    $tabs = Excel::toArray(new GenericImport, $imports['file_path']);

    $data = [];
    if(isset($tabs[0][0])){
      foreach($tabs as $tabidx=>$tab){

        // Look for information rows
        $start_idx = 1;
        for($i = 1 ; $i < count($tab) ; $i++){
          if(strpos($tab[$i][0] ?? '', '***') !== false){
            $start_idx = $i + 1;
            break;
          }
        }

        for($i = $start_idx ; $i < count($tab) ; $i++){

          $row = $tab[$i];

          if(!trim(implode('', $row))) continue;

          $obj = [];
          foreach($this->columns as $key=>$column){

            $required = $column['required'] ?? false;
            $map_to = $request->input($key);
            $map_to_idx = $data_columns_idx[$map_to] ?? -1;

            if($required && $map_to_idx < 0)
              exc(__('controllers.import-dialog-missing-map'));

            $value = trim($row[$map_to_idx] ?? false);

            switch($column['cast'] ?? ''){
              case 'datetime':
                $value = $this->castDatetime($value);
                break;
              case 'date':
                $value = $this->castDate($value);
                break;
              case 'bool':
                $value = $this->castBool($value);
                break;
              case 'number':
                $value = $this->castNumber($value);
                break;
              case 'upper':
                $value = strtoupper($value);
                break;
              case 'lower':
                $value = strtolower($value);
                break;
              case 'capitalize':
                $value = ucwords(strtolower($value));
                break;
            }

            //if($required || isset($row[$map_to_idx]))
              $obj[$key] = $value;
          }

          $data[] = $obj;
        }
      }
    }

    $this->files = $imports['files'];

    $response = $this->processData($request, $data, $this->files);

    View::share([
      'result_new'=>$this->result_new,
      'result_updates'=>$this->result_updates,
      'result_removed'=>$this->result_removed,
      'result_logs'=>$this->result_logs,
    ]);

    if(!$response) $response = htmlresponse();

    return $response
      ->html('#import-dialog', view('andiwijaya::sections.import-dialog-completed')->render())
      ->script("ui('#import-dialog').modal_resize()");
  }

  public function download(Request $request)
  {
    $columns = [];
    $data = [];
    $line1 = [];
    $line2 = [];
    foreach($this->columns as $column){
      $columns[] = $column['text'] ?? $column['name'];
      $line1[] = ($column['required'] ?? false) ? 'Harus diisi' : 'Opsional';
      $line2[] = $column['description'] ?? '';
    }
    $data[] = $line1;
    $data[] = $line2;
    $data[] = [ strtoupper('*** Hapus 3 barus keterangan ini sebelum melakukan import') ];

    $filename = $this->template_name ?? Str::slug($this->path) . '-' . Carbon::now()->format('Y-m-d-h-i') . '.xlsx';
    Excel::store(new GenericExport($columns, $data), $filename, 'files', \Maatwebsite\Excel\Excel::XLSX);

    return htmlresponse()
      ->download("/files/{$filename}");
  }
  
  public function storeDB($table, array $data, $use_importables = true)
  {
    $imported_at = Carbon::now()->format('Y-m-d H:i:s');

    $columns = null;
    $duplicates = null;
    $inserts = [];
    foreach($data as $obj){

      if(!$columns){
        $columns = [];
        foreach($obj as $key=>$value)
          $columns[] = "`{$key}`";

        array_push($columns, "created_at", "updated_at");
        if($this->use_importables && $use_importables) array_push($columns, "imported_at");
      }

      if(!$duplicates){
        foreach($obj as $key=>$val)
          $duplicates[] = "`{$key}` = VALUES(`{$key}`)";

        array_push($duplicates, "`updated_at` = VALUES(`updated_at`)");
        if($this->use_importables && $use_importables) array_push($duplicates, "`imported_at` = VALUES(`imported_at`)");
      }

      $queries = $values = [];
      foreach($obj as $key=>$val){
        $queries[] = '?';
        $values[] = $val;
      }
      array_push($queries, '?', '?');
      array_push($values, $imported_at, $imported_at);
      if($this->use_importables && $use_importables){
        array_push($queries, '?');
        array_push($values, $imported_at);
      }
      $queries = '(' . implode(', ', $queries) . ')';

      $inserts[] = [
        $queries,
        $values
      ];
    }
    $columns = implode(', ', $columns);
    $duplicates = implode(', ', $duplicates);
    
    db_insert_batch("INSERT INTO {$table} ({$columns}) VALUES {QUERIES} ON DUPLICATE KEY UPDATE {$duplicates}", $inserts);
  }

  protected function processData(Request $request, array $data, array $files = []){}

  private function castDate($value){

    try{

      $value = Date::excelToDateTimeObject($value)->format('Y-m-d');
    }
    catch(\Exception $ex){

      if(date('Y', strtotime($value)) != 1970)
        $value = date('Y-m-d', strtotime($value));
      else
        $value = '';
    }

    return $value;
  }

  private function castDatetime($value){

    try{

      $value = Date::excelToDateTimeObject($value)->format('Y-m-d H:i:s');
    }
    catch(\Exception $ex){

      if(date('Y', strtotime($value)) != 1970)
        $value = date('Y-m-d H:i:s', strtotime($value));
      else
        $value = '';
    }

    return $value;
  }

  private function castNumber($value){
    $value = floatval(str_replace([ ',' ], '', $value));
    return $value;
  }

  private function castBool($value){
    $value = $value > 0 ? 1 : 0;
    return $value;
  }
  
  protected function extractImage($key, $obj, $eobj, array $options)
  {
    $dir = $options['dir'] ?? '';
    $ratio = $options['ratio'] ?? '';
    $max_width = $options['max_width'] ?? '';
    $max_height = $options['max_height'] ?? '';
    $min_width = $options['min_width'] ?? '';
    $min_height = $options['min_height'] ?? '';
    $max_size = $options['max_size'] ?? '';
    $text = $options['text'] ?? $key;

    $value = $eobj->{$key} ?? '';
    if(isset($obj[$key]) && $obj[$key]){
      $source_path = $this->files[$obj[$key]] ?? false;

      if($source_path){

        list($width, $height, $type, $attr) = getimagesize($source_path);

        if($ratio){
          list($ratio1, $ratio2) = explode('/', $ratio);
          if($width / $height != $ratio1 / $ratio2)
            $this->result_logs[] = __('validation.dimensions', [ 'attribute'=>$text ]);
        }
        if($max_width > 0){
          if($width > $max_width)
            $this->result_logs[] = __('validation.dimensions', [ 'attribute'=>$text ]);
        }
        if($max_height > 0){
          if($height > $max_height)
            $this->result_logs[] = __('validation.dimensions', [ 'attribute'=>$text ]);
        }
        if($min_width > 0){
          if($width < $min_width)
            $this->result_logs[] = __('validation.dimensions', [ 'attribute'=>$text ]);
        }
        if($min_height > 0){
          if($height < $min_height)
            $this->result_logs[] = __('validation.dimensions', [ 'attribute'=>$text ]);
        }
        if($max_size > 0){
          $kb = filesize($source_path) / 1024;
          if($kb > $max_size)
            $this->result_logs[] = __('validation.max.file', [ 'attribute'=>$text, 'max'=>$max_size ]);
        }

        $ext = explode('.', $source_path)[1] ?? '';
        $target_path = Storage::disk('images')->path($dir . '/' . md5_file($source_path) . ($ext ? '.' . $ext : ''));
        exec("cp {$source_path} {$target_path}");
        $image_url = Storage::disk('images')->url($dir . '/' . md5_file($source_path) . ($ext ? '.' . $ext : ''));
        $value = str_replace(env('APP_URL'), '', $image_url);
      }
      else
        $this->result_logs[] = "Image {$source_path} not found";
    }
    
    return $value;
  }
}