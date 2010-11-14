<?php	# Applicatin configuration settings

# Company Information
define( 'companyName', 'Find My Monkey, L.L.C.' );
define( 'companyURL', 'http://www.findmymonkey.com/golf' );

# _SESSION Information
define( 'sessionName', 'SESSID' );

# Application Information
define( 'appName', 'FMM Admin' );
define( 'appVersion', '1.30' );
define( 'appRelease', '05/14/2010' );
define( 'appDomain', 'http://www.findmymonkey.com/golf/admin' );

# Super Administration Information
define( 'adminEmail', 'admin@findmymonkey.com' );
define( 'adminUser', 'admin' );
define( 'adminPass', 'b1ueb1rd' );

# Twitter API Information
//
//
//
$consumer_key = 'fpeAGudlrDv1yCAgcXemkA';
$consumer_secret = 'LymzqgPPwcL6OKa354NOD4mBlifiCOhlyyqreN5y8Y';

// API Key & Secret Key
define( 'consumer_key', $consumer_key );
define( 'consumer_secret', $consumer_secret );

// Request Tokens
define( 'session_request_token', $_SESSION['oauth_request_token'] );
define( 'session_request_token_secret', $_SESSION['oauth_request_token_secret'] );

// Access Tokens
define( 'session_access_token', $_SESSION['oauth_access_token'] );
define( 'session_access_token_secret', $_SESSION['oauth_access_token_secret'] );

putenv( 'TZ=America/New_York' );
?>