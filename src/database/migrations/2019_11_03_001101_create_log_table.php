<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log', function (Blueprint $table) {

          $table->bigIncrements('id');
          $table->timestamps();

          $table->smallInteger('type')->nullable();
          $table->text('data')->nullable();
          $table->text('user_agent')->nullable();
          $table->string('remote_ip', 50)->nullable();
          $table->bigInteger('user_id')->unsigned()->nullable();

          $table->string('session_id')->nullable();

          $table->bigInteger('loggable_id')->unsigned()->nullable();
          $table->string('loggable_type')->nullable();

          $table->index([ 'loggable_id', 'loggable_type' ], 'log_idx_1');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('log');
    }
}
