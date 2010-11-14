<?php    

		$toppage = 'http://www.pricewave.com/';
		$html = file_get_html($toppage);
		
		$dealpageArray[title]	= $html->find('div#deal_title', 0)->innertext;
		$dealpageArray[price]	= $html->find('div#price_value', 0)->innertext;
		$dealpageArray[value]	= $html->find('td.font11bold', 0)->innertext;
		$dealpageArray[expires]	= $html->find('td#days', 1)->innertext;
		$dealpageArray[vendor_name]	= $html->find('strong', 0)->innertext;
		$dealpageArray[address_all]	= $html->find('div#purchase_summary', 0)->innertext;
		$siteArray[] = $dealpageArray;	
	
		$dealpageArray[source_id]	 =  "15";
		$dealpageArray[location_id]	 =  "6";
			
		$siteArray[] = $dealpageArray;			

 ?>

    