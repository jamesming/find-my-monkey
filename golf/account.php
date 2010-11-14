<?php
# Require Application Class
require("app/Application.class.php");
$Application->CheckUserAuth();

include "assets/header-top.php"; ?>

<!--Title-->
<title>My Account</title>

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
<script type="text/javascript" src="assets/js/Popup.js"></script>

<?php include "assets/header-bot.php"; ?>

	<!--Share-->
	<div id="popup-container" style="width: 500px; height: 450px;">
		<a id="popup-close" href="javascript:void(0);">X</a>
		<h1>Share FMM with 5 Friends and get a $10 Credit!</h1>
		<p id="popup-content-area">
			<form name="form-data" method="post" action="offers">
			<input type="hidden" name="method" value="DoShareSite_Credit"/>
			<input type="hidden" name="b" value="1"/>
			<table cellspacing="0" cellpadding="2">
				<tr>
					<td></td>
					<td><b>Name</b></td>
					<td><b>Email Address</b></td>
				</tr>
				<?php for($p=0; $p<5; $p++) { ?>
				<tr>
					<td style="text-align: right;">Friend <?=($p+1);?> :</td>
					<td><input type="text" name="friend[<?=$p;?>][name]" size="20" class="text" value="<?=$_SESSION["form-data"]["friend"][$p]["name"];?>"/></td>
					<td><input type="text" name="friend[<?=$p;?>][email]" size="32" class="text" value="<?=$_SESSION["form-data"]["friend"][$p]["email"];?>"/></td>
				</tr>
				<?php } ?>
				<tr>
					<td style="text-align: right; vertical-align: top;">Message :</td>
					<td colspan="2">
						<textarea name="share[message]" rows="4" cols="40"><?=$Application->GetShareMessage();?></textarea>
					</td>
				</tr>
				<tr>
					<td></td>
					<td colspan="2">
					<div style="margin-top: 15px;">
						<button type="submit" name="share[btn]" class="button-special">Share!</button>
					</div>
					</td>
				</tr>
			</table>
			</form>
		</p>
	</div>
	<div id="background-popup"></div>
	<!--End Share-->

	<!--Start Content Container-->
	<div id="content-container" style="min-height: 400px; padding-left: 15px; width: 920px;">

<h1><a href="account" title="My Account">My Account</a></h1>

<?php if($message) { ?>
	<div class="message-<?=$message_type;?>"><?=$message;?></div>
<?php } ?>

<?=$Application->MyAccount();?>

	</div>
	<!--End Content Container-->

<?php include "assets/footer.php"; ?>