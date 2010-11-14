<?php
// Groupon Scraper
require("Application.class.php");
class Groupon extends Application
{//
	private $location_map = array();
	private $feed_url;
	public function __construct()
	{
		$this->location_map[ 22 ] = "new-york";
		$this->location_map[ 2 ] = "las-vegas";
		$this->location_map[ 10 ] = "austin";
		$this->location_map[ 11 ] = "boston";
		$this->location_map[ 12 ] = "jacksonville";
		$this->location_map[ 13 ] = "atlanta";
		$this->location_map[ 6 ] = "los-angeles";
	}

	public function DoScrape( $location )
	{// scrape a given location
		$this->feed_url = "http://feeds.feedburner.com/groupon" . $this->location_map[ $location ];
		$data_feed = file_get_contents( $this->feed_url );
		$xml = simplexml_load_string( $data_feed );
		$channel = $xml->channel; 
		foreach( $channel->item as $item )
		{// loop through each item
			parent::SetQuery("SELECT * FROM `table_dealaggregator` WHERE deal_url='{$item->link}'");
			$exists = parent::CountDBResults();
			if( !$exists )
			{// deal doesn't exist in the database yet
				//echo $item->title . "<br/>";
				preg_match_all('#\$[0-9]{1,3}(?:,?[0-9]{3})*(?:\.[0-9]{2})?#', $item->title, $out);
    				///print_r( $out ); exit;
    				
				parent::SetQuery("INSERT INTO `table_dealaggregator`
				VALUES ('','','{$location}','','',
				'".addslashes($item->title)."',
				'".addslashes($item->description)."',
				'{$item->link}',
				'".str_replace("$", "", $out[0][0])."',
				'".str_replace("$", "", $out[0][1])."',
				'1')");
				parent::SimpleQuery();
			}
		}
	}

	public function StartScrape()
	{// start the scrape (select a random location)
		$locations = array_keys( $this->location_map );
		foreach( $locations as $location_id => $val )
		{// loop through each location
			$this->DoScrape( $val );
			//usleep( 2500 );
		}
	}
}

$scraper = new Groupon();
$scraper->StartScrape();
?>