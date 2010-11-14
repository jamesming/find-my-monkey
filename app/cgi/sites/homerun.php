<?php

$url = 'https://homerun-ads.s3.amazonaws.com/live_deals.json';

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

foreach($jsonArray as $regionArrays){
	
	if( $regionArrays[region] == 'los-angeles'){
		foreach($regionArrays[deals] as $array){

			$dealpageArray[title]	= $array[title];
			$dealpageArray[price]	=  $array[price]/100;
			$dealpageArray[value]	=  $array[value]/100;
			$dealpageArray[description]	= $array[full_description];
			$dealpageArray[vendor_name]	= addslashes($array[name]);
			
				unset($addressItem, $address);
			
				$addressItem[address] =  $array[address];
        $addressObject = $addressItem; 
        $address[] = $addressObject;
        
				$dealpageArray[addresses] = $address; 
			
			
			$dealpageArray[address_all]	= $array[address];
			$dealpageArray[img_url]	 = $array[images][0];
//			$dealpageArray[url] = str_replace("/get","?_a=fmm",$array[deal_url]);
			$dealpageArray[url] = str_replace("/get","",$array[deal_url]);
			
			$dealpageArray[url] = str_replace("https","http", $dealpageArray[url]);
			$geo = $Application->getGeoCoord( $dealpageArray[address_all] );
			$dealpageArray[longitude] = 	$geo['lng'];
			$dealpageArray[latitude] 	= $geo['lat'];
	
			$dealpageArray[expires] =  $array[end_at];
	
			$dealpageArray[source_id]	 =  "12";
			$dealpageArray[location_id]	 =  "6";
			$dealpageArray[region_id]	 =  "6";
			
			$siteArray[] = $dealpageArray;			
		}
		
	};
	

}


    
?>