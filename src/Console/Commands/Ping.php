<?php

namespace Andiwijaya\WebApp\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Process\Process;

class Ping extends Command
{
  protected $signature = 'ping {to} {--count=4}';

  protected $description = 'Ping command';

  public function __construct()
  {
    parent::__construct();
  }

  public function handle()
  {
    $to = $this->argument('to');
    $count = $this->option('count');

    if(!$to) exc('Parameter to required');

    exec("ping {$to} -c {$count} 2>&1", $output, $return_var);

    $this->info(implode("\n", $output));

    return $return_var;
  }
}
