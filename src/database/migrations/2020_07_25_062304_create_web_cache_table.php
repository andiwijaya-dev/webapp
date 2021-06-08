<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebCacheTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('web_cache', function (Blueprint $table) {

        $table->string('key');
        $table->text('tag')->nullable();

        $table->text('created_ua')->nullable();
        $table->string('created_ip', 50)->nullable();

        $table->timestamps();

        $table->primary([ 'key' ]);

      });

      DB::statement('ALTER TABLE web_cache ADD FULLTEXT cached_index_0 (tag)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('web_cache');
    }
}
