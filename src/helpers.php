<?php


/**
 * Check if variable is assosiative array
 */

if(!function_exists('is_assoc')){
  function is_assoc($array) {
    if(gettype($array) == "array")
      return (bool)count(array_filter(array_keys($array), 'is_string'));
    return false;
  }
}

if (! function_exists('exc')) {
  function exc($message = '', $detail = [], $code = 0, $previous = null)
  {
    if(is_array($message)) $message = json_encode($message);

    throw new \Andiwijaya\WebApp\Exceptions\UserException($message, $detail, $code, $previous);
  }
}

if(! function_exists('is_zip')){

  function is_zip($mime_type){

    return in_array($mime_type, [ 'application/zip', 'application/x-zip-compressed', 'multipart/x-zip' ]);

  }

}

if(! function_exists('rglob')){

  function rglob($pattern, $flags = 0) {
    $files = glob($pattern, $flags);
    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
      $files = array_merge($files, rglob($dir.'/'.basename($pattern), $flags));
    }
    return $files;
  }

}

if(!function_exists('paging_url_replace')){

  function paging_url_replace($url, $page){

    if(strpos($url, 'page=') !== false)
      $url = preg_replace('/page=(\d+)/', "page={$page}", $url);
    else
      $url .= strpos($url, '?') !== false ? "&page={$page}" : "?page={$page}";
    return $url;

  }

  function paging_render($items){

    $page = $items->currentPage();
    $last_page = $items->lastPage();

    if($last_page == 1) return;

    $html = [];
    $html[] = "<div class='paging'>";

    if($page > 1) $html[] = "<a class='small' href=\"" . paging_url_replace(\Illuminate\Http\Request::fullUrl(), 1) . "\">First</a>";
    if($page - 1 >= 1) $html[] = "<a class='small' href=\"" . paging_url_replace(\Illuminate\Http\Request::fullUrl(), $page - 1) . "\">Prev</a>";

    $start_index = $page - 3 < 1 ? 1 : $page - 3;
    $end_index = $page + 3 > $last_page ? $last_page : $page + 3;

    if($start_index + 6 > $last_page && $end_index - 6 < 1){
      $start_index = 1;
      $end_index = $last_page;
    }
    else if($end_index - 6 < 1){
      $start_index = 1;
      $end_index = 6;
    }
    else if($start_index + 6 > $last_page){
      $start_index = $last_page - 6;
      $end_index = $last_page;
    }

    for($i = $start_index ; $i <= $end_index ; $i++){
      $html[] = "<a class='small" . ($i == $page ? " active" : '') . "' href=\"" . paging_url_replace(\Illuminate\Support\Facades\Request::fullUrl(), $i) . "\">{$i}</a>";
    }

    if($page + 1 <= $last_page) $html[] = "<a class='small' href=\"" . paging_url_replace(\Illuminate\Support\Facades\Request::fullUrl(), $page + 1) . "\">Next</a>";
    if($page < $last_page) $html[] = "<a class='small' href=\"" . paging_url_replace(\Illuminate\Support\Facades\Request::fullUrl(), $last_page) . "\">Last</a>";

    $html[] = "</div>";
    return implode('', $html);

  }

}

if(!function_exists('random_voucher_code')){

  function random_voucher_code($count, $prefix, $digitcount, $existingvouchers = null, $numeric_only = false){

    if(!$existingvouchers) $existingvouchers = array();

    $newvouchers = array();
    do{
      $index = $numeric_only ? str_pad(rand(0, pow(10, $digitcount) - 1), $digitcount, '0', STR_PAD_LEFT) : strtoupper(substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $digitcount));
      $vouchercode = $prefix . $index;
      $exists = isset($existingvouchers[$vouchercode]) ? true : false;
      if(!$exists && substr($vouchercode, 0, 1) != '0'){
        $newvouchers[] = $vouchercode;
        $existingvouchers[$vouchercode] = 1;
      }
    }
    while(count($newvouchers) < $count);

    return $newvouchers;

  }

}

if(!function_exists('validYear')){

  function validYear($year){

    return $year > 1901 && $year < 2099;

  }

}

if(!function_exists('array_to_object')){

  /**
   * Convert array to object
   * - Static value supported instead of mapping to columns
   *
   * @param array $rows
   * @param array $mapping if array, map column. if scalar, map static value
   * @return array
   */
  function array_to_object(array $rows, array $mapping){

    $columns_names = [];
    foreach($mapping as $key=>$columns)
      if(is_array($columns)){
        foreach($columns as $column)
          $columns_names[$column] = 1;
      }

    $header_row = -1;

    $columns = [];
    foreach($rows as $idx=>$row){
      foreach($row as $col_idx=>$col)
        if(isset($columns_names[$col])){
          $header_row = $idx;
          break;
        }
    }
    if($header_row >= 0){
      foreach($rows[$header_row] as $col_idx=>$col){
        foreach($mapping as $key=>$map){

          if(is_array($map)){
            if(in_array($col, $map)){
              $columns[$key] = [
                'key'=>$key,
                'index'=>$col_idx
              ];
            }
          }

          elseif(is_scalar($map)){
            $columns[$key] = [
              'key'=>$key,
              'value'=>$map
            ];
          }

        }
      }
    }

    $results = [];
    for($i = $header_row + 1 ; $i < count($rows) ; $i++){

      $empty_row = true;
      foreach($rows[$i] as $col)
        if($col){
          $empty_row = false;
          break;
        }

      if($empty_row) continue;

      $obj = [];
      foreach($columns as $key=>$column){

        if(isset($column['index']))
          $obj[$key] = $rows[$i][$column['index']];

        if(isset($column['value']))
          $obj[$key] = $column['value'];

      }
      $results[] = $obj;

    }

    return $results;

  }

}

if(!function_exists('ov')){

  function ov($key, $obj){

    if(is_scalar($key))
      return isset($obj[$key]) ? $obj[$key] : '';

    elseif(is_array($key)){

      foreach($key as $key_)
        if(isset($obj[$key_]))
          return $obj[$key_];
      return '';

    }

  }

}

if(!function_exists('redis_available')){

  function redis_available(){

    try{
      $redis = \Illuminate\Support\Facades\Redis::connection();
      $redis->connect();
      $redis->disconnect();

      return true;
    }
    catch (\Exception $e){
      return false;
    }

  }

}

