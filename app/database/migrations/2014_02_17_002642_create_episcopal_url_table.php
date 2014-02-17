<?php

use Illuminate\Database\Migrations\Migration;

class CreateEpiscopalUrlTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('episcopal_urls',function($table) {
			$table->increments('id');
			$table->timestamps();
			$table->string('parish_url',255);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('episcopal_urls');
	}

}