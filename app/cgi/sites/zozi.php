<?php



		$dealpageArray[url] = 'http://www.zozi.com/los-angeles';
		$html = file_get_html($dealpageArray[url]);
		$dealpageArray[title] =  $html->find('div.top_part h1', 0)->innertext;
		$dealpageArray[img_url]	 =  $html->find('div#loadarea img', 0)->src;
		$dealpageArray[price]	 =  stripAllNonNumerical($html->find('div.price', 0)->innertext);
		$dealpageArray[value]	=   stripAllNonNumerical($html->find('div.price', 0)->innertext) + stripAllNonNumerical($html->find('div.savings', 0)->innertext);
		$dealpageArray[expires] =	 strtotime($html->find('div#countdown_end_date', 0)->innertext);
//		$dealpageArray[vendor] =	 $html->find('a.google_map', 0)->href;
		$dealpageArray[source_id]	 =  "9";
		$dealpageArray[region_id]	 =  "6";
		
		$siteArray[] = $dealpageArray;
		
		$siteArray[] = $dealpageArray;

	
?>