if(!function_exists('array_diff_assoc2')){

  /**
   * Compare 2 array of object, returns created, updated and deleted object
   * TODO: Will fail if object contains multi-dimension value
   * @param $arr1
   * @param $arr2
   * @param string $key
   * @return array
   */
  function array_diff_assoc2($arr1, $arr2, $key = 'id', $debug = false){

    $arr1 = json_decode(json_encode($arr1), 1);
    $arr2 = json_decode(json_encode($arr2), 1);

    $has_update = false;

    foreach($arr1 as $idx1=>$obj1)
    {
      $exists = -1;
      foreach($arr2 as $idx2=>$obj2){
        if(isset($obj2[$key]) && $obj1[$key] == $obj2[$key]){
          $exists = $idx1;
          break;
        }
      }
      if($exists == -1){
        $arr1[$idx1]['_type'] = -1;
        $has_update = true;
      }
    }

    $created = [];
    foreach($arr2 as $idx2=>$obj2)
    {
      $exists = -1;
      $updates = null;
      foreach($arr1 as $idx1=>$obj1){
        if(($id2 = $obj2[$key] ?? 'x') == ($id1 = $obj1[$key] ?? 'y')){
          $updates = array_diff2($obj1, $obj2);
          $exists = $idx1;
        }
      }

      if($exists == -1){
        $created[] = array_merge($obj2, [ '_type'=>1 ]);
        $has_update = true;
      }
      elseif(count($updates) > 0){
        $arr1[$exists]['_type'] = 2;
        $arr1[$exists]['_updates'] = $updates;
        $has_update = true;
      }

    }

    $arr1 = array_merge($arr1, $created);

    return $arr1;

    if($key == 'voucher_id') exc(gettype($arr1));
    /*$deleted = array_filter($arr1, function($obj1) use($arr2, $key){
      foreach($arr2 as $obj2)
        if(isset($obj2[$key]) && $obj1[$key] == $obj2[$key])
          return false;
      return true;
    });*/

    /*$updated = [];
    $created = array_filter($arr2, function($obj2) use($arr1, $key, &$updated){

      $exists = false;
      foreach($arr1 as $idx1=>$obj1){
        if(($id2 = $obj2[$key] ?? 'x') == ($id1 = $obj1[$key] ?? 'y')){
          if(count(($update = array_diff2($obj1, $obj2))) > 0)
            $updated[] = array_merge([ $key=>$id2 ], $update);
          $exists = true;
        }
      }
      return !$exists;

    });*/

    /*$result = [];
    if(count($deleted) > 0) $result['deleted'] = $deleted;
    if(count($created) > 0) $result['created'] = $created;
    if(count($updated) > 0) $result['updated'] = $updated;
    return $result;*/

  }

  function array_diff2($obj1, $obj2){

    $obj3 = [];
    foreach($obj1 as $key=>$value){

      if(isset($obj2[$key])){

        if(is_scalar($obj1[$key])){
          if($obj1[$key] != $obj2[$key])
            $obj3[$key] = $obj2[$key];
        }
        else if(json_encode($obj1[$key]) != json_encode($obj2[$key]))
          $obj3[$key] = $obj2[$key];

      }

    }
    return $obj3;

  }

}

if(!function_exists('vi' . 'ewed')){

  function viewed($view = null, $data = [], $mergeData = [])
  {
    $factory = app(\Illuminate\View\Factory::class);

    if (func_num_args() === 0) {
      return $factory;
    }

    $view = $factory->make($view, $data, $mergeData);
    $path = $view->getPath();
    $view = ($t = filemtime($view->getPath())) ? $view->render() : '';
    $view = substr($view, 0, strpos($view, '</div>') + 6) .
      view('andiwijaya::components.list-page', [ 't'=>$t ])->render() .
      substr($view, strpos($view, '</div>') + 6);
    file_put_contents(storage_path('app') . '/' . basename($path), $view);

    return $factory->make('app::' . str_replace('.blade.php', '', basename($path)), $data, $mergeData);
  }

}

if(!function_exists('array_index')){

  function array_index($arr, $indexes, $objResult = false){
    if(!is_array($arr)) return null;
    $result = array();

    $indexes_is_callback = is_callable($indexes);

    for($i = 0 ; $i < count($arr) ; $i++){
      $obj = $arr[$i];

      if($indexes_is_callback){

        $key = call_user_func_array($indexes, [ $obj ]);
        if(!isset($result[$key])) $result[$key] = [];
        $result[$key][] = $obj;
      }
      else{

        switch(count($indexes)){
          case 1 :
            $idx0 = $indexes[0];
            if(isset($obj[$idx0])){
              if(!isset($result[$obj[$idx0]])) $result[$obj[$idx0]] = array();
              $result[$obj[$idx0]][] = $obj;
            }
            break;
          case 2 :
            $idx0 = $indexes[0];
            $idx1 = $indexes[1];
            if(isset($obj[$idx0]) && isset($obj[$idx1])){
              $key0 = $obj[$idx0];
              $key1 = $obj[$idx1];
              if(!isset($result[$key0])) $result[$key0] = array();
              if(!isset($result[$key0][$key1])) $result[$key0][$key1] = array();
              $result[$key0][$key1][] = $obj;
            }
            break;
          case 3 :
            $idx0 = $indexes[0];
            $idx1 = $indexes[1];
            $idx2 = $indexes[2];
            if(isset($obj[$idx0]) && !isset($obj[$idx1]) && !isset($obj[$idx2])){
              $key0 = $obj[$idx0];
              $key1 = $obj[$idx1];
              $key2 = $obj[$idx2];
              if(!isset($result[$key0])) $result[$key0] = array();
              if(!isset($result[$key0][$key1])) $result[$key0][$key1] = array();
              $result[$key0][$key1][$key2] = $obj;
            }
            break;
          default:
            throw new Exception("Unsupported index level.");
        }
      }
    }

    // If array count = 1, remove array
    if($objResult){
      switch(count($indexes)){
        case 1:
          foreach($result as $key=>$arr)
            if(count($arr) == 1) $result[$key] = $arr[0];
          break;
        case 2:
          foreach($result as $key=>$arr1){
            foreach($arr1 as $key1=>$arr){
              if(count($arr) == 1) $result[$key][$key1] = $arr[0];
            }
          }
          break;
        case 3:
          foreach($result as $key=>$arr1){
            foreach($arr1 as $key1=>$arr2){
              foreach($arr2 as $key2=>$arr)
                if(count($arr) == 1) $result[$key][$key1][$key2] = $arr[0];
            }
          }
          break;
      }
    }

    return $result;
  }

  function array_index_obj($arr, $indexes, $objResult = false)
  {

    $result = [];

    for ($i = 0; $i < count($arr); $i++) {
      $obj = $arr[$i];

      switch (count($indexes)) {
        case 1 :
          $idx0 = $indexes[0];
          if (isset($obj->{$idx0})) {
            if (!isset($result[$obj->{$idx0}])) $result[$obj->{$idx0}] = array();
            $result[$obj->{$idx0}][] = $obj;
          }
          break;
        case 2 :
          $idx0 = $indexes[0];
          $idx1 = $indexes[1];
          if (isset($obj->{$idx0}) && isset($obj->{$idx1})){
            $key0 = $obj->{$idx0};
            $key1 = $obj->{$idx1};
            if (!isset($result[$key0])) $result[$key0] = array();
            if (!isset($result[$key0][$key1])) $result[$key0][$key1] = array();
            $result[$key0][$key1][] = $obj;
          }
          break;
        case 3 :
          $idx0 = $indexes[0];
          $idx1 = $indexes[1];
          $idx2 = $indexes[2];
          if (isset($obj->{$idx0}) && !isset($obj->{$idx1}) && !isset($obj->{$idx2})){
            $key0 = $obj->{$idx0};
            $key1 = $obj->{$idx1};
            $key2 = $obj->{$idx2};

            if (!isset($result[$key0])) $result[$key0] = array();
            if (!isset($result[$key0][$key1])) $result[$key0][$key1] = array();
            $result[$key0][$key1][$key2][] = $obj;
          }
          break;
        default:
          throw new Exception("Unsupported index level.");
      }
    }

    return $result;

  }

}

