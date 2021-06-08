<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSyslogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('syslog', function (Blueprint $table) {

        $table->bigIncrements('id');

        $table->smallInteger('type'); // error, warning, info

        $table->string('ref')->nullable();
        $table->bigInteger('ref_id')->unsigned()->nullable();

        $table->string('message');
        $table->longText('data')->nullable();
        $table->text('tag')->nullable();

        $table->timestamps();
      });

      DB::statement('ALTER TABLE syslog ADD FULLTEXT syslog_ft1 (tag)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('syslog');
    }
}
