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

  protected $casts = [
    'value'=>'array'
  ];
}