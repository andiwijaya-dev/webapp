<?php

namespace Andiwijaya\WebApp\Console\Commands;

use Andiwijaya\WebApp\Models\WebCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Ixudra\Curl\Facades\Curl;

class WebCacheLoad extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'web-cache:load {--id=} {--url=}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Command description';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $id = $this->option('id');
    $url = $this->option('url');
    $device = 'all';
    $user_agents = [
      'm'=>'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1',
      'mobile'=>'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1',
      'd'=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36',
      'desktop'=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36'
    ];

    if($id > 0){

      $webcache = WebCache::findOrFail($id);
      list($method, $url, $type, $device) = explode(' ', $webcache->key);

    }


    $counter = 0;
    if(filter_var($url, FILTER_VALIDATE_URL)){

      if($device == 'all'){

        foreach($user_agents as $user_agent){

          Curl::to($url)
            ->withHeader("User-Agent: {$user_agent}")
            ->get();
          $counter++;

        }

      }
      else{

        $user_agent = $user_agents[$device] ?? null;

        if($user_agent){

          Curl::to($url)
            ->withHeader("User-Agent: {$user_agent}")
            ->get();
          $counter++;

        }

      }

    }

    else
      $this->error("Invalid url: {$url}");

    Log::info("Web cache load completed: {$url} ({$id})");

    $this->info("Completed, count:{$counter}");

  }
}
