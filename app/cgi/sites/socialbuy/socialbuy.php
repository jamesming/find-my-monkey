<?php


$url = 'http://www.socialbuy.com/api/v1/deals.php?public_key=8PmU4x24mU&page=los-angeles&link_id=139';


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_FAILONERROR,1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$retValue = curl_exec($ch);                      
curl_close($ch);


$jsonArray=json_decode($retValue,true);


//echo "<pre>";print_r($jsonArray);echo "</pre>";exit;
	

	$index = count($jsonArray["deals"]);
	foreach($jsonArray["deals"][$index - 1] as $array){
		foreach($array as $key => $value){
			
			// $deal[$key] = (string)$value;
			
				if($key == 'title') $dealpageArray["title"] =  (string)$value;
				if($key == 'deal_url') $dealpageArray["url"] =  (string)$value;
				if($key == 'value') $dealpageArray["value"] =  $Application->numberOnly((int)$value);
				if($key == 'price') $dealpageArray["price"] =  $Application->numberOnly((int)$value);
				if($key == 'large_image_url') $dealpageArray["img_url"] =  "http://www.socialbuy.com/images/deal/".(string)$value;
				if($key == 'end_date') $dealpageArray["expires"] =  strtotime((string)$value);
				//if($key == 'end_date') $dealpageArray[expires2] =  strtotime((string)$value);
				$dealpageArray["region_id"]	 =  "6";
				
				if($key == 'addresses') {
					
						unset($address);
						
						
						
						foreach($value as $addresses){
							
							
							foreach($addresses as $k => $v){
								
								$addressItem = array();
								$addressItem["city"] = "undefine";
								
								if($k == 'street') $addressItem["address"] =  (string)$v;
								if($k == 'city_name') $addressItem["city"] =  (string)$v;
								if($k == 'state_name') $addressItem["state"] =  (string)$v;
								if($k == 'zip') $addressItem["zipcode"] =  (string)$v;
								
								$geo = $Application->getGeoCoord($addressItem["address"].",".$addressItem["city"].",".$addressItem["state"].",".$addressItem["zipcode"]);
								$addressItem["longitude"] = 	$geo['lng'];
								$addressItem["latitude"] 	= $geo['lat'];								
								
								$addressObject = $addressItem;  
								
//							echo $k."--".$v."<br>";

							}

							
							$address[] = $addressObject;
						}
						
						
						$dealpageArray["addresses"] = $address;
					
				};
				
				
			$dealpageArray["source_id"]	 =  "3";
			$dealpageArray["region_id"]	 =  "6";
				
		}
		
		$siteArray[] = $dealpageArray;
		
	}


?>