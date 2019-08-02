<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLyLtsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ly_lts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('image')->comment('节目封面');
            $table->string('author')->nullable()->comment('授课老师、分割');
            $table->string('code')->comment('课程命名前缀vfe0')->unique();
            $table->unsignedTinyInteger('count')->comment('课程数量');
            $table->smallInteger('index')->nullable()->comment('课程编号#101-999')->unique();
            $table->unsignedTinyInteger('category')->default(0)->comment('课程分类');
            $table->integer('weight')->default(0);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ly_lts');
    }
}
