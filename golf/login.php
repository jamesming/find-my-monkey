<?php
# Require Application Class
require("app/Application.class.php");

include "assets/header-top.php"; ?>

<!--Title-->
<title>Account Login</title>

<!--Meta-->
<meta name="keywords" content=""/>
<meta name="description" content=""/>

<?php include "assets/header-mid.php"; ?>

<!--Page-Specific JavaScripts-->
<script type="text/javascript">
<!--

$(document).ready(function(){

});

//-->
</script>

<?php include "assets/header-bot.php"; ?>


	<!--Start Content Container-->
	<div id="content-container" style="min-height: 400px; padding-left: 15px; width: 920px;">

<h1>Member System</h1>

<?php if($message) { ?>
	<div class="message-<?=$message_type;?>"><?=$message;?></div>
<?php } ?>

<?=$Application->LoginForm();?>

	</div>
	<!--End Content Container-->

<?php include "assets/footer.php"; ?>