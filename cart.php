<?php
# Require Application Class
require("app/Application.class.php");

include "assets/header-top.php"; ?>

<!--Title-->
<title>My Cart</title>

<!--Meta-->
<meta name="keywords" content=""/>
<meta name="description" content=""/>

<?php include "assets/header-mid.php"; ?>

<!--Page-Specific JavaScripts-->
<script type="text/javascript">
<!--

$(document).ready(function(){

});

	function ConfirmationMessage( message, url )
	{// confirmation popup (for confirming deletion)
		var ask = confirm( message + '\n\nPress "Ok" to Continue.\nPress "Cancel" to Return to the previous screen.' );
		if( ask )
		{// confirmed
			location = url;
		}
		else
		{// cancelled
			return;
		}
	}

//-->
</script>

<?php include "assets/header-bot.php"; ?>


	<!--Start Content Container-->
	<div id="content-container" style="min-height: 400px; padding-left: 15px; width: 920px; position: relative; z-index: -1;">

<h1>My Cart</h1>

<?php if($message) { ?>
	<div class="message-<?=$message_type;?>"><?=$message;?></div>
<?php } ?>

<?=$Application->DisplayCart();?>

	</div>
	<!--End Content Container-->

<?php include "assets/footer.php"; ?>