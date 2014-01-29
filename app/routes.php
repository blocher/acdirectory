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

