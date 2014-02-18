<?php

use Illuminate\Database\Migrations\Migration;

class CreateEpiscopalParish extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('episcopal_parishes',function($table) {
			$table->increments('id');
			$table->timestamps();
			$table->string('parish_name',255);
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
		Schema::drop('episcopal_parishes');
	}

}