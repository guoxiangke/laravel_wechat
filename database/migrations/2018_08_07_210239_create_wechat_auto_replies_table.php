<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWechatAutoRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_auto_replies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('规则名字/简单描述');
            $table->string('to_user_name')->comment('对应的公众号gh_id,gh_all==ALL');
            $table->enum('type', ['Text', 'Image', 'Video', 'Voice', 'News', 'Transfer'])->default('Text');
            $table->string('patten')->comment('正则表达式,多个用行分隔，subscribe类型为 关注时回复');
            // todo 关注时回复内容
            $table->text('content')->nullable()->comment('回复内容');
            $table->smallInteger('weight')->default(0)->comment('权重'); //0-65536

            $table->softDeletes();
            $table->timestamps();

            $table->index('to_user_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wechat_auto_replies');
    }
}
