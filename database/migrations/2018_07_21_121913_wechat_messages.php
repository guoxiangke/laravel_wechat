<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WechatMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->BigInteger('msg_id')->unsigned()->unique();
            $table->string('to_user_name');
            $table->string('from_user_name');
            $table->string('msg_type')->comment('image voice lik location file');
            $table->text('content')->comment('除文本外，剩下的json_encode存储');
            $table->timestamps();

            $table->index('to_user_name');
            $table->index('from_user_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wechat_messages');
    }
}
