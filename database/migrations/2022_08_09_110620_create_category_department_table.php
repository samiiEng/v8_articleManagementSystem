<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_department', function (Blueprint $table) {
            $table->id('category_department_id');
            $table->unsignedBigInteger('department_ref_id');
            $table->unsignedBigInteger('category_ref_id');
            $table->unique(['department_ref_id', 'category_ref_id'], 'category_department_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_department');
    }
};
