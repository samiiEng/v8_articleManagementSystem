<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->unsignedInteger('national_code')->unique();
            $table->unsignedInteger('personnel_code')->unique()->nullable();
            $table->string('username', 50)->unique();
            $table->string('password');
            $table->string('email')->unique();
            $table->timestampTZ('email_verified_at')->nullable();
            $table->integer('phone_number');
            $table->timestampTZ('phone_number_verified_at');
            $table->string('avatar_image_path')->nullable();
            $table->string('role', 50)->nullable();
            $table->unsignedInteger('department_ref_id');
            $table->json('extra')->nullable();
            $table->boolean('is_normal');
            $table->boolean('is_active')->default(0);
            $table->rememberToken();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
