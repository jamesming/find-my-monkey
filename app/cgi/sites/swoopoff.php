<?php



		$dealpageArray[url] = 'http://www.swoopoff.com/deal/3-los-angeles-westside';
		$html = file_get_html($dealpageArray[url]);
		$dealpageArray[title] =  $html->find('title', 0)->innertext;
		$dealpageArray[img_url]	 =  $html->find('div.banner img', 0)->src;
		
		$dealpageArray[price]	 =  str_replace("$","",$html->find('input.addtocart', 0)->value);
		$dealpageArray[value]	=   str_replace("$","",$html->find('div.value', 0)->innertext);
		$dealpageArray[expires] =	strtotime($html->find('span#countdown1', 0)->innertext);
		
		$dealpageArray[source_id]	 =  "6";
		$dealpageArray[region_id]	 =  "6";
		
		$siteArray[] = $dealpageArray;

	
?>