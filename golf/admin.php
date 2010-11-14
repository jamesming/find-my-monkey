<?php
# Require Application Class
require("app/Application.class.php");

# Check if Admin Logged In
$Application->CheckIsAuth( true );

# Hide left navigation
$hideLeftNavigation = true;

include "assets/header-top.php"; ?>

<!--Title-->
<title><?=appName;?> &mdash; Administration</title>

<!--Meta-->
<meta name="keywords" content=""/>
<meta name="description" content=""/>

<?php include "assets/header-mid.php"; ?>

<!--Page-Specific JavaScripts-->
<script type="text/javascript">
<!--

$(document).ready(function(){
	LoadData( 'admin[username]', Cookie.GetCookieData( 'admin[username]' ) );
	if( Cookie.GetCookieData( 'admin[username]' ) )
	{// admin username saved
		DoFocus( 'admin[password]' );
	}
	else
	{// admin username not saved
		DoFocus( 'admin[username]' );
	}
});

function AppSuccess( data, status )
{// finished fetching data, now process the data

}

//-->
</script>

<?php include "assets/admin/header-bot.php"; ?>

<h1>Administration Login</h1>

<?php if( $message ) { ?>
	<div class="message-<?=$message_type;?>" style="width: 260px; margin: auto; margin-bottom: 5px;"><?=stripslashes($message);?></div>
<?php } ?>

<form class="login" name="admin[login-form]" action="admin" method="post" onmouseover="this.style.border='1px solid gray';" onmouseout="this.style.border='1px dotted gray';">

	<div style="font-weight: bold;"><?=appName;?></div>

	<input type="hidden" name="method" value="DoAdminLogin"/>
	<input type="hidden" name="b" value="1"/>

	<span>Username :</span><br/>
	<input type="text" class="text" name="admin[username]" id="admin[username]" style="width: 230px;" value=""/><br/>
	<span>Password :</span><br/>
	<input type="password" class="text" name="admin[password]" id="admin[password]" style="width: 230px;" value=""/><br/>
	<input type="submit" class="button-default" name="admin[login-button]" value="Login"/>

	<div style="font-size: 10px;">(v<?=appVersion;?>, Build: <?=appRelease;?>)</div>

</form>

<?php include "assets/admin/footer.php"; ?>