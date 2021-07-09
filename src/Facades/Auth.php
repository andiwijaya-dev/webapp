<?php

namespace Andiwijaya\WebApp\Facades;

use Illuminate\Support\Facades\Facade;

class Auth extends Facade{

  protected static function getFacadeAccessor(){ return 'Auth'; }

}