<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
			$table->increments('id');
			$table->bigInteger('account_id')->unsigned();
			$table->bigInteger('post_id');
			$table->string('title', 225);
			$table->string('datetime', 225);
			$table->string('description', 225);
			$table->integer('num_favorites');
			$table->integer('num_replies');
			$table->integer('num_retweets');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('posts');
    }
}
