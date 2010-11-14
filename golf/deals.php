<?php
# Require Application Class
require("app/Application.class.php");

$Application->CheckMyLocation();
$offerData = $Application->GetOffers();
$timer = $Application->GetOfferDetails( $offerData[0]["offer_code"] );

// offers available
$notAvailable = false;

if( sizeOf( $offerData[0] ) == 0 ){ $notAvailable = true; }

$locationInformation = $Application->CurrentViewingLocationRSS();

include "assets/header-top.php"; ?>

<!--Title-->
<title><?=$locationInformation["location"];?> Deals</title>

<!--Meta-->
<meta name="keywords" content=""/>
<meta name="description" content=""/>

<?php include "assets/header-mid.php"; ?>

<!--Page-Specific JavaScripts-->
<script type="text/javascript">
<!--

$(document).ready(function(){
	<?php if( $_GET["share-post"] && $message ) { ?>
	DoSharePopup();
	<?php } ?>
});

function AlertSignup()
{// City Alerts Signup
	AppCall( 'Application', {'method' : 'DoAlertSignup', 'b' : 1,
	 'email_address' : GetData('alerts-email'),
	 'location_id' : '<?=$Application->GetData( 'location_id' );?>'},
	 {'success' : AppSignupStatus} );
}

function AppSignupStatus( data, status )
{// outcome of alerts signup
	alert( data[ "message" ] );
}

//-->
</script>
<script type="text/javascript" src="assets/js/Popup.js"></script>

<?php include "assets/header-bot.php"; ?>

	<!--Share-->
	<div id="popup-container">
		<a id="popup-close" href="javascript:void(0);">X</a>
		<h1>Share this Deal with a Friend!</h1>

		<?php if( $message ) { ?><div class="message-<?=$message_type;?>"><?=$message;?></div><?php } ?>

		<p id="popup-content-area">
		<form name="form-data" method="post" action="offers">
		<input type="hidden" name="offer_id" value="<?=$offerData[0]["offer_id"];?>"/>
		<input type="hidden" name="method" value="DoShareSite"/>
		<input type="hidden" name="b" value="1"/>
		<table cellspacing="0" cellpadding="2">
			<tr>
				<td style="text-align: right;">Your Name :</td>
				<td><input type="text" name="your[name]" size="25" class="text" value="<?=$_SESSION["form-data"]["your"]["name"];?>"/></td>
			</tr>
			<tr>
				<td style="text-align: right;">Your Email :</td>
				<td><input type="text" name="your[email]" size="35" class="text" value="<?=$_SESSION["form-data"]["your"]["email"];?>"/></td>
			</tr>
			<tr>
				<td style="text-align: right;">Friend's Name :</td>
				<td><input type="text" name="friend[name]" size="25" class="text" value="<?=$_SESSION["form-data"]["friend"]["name"];?>"/></td>
			</tr>
			<tr>
				<td style="text-align: right;">Friend's Email :</td>
				<td><input type="text" name="friend[email]" size="35" class="text" value="<?=$_SESSION["form-data"]["friend"]["email"];?>"/></td>
			</tr>
			<tr>
				<td style="text-align: right; vertical-align: top;">Message :</td>
				<td>
					<textarea name="share[message]" rows="4" cols="40">Find great deals in <?=$locationInformation["location"];?> @ #FindMyMonkey http://www.findmymonkey.com/deals?location_id=<?=$Application->GetData('location_id');?></textarea>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
				<div style="margin-top: 15px;">
					<button type="submit" name="share[btn]" class="button-special">Share These Deals!</button>
				</div>
				</td>
			</tr>
		</table>
		</form>
		</p>
	</div>
	<div id="background-popup"></div>
	<!--End Share-->

	<!--Start Top Content Container-->
	<div id="top-content-container-outer" style="height: 50px;">
	<div id="top-content-container">
		<div id="top-content-nav">
			<div class="col-1">
				Today is <?=date("F");?> <?=date("dS");?>, <?=date("Y");?>
			</div>
			<div class="col-2">
				<a href="offers">TODAY'S DEALS</a>
				<a href="recent-deals">RECENT DEALS</a>
				<a href="banana-corner">BANANA CORNER</a>
				<a href="how-it-works">HOW IT WORKS</a>
			</div>
			<div class="col-3" style="text-align: right;">
				<form action="search" method="get" style="margin: 0px; padding: 0px;">
					<input type="text" class="search-box" name="q" style="float: left; width: 125px; padding: 3px;" value="search.." onfocus="if(this.value=='search..'){this.value='';}" onblur="if(this.value==''){this.value='search..';}"/>
					<input type="submit" class="search-btn" value="" style="float: left;"/>
				</form>
			</div>
		</div>
	</div>
	</div>

	<!--Start Content Container-->
	<div id="content-container" style="min-height: 400px; padding-left: 15px; width: 920px;">

	<h2>Deals in <?=$locationInformation["location"];?></h2>

		<?=$Application->GetAggregatedDeals($locationInformation["location"]);?>

	</div>
	<!--End Content Container-->

<?php include "assets/footer.php"; ?>