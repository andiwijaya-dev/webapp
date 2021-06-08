<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduledTaskResultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('scheduled_task_result', function (Blueprint $table) {

        $table->bigIncrements('id');

        $table->bigInteger('task_id')->unsigned();

        $table->smallInteger('status');

        $table->longText('verbose')->nullable();

        $table->timestamps();
        $table->dateTime('started_at')->nullable();
        $table->dateTime('completed_at')->nullable();
        $table->double('ellapsed', 6, 3)->nullable();
        $table->integer('pid')->nullable();

        $table->foreign('task_id')->references('id')->on('scheduled_task')->onDelete('cascade')->onUpdate('cascade');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scheduled_task_result');
    }
}
