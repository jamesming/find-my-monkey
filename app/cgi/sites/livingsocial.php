<?php


		$dealpageArray[url] = "http://livingsocial.com/deals?preferred_city=4";
		
		$html = file_get_html($dealpageArray[url] );
		$dealpageArray[title] = $html->find('title', 0)->innertext;
		$dealpageArray[img_url] = $html->find('div.grid_5', 0)->style;
		$dealpageArray[img_url] = get_string_between($dealpageArray[img_url], "url(", ")");
		$dealpageArray[price]	 =  numberOnly($html->find('div.deal-price', 0)->plaintext);
		$dealpageArray[value]	=   str_replace("$","",$html->find('span.value-unit strong', 0)->innertext);
		$dealpageArray[expires] =	strtotime('midnight + 5 hours');
		
		$dealpageArray[source_id]	 =  "4";
		$dealpageArray[region_id]	 =  "6";
		$siteArray[] = $dealpageArray;
					
		echo '<pre>';print_r($siteArray); echo '</pre>';
	

?>