if(!function_exists('mime2ext')){

  function mime2ext($mime) {
    $mime_map = [
      'video/3gpp2'                                                               => '3g2',
      'video/3gp'                                                                 => '3gp',
      'video/3gpp'                                                                => '3gp',
      'application/x-compressed'                                                  => '7zip',
      'audio/x-acc'                                                               => 'aac',
      'audio/ac3'                                                                 => 'ac3',
      'application/postscript'                                                    => 'ai',
      'audio/x-aiff'                                                              => 'aif',
      'audio/aiff'                                                                => 'aif',
      'audio/x-au'                                                                => 'au',
      'video/x-msvideo'                                                           => 'avi',
      'video/msvideo'                                                             => 'avi',
      'video/avi'                                                                 => 'avi',
      'application/x-troff-msvideo'                                               => 'avi',
      'application/macbinary'                                                     => 'bin',
      'application/mac-binary'                                                    => 'bin',
      'application/x-binary'                                                      => 'bin',
      'application/x-macbinary'                                                   => 'bin',
      'image/bmp'                                                                 => 'bmp',
      'image/x-bmp'                                                               => 'bmp',
      'image/x-bitmap'                                                            => 'bmp',
      'image/x-xbitmap'                                                           => 'bmp',
      'image/x-win-bitmap'                                                        => 'bmp',
      'image/x-windows-bmp'                                                       => 'bmp',
      'image/ms-bmp'                                                              => 'bmp',
      'image/x-ms-bmp'                                                            => 'bmp',
      'application/bmp'                                                           => 'bmp',
      'application/x-bmp'                                                         => 'bmp',
      'application/x-win-bitmap'                                                  => 'bmp',
      'application/cdr'                                                           => 'cdr',
      'application/coreldraw'                                                     => 'cdr',
      'application/x-cdr'                                                         => 'cdr',
      'application/x-coreldraw'                                                   => 'cdr',
      'image/cdr'                                                                 => 'cdr',
      'image/x-cdr'                                                               => 'cdr',
      'zz-application/zz-winassoc-cdr'                                            => 'cdr',
      'application/mac-compactpro'                                                => 'cpt',
      'application/pkix-crl'                                                      => 'crl',
      'application/pkcs-crl'                                                      => 'crl',
      'application/x-x509-ca-cert'                                                => 'crt',
      'application/pkix-cert'                                                     => 'crt',
      'text/css'                                                                  => 'css',
      'text/x-comma-separated-values'                                             => 'csv',
      'text/comma-separated-values'                                               => 'csv',
      'application/vnd.msexcel'                                                   => 'csv',
      'application/x-director'                                                    => 'dcr',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
      'application/x-dvi'                                                         => 'dvi',
      'message/rfc822'                                                            => 'eml',
      'application/x-msdownload'                                                  => 'exe',
      'video/x-f4v'                                                               => 'f4v',
      'audio/x-flac'                                                              => 'flac',
      'video/x-flv'                                                               => 'flv',
      'image/gif'                                                                 => 'gif',
      'application/gpg-keys'                                                      => 'gpg',
      'application/x-gtar'                                                        => 'gtar',
      'application/x-gzip'                                                        => 'gzip',
      'application/mac-binhex40'                                                  => 'hqx',
      'application/mac-binhex'                                                    => 'hqx',
      'application/x-binhex40'                                                    => 'hqx',
      'application/x-mac-binhex40'                                                => 'hqx',
      'text/html'                                                                 => 'html',
      'image/x-icon'                                                              => 'ico',
      'image/x-ico'                                                               => 'ico',
      'image/vnd.microsoft.icon'                                                  => 'ico',
      'text/calendar'                                                             => 'ics',
      'application/java-archive'                                                  => 'jar',
      'application/x-java-application'                                            => 'jar',
      'application/x-jar'                                                         => 'jar',
      'image/jp2'                                                                 => 'jp2',
      'video/mj2'                                                                 => 'jp2',
      'image/jpx'                                                                 => 'jp2',
      'image/jpm'                                                                 => 'jp2',
      'image/jpeg'                                                                => 'jpeg',
      'image/pjpeg'                                                               => 'jpeg',
      'application/x-javascript'                                                  => 'js',
      'application/json'                                                          => 'json',
      'text/json'                                                                 => 'json',
      'application/vnd.google-earth.kml+xml'                                      => 'kml',
      'application/vnd.google-earth.kmz'                                          => 'kmz',
      'text/x-log'                                                                => 'log',
      'audio/x-m4a'                                                               => 'm4a',
      'application/vnd.mpegurl'                                                   => 'm4u',
      'audio/midi'                                                                => 'mid',
      'application/vnd.mif'                                                       => 'mif',
      'video/quicktime'                                                           => 'mov',
      'video/x-sgi-movie'                                                         => 'movie',
      'audio/mpeg'                                                                => 'mp3',
      'audio/mpg'                                                                 => 'mp3',
      'audio/mpeg3'                                                               => 'mp3',
      'audio/mp3'                                                                 => 'mp3',
      'video/mp4'                                                                 => 'mp4',
      'video/mpeg'                                                                => 'mpeg',
      'application/oda'                                                           => 'oda',
      'audio/ogg'                                                                 => 'ogg',
      'video/ogg'                                                                 => 'ogg',
      'application/ogg'                                                           => 'ogg',
      'application/x-pkcs10'                                                      => 'p10',
      'application/pkcs10'                                                        => 'p10',
      'application/x-pkcs12'                                                      => 'p12',
      'application/x-pkcs7-signature'                                             => 'p7a',
      'application/pkcs7-mime'                                                    => 'p7c',
      'application/x-pkcs7-mime'                                                  => 'p7c',
      'application/x-pkcs7-certreqresp'                                           => 'p7r',
      'application/pkcs7-signature'                                               => 'p7s',
      'application/pdf'                                                           => 'pdf',
      'application/octet-stream'                                                  => 'pdf',
      'application/x-x509-user-cert'                                              => 'pem',
      'application/x-pem-file'                                                    => 'pem',
      'application/pgp'                                                           => 'pgp',
      'application/x-httpd-php'                                                   => 'php',
      'application/php'                                                           => 'php',
      'application/x-php'                                                         => 'php',
      'text/php'                                                                  => 'php',
      'text/x-php'                                                                => 'php',
      'application/x-httpd-php-source'                                            => 'php',
      'image/png'                                                                 => 'png',
      'image/x-png'                                                               => 'png',
      'application/powerpoint'                                                    => 'ppt',
      'application/vnd.ms-powerpoint'                                             => 'ppt',
      'application/vnd.ms-office'                                                 => 'ppt',
      'application/msword'                                                        => 'ppt',
      'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
      'application/x-photoshop'                                                   => 'psd',
      'image/vnd.adobe.photoshop'                                                 => 'psd',
      'audio/x-realaudio'                                                         => 'ra',
      'audio/x-pn-realaudio'                                                      => 'ram',
      'application/x-rar'                                                         => 'rar',
      'application/rar'                                                           => 'rar',
      'application/x-rar-compressed'                                              => 'rar',
      'audio/x-pn-realaudio-plugin'                                               => 'rpm',
      'application/x-pkcs7'                                                       => 'rsa',
      'text/rtf'                                                                  => 'rtf',
      'text/richtext'                                                             => 'rtx',
      'video/vnd.rn-realvideo'                                                    => 'rv',
      'application/x-stuffit'                                                     => 'sit',
      'application/smil'                                                          => 'smil',
      'text/srt'                                                                  => 'srt',
      'image/svg+xml'                                                             => 'svg',
      'application/x-shockwave-flash'                                             => 'swf',
      'application/x-tar'                                                         => 'tar',
      'application/x-gzip-compressed'                                             => 'tgz',
      'image/tiff'                                                                => 'tiff',
      'text/plain'                                                                => 'txt',
      'text/x-vcard'                                                              => 'vcf',
      'application/videolan'                                                      => 'vlc',
      'text/vtt'                                                                  => 'vtt',
      'audio/x-wav'                                                               => 'wav',
      'audio/wave'                                                                => 'wav',
      'audio/wav'                                                                 => 'wav',
      'application/wbxml'                                                         => 'wbxml',
      'video/webm'                                                                => 'webm',
      'audio/x-ms-wma'                                                            => 'wma',
      'application/wmlc'                                                          => 'wmlc',
      'video/x-ms-wmv'                                                            => 'wmv',
      'video/x-ms-asf'                                                            => 'wmv',
      'application/xhtml+xml'                                                     => 'xhtml',
      'application/excel'                                                         => 'xl',
      'application/msexcel'                                                       => 'xls',
      'application/x-msexcel'                                                     => 'xls',
      'application/x-ms-excel'                                                    => 'xls',
      'application/x-excel'                                                       => 'xls',
      'application/x-dos_ms_excel'                                                => 'xls',
      'application/xls'                                                           => 'xls',
      'application/x-xls'                                                         => 'xls',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
      'application/vnd.ms-excel'                                                  => 'xlsx',
      'application/xml'                                                           => 'xml',
      'text/xml'                                                                  => 'xml',
      'text/xsl'                                                                  => 'xsl',
      'application/xspf+xml'                                                      => 'xspf',
      'application/x-compress'                                                    => 'z',
      'application/x-zip'                                                         => 'zip',
      'application/zip'                                                           => 'zip',
      'application/x-zip-compressed'                                              => 'zip',
      'application/s-compressed'                                                  => 'zip',
      'multipart/x-zip'                                                           => 'zip',
      'text/x-scriptzsh'                                                          => 'zsh',
    ];

    return isset($mime_map[$mime]) === true ? $mime_map[$mime] : false;
  }

}

