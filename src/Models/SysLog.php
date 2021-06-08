<?php

namespace Andiwijaya\WebApp\Models;

use Andiwijaya\WebApp\Models\Traits\FilterableTrait;
use Illuminate\Database\Eloquent\Model;

class SysLog extends Model
{
  use FilterableTrait;

  const TYPE_ERROR = 1;
  const TYPE_WARNING = 2;
  const TYPE_INFO = 0;

  protected $table = 'syslog';

  protected $fillable = [ 'type', 'ref', 'ref_id', 'message', 'data', 'tag' ];

  protected $casts = [
    'data'=>'array'
  ];

  protected $filter_searchable = [
    'id:=',
    'message:like'
  ];

  public function getTypeTextAttribute(){

    switch($this->type){

      case self::TYPE_ERROR: return 'Error'; break;
      case self::TYPE_WARNING: return 'Warning'; break;
      case self::TYPE_INFO: return 'Info'; break;
    }
  }
}
