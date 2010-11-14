<?php
require( "app-config.php" );

class TWAPI
{// Twitter API Calls
	public function __construct()
	{

	}

	public function DoAPICall( $url, $query )
	{
		$ch = curl_init();
		$options = array(
			CURLOPT_URL => "{$url}{$query}",
			CURLOPT_USERAGENT => app_name,
			CURLOPT_RETURNTRANSFER => true
		);
		curl_setopt_array( $ch, $options );
		$response = curl_exec( $ch );
		$results = simplexml_load_string( $response );
		curl_close( $ch );
		return $results;
	}
}

$TWAPI = new TWAPI();	//
?>