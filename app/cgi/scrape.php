<?php
require("../Application.class.php");
include('../simplehtmldom/simple_html_dom.php');

//				$pattern = "/src=[\"']?([^\"']?.*(png|jpg|gif))[\"']?/i";
//				preg_match_all($pattern, $deal["description"], $images);
// 				strtotime( $item->pubDate )

//  str_replace(" ", "%20", $sting)


//$str = 'midnight - 1 day';
// $str = 'midnight';
$str = 'midnight + 3 day';


if (($timestamp = strtotime($str)) === false) {
    echo "The string ($str) is bogus";
} else {
    echo "$str == " . date('l dS \o\f F Y h:i:s A', $timestamp)." -- " .  strtotime($str);
}

// date('l dS \o\f F Y h:i:s A', strtotime($string))

// strtotime('midnight');
// date('l dS \o\f F Y h:i:s A', strtotime('midnight + 5 hours'));

function get_string_between($string, $start, $end){// echo get_string_between("this is a test", "this ", " a test"); //RETURNS 'is'
	$string = " ". $string;
	$ini = strpos($string,$start);
	if ($ini == 0) return "";
	$ini += strlen($start);
	$len = strpos($string, $end, $ini) - $ini;
	return substr($string, $ini, $len);
}

function stripAllNonNumerical($string){
	return preg_replace("/[^a-zA-Z0-9\s]/", "", $string);	
}

function numberOnly($string){
	return preg_replace("/[^0-9]/", '', $string);	
}

//exit;

 

    
		$dealpageArray[url] = "http://livingsocial.com/deals?preferred_city=4";
		
		$html = file_get_html($dealpageArray[url] );
		$dealpageArray[title] = $html->find('title', 0)->innertext;
		$dealpageArray[img_url] = $html->find('div.grid_5', 0)->style;
		$dealpageArray[img_url] = get_string_between($dealpageArray[img_url], "url(", ")");
		$dealpageArray[price]	 =  numberOnly($html->find('div.deal-price', 0)->plaintext);
		$dealpageArray[value]	=   str_replace("$","",$html->find('span.value-unit strong', 0)->innertext);
		$dealpageArray[expires] =	strtotime('midnight + 5 hours');
		$dealpageArray[vendor_name] =	$html->find('div.deal-title h1', 0)->innertext;
		$dealpageArray[address_all] =	$html->find('span.street_1', 0)->innertext;
		
		$dealpageArray[source_id]	 =  "4";
		$dealpageArray[region_id]	 =  "6";
		$siteArray[] = $dealpageArray;
					
		echo '<pre>';print_r($siteArray); echo '</pre>';
		
		
		exit;



?>
