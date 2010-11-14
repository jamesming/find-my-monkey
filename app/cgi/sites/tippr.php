<?php



$url = 'http://tippr.com/api/v2/offers/?apikey=b36a9a1c3fdcd3432d3bbcb2c01dc6e8&format=xml&channel=tippr-los-angeles&publisher=tippr';

$tipprDeals = simplexml_load_file($url);
//echo '<pre>';print_r($tipprDeals); echo '</pre>';
//exit;

foreach($tipprDeals as $tipprDeal){
	
	foreach($tipprDeal as $key => $value){
		
		$deal[$key] = (string)$value;
		
		if($key == 'headline') $dealpageArray[title] =  (string)$value;
		if($key == 'url') $dealpageArray[url] =  (string)$value;
		if($key == 'value') $dealpageArray[value] =  (string)$value;
		if($key == 'price') $dealpageArray[price] =  (string)$value;
		if($key == 'large_image_url') $dealpageArray[img_url] =  (string)$value;
		if($key == 'end_date') $dealpageArray[expires] =  strtotime((string)$value);
		
		if($key == 'merchant'){
		
        $merchant =$value->children();
        foreach ($merchant as $k => $v){
        	if( $k == 'name'){
        			$dealpageArray[vendor_name] =  (string)$v;
        	};
        }

		}
		
		if($key == 'locations'){
		
        $locations = $value->children();
        foreach ($locations as $location){
        	unset($address);
        	foreach ($location as $k => $v){
	        	if( $k == 'address')$addressItem[address] =  (string)$v;
	        	if( $k == 'city') $addressItem[city] =  (string)$v;
	        	if( $k == 'state')$addressItem[state] =  (string)$v;
	        	if( $k == 'zipcode')$addressItem[zipcode] =  (string)$v;
        	}
					$geo = $Application->getGeoCoord($addressItem[address].",".$addressItem[city].",".$addressItem[state].",".$addressItem[zipcode]);
					$addressItem[longitude] = 	$geo['lng'];
					$addressItem[latitude] 	= $geo['lat'];
	        $addressObject = $addressItem; 
	        $address[] = $addressObject;
        }
				$dealpageArray[addresses] = $address; 
		}
		
		
		$dealpageArray[source_id]	 =  "10";
		$dealpageArray[region_id]	 =  "6";
	  $dealObject[] = $deal;		
		
	}
	$siteArray[] = $dealpageArray;

}

?>