<?php

use Illuminate\Database\Migrations\Migration;

class AddFieldsToEpiscopalParish extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('episcopal_parishes', function($table)
			{
			    $table->string('clergy',255);
			    $table->string('website',255);
			    $table->string('email',255);
			    $table->string('phone',255);
			    $table->string('twitter',255);
			    $table->string('facebook',255);
			    $table->string('diocese',255);
			    $table->string('about_us',255);
			    $table->string('street',255);
			    $table->string('locality',255);
			    $table->string('postalcode',255);
			    $table->string('region',255);
			    $table->string('country',255);
			    $table->string('map',255);
			    $table->string('name',255);
			    $table->double('lat',15,8);
			    $table->double('long',15,8);
			    $table->integer('episcopal_id');


			});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		 		$table->dropColumn('clergy');
			    $table->dropColumn('website');
			    $table->dropColumn('email');
			    $table->dropColumn('phone');
			    $table->dropColumn('twitter');
			    $table->dropColumn('facebook');
			    $table->dropColumn('diocese');
			    $table->dropColumn('about_us');
			    $table->dropColumn('street');
			    $table->dropColumn('locality');
			    $table->dropColumn('postalcode');
			    $table->dropColumn('region');
			    $table->dropColumn('country');
			    $table->dropColumn('map');
			    $table->dropColumn('name');
			    $table->dropColumn('clergy');
			    $table->dropColumn('lat');
			    $table->dropColumn('long');
			    $table->dropColumn('episcopal_id');
	}

}