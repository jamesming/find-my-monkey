<?php
			
		$html = file_get_html('http://www.bloomspot.com/los-angeles/');
		
		echo "test"."<br>";
		
		$count=0;

    foreach($html->find('table') as $e){
      $subhtml = str_get_html( $e->innertext );
      $innerSub =  str_get_html( $subhtml->find('div', 0)->innertext ); 
      
	      foreach($innerSub->find('a') as $f) {
	    		$dealpageArray[url] = $f->href;
				}
				
	      foreach($innerSub->find('img') as $f) {
	      	
	      	if( substr($f->src, -3) == 'jpg'){
	      		$dealpageArray[img_url] = $f->src;
	      	}else{
	      		$dealpageArray[status] =  $f->src;
	      	};
				}			
				
				$dealpageArray[source_id]	 =  "8";
				$dealpageArray[region_id]	 =  "6";
					
	    	
	    	if( $count == 7 ){  		
	    		$count = 0;
	    	};
	    	
	    	if( $count == 0 ){
	    		$siteArray[] = $dealpageArray;  
	    	};
				
    $count++;		
  	}		


		$count=0;
		foreach($siteArray as $dealpageArray){
			
			if( $dealpageArray[status] == '/media/css/images/tag-active.png' && 
					substr($dealpageArray[img_url], -3) == 'jpg'
			){
				
		    	$htmldeal = file_get_html($dealpageArray[url]);
					$siteArray[$count][title] = $htmldeal->find('title', 0)->innertext;
					
					$siteArray[$count][price]	 =  "-";
					$siteArray[$count][value]	=   "-";
					
					
			}else{
				
				unset($siteArray[$count]);
				
			};
			$count++;
		}
		
		echo '<pre>';print_r($siteArray); echo '</pre>';
				

?>