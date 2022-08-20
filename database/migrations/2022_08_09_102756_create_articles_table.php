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
        Schema::create('articles', function (Blueprint $table) {
            $table->id('article_id');
            $table->unsignedBigInteger('article_code')->unique();
            $table->string('title', 100)->nullable();
            $table->longText('body')->nullable();
            $table->string('category_department_ref_id', 50)->nullable();
            $table->string('tag_ref_id', 50)->nullable();
            $table->unsignedBigInteger('user_ref_id');
            $table->json('contributors_ref_id')->nullable()->comment("pairs of contributorID/articleID");
            $table->string('waiting_contributors_ref_id', 150)->nullable();
            $table->string('rejected_contributors_ref_id', 150)->nullable();
            $table->json('invitation_messages_ref_id')->nullable()->comment("It's constructed of pairs of contributorID/messageID.");
            $table->string('published_articles_ref_id', 150)->nullable()->comment('The id of published articles that are used in this article');
            $table->string('parent_ref_id')->nullable();
            $table->unsignedBigInteger('revision_ref_id')->nullable();
            $table->unsignedInteger('revision_number')->nullable();
            $table->unique(['revision_ref_id', 'revision_number']);
            $table->dateTimeTz('publish_date')->nullable();
            $table->boolean('is_last_revision');
            $table->string('status', 50);
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
        Schema::dropIfExists('articles');
    }
};