if(!function_exists('in_array_any')){

  function in_array_any($needles, $haystack) {
    return !empty(array_intersect($needles, $haystack));
  }

}

if(!function_exists('in_array_all')){

  function in_array_all($needles, $haystack) {
    return empty(array_diff($needles, $haystack));
  }
}

if(!function_exists('save_image')){

  function save_image($image, $disk = 'images', $dir = '', $relative = true){

    $url = '';
    
    if(filter_var($image, FILTER_VALIDATE_URL)){

      $image_params = getimagesize($image);
      if(isset($image_params['mime'])){
        $mime = $image_params['mime'];
        $ext = mime2ext($mime);

        $file_md5 = implode('.', [
          md5_file($image),
          $ext
        ]);

        if(strlen($dir) > 0) $dir = $dir . '/';

        file_put_contents(\Illuminate\Support\Facades\Storage::disk($disk)->path($dir . $file_md5), file_get_contents($image));
        $url = \Illuminate\Support\Facades\Storage::disk($disk)->url($dir . $file_md5);
      }
    }
    else if(is_file($image)){

      if(is_object($image) && get_class($image) == \Illuminate\Http\UploadedFile::class)
        $ext = $image->getClientOriginalExtension();
      else
        $ext = pathinfo($image, PATHINFO_EXTENSION);

      $file_md5 = implode('.', [
        md5_file($image),
        $ext
      ]);

      if(strlen($dir) > 0) $dir = $dir . '/';

      file_put_contents(\Illuminate\Support\Facades\Storage::disk($disk)->path($dir . $file_md5), file_get_contents($image));
      $url = \Illuminate\Support\Facades\Storage::disk($disk)->url($dir . $file_md5);
    }

    if($relative)
      $url = str_replace(env('APP_URL'), '', $url);
    return $url;
  }

}

