<?php

class ScraperController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public function scraperGetParishes()
	{
		


		$goutte = new Goutte\Client();
		$url = 'http://www.episcopalchurch.org/browse/parish/a?title=';
		$crawler = $goutte->request('GET', $url);


		$domSelector = '.views-table tr';
		$results = $crawler->filter($domSelector)->each(function ($node, $i)  {
			echo $node->first()->text();
		});

		
	}

	public function scraperGetParishesInDiocese($url) {

		$querystring = parse_url($url, PHP_URL_QUERY);
		parse_str($querystring, $vars);
		$diocese_id = $vars['ID'];


		$goutte = new Goutte\Client();
		$crawler = $goutte->request('GET', $url);


		$domSelector = '.tableInline tr td a';
		
		$results = $crawler->filter($domSelector)->each(function ($node, $i) use ($diocese_id) {
			$temp = array();
			$temp['url'] = 'https://www.theredbook.org/'.$node->attr('href');
			$temp['name'] = trim(utf8_decode($node->text()));
			$temp['diocese_id_num'] = $diocese_id;
			$querystring = parse_url($temp['url'], PHP_URL_QUERY);
			parse_str($querystring, $vars);
			if (isset($vars['organization_id'])) {
				$temp['id'] = $vars['organization_id'];
				return $temp;
			}
			
		});
		$results = array_filter($results);
		return $results;

	}


	public function scraperGetDiocesesOnePage($url)
	{
		$goutte = new Goutte\Client();
		$crawler = $goutte->request('GET', $url);

		$domSelector = '.tableInline td a';
		
		$results = $crawler->filter($domSelector)->each(function ($node, $i)  {
			$temp = array();
			$temp['url'] = 'https://www.theredbook.org'.$node->attr('href');
			$temp['name'] = trim(utf8_decode($node->text()));
			$querystring = parse_url($temp['url'], PHP_URL_QUERY);
			parse_str($querystring, $vars);
			$temp['id'] = $vars['ID'];
			//$temp['diocese_id'] = $diocese_id;
			return $temp;
			
		});
		return $results;
	}

	public function scraperGetDioceses() {

		$url = 'https://www.theredbook.org/?event=diocese.manage';
		$page1 = $this->scraperGetDiocesesOnePage($url);
		$url = "https://www.theredbook.org/?event=diocese.manage&startRow=101";
		$page2 = $this->scraperGetDiocesesOnePage($url);
		$result = array_merge ($page1,$page2);
		return $result;
	}

	public function ScraperFilterParishes($parishes) {

		//wow this is bad code -- clean it up
		// can it all be done with the query?

		$ids = array();
		foreach ($parishes as $parish) {
			$ids[] = $parish['id'];
		}
		$existing_ids = DB::table('parishes')->lists('id');
		$different = array_diff($ids,$existing_ids);
		$final_parishes = array();

		foreach ($parishes as $parish) {
			if (in_array($parish['id'],$different)) {
				$final_parishes[] = $parish;
			}
		}

		return $final_parishes;
	}

	public function ScraperScrapeAway() {
		$i=0;
		$dioceses = $this->scraperGetDioceses();
		foreach ($dioceses as $diocese) {
			echo "\n\n".'###################'.$diocese['id'].' '.$diocese['name'].' beginning ##############'."\n\n";
			$parishes = $this->scraperGetParishesInDiocese($diocese['url']);
			$parishes = $this->scraperFilterParishes($parishes);
			
			foreach ($parishes as $parish) {
				$i++;
				//if ($i>5) exit();
				$parish = $this->scraperGetParish($parish['url'],$parish['diocese_id_num']);
				$parish_object = Parish::find($parish['id']);
				if ($parish_object!=null) {
					if (!isset($parish['organization_name'])) {
						$parish['organization_name']='NA';
					}
					echo $parish['id'].' '.$parish['organization_name'].' already entered '."\n";
					usleep(300000);
					continue;
				}
				$parish_object = new Parish();
				foreach ($parish as $key => $value) {
					if ($key=='clergy') {
						$value = json_encode($value);
					}
					$parish_object->$key = $value;
					

				}
				$parish_object->save();
				if (!isset($parish['organization_name'])) {
						$parish['organization_name']='NA';
				}
				echo $parish['id'].' '.$parish['organization_name'].' saved '."\n";
				usleep(300000);

			}
		}

	}
}