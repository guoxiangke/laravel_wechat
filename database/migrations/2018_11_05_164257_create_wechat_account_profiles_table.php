<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWechatAccountProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_account_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nickname')->index();
            $table->string('to_user_name')->index(); //user_name
            $table->string('app_id')->nullable();
            $table->string('description')->nullable();
            $table->string('head_img_url');

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
        Schema::dropIfExists('wechat_account_profiles');
    }
}
