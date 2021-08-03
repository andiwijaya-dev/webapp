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
    'last_login_at'=>'datetime',
    'configs'=>'array'
  ];

  protected $fillable = [ 'status', 'code', 'name', 'email', 'avatar_url', 'require_password_change', 'role', 'configs',
    'last_login_at', 'last_url' ];

  public function preDelete()
  {
    if($this->is_system > 0)
      exc(__('Unable to remove system user'));
  }

  public function pathAllowed($path)
  {
    return true;
  }

  public function can($action){}

  public function filterActions(array $actions)
  {
    return $actions;
  }
}
