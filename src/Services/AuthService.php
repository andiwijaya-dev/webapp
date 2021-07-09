<?php

namespace Andiwijaya\WebApp\Services;

use Andiwijaya\WebApp\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AuthService{

  protected $user;

  public function isAuth()
  {
    return isset($this->user->id);
  }

  public function load()
  {
    $this->user = User::find(Session::get('user_id'));
  }

  public function login(array $params)
  {
    $user_id = $params['user_id'] ?? null;
    $password = $params['password'] ?? null;
    
    $user = User::where('code', '=', $user_id)
      ->orWhere('email', '=', $user_id)
      ->first();

    if(!$user)
      throw new \Exception('User not found');

    if(!Hash::check($password, $user->password))
      throw new \Exception('Invalid password');

    Session::put('user_id', $user->id);
    $this->user = $user;
  }

  public function logout()
  {
    Session::pull('user_id');
    $this->user = null;
  }

  public function changePassword(array $params)
  {
    $validator = Validator::make(
      $params,
      [
        'password'=>'required|min:6|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%@]).*$/|confirmed',
      ]);
    if($validator->fails()) exc($validator->errors()->first());

    $this->user->password = Hash::make($params['password']);
    $this->user->save();

    return $this;
  }


  public function user()
  {
    return $this->user;
  }
}