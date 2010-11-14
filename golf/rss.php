<?php
// Create an RSS Feed
require("app/app-config.php");
require("app/db-config.php");
require("app/HtmlElement.class.php");
require("app/MysqlDatabase.class.php");

class RSS extends MysqlDatabase
{

	public $feed = array();
	public $localData = array();
	public function __construct( $data )
	{// construct RSS Class
		$this->localData = $data;
		parent::Connect( dbHost, dbUser, dbPass, dbName );
		switch( $this->localData[ "feed_type" ] )
		{
			case 'by_location' :
				parent::SetQuery("SELECT * FROM `table_locations` WHERE location_id='{$this->localData["location_id"]}'");
				$locationData = parent::DoQuery();
				$this->CreateRSS("SELECT * FROM `table_offerlocations`,`table_offers` WHERE 
				`table_offers`.`offer_id`=`table_offerlocations`.`offer_id` AND 
				`table_offerlocations`.`location_id`='{$this->localData["location_id"]}'
				ORDER BY expiration DESC LIMIT 15", 
					'Find My Monkey, Recent Deals in ' . $locationData[0]["location"], 
					'http://www.findmymonkey.com/offers?location_id=' . $this->localData["location_id"],
					'Get the latest Monkey Deals in ' .$locationData[0]["location"] . '!');
				break;
			case 'most_recent' :
				$this->CreateRSS("SELECT * FROM `table_offers` ORDER BY expiration DESC LIMIT 15",
				'Find My Monkey, Deals RSS',
				'http://www.findmymonkey/offers',
				'Get the latest Monkey Deals for Find My Monkey.');
				break;
		}
	}

	public function CreateRSS( $query, $title_p, $link_p, $description_p )
	{// recent
		$rss_top = new HtmlElement('rss');
		$rss_top->Set('version', '2.0');

		$channel = new HtmlElement('channel');

		$title = new HtmlElement('title');
		$title->Set('text', $title_p);

		$link = new HtmlElement('link');
		$link->Set('text', $link_p);

		$description = new HtmlElement('description');
		$description->Set('text', $description_p);

		$language = new HtmlElement('language');
		$language->Set('text', 'en-us');

		$webmaster = new HtmlElement('webMaster');
		$webmaster->Set('text', 'admin@findmymonkey.com');

		$channel->Inject( $title );
		$channel->Inject( $link );
		$channel->Inject( $description );
		$channel->Inject( $language );
		$channel->Inject( $webmaster );

		parent::SetQuery( $query );
		$exists = parent::CountDBResults();
		if( $exists )
		{// records exist
			$num = 0;
			$results = parent::DoQuery();
			while( $results[ $num ] )
			{// loop through results

				$item = new HtmlElement('item');

				$title = new HtmlElement('title');
				$title->Set('text', $results[ $num ][ "one_liner" ] );

				$link = new HtmlElement('link');
				$append_to_url[] = "offer_id={$results[$num]["offer_id"]}";
				$offer_url = "offer-details";
				if( $this->localData["aff_id"] )
				{// affiliate set
					$append_to_url[] = "aff_id={$this->localData["aff_id"]}";
				}
				if( $this->localData["location_id"] )
				{// location id set
					//$append_to_url[] = "location_id={$this->localData["location_id"]}";
					$offer_url = "offer-details";
				}
				if( sizeOf( $append_to_url ) > 0 )
				{// append parameters to URL
					$appended_url_string = "?" . join("&amp;", $append_to_url );
				}
				$link->Set('text', 'http://www.findmymonkey.com/' . $offer_url . $appended_url_string );

				parent::SetQuery("SELECT * FROM `table_offerlocations`,`table_locations` WHERE 
				`table_offerlocations`.`location_id`=`table_locations`.`location_id`
				AND
				`table_offerlocations`.`offer_id`='{$results[$num]["offer_id"]}'");
				$offerLocations = parent::DoQuery();
				$locationArr = array();
				foreach( $offerLocations as $offerLocation )
				{// loop through each location
					$locationArr[] = $offerLocation["location"];
				}
				$locationStr = join(", ", $locationArr);

				$description = new HtmlElement('description');
				$description->Set('text', $results[ $num ][ "description" ]. 
				htmlentities( "<br/><br/>A <b>\${$results[$num]["value"]} value</b> for <i>only</i> <b>\${$results[$num]["price"]}</b>"
				. " - Limit: <b>{$results[$num]["limit"]}</b>"
				. "<br/><br/>Offer Available for these Locations: {$locationStr}<br/><br/>"
				. "" , ENT_QUOTES ));

				$pubDate = new HtmlElement('pubDate');
				$pubDate->Set('text', date("M-d-Y H:i:s", time()));
			
				$item->Inject( $title );
				$item->Inject( $link );
				$item->Inject( $description );
				$item->Inject( $pubDate );
				$channel->Inject( $item );
				$num+=1;
			}

			$rss_top->Inject($channel);

			echo '<?xml version="1.0" encoding="ISO-8859-1"?>';
			echo $rss_top->BuildHTML();
		}
	}
}

$RSS = new RSS( array_merge( $_GET, $_POST ) );
?>