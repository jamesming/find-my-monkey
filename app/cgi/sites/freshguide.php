<?php

    
		$dealpageArray[url] =  $html->find('div.slideshow a', 0)->href;
		
		$inHtml = file_get_html($dealpageArray[url]);
		
		$dealpageArray[title]	= $inHtml->find('title', 0)->innertext;
		$dealpageArray[value]	= $inHtml->find('td.savings_breakdown_val', 0)->innertext;
		$dealpageArray[price]	= $inHtml->find('td.savings_breakdown_val', 2)->innertext;
		$dealpageArray[expires]	= $inHtml->find('span.countdown_amount', 0)->innertext;
		$dealpageArray[vendor_name]	= $inHtml->find('span.name_address', 0)->innertext;
		$dealpageArray[address_all]	= $inHtml->find('span.name_address', 1)->innertext.", ".$inHtml->find('span.name_address', 2)->innertext;
		$dealpageArray[img_url]	= $inHtml->find('img.company_image', 0)->src;
		
		
		$siteArray[] = $dealpageArray;	
		
		$dealpageArray[source_id]	 =  "13";
		$dealpageArray[region_id]	 =  "6";
		
		$siteArray[] = $dealpageArray;

    
?>