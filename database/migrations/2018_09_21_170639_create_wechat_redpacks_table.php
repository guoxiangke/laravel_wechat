<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWechatRedpacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_redpacks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('return_code');
            $table->string('return_msg');
            $table->string('result_code');
            $table->string('err_code');
            $table->string('err_code_des');
            $table->string('mch_billno')->index();
            $table->string('mch_id');
            $table->string('wxappid');
            $table->string('re_openid')->index();
            $table->integer('total_amount')->unsigned();
            $table->string('send_listid')->nullable();

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
        Schema::dropIfExists('wechat_redpacks');
    }
}