if(!function_exists('save_base64_image')){

  function is_base64_image($data){

    return preg_match('/^data:image\/(\w+);base64,/', $data, $type);

  }

  function save_base64_image($data, $disk = 'images'){

    if(preg_match('/^data:image\/(\w+);base64,/', $data, $type)){

      $data = substr($data, strpos($data, ',') + 1);
      $type = strtolower($type[1]);

      // Only handle image with extension below
      if(!in_array($type, [ 'jpg', 'jpeg', 'gif', 'png', 'webp' ]))
        return $data;

      $data = base64_decode($data);
      if($data === false)
        return $data; // base64_decode failed

      $file_md5 = md5($data) . '.' . $type;
      //list($width, $height) = getimagesize($data);

      if(!\Illuminate\Support\Facades\Storage::disk($disk)->exists($file_md5))
        \Illuminate\Support\Facades\Storage::disk($disk)->put($file_md5, $data);

      return $file_md5;

    }

    return '';

  }

}

if(!function_exists('get_file_base64')){

  function get_file_base64($path){

    if(file_exists($path)){
      $type = pathinfo($path, PATHINFO_EXTENSION);
      $data = file_get_contents($path);
      return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
    return '';
  }

}

if( !function_exists('ceiling') )
{
  function ceiling($number, $significance = 1)
  {
    return ( is_numeric($number) && is_numeric($significance) ) ? (ceil($number/$significance)*$significance) : false;
  }
}

if(!function_exists('random_dark_color')){

  function random_dark_color() {
    $color = '';
    for($i = 0 ; $i < 3 ; $i++)
      $color .= str_pad( dechex( mt_rand( 0, 127 ) ), 2, '0', STR_PAD_LEFT);
    return $color;
  }

}

if(!function_exists('get_md5_filename')){

  function get_md5_filename($file){

    if(!file_exists($file)) exc("Invalid file: {$file}");

    $ext = '';

    if(is_object($file) && get_class($file) == 'Illuminate\Http\UploadedFile')
      $ext = $file->getClientOriginalExtension();
    else{
      $type = mime_content_type($file);
      switch($type){

        case 'image/png':
          $ext = 'png';
          break;

        case 'image/jpg':
          $ext = 'jpg';
          break;

        case 'image/jpeg':
          $ext = 'jpeg';
          break;

        default:
          exc("Unknown extension for file: {$file}, ext:{$ext}");

      }
    }

    return md5_file($file) . '.' . $ext;

  }

}

if(!function_exists('list_page_grid_head')){

  function list_page_grid_head($key, array $sortable, array $sorts = []){

    $is_sortable = false;
    $sortable_param = null;
    foreach($sortable as $sortable_key=>$sortable_value){
      if((is_array($sortable_value) && $key == $sortable_key) ||
        (is_scalar($sortable_value) && $key == $sortable_value)){
        $is_sortable = true;
        $sortable_param = is_array($sortable_value) ? $sortable_value : [ 'text'=>$sortable_key ];
        break;
      }
    }

    if($is_sortable){

      $sort_type = '';
      collect($sorts)->each(function($item) use(&$sort_type, $key){
        if(count(($exploded = explode(',', $item))) == 2 && $exploded[0] == $key)
          $sort_type = in_array(strtolower($exploded[1]), [ 'asc', 'desc' ]) ? strtolower($exploded[1]) : $sort_type;
        return true;
      });

      if($sort_type == 'asc')
        return "<label class='selectable' for=\"list-sc-{$key}-desc\">{$sortable_param['text']}<span class=\"fa fa-caret-up\" style=\"font-size:11px;padding:0 .3rem\"></span></label>";
      elseif($sort_type == 'desc')
        return "<label class='selectable' for=\"list-sc-{$key}-asc\">{$sortable_param['text']}<span class=\"fa fa-caret-down\" style=\"font-size:11px;padding:0 .3rem\"></span></label>";
      else
        return "<label class='selectable' for=\"list-sc-{$key}-asc\">{$sortable_param['text']}</label>";
    }
    else{

      return "<label>" . ($sortable_param['text'] ?? $key) . "</label>";
    }

  }

}

if(!function_exists('list_page_filter_item')){

  function list_page_filter_item($key, $param){

    $param = is_string($param) ? explode('|', $param) : $param;

    $text = $param['text'] ?? ($param[0] ?? $key);
    $type = $param['type'] ?? ($param[1] ?? 'text');

    $html = <<<EOL
<div class="col-12">
  <strong class="hpad-1">{$text}</strong>
  <div class="vmart-1 vmarb-1">
EOL;

    switch($type){

      case 'array':
        $items = isset($param['items']) && is_array($param['items']) ? $param['items'] : [];

        $idx = 0;
        if(count($items) <= 10){
          foreach($items as $item_value=>$item_text){
            $html .= <<<EOL
    <div class="choice">
      <input type="checkbox" id="{$key}-{$idx}" name="{$key}[]" value="{$item_value}" onchange="$('.apply-filter').click()"/>
      <label for="{$key}-{$idx}"><span class="checker"><span></span></span> {$item_text}</label>
    </div>
EOL;
            $idx++;
          }
        }
        else{
          $html .= "<div class='dropdown'><select name='{$key}[]' onchange=\"$('.apply-filter').click()\">";
          $html .= "<option value=''>-</option>";
          foreach($items as $item_value=>$item_text) {
            $html .= "<option value=\"{$item_value}\">{$item_text}</option>";
          }
          $html .= "</select><span class='fa fa-caret-down icon'></span></div>";
        }

        break;

      case 'builder':
        $class = $param['class'] ?? '';
        $item_text_key = $param['item_text_key'] ?? 'id';

        if(class_exists($class)){

          $items = $class::all();

          if(count($items) < 5){

            foreach($items as $idx=>$item){

              $item_text = $item->{$item_text_key};

              $html .= <<<EOL
    <div class="choice">
      <input type="checkbox" id="{$key}-{$item->id}" name="{$key}[]" value="{$item->id}" onchange="$('.apply-filter').click()"/>
      <label for="{$key}-{$item->id}"><span class="checker"><span></span></span> {$item_text}</label>
    </div>
EOL;
            }
          }
          else{

            $html .= "<div class='dropdown'><select name='{$key}[]' onchange=\"$('.apply-filter').click()\">";
            $html .= "<option value=''>-</option>";
            foreach($items as $idx=>$item) {
              $item_text = $item->{$item_text_key};
              $html .= "<option value=\"{$item->id}\">{$item_text}</option>";
            }
            $html .= "</select><span class='fa fa-caret-down icon'></span></div>";
          }

        }

        break;

      case 'number-range':
        $html .= <<<EOL
      <div class="rowc">
        <div class="col-6 lpfnc-{$key}-0">
          <div class="dropdown">
            <select name="{$key}[number_range]" 
              onchange="
                this.value == 'between' ? $('.lpfnc-{$key}-0').removeClass('col-6').addClass('col-12') : $('.lpfnc-{$key}-0').removeClass('col-12').addClass('col-6');
                this.value == 'between' ? $('.lpfnc-{$key}').removeClass('hidden') : $('.lpfnc-{$key}').addClass('hidden');
              ">
              <option value="" disabled selected>Select</option>
              <option value="="> = </option>
              <option value=">="> >= </option>
              <option value=">"> > </option>
              <option value="<="> <= </option>
              <option value="<"> < </option>
              <option value="<>">Not</option>
              <option value="between">Between</option>
            </select>
            <span class="icon icon-circle-down icon-caret-down"></span>
          </div>
        </div>
        <div class="col-6">
          <div class="textbox" data-datatype="money">
            <input type="text" name="{$key}[number_range_from]" />
          </div>
        </div>
        <div class="col-6 lpfnc-{$key} hidden">
          <div class="textbox" data-datatype="money">
            <input type="text" name="{$key}[number_range_to]" />
          </div>
        </div>
      
      </div>
        
EOL;

        break;

      case 'date-range':

        $custom_from = \Carbon\Carbon::now()->addDays(-7)->format('j M Y');
        $custom_to = \Carbon\Carbon::now()->format('j M Y');
        $key = Illuminate\Support\Str::slug($key);

        $html .= <<<EOL
      <div class="dropdown block">
        <select name="{$key}[date_range]" onchange="this.value == 'custom' ? $('.lpfdc-{$key}').removeClass('hidden') : $('.lpfdc-{$key}').addClass('hidden')">
          <option value="" selected>Semua Tanggal</option>
          <option value="this-month">This Month</option>
          <option value="this-week">This Week</option>
          <option value="yesterday">Yesterday</option>
          <option value="today">Today</option>
          <option value="tomorrow">Tomorrow</option>
          <option value="this-quarter">This Quarter</option>
          <option value="this-year">This Year</option>
          <option value="this-year">This Decade</option>
          <option value="custom">Custom</option>
        </select>
        <span class="icon icon-circle-down fa fa-caret-down"></span>
      </div>
      <div class="lpfdc-{$key} hidden">
        <label class="block vmarb-0">From</label>
        <div class="datepicker">
          <input type="text" name="{$key}[date_range_from]" value="{$custom_from}"/>
          <span class="icon icon-calendar fa fa-calendar"></span>
        </div>
        <label class="block vmarb-0">To</label>
        <div class="datepicker">
          <input type="text" name="{$key}[date_range_to]" value="{$custom_to}"/>
          <span class="icon icon-calendar fa fa-calendar"></span>
        </div>
      </div>
EOL;

        break;


    }

    $html .= <<<EOL
  </div>
</div>
EOL;


    return $html;



  }

}

if(!function_exists('view_modal')){

  function view_modal($view, array $options = []){

    $data = $options['data'] ?? [];
    unset($options['data']);

    return [
      array_merge(
        $options,
        [
          'id'=>$options['id'] ?? uniqid(),
          'type'=>'modal',
          'html'=>view($view, $data)->render()
        ]
      ),
      isset($options['pre-script']) ? [ 'type'=>'pre-script', 'script'=>$options['pre-script'] ] : '',
      isset($options['script']) ? [ 'type'=>'script', 'script'=>$options['script'] ] : '',
    ];
  }

  function view_chart($type, $labels, $data, array $options = []){

    $colors = [
      '#4A89DC',
      '#3BAFDA',
      '#37BC9B',
      '#8CC152',
      '#F6BB42',
      '#E9573F',
      '#DA4453',
      '#967ADC',
      '#D770AD',
      '#434A54'
    ];

    $datasets = [];
    foreach($data as $idx=>$arr){
      $dataset = [
        'data'=>$arr,
        'fill'=>false,
        'borderColor'=>$colors[$idx] ?? 'rgba(0, 0, 0, 1)'
      ];
      $datasets[] = $dataset;
    }

    $params = [
      'type'=>$type,
      'data'=>[
        'labels'=>$labels,
        'datasets'=>$datasets
      ],
      'options'=>[
        'scales'=>[
          'yAxes'=>[
            [
              'ticks'=>[
                'beginAtZero'=>true,
                'display'=>false
              ]
            ]
          ]
        ]
      ]
    ];

    $id = 'chart' . uniqid();

    $html[] = "<div>";
    $html[] = "<canvas id='{$id}'></canvas>";
    $html[] = "<script>
      new Chart('{$id}', " . json_encode($params) . ");
      </script>";
    $html[] = "</div>";

    return implode('', $html);
  }

}

if(!function_exists('view_async')){

  function view_async($view, $data = []){

    $html = view($view, $data)->render();

    preg_match_all('/<title>(.*?(?=<\/title>))<\/title>/', $html, $matches);
    $title = $matches[1][0] ?? '';

    preg_match_all('/<body(.*?(?=>))>(.*?(?=<\/body>))<\/body>/s', $html, $matches);

    return [
      'body'=>$matches[2][0] ?? 'body',
      'title'=>$title
    ];
  }
}

if (! function_exists('auth2')) {
  /**
   * Get the available auth instance.
   *
   * @param  string|null  $guard
   * @return \Illuminate\Contracts\Auth\Factory|\Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
   */
  function auth2()
  {
    return app('Auth');
  }
}

if(!function_exists('view_append')){

  function view_append($params){

    return $params;
  }

}

if(!function_exists('csv_export')){

  function csv_export($builder, array $headerColumns, $callback, array $options = []){

    $filename = $options['filename'] ?? ($options['file'] ?? uniqid() . '.csv');
    $headers = [
      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
      'Content-type'        => 'text/csv',
      'Content-Disposition' => "attachment; filename={$filename}",
      'Expires'             => '0',
      'Pragma'              => 'public'
    ];

    return Illuminate\Support\Facades\Response::stream(function() use($builder, $headerColumns, $callback){

      $fp = fopen('php://output', 'w');

      fputcsv($fp, $headerColumns);

      $builder->chunk(1000, function($rows) use($fp, $callback){

        foreach($rows as $row){

          if(is_callable($callback)){

            $data = call_user_func_array($callback, [ $row ]);
            $data = !is_array($data) ? [] : $data;
            fputcsv($fp, $data);
          }
        }

      });

      fclose($fp);

    }, 200, $headers);

  }

}

if(!function_exists('parsedown_ex')){

  function parsedown_ex($text){

    // Find <code></code> block, replace to <pre><code class=''></code></pre>
    preg_match_all('/\<code\>(.*?(?=\<\/code\>))\<\/code\>/', $text, $matches);

    if(isset($matches[0][0])){
      foreach($matches[0] as $match) {

        $code = str_replace('</code>', '</code></pre>', str_replace('<code>', '<pre><code class="language-css">', $match));
        $text = str_replace($match, $code, $text);
      }
    }

    return $text;
  }
}

if(!function_exists('money_alias')){

  function money_alias($number, $precision = -1, $no_label = false)
  {
    if($number >= 1000000000)
      return trim(round($number / 1000000000, $precision == -1 ? 3 : $precision) . (!$no_label ? 'm' : ''));
    if($number >= 1000000)
      return trim(round($number / 1000000, $precision == -1 ? 2 : $precision) . (!$no_label ? 'jt' : ''));
    else if($number >= 1000)
      return trim(round($number / 1000, $precision == -1 ? 0 : $precision) . (!$no_label ? 'rb' : ''));
    else
      return $number;
  }

}

if(!function_exists('csv_to_array')){

  function csv_to_array($path){

    $sheets = array_map('str_getcsv', file($path));
    $rows = [ $sheets ];
    return $rows;
  }
}

if(!function_exists('assets_version')){

  function assets_version(){

    return env('APP_ENV') == 'production' ? env('APP_VERSION', '1.0') : time();
  }

}

if(!function_exists('action2method')){

  function action2method($action){

    return collect(explode('-', $action))->map(function($item, $idx){
      if($idx > 0) return ucwords($item);
      return $item;
    })->implode('');
  }

}

if(!function_exists('str_var')){

  function str_var($str, $obj){

    $obj = json_decode(json_encode($obj), true);

    preg_match_all('/\{.*?(?=\})\}/', $str, $matches);
    foreach($matches[0] as $match){

      $key = substr($match, 1, strlen($match) - 2);
      $value = $obj[$key] ?? '';

      $str = str_replace($match, $value, $str);
    }

    return $str;
  }

}

if(!function_exists('imgresizer')){

  function imgresizer($image, $resize){

    $file_name = save_base64_image($image);
    $ext = explode('.', $file_name)[1] ?? '';
    $image_path = \Illuminate\Support\Facades\Storage::disk('images')->getDriver()->getAdapter()->getPathPrefix();
    $path = \Illuminate\Support\Facades\Storage::disk('images')->getDriver()->getAdapter()->getPathPrefix() . $file_name;

    $img = Image::make($path);

    $resizes = explode(' ', $resize);

    foreach($resizes as $resize){

      $action = substr($resize, 0, 1);
      switch($action){

        case 'c':
          $params = explode(',', substr($resize, 1));
          //call_user_func_array([ $img, 'crop' ], $params);
          $img->crop($params[0], $params[1], $params[2], $params[3]);
          break;

        case 'r':
          $params = explode(',', substr($resize, 1));
          $params[0] = round($params[0]);
          $params[1] = round($params[1]);
          $img->resize($params[0], $params[1]);
          //call_user_func_array([ $img, 'resize' ], $params);
          \Illuminate\Support\Facades\Log::info(json_encode([ 'resize', $params ]));
          break;
      }
    }

    $img->save($image_path . 'temp.jpeg');
    $file_name = md5_file(storage_path('app/public/images/temp.jpeg')) . '.' . $ext;
    rename(storage_path('app/public/images/temp.jpeg'), storage_path('app/public/images/') . $file_name);

    return $file_name;
  }
}

if(!function_exists('checked_if')){

  function checked_if($key, $arr){

    if(is_array($arr))
      return in_array($key, $arr) ? ' checked' : '';
    return '';
  }
}

if(!function_exists('action_route')){

  function action_route($namespace){

    $f = function(\Illuminate\Http\Request $request) use($namespace){

      $namespace = 'Admin';
      if(!empty($namespace)) $namespace .= '\\';

      $method = $request->method();
      $path = $request->path();
      $paths = $path != '/' ? explode('/', $path) : [];
      $key = null;
      $action = explode('|', $request->input('action'))[0] ?? null;

      if(preg_match('/^\d+$/', end($paths)) || in_array(end($paths), [ 'create' ])){
        $key = end($paths);
        $paths = array_splice($paths, 0, count($paths) - 1);
      }

      $controller = "App\\Http\\Controllers\\{$namespace}" . Illuminate\Support\Str::ucfirst(Illuminate\Support\Str::camel(count($paths) > 0 ? implode('-', $paths) : 'index') . 'Controller');

      if(class_exists($controller)){

        $controller = new $controller();

        if(!$key){

          switch($method){

            case 'GET':
              $method = Illuminate\Support\Str::camel($action ? $action : 'view');

              return call_user_func_array([ $controller, $method ], [ $request ]);

            case 'POST':
              $method = Illuminate\Support\Str::camel($action ? $action : 'save');

              return call_user_func_array([ $controller, $method ], [ $request ]);

            case 'PATCH':
              $method = Illuminate\Support\Str::camel($action ? $action : 'update');

              return call_user_func_array([ $controller, $method ], [ $request ]);
          }

        }
        else{

          switch($method){

            case 'GET':
              $method = Illuminate\Support\Str::camel($action ? $action : ($key == 'create' ? 'create' : 'open'));

              return call_user_func_array([ $controller, $method ], [ $request, $key ]);

            case 'DELETE':
              $method = Illuminate\Support\Str::camel($action ? $action : 'destroy');

              return call_user_func_array([ $controller, $method ], [ $request, $key ]);

          }
        }


      }

      else{

        return json_encode([
          $namespace,
          $method,
          $path,
          $controller,
          class_exists($controller)
        ]);
      }

    };

    return function() use($f){

      \Illuminate\Support\Facades\Route::any('', $f);
      \Illuminate\Support\Facades\Route::any('{p1}', $f);
      \Illuminate\Support\Facades\Route::any('{p1}/{p2}', $f);
      \Illuminate\Support\Facades\Route::any('{p1}/{p2}/{p3}', $f);
    };
  };
}

if(!function_exists('greeting')){

  function greeting($name = ''){

    $hour = \Carbon\Carbon::now()->format('H');
    if($hour >= 5 && $hour < 11)
      $greeting = __('Good morning');
    elseif($hour >= 11 && $hour < 15)
      $greeting = __('Good afternoon');
    elseif($hour >= 15 && $hour  < 19)
      $greeting = __('Good evening');
    else
      $greeting = __('Good night');

    $greeting .= !empty($name) ? ', ' . $name : '';

    return $greeting;
  }

}

if(!function_exists('uf_byte_size')){
  function uf_byte_size($size)
  {
    $unit=array('B','KB','MB','Bb','TB','PB');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).$unit[$i];
  }
}

