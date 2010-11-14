<?php 
	require("/var/www/html/fmm/app/Application.class.php");
	include('/var/www/html/fmm/app/simplehtmldom/simple_html_dom.php');
	
	
class setMissingInfo extends Application
{
	
	public function setDealsMissingInfo($description, $aggregate_deal_id){
		
		parent::SetQuery("UPDATE aggregate_deal SET description = '".addslashes($description)."' WHERE aggregate_deal_id = $aggregate_deal_id");
		//parent::PrintQuery();
		parent::SimpleQuery();
		
	}
	
	public function insertMissingInfo($address, $aggregate_deal_id){
			parent::SetQuery("INSERT INTO aggregate_deal_location (
																															address_all,
																															aggregate_deal_id)
																											values(
																															'{$address}',
																															$aggregate_deal_id
																															) ");
			$mysql_insert_id = parent::InsertQuery();	
	}

	
	
};


$setMissingInfo = new setMissingInfo("");
	
	
$html = file_get_html( $argv[1] );	
echo "<hr>".$argv[1]."<br>";

if( $html->find('li.street-address', 0)->innertext == "" ){
	$address =$html->find('p.location_note', 0)->innertext;
}else{
	$address = $html->find('li.street-address', 0)->innertext.", ".$html->find('li.locality', 0)->innertext;	
};

$description = $html->find('div.article p', 0)->innertext;

$description = $Application->truncateThis( $description , $numOfWords2Limit = 200, $break=" ", $pad=" ...&nbsp;&nbsp;(cont)" );

echo $description."<br>";

$setMissingInfo->setDealsMissingInfo( $description, $aggregate_deal_id = $argv[2] );
$setMissingInfo->insertMissingInfo($address, $aggregate_deal_id = $argv[2] );


echo "<br>" ."aggregate_deal_id is:" . $argv[2] ."<br>";
?>