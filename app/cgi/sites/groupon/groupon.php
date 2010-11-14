<?php



$url = 'http://www.groupon.com/api/v1/los-angeles/deals?format=json';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_FAILONERROR,1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$retValue = curl_exec($ch);                      
curl_close($ch);

$jsonArray=json_decode($retValue,true);

//echo '<pre>';print_r(  $jsonArray   );echo '</pre>';  exit;

foreach($jsonArray["deals"] as $arrays){

			$dealpageArray["img_url"]	 = $arrays["medium_image_url"];
			$dealpageArray["title"]	= $arrays["title"];
			$dealpageArray["price"]	=  $Application->numberOnly($arrays["price"])/100;
			$dealpageArray["value"]	=  $Application->numberOnly($arrays["value"])/100;

			$dealpageArray["vendor_name"]	= $arrays["vendor_name"];
			$dealpageArray["url"] =  $arrays["deal_url"];			
		
/*			$html = file_get_html( $dealpageArray["url"] );	
			$dealpageArray[address_all] =	$html->find('li.street-address', 0)->innertext;
			
				unset($addressItem, $address);
			
				$addressItem[address] =  $array[address];
        $addressObject = $addressItem; 
        $address[] = $addressObject;
        
				$dealpageArray[addresses] = $address; 
			
			
				$dealpageArray[address_all]	= $array[address];
				

			
			$dealpageArray[url] = str_replace("https","http", $dealpageArray[url]);
			$geo = $Application->getGeoCoord( $dealpageArray[address_all] );
			$dealpageArray[longitude] = 	$geo['lng'];
			$dealpageArray[latitude] 	= $geo['lat'];
	*/
			$dealpageArray["expires"] =  $arrays["end_date"];
	
			$dealpageArray["source_id"]	 =  "1";
			$dealpageArray["region_id"]	 =  "6";
			
			$siteArray[] = $dealpageArray;			
		
	
}



//foreach($siteArray as $dealpageArray){
//
//			$html = file_get_html( $dealpageArray["url"] );	
//			echo $html->find('li.street-address', 0)->innertext;
//			unset($html);
//
//	
//			$dealpageArray["description"]	= $arrays["full_description"];
//}

    
?>