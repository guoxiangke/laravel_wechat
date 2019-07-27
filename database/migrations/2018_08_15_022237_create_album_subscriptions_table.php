<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlbumSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('album_subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->unsigned()->index();
            $table->string('wechat_account_id')->comment('gh_id');
            $table->morphs('target');
            // smallInteger 65536
            $table->smallInteger('price')->default(0)->comment('实际订阅价格，单位分');
            $table->string('recommenders')->nullable()->comment('推荐的用户');
            // ALTER TABLE album_subscriptions ADD COLUMN recommenders VARCHAR(255) DEFAULT NULL AFTER price;
            $table->unsignedBigInteger('wechat_pay_order_id')->nullable()->unsigned()->index();
            // ALTER TABLE album_subscriptions ADD COLUMN wechat_pay_order_id INT DEFAULT NULL AFTER price;
            $table->smallInteger('count')->unsigned()->default(0)->comment('++成功推送次数');
            $table->boolean('active')->default(true);//done or cancled
            // ALTER TABLE album_subscriptions ADD COLUMN active TinyInt(1) NOT NULL DEFAULT 1 AFTER count;
            $table->smallInteger('push_type')->unsigned()->default(0)->comment('推送方式音频music、单图文news');

            //高级会员订阅,自定义发送时间
            $table->tinyInteger('send_at')->unsigned()->default('6')->comment('发送时间:5-22');
            $table->string('rrule')->nullable();
            // ALTER TABLE album_subscriptions ADD COLUMN rrule VARCHAR(255) AFTER send_at;

            $table->softDeletes();//失效时间：取消订阅或订阅结束
            $table->timestamps();//生效时间

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');//
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('album_subscriptions');
    }
}
