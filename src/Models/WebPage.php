<?php

namespace Andiwijaya\WebApp\Models;

use Illuminate\Database\Eloquent\Model;

class WebPage extends Model
{
  protected $table = 'web_page';

  protected $fillable = [ 'title', 'meta_description', 'meta_keywords', 'footer_article',
    'og_type', 'og_title', 'og_description', 'og_image', 'og_site_name', 'og_updated_time',
    'back', 'bottom_bar', 'footer', 'header' ];
}