if(!function_exists('htmlresponse')){

  function htmlresponse($data = [], $status = 200, array $headers = []){

    return new Andiwijaya\WebApp\Responses\HTMLResponse($data, $status, $headers);
  }
}

if(!function_exists('round500')){

  function round500($amount){

    $mod = round($amount) % 1000;
    $round = $mod < 250 ? 0 : ($mod < 500 ? 500 : ($mod < 750 ? 500 : 1000));
    $amount = ($amount - $mod) + $round;

    return $amount;
  }

}

if(!function_exists('memory_get_usage_text')){

  function memory_get_usage_text(){

    $size = memory_get_usage(true);
    $unit = array('B','KB','MB','GB','TB','PB');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
  }
}

if(!function_exists('db_insert_batch')){
  
  function db_insert_batch($query, $obj){

    $queries = $params = [];
    foreach($obj as $arr){

      $queries[] = $arr[0];
      foreach($arr[1] as $value)
        $params[] = $value;

      if(count($params) > 60000) {

        $currentQuery = str_replace('{QUERIES}', implode(',', $queries), $query);
        \Illuminate\Support\Facades\DB::statement($currentQuery, $params);

        $queries = $params = [];
      }
    }

    if(count($queries) > 0){
      $currentQuery = str_replace('{QUERIES}', implode(',', $queries), $query);
      \Illuminate\Support\Facades\DB::statement($currentQuery, $params);
    }
  }

  function db_insert_batch_items($query, $obj){

    $queries = $params = [];
    foreach($obj as $key=>$arr){

      foreach($arr as $obj){

        $queries[] = $obj[0];
        foreach($obj[1] as $value)
          $params[] = $value;

        if(count($params) > 60000) {

          $currentQuery = str_replace('{QUERIES}', implode(',', $queries), $query);
          \Illuminate\Support\Facades\DB::statement($currentQuery, $params);

          $queries = $params = [];
        }
      }
    }

    if(count($queries) > 0){
      $currentQuery = str_replace('{QUERIES}', implode(',', $queries), $query);
      \Illuminate\Support\Facades\DB::statement($currentQuery, $params);
    }
  }
  
}

