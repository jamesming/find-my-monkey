<?php
# Require Application Class
require("app/Application.class.php");

$pageInfo = $Application->GetPageContent( 'contact' );

include "assets/header-top.php"; ?>

<!--Title-->
<title><?=$pageInfo[0]["page_title"];?></title>

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
	<div id="content-container" style="min-height: 400px; padding-left: 15px; width: 920px; overflow: hidden;">

		<div style="float: left; width: 50%; margin-right: 15px;">
			<?=html_entity_decode( $pageInfo[0]["content"] , ENT_QUOTES );?>
		</div>

		<div style="float: left; width: 40%;">
	<?php if( $message ) { ?>
		<div class="message-<?=$message_type;?>"><?=stripslashes($message);?></div>
	<?php } ?>
			<form name="contact[form]" method="post" action="contact">
			<input type="hidden" name="method" value="DoSendContact"/>
			<input type="hidden" name="b" value="1"/>
			<input type="hidden" name="form-data" value="1"/>
			<div>Name</div>
			<input type="text" class="text" name="contact[name]" size="25" value="<?=$_SESSION["form-data"]["contact"]["name"];?>"/>
			<div style="margin-top: 8px;">E-mail Address</div>
			<input type="text" class="text" name="contact[email]" size="35" value="<?=$_SESSION["form-data"]["contact"]["email"];?>"/>
			<div style="margin-top: 8px;">Message</div>
			<textarea name="contact[message]" rows="4" cols="40"><?=$_SESSION["form-data"]["contact"]["message"];?></textarea>
			
			<div style="margin-top: 8px;">
				<button type="submit" class="button-special">Send</button>
			</div>
			</form>
		</div>

	</div>
	<!--End Content Container-->

<?php include "assets/footer.php"; ?>