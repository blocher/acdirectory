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

	public function scraperGetParish($url,$diocese_id_num='')
	{
		


		$goutte = new Goutte\Client();
		//$url = 'https://www.theredbook.org/?event=church.view&organization_id=12589';
		//$url = 'http://acdirectory.local/test.html';
		$crawler = $goutte->request('GET', $url);


		$domSelector = '.tableInline tr';
		$clergy = array();
		$cleric = array();
		$results = $crawler->filter($domSelector)->each(function ($node, $i) use (&$clergy,&$cleric,&$columns) {
			 $node->children()->each(function ($child_node,$j) use (&$clergy,&$cleric,&$columns, $i) {
			    if ($i==0 && $j!=0) {
			    	$columns[$j] = trim(utf8_decode($child_node->text()));
			    } else if ($j!=0) {
			    	$text = trim(utf8_decode($child_node->text()));
			    	if (urlencode($text)=='%A0') {
			    		$text = '';
			    	}
				    	$column = $columns[$j];
				    	$cleric[$column] = $text;
				    	if ($column=='Name') {
				    		$grand_child_node = $child_node->children()->first();
				    		$cleric_url = $grand_child_node->attr('href');
				    		parse_str($cleric_url, $vars);
							if (isset($vars['ID'])) {
								$cleric['id'] = $vars['ID'];
							}
				    	}
				 
			    }
			 });   
			   
			 if ($i!=0 && count($cleric)!=0) {
			 	$clergy[] = $cleric;
			 }
			 $cleric = array();
		

		});

		//var_dump($clergy);

		//echo $url; die();
		$querystring = parse_url($url, PHP_URL_QUERY);
		parse_str($querystring, $vars);
		$organization_id = $vars['organization_id'];
		
		$domSelector = '.editForm tr';
		$fields = array();
		$field_name = '';
		$value = '';
		$results = $crawler->filter($domSelector)->each(function ($node, $i) use (&$fields, &$field_name, &$value) {
			 $node->children()->each(function ($child_node,$j) use (&$fields, &$field_name, &$value) {
			 	
			 	try {
	    			$field_name = $child_node->children()->eq(0)->attr('for');
	    			
				} catch (Exception $e) {
					$value = trim(utf8_decode($child_node->text()));
					//echo $value; 
					if (isset ($field_name) && $field_name !== '') {
						$fields[$field_name] = $value;
						$field_name = '';
						$value = '';
						
					}
	   				//$fields[$field_name]
				}
				

			});	
	           
	            //return $node->nodeValue;	// This is a DOMElement Object
	    });

	    $fields['clergy'] = $clergy;
	    $fields['id'] = $organization_id;
	    $fields['diocese_id_num'] = $diocese_id_num;

	   return $fields;

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