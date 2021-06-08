<?php

namespace Andiwijaya\WebApp\Http\Controllers;

use Andiwijaya\WebApp\Facades\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class WebEventController{
  
  public function store(Request $request){
    
    $remoteAddr = $request->ip();
    $userAgent = $request->userAgent();
    $sessionId = Session::getId();
    $userId = Session::get('user_id');
    $timestamp = Carbon::now()->format('Y-m-d H:i:s');

    $inserts = [];
    $data = $request->all();
    if(is_array($data))
      foreach($data as $obj){
        if(isset($obj[0])){
          $inserts[] = [
            "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
              $sessionId,
              $userId,
              $remoteAddr,
              $userAgent,
              $obj[0],
              $obj[1] ?? null,
              $obj[2] ?? null,
              $obj[3] ?? null,
              $obj[4] ?? null,
              $obj[5] ?? null,
              $timestamp,
              $timestamp
            ]
          ];
        }
      }

    if(count($inserts) > 0)
      db_insert_batch("INSERT INTO web_event (session_id, user_id, remote_addr, user_agent, `event`, " .
        "value1, value2, value3, value4, value5, created_at, updated_at) VALUES {QUERIES}", $inserts);
  }
  
}