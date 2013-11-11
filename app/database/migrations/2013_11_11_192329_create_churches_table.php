<?php

use Illuminate\Database\Migrations\Migration;

class CreateChurchesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('churches', function ($table)
		{
			$table->increments('id');
			$table->string('name',256);
			$table->string('address1',256);
			$table->string('address2',256);
			$table->string('city',256);
			$table->string('state',2);
			$table->string('zip',10);
			$table->float('lat');
			$table->float('lng');
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
		Schema::drop('churches');
	}

}