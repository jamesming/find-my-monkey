<?php


header("Content-type: text/xml");
echo  "<?xml version=\"1.0\"?>\n";

require_once("app/Application.class.php");	

$Application->insertAggregateDealReferred( $_GET[aggregate_deal_source_id]  );

//echo  "<container>";							
//	echo  "<data>{$deal[0][title]}</data>";
//echo  "</container>";
			
?>