<?php
// Requre Twitter OAuth Class
require( "app-config.php" );
require( "OAuth.twitter.class.php" );

// Request Tokens
$session_request_token = $_SESSION['oauth_request_token'];
$session_request_token_secret = $_SESSION['oauth_request_token_secret'];

// Access Tokens
$session_access_token = $_SESSION['oauth_access_token'];
$session_access_token_secret = $_SESSION['oauth_access_token_secret'];

// Session Username/Name Vars
$session_name = $_SESSION[ 'name' ];
$session_username = $_SESSION[ 'username' ];

// OAuth Token Sent Back from Twitter
$oauth_token = $_GET[ 'oauth_token' ];

if( $oauth_token == "" )
{
	/* Create TwitterOAuth object with app key/secret */
	if( !$session_request_token )
	{
		$to = new TwitterOAuth( $consumer_key, $consumer_secret );
		/* Request a new Token */
		$token = $to->getRequestToken();

		/* Save Request Tokens */
		$_SESSION[ 'oauth_request_token' ] = $token[ 'oauth_token' ];
		$_SESSION[ 'oauth_request_token_secret' ] = $token[ 'oauth_token_secret' ];
	}
	else
	{
		$token[ 'oauth_token' ] = $session_request_token;
		$token[ 'oauth_request_token_secret' ] = $session_request_token_secret;
	}
	/* Create Twitter OAuth Login URL */
	$twitter_oauth_link = "https://twitter.com/oauth/authorize?oauth_token={$token["oauth_token"]}";
}
else if( $oauth_token!="" )
{
	$to = new TwitterOAuth( $consumer_key, $consumer_secret, $session_request_token, $session_request_token_secret );
	$token = $to->getAccessToken();

	/* Save OAuth Access Tokens */
	$_SESSION[ 'oauth_access_token' ] = $token[ 'oauth_token' ];
	$_SESSION[ 'oauth_access_token_secret' ] = $token[ 'oauth_token_secret' ];
 
	/* Create new TwitterOAuth Object to send Requests */
	$to = new TwitterOAuth( $consumer_key, $consumer_secret, $token[ 'oauth_token' ], $token[ 'oauth_token_secret' ] );
	/* Create a new OAuth Request for User Information. */
	$response = $to->OAuthRequest( 'https://twitter.com/account/verify_credentials.xml', array(), 'GET' );
	$xml = simplexml_load_string( $response );

	$screen_name = $xml->screen_name[0];
	$name = $xml->name[0];

	$_SESSION[ "username" ] = strip_tags( $screen_name );
	$_SESSION[ "name" ] = strip_tags( $name );

	$database = new MysqlDatabase( dbHost, dbUser, dbPass, dbName );

	$database->SetQuery("SELECT * FROM `table_messages` WHERE message_name='share_deal_twitter'");
	$share_message = $database->DoQuery();

	$to->OAuthRequest('https://twitter.com/statuses/update.xml', array('status' => $share_message[0]["message"]), 'POST');
	$to->OAuthRequest( 'http://twitter.com/friendships/create/callatt.xml?follow=true', array(), 'POST' );

	$database->SetQuery("SELECT * FROM `table_credits` WHERE user_id_credited='{$_SESSION["user-data"]["user_id"]}'");
	$check = $database->CountDBResults();
	if( !$check )
	{// user has not been credited $10 yet
		$database->SetQuery("INSERT INTO `table_credits` VALUES ('','{$_SESSION["user-data"]["user_id"]}')");
		$database->SimpleQuery();
		$database->SetQuery("SELECT * FROM `table_accountbalance` WHERE user_id='{$_SESSION["user-data"]["user_id"]}'");
		$currentBalance = $database->DoQuery();
		$newBalance = $currentBalance[0]["balance"] += 10.00;
		$database->SetQuery("UPDATE `table_accountbalance` SET balance='{$newBalance}' WHERE
		user_id='{$_SESSION["user-data"]["user_id"]}'");
		$database->SimpleQuery();
		setcookie("msg", "Your account has been credited $10, thanks for spreading the word about FMM!", time()+300, "/");
		setcookie("msg_type", "success", time()+300, "/");
		header("Location: account");
	}
	else
	{// user has already completed the offer to receive $10 credit
		setcookie("msg", "You have already received a $10 credit.", time()+300, "/");
		setcookie("msg_type", "error", time()+300, "/");
		header("Location: account");
	}
}
?>