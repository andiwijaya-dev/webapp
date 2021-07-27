<?php

namespace Andiwijaya\WebApp\Exceptions;

use Throwable;

class UserException extends \Exception{

  protected $errors;
  
  public function __construct($message = "", array $errors = [], $code = 0, Throwable $previous = null)
  {
    $this->errors = $errors;
    
    parent::__construct($message, $code, $previous);
  }
  
  public function getErrors()
  {
    return $this->errors;
  }
}