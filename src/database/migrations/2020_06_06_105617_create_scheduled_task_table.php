<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduledTaskTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('scheduled_task', function (Blueprint $table) {

      $table->bigIncrements('id');

      $table->smallInteger('status');
      $table->string('description', 100);
      $table->bigInteger('creator_id')->unsigned()->nullable()->default(0);
      $table->text('command');
      $table->smallInteger('repeat');

      $table->dateTime('start')->nullable();
      $table->text('repeat_custom')->nullable();

      $table->integer('count')->nullable()->default(0);
      $table->integer('error')->nullable()->default(0);
      $table->boolean('remove_after_completed')->nullable()->default(0);

      $table->timestamps();
      $table->timestamp('last_run_at')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('scheduled_task');
  }
}
