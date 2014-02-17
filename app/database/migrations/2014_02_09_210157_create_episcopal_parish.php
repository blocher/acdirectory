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
			/*
			$table->string('diocese_id',255);
			$table->string('legalName',255);
			$table->string('organization_name',255);
			$table->string('short_name',255);
			$table->string('country_code',24);
			$table->string('web_address',255);
			$table->string('church_type',24);
			$table->smallInteger('established');
			$table->smallInteger('no_of_communicants');
			$table->smallInteger('capacity');
			$table->string('school',10);
			$table->string('parish_day_school',10);
			$table->string ('address',256);
			$table->string('address2',256);
			$table->string('city',256);
			$table->string('county',256);
			$table->string('state',256);
			$table->string('country',256);
			$table->string('zip',24);
			$table->string('mail_address',256);
			$table->string('mail_address2',256);
			$table->string('mail_city',256);
			$table->string('mail_state',256);
			$table->string('mail_zip',24);
			$table->string('shipping_address',256);
			$table->string('shipping_address2',256);
			$table->string('shipping_city',256);
			$table->string('shipping_state',256);
			$table->string('shipping_zip',24);
			$table->string('clergy',256);
			$table->string('email',256);
			$table->integer('diocese_id_num');
			*/
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