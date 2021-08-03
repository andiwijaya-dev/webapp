<?php

namespace Andiwijaya\WebApp;

use Andiwijaya\WebApp\Models\Config;
use Illuminate\Mail\TransportManager;

class CustomTransportManager extends TransportManager{

  public function __construct($app)
  {
    $this->app = $app;

    $configs = Config::where('key', 'like', 'smtp%')->get()->groupBy('key');

    $mail = [
      'driver'        => config('mail.driver'),
      'host'          => $configs['smtp.host'][0]->value ?? config('mail.host'),
      'port'          => $configs['smtp.port'][0]->value ?? config('mail.port'),
      'from'          => [
        'address'   => config('mail.from.address'),
        'name'      => config('mail.from.name')
      ],
      'encryption'    => $configs['smtp.encryption'][0]->value ?? config('mail.encryption'),
      'username'      => $configs['smtp.username'][0]->value ?? config('mail.username'),
      'password'      => $configs['smtp.password'][0]->value ?? config('mail.password'),
      'sendmail'      => config('mail.sendmail'),
      'pretend'       => config('mail.pretend')
    ];

    if(isset($configs['smtp.reply_to']))
      $mail['reply_to'] = [
        'address'=>$configs['smtp.reply_to'][0]->value,
        'name'=> config('mail.from.name')
      ];

    $this->app['config']['mail'] = $mail;
  }

}