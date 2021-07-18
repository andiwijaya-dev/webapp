<?php

namespace Andiwijaya\WebApp\Models;

use Andiwijaya\WebApp\Models\Traits\LoggedTraitV3;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
  use LoggedTraitV3;

  public const NOTIFICATION_EMAIL = 1;
  public const NOTIFICATION_WHATSAPP = 2;

  protected $table = 'config';

  protected $fillable = [ 'key', 'value' ];

  public static function get($key, $default_value = '')
  {
    return Config::where('key', $key)->first()->value ?? $default_value;
  }
}