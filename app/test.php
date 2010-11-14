<?php     


 $url = "http://livingsocial.com/deals?preferred_city=4"; 
 $input = @file_get_contents($url) or die("Could not access file: $url"); 
 $regexp = "<a\s[^>]*href=(\"??)(http:[^\" >]*?)\\1[^>]*>(.*)<\/a>"; 
 
 
 
 if(preg_match_all("/$regexp/siU", $input, $matches)) { 
 	# $matches[2] = array of link addresses 
 	# $matches[3] = array of link text - including HTML code 
 }
 
		echo '<pre>';
    print_r($matches);
    echo '</pre>';
 
//
//		$data_feed = file_get_contents( "http://livingsocial.com/deals?preferred_city=4" );
//		// echo $data_feed;
//		
//		$rows=explode("/n",$data_feed); 
//		
//		echo '<pre>';
//    print_r($rows);
//    echo '</pre>';

?>