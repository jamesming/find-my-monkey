<?php 

require("/var/www/html/fmm/app/Application.class.php");
include('/var/www/html/fmm/app/simplehtmldom/simple_html_dom.php');



$argv[1] = str_replace("amperSign", "&",$argv[1] );

echo "<hr>".  $argv[1]  ."<br>";

$html = file_get_html( $url =  $argv[1] );

echo $html->find('div.article_text p', 1)->innertext."<br>";
//echo $html->find('div.address', 0)->innertext."<br>";


//if( $html->find('li.street-address', 0)->innertext == "" ){
//	$address =$html->find('p.location_note', 0)->innertext;
//}else{
//	$address = $html->find('li.street-address', 0)->innertext.", ".$html->find('li.locality', 0)->innertext;	
//};

//$description = $html->find('div.article p', 0)->innertext;
//
//$description = $Application->truncateThis( $description , $numOfWords2Limit = 200, $break=" ", $pad=" ...&nbsp;&nbsp;(cont)" );
//
//echo $description."<br>";




?>