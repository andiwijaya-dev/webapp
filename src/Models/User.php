<?php

namespace Andiwijaya\WebApp\Models;

use Andiwijaya\WebApp\Models\Traits\LoggedTraitV3;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
  use LoggedTraitV3;

  protected $table = 'user';

  const STATUS_ACTIVE = 1;
  const STATUS_INACTIVE = -1;

  const ROLE_ADMIN = 1;
  const ROLE_USER = 2;

  protected $attributes = [
    'status'=>User::STATUS_ACTIVE
  ];

  protected $casts = [
    'last_login_at'=>'datetime'
  ];

  protected $fillable = [ 'status', 'code', 'name', 'email', 'avatar_url', 'require_password_change', 'role',
    'last_login_at' ];
}
