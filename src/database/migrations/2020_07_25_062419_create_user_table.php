<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table)
        {
          $table->bigIncrements('id');

          $table->smallInteger('status');

          $table->string('code')->nullable();
          $table->string('name');
          $table->string('email')->nullable();
          $table->string('avatar_url')->nullable();
          $table->string('password')->nullable();
          $table->boolean('require_password_change')->default(0)->nullable();
          $table->boolean('is_system')->default(0);

          $table->smallInteger('role')->nullable();

          $table->text('configs')->nullable();
          $table->string('last_url')->nullable();

          $table->rememberToken();
          $table->timestamps();
          $table->dateTime('last_login_at')->nullable();
        });

        \Andiwijaya\WebApp\Models\User::insert([
          'status'=>\Andiwijaya\WebApp\Models\User::STATUS_ACTIVE,
          'code'=>'admin',
          'name'=>'admin',
          'avatar_url'=>'/images/avatars/avatar-admin.png',
          'password'=>\Illuminate\Support\Facades\Hash::make('admin'),
          'role'=>\Andiwijaya\WebApp\Models\User::ROLE_ADMIN,
          'is_system'=>1
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user');
    }
}
