<?php

    
		$dealpageArray[url] = 'http://www.adilitydeal.com/losangeles';
		$html = file_get_html($dealpageArray[url]);
		$dealpageArray[title] =  $html->find('div.deal-header h1', 0)->innertext;
		$dealpageArray[img_url]	 =  $html->find('div.img img', 0)->src;
		$dealpageArray[price]	 =  str_replace("$","",$html->find('strong.price', 0)->innertext);
		$dealpageArray[value]	=   str_replace("$","",$html->find('span.value-unit strong', 0)->innertext);
		$dealpageArray[expires] = strtotime('midnight');	
		
		$dealpageArray[source_id]	 =  "5";
		$dealpageArray[region_id]	 =  "6";
		
		$siteArray[] = $dealpageArray;

    
?>