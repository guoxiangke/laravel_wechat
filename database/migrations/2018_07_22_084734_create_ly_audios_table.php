<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLyAudiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ly_audios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('target');
            // $table->integer('ly_meta_id')->unsigned()->index();
            // 180805|24
            $table->integer('play_at')->unsigned()->index()->comment('节目播放日期或lts的index');
            $table->text('excerpt')->nullable()->comment('节目梗概'); //todo 全文搜索🔍
            $table->text('body')->nullable()->comment('节目图文');
            $table->json('wave')->nullable()->comment('节目波形');
            // album_id
            $table->unsignedBigInteger('album_id')->unsigned()->nullable()->default(null);
            // ALTER TABLE ly_audios ADD COLUMN album_id INT DEFAULT NULL AFTER wave;
            $table->string('slug')->nullable(); //todo unique index
            // ALTER TABLE ly_audios ADD COLUMN slug VARCHAR(255) DEFAULT NULL AFTER wave;

            $table->softDeletes();
            $table->timestamps();
            //todo
            $table->foreign('album_id')
                ->references('id')
                ->on('albums')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ly_audios');
    }
}
