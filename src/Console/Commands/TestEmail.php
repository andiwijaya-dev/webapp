<?php

namespace Andiwijaya\WebApp\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email {--to=}';

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
      $to = $this->option('to') ?? exc('Parameter to required');

      Mail::raw('This is test email, if you receive this email it means the email is setup correctly.', function($message)
        use($to){
        //$message->from(env('MAIL_FROM'), env('MAIL_FROM_NAME'));
        $message->to($to);
        $message->subject("Test email from " . env('APP_NAME'));
      });
    }
}
