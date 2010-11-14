<?php

require("/var/www/html/fmm/app/Application.class.php");

class scrapeMissingInfo extends Application
{
	
	public function getDealsMissingInfo(){
		parent::SetQuery("SELECT aggregate_deal_id,  deal_url FROM aggregate_deal WHERE aggregate_deal_source_id = 3");
		$arrays = parent::DoQuery();
		return $arrays;
	}
	
	
};


$scrapeMissingInfo = new scrapeMissingInfo("");


//ini_set('display_errors',1);
//error_reporting(E_ALL|E_STRICT); 
//error_reporting (E_ALL ^ E_NOTICE);

$arrays = $scrapeMissingInfo->getDealsMissingInfo();

//echo '<pre>';print_r(  $arrays    );echo '</pre>';  exit;

foreach( $arrays as $array){
	
//	echo urlencode($array[deal_url])."<br>";

$array[deal_url] = str_replace("&", "amperSign", $array[deal_url] );
	
echo exec('php /var/www/html/fmm/app/cgi/sites/socialbuy/_scrapeMissingSocialBuy.php '.  $array[deal_url]  .' '.$array[aggregate_deal_id]);

}

exit;

//	require("/var/www/html/fmm/app/Application.class.php");
//	include('/var/www/html/fmm/app/simplehtmldom/simple_html_dom.php');

	$html = file_get_html('http://www.socialbuy.com/affiliate/hit/?link_id=139&deal_id=1294');

  echo $html->find('title', 0)->innertext."<br>";

?>
