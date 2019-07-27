<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLyMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ly_metas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('author')->nullable()->comment('节目主持人、分割');
            $table->string('description')->nullable();
            $table->string('code')->comment('节目代码ee')->unique();
            $table->string('day')->nullable()->comment('周几播出');
            $table->smallInteger('index')->nullable()->comment('节目编号6XX')->unique();
            $table->smallInteger('ly_index')->nullable()->comment('良友微信编号10X')->unique();
            $table->unsignedTinyInteger('category')->default(0)->comment('节目分类');
            $table->string('image')->comment('节目封面');
            $table->timestamp('stop_play_at')->nullable()->comment('节目停播时间');

            $table->softDeletes();
            $table->timestamps();

            $table->index('code');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ly_metas');
    }
}
