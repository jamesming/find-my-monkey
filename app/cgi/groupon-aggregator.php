<?php
// Groupon Scraper
require("../Application.class.php");
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
		
		// parent:: d($channel);
		// exit();
		
		foreach( $channel->item as $item )
		{// loop through each item
			parent::SetQuery("SELECT * FROM `aggregate_deal` WHERE deal_url='{$item->link}'");
			$exists = parent::CountDBResults();
			if( !$exists )
			{// deal doesn't exist in the database yet
				//echo $item->title . "<br/>";
				preg_match_all('#\$[0-9]{1,3}(?:,?[0-9]{3})*(?:\.[0-9]{2})?#', $item->title, $out);

				$pattern = "/src=[\"']?([^\"']?.*(png|jpg|gif))[\"']?/i";
				preg_match_all($pattern, $item->description, $images);		
				
   				
				parent::SetQuery("INSERT INTO `aggregate_deal`
				VALUES ('','','{$location}','','',
				'".addslashes($item->title)."',
				'".addslashes($item->description)."',
				'{$item->link}',
				'".str_replace("$", "", $out[0][0])."',
				'".str_replace("$", "", $out[0][1])."',
				'1',
				'".strtotime('midnight')."',
				now(),
				'{$images[1][0]}','')");
				parent::SimpleQuery();
			}
			parent::SetQuery("SELECT * FROM `aggregate_deal`");
			$results = parent::DoQuery();
			foreach( $results as $result )
			{// loop through each result to see if it has expired, delete it if it has
				$expired = mktime( 0, 0, 0, date("m"), date("d", $result["time_added"])+1, date("Y") );
				if( time() > $expired )
				{
					parent::SetQuery("DELETE FROM `aggregate_deal` WHERE aggregate_deal_id='{$result["deal_id"]}'");
					parent::SimpleQuery();
				}
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