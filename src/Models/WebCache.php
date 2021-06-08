<?php

namespace Andiwijaya\WebApp\Models;

use Andiwijaya\WebApp\Models\Traits\SearchableTrait;
use Illuminate\Database\Eloquent\Model;

class WebCache extends Model{

  use SearchableTrait;

  protected $table = 'web_cache';

  protected $fillable = [ 'key', 'tag' ];

  protected $searchable = [
    'tag'
  ];

}
