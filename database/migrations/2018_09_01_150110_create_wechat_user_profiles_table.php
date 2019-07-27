<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWechatUserProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_user_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->unsigned()->index()->unique(); //外健索引并不能唯一!
            //todo 索引index 唯一unique 和外健foreign 的作用区别?
            // ALTER TABLE `wechat_user_profiles` ADD unique(`user_id`);

            $table->string('openid');
            $table->string('subscribe')->nullable();
            $table->string('nickname')->nullable();
            $table->boolean('sex')->default(0);
            $table->string('language')->nullable();
            $table->string('city')->nullable()->index();
            $table->string('province')->nullable()->index();
            $table->string('country')->nullable();
            $table->string('headimgurl')->default('/images/getheadimg.jpeg');
            $table->unsignedInteger('subscribe_time')->nullable();
            $table->string('unionid')->nullable();
            $table->string('remark')->nullable();
            $table->smallInteger('groupid')->nullable();
            $table->string('tagid_list')->nullable();
            $table->string('subscribe_scene')->nullable();
            $table->smallInteger('qr_scene')->nullable();
            $table->string('qr_scene_str')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('wechat_user_profiles');
    }
}
