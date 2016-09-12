<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccauntsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accaunts', function (Blueprint $table) {
            $table->increments('id');
			$table->bigInteger('account_id')->unsigned();
			$table->string('screen_name', 225);
			$table->integer('refresh_interval');
			$table->integer('posts number');
			$table->text('title');
			$table->text('options');
			$table->string('status', 10);
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
        Schema::drop('accaunts');
    }
}
