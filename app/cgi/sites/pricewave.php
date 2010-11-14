
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
					
		echo '<pre>';print_r($siteArray); echo '</pre>';
		
		exit;
		
		
		
		
		exit;
		$dealpageArray[price]	= $html->find('a.buy_now', 0)->innertext;
		$dealpageArray[value]	= $html->find('div#info big', 0)->innertext;
		$dealpageArray[expires]	= $html->find('div#cd', 0)->innertext;
		$dealpageArray[vendor_name]	= $html->find('strong', 0)->innertext;
		$dealpageArray[address_all]	= $html->find('table tbody tr td', 0)->innertext;
		$siteArray[] = $dealpageArray;	
					
		echo '<pre>';print_r($siteArray); echo '</pre>';

		exit;
		$switch = 0;
		foreach($html->find('div.image-wrapper a') as $e){
		
		unset($dealpageArray);
		
		if($switch){
			$switch=0;
			$dealpageArray[url]	= 'http://homerun.com'.$e->href;
			
			$inHtml = file_get_html($dealpageArray[url]);
			
			$dealpageArray[title]	= str_replace("HomeRun.com :: ","", $inHtml->find('title', 0)->innertext);
			$dealpageArray[price]	= numberOnly(str_replace("Buy Now $","", $inHtml->find('button', 0)->innertext));
			$dealpageArray[value]	= stripAllNonNumerical($inHtml->find('td.val span', 0)->innertext);
			$dealpageArray[vendor_name]	= $inHtml->find('div.spot div', 0)->innertext;
			$dealpageArray[address_all]	= $inHtml->find('div.spot div', 1)->innertext;
			$dealpageArray[img_url]	 = $inHtml->find('img.deal', 0)->src;
			
			$geo = $Application->getGeoCoord($dealpageArray[address]);
			$dealpageArray[longitude] = 	$geo['lng'];
			$dealpageArray[latitude] 	= $geo['lat'];
			
			
			
			$dealpageArray[source_id]	 =  "12";
			$dealpageArray[location_id]	 =  "6";
			$siteArray[] = $dealpageArray;				
			}
			else {
			$switch=1;
			};
			
		
			
			
//		$dealpageArray[expires] =	strtotime('midnight');
//					


		};

		
					
		echo '<pre>';print_r($siteArray); echo '</pre>';


?>