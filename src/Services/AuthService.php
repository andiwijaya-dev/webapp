<?php

namespace Andiwijaya\WebApp\Services;

use Andiwijaya\WebApp\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
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
    $this->user = Session::get('user_id') > 0 ? User::find(Session::get('user_id')) : null;
    
    if(!$this->user && Cookie::has('keep_login'))
    {
      $this->user = User::where('remember_token', Cookie::get('keep_login'))->first();
      if($this->user)
        Cookie::queue('keep_login', $this->user->remember_token, 1440);
    }
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

    if($params['keep_login'] ?? 0){
      $user->remember_token = uniqid();
      Cookie::queue('keep_login', $user->remember_token, 1440);
      $user->save();
    }

    Session::put('user_id', $user->id);
    $this->user = $user;
  }

  public function logout()
  {
    Session::pull('user_id');
    Cookie::queue('keep_login', '', -1);
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