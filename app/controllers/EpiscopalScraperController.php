<?php

class EpiscopalScraperController extends BaseController {

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

		foreach(range('a','z') as $letter) 
		{ 
		   echo '==========='.$letter.'====================='."\n\n"; 
		

			$base = 'http://www.episcopalchurch.org';
			$after = '/browse/parish/'.$letter.'?title=';
			$url = $base.$after;


			//first to get maximum number of pages
			$crawler = $goutte->request('GET', $url);

			$domSelector = '.pager-last a';
			$max = 0;
			$max = $crawler->filter($domSelector)->each(function ($node, $i) {
			
				$last_url =  explode('?',$node->attr('href'));
				parse_str($last_url[1], $output);
				$max = $output['page'];
				
				return $max;
		
			});
			if (is_array($max)) {
				if (isset($max[0])) {
					$max = $max[0];
				} else {
					$max = 0;
				}
			}

			//and then to loop through the pages
			for ($i=0; $i<=$max; $i++) {

				$url = $base.$after.'&page='.$i;
				echo "\n".$url."\n";
				echo "\n\n".$url."\n\n"; 
				$crawler = $goutte->request('GET', $url);
			
				$domSelector = '.views-table a';
				$results = $crawler->filter($domSelector)->each(function ($node, $i)  {
					//$node =  $node->first();
					$parish_url = $node->attr('href'); 
					echo $parish_url;
					$url_count = EpiscopalURL::where('parish_url', '=', $parish_url)->count();
					if ($url_count>=1) {
						echo " | ALREADY ENTERED"; echo "\n";
						
					} else {
						$episcopalurl = new EpiscopalURL;

						$episcopalurl->parish_url = $parish_url;

						$episcopalurl->save();
						echo " | INSERTED"; echo "\n";
					}
				});

				

			}
		} 

		
	}

	public function getParish() {

/*
		$urls = EpiscopalURL::all() {

		}*/

		$url = 'http://www.episcopalchurch.org/parish/bruton-parish-episcopal-church-williamsburg-va';

		$goutte = new Goutte\Client();
		$crawler = $goutte->request('GET', $url);


		$domSelector = '.field-item';
		$result = array();
		$results = $crawler->filter($domSelector)->each(function ($node, $i)  use (&$result) {
			$full_html = $node->text();
			$label_node = $node->children()->filter('.field-label-inline-first')->first();
			$label_unedited = $label_node->text();

			$label = html_entity_decode($label_unedited);
			$label = trim($label);
			$label = str_replace(':','',$label);
			$label = substr($label,0,strlen($label)-2);


			$full_html = str_replace($label_unedited,'',$full_html);
			$full_html = trim($full_html);
	

			$result[$label] = $full_html;
		});

		$result['street'] = $crawler->filter('.street-address')->first()->text();
		$result['locality'] = $crawler->filter('.locality')->first()->text();
		$result['postalcode'] = $crawler->filter('.postal-code')->first()->text();
		$result['region'] = $crawler->filter('.region')->first()->text();
		$result['country']  = $crawler->filter('.country-name')->first()->text();
		$result['map']  = $crawler->filter('.map-link a')->first()->attr('href');

		$result['name']  = $crawler->filter('h1')->first()->text();
		$result['name']  = explode(',',$result['name']);
		if (count($result['name'])>=3) {
			array_pop($result['name']); array_pop($result['name']);
		} else if (count($result['name'])>=2) {
			array_pop($result['name']);
		}
		$result['name']  = implode(',',$result['name']);
		
		$parsed_url = parse_url($result['map'], PHP_URL_QUERY);
		parse_str($parsed_url, $query_vars);
		$address_parts = explode(" ",$query_vars['q']);
		$result['lat'] = $address_parts[0];
		$result['lng'] = $address_parts[1];

		$result['episcopal_id']  = str_replace('node-','',$crawler->filter('.clearfix')->first()->attr('id'));

		foreach ($result as $key => $value) {
			unset($result[$key]);
			$key = str_replace(' ','_',strtolower(trim($key)));
			$result[$key] = trim($value);
		}

		var_dump($result);
	}

	public function scraperGetParishesInDiocese($url) {


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