<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebEvent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_event', function (Blueprint $table) {

          $table->bigIncrements('id');

          $table->string('session_id')->nullable();
          $table->bigInteger('user_id')->unsigned()->nullable();
          $table->string('remote_addr')->nullable();
          $table->text('user_agent')->nullable();

          $table->string('event');

          $table->text('value1')->nullable();
          $table->text('value2')->nullable();
          $table->text('value3')->nullable();
          $table->text('value4')->nullable();
          $table->text('value5')->nullable();

          $table->timestamps();

          $table->index('session_id', 'web_event|index|session_id');
          $table->index('user_id', 'web_event|index|user_id');
          $table->index('event', 'web_event|index|event');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('web_event');
    }
}