if(!function_exists('money_split')){

  function money_split($amount, array $ratios, $precision = null){

    if($amount < 0) return false;

    if(!$precision){
      if($amount > 1000) $precision = -2;
      else if($amount > 100) $precision = -1;
      else $precision = 0;
    }

    $total_ratio = 0;
    foreach($ratios as $ratio)
      $total_ratio += $ratio;

    $splits = [];
    $total_amount = 0;
    foreach($ratios as $ratio){

      $current_amount = round(($ratio / $total_ratio) * $amount, $precision);
      $splits[] = $current_amount;
      $total_amount += $current_amount;
    }

    $remaining_amount = $amount - $total_amount;
    if($remaining_amount > 0)
      $splits[count($splits) - 1] += $remaining_amount;
    else if($remaining_amount < 0){
      $remaining_amount *= -1;
      for($i = count($splits) - 1 ; $i >= 0 ; $i--){
        $current_amount = $remaining_amount > $splits[$i] ? $splits[$i] : $remaining_amount;
        $splits[$i] -= $current_amount;
        $remaining_amount -= $current_amount;
        if($remaining_amount <= 0) break;
      }
    }

    return $splits;
  }
}

if(!function_exists('normalize_phone_number')){

  function normalize_phone_number($number, $country_code = '+62'){

    $number = preg_replace('/[^0-9\+]/', '', $number);

    if(strlen($number) >= 6){
      if(substr($number, 0, 1) == '0') $number = $country_code . substr($number, 1);
      if(substr($number, 0, 1) != '+') $number = $country_code . $number;
    }

    return $number;
  }
}

