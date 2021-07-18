<?php

namespace Andiwijaya\WebApp;

use Illuminate\Mail\MailServiceProvider;
use Illuminate\Support\ServiceProvider;

class CustomMailServiceProvider extends MailServiceProvider
{
    protected function registerSwiftTransport()
    {
      $this->app->singleton('swift.transport', function () {
        return new CustomTransportManager($this->app);
      });
    }
}
