<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('message');
            $table->morphs('pointable');
            // $table->morphs('ref');
            $table->double('amount');
            $table->double('current');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('point_transactions');
    }
}