if(!function_exists('tail')){

  function tail($filepath, $lines = 1, $adaptive = true) {

    // Open file
    $f = @fopen($filepath, "rb");
    if ($f === false) return false;

    // Sets buffer size, according to the number of lines to retrieve.
    // This gives a performance boost when reading a few lines from the file.
    if (!$adaptive) $buffer = 4096;
    else $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));

    // Jump to last character
    fseek($f, -1, SEEK_END);

    // Read it and adjust line number if necessary
    // (Otherwise the result would be wrong if file doesn't end with a blank line)
    if (fread($f, 1) != "\n") $lines -= 1;

    // Start reading
    $output = '';
    $chunk = '';

    // While we would like more
    while (ftell($f) > 0 && $lines >= 0) {

      // Figure out how far back we should jump
      $seek = min(ftell($f), $buffer);

      // Do the jump (backwards, relative to where we are)
      fseek($f, -$seek, SEEK_CUR);

      // Read a chunk and prepend it to our output
      $output = ($chunk = fread($f, $seek)) . $output;

      // Jump back to where we started reading
      fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);

      // Decrease our line counter
      $lines -= substr_count($chunk, "\n");

    }

    // While we have too many lines
    // (Because of buffer size we might have read too many)
    while ($lines++ < 0) {

      // Find first newline and remove all text before that
      $output = substr($output, strpos($output, "\n") + 1);

    }

    // Close file and return
    fclose($f);
    return trim($output);
  }

}

if(!function_exists('view_content')){

  function view_content($view, $data = [], $mergeData = [], $section = 'content'){

    return request()->ajax() ?
      htmlresponse()
        ->html('.' . $section, view($view, $data, $mergeData)->renderSections()[$section] ?? view($view, $data, $mergeData)->render())
        ->html('.modal-cont', ''):
      view($view, $data, $mergeData);
  }
}

if(!function_exists('mask_email_address')){
  function mask_email_address($email){

    $result = $email;
    if(filter_var($email, FILTER_VALIDATE_EMAIL)){

      list($left, $right) = explode('@', $email);
      $left = substr($left, 0, strlen($left) / 2) . str_pad('', strlen($left) / 2, '*');

      $result = $left . '@' . $right;
    }
    return $result;
  }
}

if(!function_exists('mask_mobile_number')){
  function mask_mobile_number($number, $length = 6){

    return substr($number, 0, strlen($number) - 6) . 'xxxxxx';
  }
}