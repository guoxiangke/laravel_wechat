<?php

/** actuallymab | 12.06.2016 - 02:00 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCommentsTable extends Migration
{
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('commentable_id')->nullable();
            $table->string('commentable_type')->nullable();
            $table->index(['commentable_id', 'commentable_type']);
            $table->string('commented_id')->nullable();
            $table->string('commented_type')->nullable();
            $table->index(['commented_id', 'commented_type']);
            $table->longText('comment');
            $table->boolean('approved')->default(true);
            $table->double('rate', 15, 8)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('comments');
    }
}
