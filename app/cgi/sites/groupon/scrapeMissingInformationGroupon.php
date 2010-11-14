<?php  
   
require("/var/www/html/fmm/app/Application.class.php");

class scrapeMissingInfo extends Application
{
	
	public function getDealsMissingInfo(){
		parent::SetQuery("SELECT aggregate_deal_id,  deal_url FROM aggregate_deal WHERE aggregate_deal_source_id = 1");
		$arrays = parent::DoQuery();
		return $arrays;
	}
	
	
};


$scrapeMissingInfo = new scrapeMissingInfo();

$arrays = $scrapeMissingInfo->getDealsMissingInfo();

foreach( $arrays as $array){
	echo exec('php /var/www/html/fmm/app/cgi/sites/groupon/_scrapeMissingInformationGroupon.php '.$array[deal_url].' '.$array[aggregate_deal_id]);
}



?>