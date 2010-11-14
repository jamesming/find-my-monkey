<?php

		$html = file_get_html('http://www.screamincoupons.com/deals/');
		
    foreach(  $html->find('div.box_part4 a'  ) as $e){
    	
    	$dealpageArray[url]  = "http://www.screamincoupons.com".$e->href;
    	
			$html2 = file_get_html($dealpageArray[url]);
		
			$dealpageArray[title] =  $html2->find('div#dealTitle', 0)->innertext;
			$dealpageArray[img_url]	 =  "http://www.screamincoupons.com".$html2->find('div.dealDetail_left_content img', 0)->src;
			
			$dealpageArray[price]	 =  (int)str_replace("$","",$html2->find('td#dealValues_price', 0)->innertext);
			$dealpageArray[value]	 =  (int)str_replace("$","",$html2->find('td#dealValues_price', 0)->innertext) + (int)str_replace("$","",$html2->find('td#dealValues_savings', 0)->innertext);
			$dealpageArray[expires] =	strtotime('midnight');	
			
//			foreach(  $html2->find('table#_ctl0_holderMainBody_ucDealDetail_lstLocations') as $f){
//				
//				$dealpageArray[vendor] =  $f->innertext;
//				
//			};
			
			$dealpageArray[source_id]	 =  "11";
			$dealpageArray[region_id]	 =  "6";
    	
    	$siteArray[] = $dealpageArray;
    	
		}
	
	
	
?>
