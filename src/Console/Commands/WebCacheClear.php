<?php

namespace Andiwijaya\WebApp\Console\Commands;

use Andiwijaya\WebApp\Facades\WebCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class WebCacheClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'web-cache:clear {--key=} {--tag=} {--recache} {--background=0}';

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
      $key = $this->option('key');
      $tag = $this->option('tag');
      $recache = $this->option('recache');
      $background = $this->option('background');

      if($background){

        $process = new Process("php artisan web-cache:clear --key={$key} --tag={$tag}" . ($recache ? ' --recache' : '') . " > /dev/null 2>&1 &", base_path());
        $process->setTimeout(3600);
        $process->run();

      }
      else{

        if(strlen($key) > 0)
          WebCache::clearByKey($key, $recache);

        else if(strlen($tag) > 0)
          WebCache::clearByTag($tag, $recache);

        else
          WebCache::clearAll($this->option('recache'));

      }

    }
}
