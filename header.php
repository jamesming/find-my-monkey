<?php
session_start();
ini_set("display_errors","0");
ini_set("display_startup_errors","0");


if(empty($_SESSION['user']) &&  !empty($_COOKIE['registerCookie'])) $_SESSION['user'] = unserialize( $_COOKIE['registerCookie'] );


function dbconnect()
{
	mysql_connect( "findmymonkeycom.ipagemysql.com" , "fmmdbuser" , "di87Yh5nH5Gb4fR");
	$e = mysql_error();
	if(!empty($e)){ error_log($e); return false; }
	mysql_select_db("fmmdb");
	$e = mysql_error();
	if(!empty($e)){ error_log($e); return false; }
	return true;
}


function registerUser( $user_data = array() )
{
	## connect to db
	if(!dbconnect()) return "There was an internal system error";

	## must enter full name and city
	//if(empty($user_data['full_name']) || empty($user_data['city'])) return "You must enter both your full name and your city";

	## valid e-mail
	if( empty($user_data['email_address']) || preg_match("#^[0-9a-z_\-\.]+@[0-9a-z_\-\.]+\.[0-9a-z_\-\.]{2,3}$#",$user_data['email_address']) != 1 ) return "You must enter a valid e-mail address";

	//$safe_fullName = mysql_real_escape_string($user_data['full_name']);
	$safe_emailAddress = mysql_real_escape_string($user_data['email_address']);
	//$safe_city = mysql_real_escape_string($user_data['city']);

	//$sql = "INSERT INTO users (full_name,email_address,city,date_added) VALUES ('{$safe_fullName}','{$safe_emailAddress}','{$safe_city}',NOW())";
	$sql = "INSERT INTO users (email_address,date_added) VALUES ('{$safe_emailAddress}',NOW())";
	mysql_query($sql);
	$newUserId = mysql_insert_id();
	if(empty($newUserId)) return "There was an internal system error";
	return 0;
}





if(!empty($_POST['register']))
{
	// db add user
	$newUser = registerUser( $_POST );

	if(empty($newUser))
	{
		//$user_info = array('full_name'=>$_POST['full_name'],'email_address'=>$_POST['email_address'],'city'=>$_POST['city']);
		$user_info = array('email_address'=>$_POST['email_address']);

		$cookie_val = serialize($user_info);
		setcookie( "registerCookie", $cookie_val , time()+60*60*24*30 , "/", ".findmymonkey.com" );

		$_SESSION['user'] = $user_info;
		$created_user = true;

		session_write_close();
		header("Location: /home.php");
		exit;
	}
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<!-- this template was designed by http://www.tristarwebdesign.co.uk - please visit for more templates & information - thank you. -->

<head>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7"/>

<meta http-equiv="Content-Language" content="en-gb" />

<title> Featured Deal - Our Story</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252" />

<!-- style sheet links -->
<!--[if lt IE 7.]><script defer type="text/javascript" src="/files/theme/pngfix.js"></script><![endif]-->

<!--
<script type='text/javascript'>var STATIC_BASE = 'http://www.dragndropbuilder.com/';</script>
<script type='text/javascript' src='http://www.dragndropbuilder.com/weebly/images/common/prototype-1.6.0.3.js' ></script>
<script type='text/javascript' src='http://www.dragndropbuilder.com/weebly/images/common/effects-1.8.2.js' ></script>
<script type='text/javascript' src='http://www.dragndropbuilder.com/weebly/images/common/weebly.js' ></script>
<script type='text/javascript' src='http://www.dragndropbuilder.com/weebly/images/common/lightbox202.js' ></script>
<script type='text/javascript' src='http://www.dragndropbuilder.com/weebly/libraries/flyout_menus.js?2'></script>
-->
<script type='text/javascript' src='/jquery/jquery-1.4.2.min.js'></script>
<script type='text/javascript' src='/jquery/jquery.simplemodal-1.3.3.min.js'></script>

<script type='text/javascript'>

function initFlyouts()
{
	initPublishedFlyoutMenus([{"id":"46971015","title":"Our Story","url":"index.php"},{"id":"28313262","title":"Past Monkey Deals","url":"past-monkey-deals.php"},{"id":"91739932","title":"How FindMyMonkey Works","url":"how-findmymonkey-works.php"},{"id":"72338942","title":"Contact Us","url":"contact-us.php"},{"id":"692684504406747","title":"Gone Bananas","url":"gone-bananas.php"},{"id":"58115887","title":"","url":"page.php"}],'46971015','<li class=\'weebly-nav-more\'><a href=\"#\">more...</a></li>','active')
}

//if(Prototype.Browser.IE) window.onload=initFlyouts; else document.observe('dom:loaded', initFlyouts);

</script>

<link rel='stylesheet' href='http://www.dragndropbuilder.com/weebly/images/common/common.css?3' type='text/css' />
<link rel='stylesheet' type='text/css' href='/files/main_style.css' title='weebly-theme-css' />
</head>