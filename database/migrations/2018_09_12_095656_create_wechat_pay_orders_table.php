<?php

use App\Models\Page;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWechatPayOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_pay_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->unsigned()->index();
            $table->morphs('target'); //支付关联模型 //album:albumId post:postId page:pageId
            $table->string('body')->default('支付描述');
            $table->string('out_trade_no')->index();
            $table->integer('total_fee')->unsigned()->default(0)->comment('单位分');
            $table->string('trade_type')->default('JSAPI'); //'JSAPI', //JSAPI，NATIVE，APP...
            $table->string('prepay_id')->nullable();
            $table->boolean('success')->default(false);
            $table->string('transaction_id')->nullable();
            $table->string('bank_type')->nullable();

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
        Schema::dropIfExists('wechat_pay_orders');
    }
}
