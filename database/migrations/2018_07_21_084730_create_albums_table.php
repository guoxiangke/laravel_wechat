<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlbumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('albums', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('image')->nullable(); //专辑封面
            //推送方式 记录在订阅中: 音频发送 or 图文发送
            //or 达最大次数后,微信通知类型: 您的[每日亲近神]内容01/20已经预备好, 请您回复Z1001获取今日内容.
            $table->string('title')->comment('专辑名字'); //可被订阅的图文专辑Post
            $table->text('excerpt');
            $table->text('body');
            $table->integer('price')->unsigned()->default(0)->comment('单位分');
            $table->integer('ori_price')->unsigned()->default(0)->comment('单位分');
            $table->timestamp('expire_at')->nullable(); //限时促销价格过期时间
            $table->unsignedBigInteger('category_id')->unsigned()->nullable()->comment('专辑所属子分类：亲子教育\曼曼成长记');
            $table->smallInteger('count')
                ->unsigned()
                ->default(0)
                ->comment('包含内容数量,0为每天更新=无限/365');
            $table->string('url')->nullable()->comment('专辑来源链接');

            $table->unsignedBigInteger('lymeta_id')->nullable()->unsigned(); //ee——id album_id
            // audio_only
            // ALTER TABLE albums ADD COLUMN audio_only TinyInt(1) DEFAULT NULL AFTER lymeta_id;
            // active
            // ALTER TABLE albums ADD COLUMN active TinyInt(1) DEFAULT NULL AFTER audio_only;
            $table->boolean('audio_only')->nullable()->comment('只有音频'); //done or cancled
            $table->boolean('active')->nullable()->comment('active');
            $table->string('rrule')->nullable()->comment('日期规则 for 良友小专辑LyMeta/');

            $table->unsignedBigInteger('author_id')->unsigned()->nullable()->comment('来源公众号uid');
            //wxid@mp role=>mpaccount
            $table->unsignedBigInteger('user_id')->unsigned()->comment('创建者');
            $table->unsignedBigInteger('modified_id')->unsigned()->comment('最后改动者');

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
        Schema::dropIfExists('albums');
    }
}
