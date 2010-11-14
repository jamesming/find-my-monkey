<?php
require("/var/www/html/fmm/app/Application.class.php");

class insertIntoAggregator extends Application
{
	
	public function insert( $url, $title, $img_url, $source_id, $region_id, $price, $value, $time_added, $vendor_name, $addresses,$description )
	{
		
				parent::SetQuery("SELECT * FROM `aggregate_deal` WHERE deal_url='$url'");
				$exists = parent::CountDBResults();
				if( !$exists )
				{		
					
					parent::SetQuery("INSERT INTO `aggregate_deal`
					(
					aggregate_deal_region_id,
					title, 
					deal_url, 
					img_url,
					aggregate_deal_source_id,
					date,
					price,
					value,
					time_added,
					vendor_name,
					description
					)
					VALUES (
					'{$region_id}',
					'".addslashes($title)."',
					'{$url}',
					'{$img_url}',
					$source_id,
					now(),
					$price,
					$value,
					$time_added,
					'{$vendor_name}',
					'{$description}'
					)");
					$mysql_insert_id = parent::InsertQuery();	
					
					
					if( $vendor_name != "-"){
							foreach($addresses as $address){
								
								foreach($address as $k => $v){
									
									if( $k == 'address')$address = $v;
									if( $k == 'city')$city = $v;
									if( $k == 'state')$state = $v;
									if( $k == 'zipcode')$zipcode = $v;
									if( $k == 'latitude')$latitude = $v;
									if( $k == 'longitude')$longitude = $v;
		
								}
								
								$address = addslashes($address);
								
								parent::SetQuery("INSERT INTO aggregate_deal_location (
																																				address_all,
																																				address, 
																																				city, 
																																				state, 
																																				zipcode, 
																																				latitude, 
																																				longitude, 
																																				aggregate_deal_id)
																																values(
																																				'{$address}',
																																				'{$address}',
																																				'{$city}',
																																				'{$state}',
																																				'{$zipcode}', 
																																				'{$latitude}',
																																				'{$longitude}',
																																				$mysql_insert_id
																																				) ");
								$mysql_insert_id = parent::InsertQuery();	
								
							}						
					}else{
						$mysql_insert_id = 0;
					};
					
					
					
					
				}				    
				    
	}


}

$insertIntoAggregatorObject = new insertIntoAggregator();

include($argv[1]);  // ARGUMENT IS FOR EXAMPLE: sites/groupon.php

print_r($siteArray);

foreach($siteArray as $dealpageArray){
	
	if( !isset($dealpageArray[vendor_name])  ){
		$dealpageArray[vendor_name]="-";
		$dealpageArray[addresses] = array();
	};


	$dealpageArray[description] = $insertIntoAggregatorObject->truncateThis($dealpageArray[description], $numOfWords2Limit = 200, $break=" ", $pad=" ...&nbsp;&nbsp;(cont)");
	
	$insertIntoAggregatorObject->insert($dealpageArray[url], 
																		  $dealpageArray[title], 
																		  $dealpageArray[img_url],
																		  $dealpageArray[source_id],
																		  $dealpageArray[region_id],
																		  (int)$dealpageArray[price],
																		  (int)$dealpageArray[value],
																		  (int)$dealpageArray[expires],
																		  addslashes($dealpageArray[vendor_name]),
																		  $dealpageArray[addresses],
																		  addslashes($dealpageArray[description])
																		  );
}







?>