<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWechatAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->index()->comment('公号名称');
            $table->string('description')->nullable()->comment('公号简介');
            $table->string('to_user_name')->index()->comment('公号原始gh_ID');
            $table->string('app_id')->comment('开发者ID(AppID)');
            $table->string('secret')->comment('开发者密码(AppSecret)');
            $table->string('token')->nullable()->comment('令牌(Token)');
            $table->string('aes_key')->nullable()->comment('消息加解密密钥(EncodingAESKey)');
            $table->boolean('is_certified')->default(false)->comment('微信是否认证，默认：否');
            //blob for serialize data
            $table->json('menu')->nullable()->comment('menu json');
            $table->json('resources')->nullable()->comment('json：开启的资源');
            $table->string('image_qr')->nullable();
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
        Schema::dropIfExists('wechat_accounts');
    }
}
