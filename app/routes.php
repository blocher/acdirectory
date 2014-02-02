<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('hello');
});

Route::get('/diocese', function() {
	$scraper = new ScraperController();
	var_dump($scraper->scraperDiocese());
});

Route::get('/test', function()
{
	$scraper = new ScraperController();
	var_dump($scraper->scraperParish());
	
});

Route::get('/dioceseparishes', function()
{
	$scraper = new ScraperController();
	var_dump($scraper->scraperDioceseParishes());
	
});

Route::get('/scrape', function()
{
	$scraper = new ScraperController();
	var_dump($scraper->ScraperScrapeAway());
	
});

Route::get('/geocode', function()
{
	$key = 'a8b62bb12b5bf561ab16585eee29952282bfbf5';
	$data = Geocodio::get('2430 K St NW; Washington, DC', $key);
	var_dump($data->response->results);
	
});

Route::get('/geocodemany', function()
{
	$key = 'a8b62bb12b5bf561ab16585eee29952282bfbf5';
	
	$data = array(
  		'54'=>'mkkln',
  		'test'=>'2430 K St NW Washington DC'
	);
	
	$results = Parish::get(array('id','address','address2','city','state','zip','country'));

	$parishes = array();
	foreach ($results as $result) {
		$address = 
			$result->address . ' ' .
			$result->address2 . ' ' .
			$result->city . ' ' .
			$result->state . ' ' .
			substr($result->zip,0,5);
		$parishes[$result->id] = $address;
	}

	
	$parishes = array_slice($parishes,0,10);
	var_dump($parishes);
	
	$data = Geocodio::geocode($parishes, $key);
	$data = $data->response->results;
	foreach ($data as $datum) {
		if (isset($datum->response->results)) {
			var_dump($datum->response->results);
		}
	}
});