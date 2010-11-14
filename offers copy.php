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

<!--RSS Feeds, Location Specific-->
<link rel="alternate" type="application/rss+xml" title="FindMyMonkey.com, Recent Deals for <?=$locationInformation["location"];?>" href="http://www.findmymonkey.com/rss?feed_type=by_location&location_id=<?=$locationInformation["location_id"];?>"/>

<?php include "assets/header-mid.php"; ?>

<!--Page-Specific JavaScripts-->
<script type="text/javascript">
<!--

$(document).ready(function(){

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

<?php include "assets/header-bot.php"; ?>

	<!--Start Top Content Container-->
	<div id="top-content-container-outer">
	<div id="top-content-container">
		<div id="top-content-nav">
			<div class="col-1">
				<?php
				$day = date("j");
				if( substr( $day, 0, -1 ) == "1")
				{// 1st
					$day = $day . "st";
				}
				else if( substr( $day, 0, -1 ) == "2")
				{// nd
					$day = $day . "nd";
				}
				else if( substr( $day, 0, -1 ) == "3")
				{// rd
					$day = $day . "rd";
				}
				else
				{// th
					$day = $day . "th";
				}
				?>
				Today is <?=date("F");?> <?=$day;?>, <?=date("Y");?>
			</div>
			<div class="col-2">
				<a href="#">TODAY'S DEALS</a>
				<a href="#">RECENT DEALS</a>
				<a href="banana-corner">BANANA CORNER</a>
				<a href="how-it-works">HOW IT WORKS</a>
			</div>
			<div class="col-3" style="text-align: right;">
				<input type="text" class="search-box" style="float: left; width: 125px; padding: 3px;" value="search.." onfocus="if(this.value=='search..'){this.value='';}" onblur="if(this.value==''){this.value='search..';}"/>
				<input type="submit" class="search-btn" value="" style="float: left;"/>
			</div>
		</div>

		<div id="top-content">
			<div class="col-1">
			<?php if(!$notAvailable){ ?>
				<div class="ribbon">
					<img src="assets/gfx/misc/todays-deal.png" alt="" title=""/>
				</div>
				<div style="font-weight: bold; font-family: Arial; font-size:13px; position: relative; top: 5px; left: 5px;"><?=$offerData[0]["one_liner"];?></div>
				<div style="margin-top: 15px; margin-bottom: 5px; border-top: 3px dashed #382F25; 
				border-bottom: 3px dashed #382F25; text-align: center;">
					<div style="padding-bottom: 6px; padding-top: 6px; margin-top: 4px; 
					margin-bottom: 4px; background-color: #2A2015;">
						<span class="yellow-special">$<?=$offerData[0]["price"];?></span>
					</div>
				</div>
				<div style="text-align: center;">
				<?php if( time() < $offerData[0]["expiration"] ) { ?>
					<input type="button" class="buynow-btn" value="" style="cursor: pointer;" onclick="AddToCart('<?=$offerData[0]["offer_code"];?>');"/>
				<?php } ?>
				</div>
				<div style="text-align: center; overflow: hidden; margin-top: 12px;">
					<span style="font-size: 15px; font-family: Georgia; font-style: italic; float: left;">Share this Deal</span>
					<!--Social Media Links-->
					<div style="float: left; padding-left: 7px;">
						<a href="http://twitter.com/home?status=<?=urlencode($offerData[0]["one_liner"] . " for just $" . $offerData[0]["price"] . " at #FindMyMonkey http://www.findmymonkey.com");?>"><img src="assets/gfx/icons/twitter-ico.png" alt="" title="" border="0"/></a>
						<a href="http://www.facebook.com/sharer.php?u=<?=urlencode($offerData[0]["one_liner"] . " for just $" . $offerData[0]["price"] . " at #FindMyMonkey http://www.findmymonkey.com");?>"><img src="assets/gfx/icons/fb-ico.png" alt="" title="" border="0"/></a>
						<a href="#"><img src="assets/gfx/icons/email-ico.png" alt="" title="" border="0"/></a>
					</div>
				</div>
			<?php } ?>
			</div>
			<div class="col-2">
				<?php if(!$notAvailable){ ?><img src="<?=$offerData[0]["graphic"];?>" alt="" title="" style="max-height: 195px;"/><?php } ?>
			</div>
			<div class="col-alerts" style="text-align: center;">
				<div style="text-align: center; font-size: 14px; font-style: italic; margin-bottom: 10px;">
					Get Monkeypon Alerts!
				</div>
				<input type="text" class="text" id="alerts-email" value="Enter your email" style="padding: 6px;" onfocus="if(this.value=='Enter your email'){this.value='';}" onblur="if(this.value==''){this.value='Enter your email';}"/><br/>
				<input type="text" class="text" id="city-alerts" value="Locating..." style="padding: 6px;" readonly="readonly"/><br/>
				<input type="button" class="signup-btn" value="" style="cursor: pointer; margin-top: 10px;" onclick="AlertSignup()"/>
				
				<p style="margin-bottom: 0px; padding-bottom: 0px; margin-bottom: 4px;"><img src="assets/gfx/misc/total-monkeypons.png" alt="" title=""/></p>
				<?=$Application->GetTotalCoupons();?>
				<p style="margin-bottom: 0px; padding-bottom: 0px; margin-bottom: 4px;"><img src="assets/gfx/misc/total-dollars-saved.png" alt="" title=""/></p>
				$<?=$Application->GetTotalSaved();?>
			</div>
		</div>
	</div>

<?php if(!$notAvailable){ ?>
	<!--Timer-->
	<script type="text/javascript">
	<!--
		var timeLeft = <?=$timer["timeLeft"]?>;
		var timer;
	//-->
	</script>
	<script type="text/javascript" src="assets/js/Timer.js"></script>
	<script type="text/javascript">
	<!--
		window.onload = Timer;
	//-->
	</script>
<?php } ?>
</div>
	<!--Start Content Container-->
	<div id="content-container" style="min-height: 400px;">

		<div class="content-main">
		<?php if(!$notAvailable){ ?>
			<div class="ribbon-details" style="width: 215px;">
				<img src="assets/gfx/misc/deal-details.png" alt="" title=""/>
			</div>
			<!--Start Offer Details-->
			<div id="offer-details">
				<div id="offer-pricing">
					<p>
						<span class="default-special">Value:</span>
						<span class="yellow-special">$<?=$offerData[0]["value"];?></span>
					</p>
					<p>
						<span class="default-special">Discount:</span>
						<span class="yellow-special"><?=$offerData[0]["discount"];?>%</span>
					</p>
					<p>
						<span class="default-special">You Save:</span>
						<span class="yellow-special">$<?=number_format($offerData[0]["value"]-$offerData[0]["price"], 2, '.', '');?></span>
					</p>
				</div>
				<div id="offer-details-deal">
					<span class="yellow-special" style="font-size: 18px; 
					font-weight: bold; font-style: none;">Details of the Deal</span><br/>
					<div>
					<?=html_entity_decode( $offerData[0]["details"], ENT_QUOTES );?>
					</div>
				</div>
			</div>
			<!--End Offer Details-->

			<!--Start Description-->
			<div style="margin: 15px;">
			<span style="font-size: 19px; color: #000000; font-style: italic; font-family: Georgia;">Description:</span><br/><br/>
				<?=html_entity_decode( $offerData[0]["description"], ENT_QUOTES );?>
			</div>
			<!--End Description-->

			<!--Discussion Tool-->
			<div style="padding: 15px;">
				<?=$Application->Discussions( $offerData[0]["offer_id"] );?>
			</div>
			<!--Post a Message-->
			<div style="padding: 15px;">
			<h3>Post a Message</h3>
				<?=$Application->DiscussionForm( $offerData[0]["offer_id"] );?>
			</div>

			<!--Start Paging-->
			<div style="margin: 15px;">
				<?=html_entity_decode( $offerData["paging"], ENT_QUOTES );?>
			</div>
			<!--End Paging-->
		<?php } else { ?>
		<div style="font-size: 14px; font-style: italic;">
			There are currently no offers available for this location.
		</div>
		<?php } ?>
		</div>

		<div class="content-sidebar">

			<div>
			<?php if(!$notAvailable){ ?>
				<p><img src="assets/gfx/misc/time-left-to-buy.png" alt="" title=""/></p>
				<!--Countdown Timer-->
				<table cellspacing="0" cellpadding="2" align="center">
					<tr>
						<td><span id="days" class="timer-box">00</span></td>
						<td>:</td>
						<td><span id="hours" class="timer-box">00</span></td>
						<td>:</td>
						<td><span id="minutes" class="timer-box">00</span></td>
						<td>:</td>
						<td><span id="seconds" class="timer-box">00</span></td>
					</tr>
					<tr>
						<td style="text-align: center; font-size: 10px;">days</td>
						<td></td>
						<td style="text-align: center; font-size: 10px;">hours</td>
						<td></td>
						<td style="text-align: center; font-size: 10px;">mins</td>
						<td></td>
						<td style="text-align: center; font-size: 10px;">secs</td>
					</tr>
				</table>
				<p><img src="assets/gfx/misc/the-company.png" alt="" title=""/></p>
				<?php
				// fetch company info
				$companyInfo = $Application->GetCompanyInfo( $offerData[0]["company"] );
				?>
				<b><?=$companyInfo["company_name"];?></b><br/>
				<?=$companyInfo["street_address"];?><br/>
				<?=$companyInfo["city"];?>, <?=$companyInfo["state"];?> <?=$companyInfo["zipcode"];?><br/>
				<i><a href="<?=$companyInfo["website_url"];?>" target="_blank"><?=str_replace("http://", "", $companyInfo["website_url"]);?></a></i>
				<p><img src="assets/gfx/misc/monkeys-giving-back.png" alt="" title=""/></p>
				<p>FindMyMonkey gives 5% of all proceeds directly to Charity.</p>
				<p><img src="assets/gfx/misc/send-money.png" alt="" title=""/></p>
				<p><img src="assets/gfx/misc/refer-a-friend.png" alt="" title=""/></p>
				<p>Get $10 for every friend you refer to Find My Monkey. <a href="refer-a-friend">Read all about our referral program</a>.</p>
				<p><img src="assets/gfx/misc/affiliate-program.png" alt="" title=""/></p>
				<p>Make money by promoting a daily Find My Monkey.  <a href="affiliates">We are looking for affiliates.</a></p>
				<p><img src="assets/gfx/misc/stay-connected.png" alt="" title=""/></p>
				<p><a  href="http://twitter.com/FindMyMonkeyLA" target="_blank" <img src="assets/gfx/misc/follow-twitter.png" border="none" style="margin-left: 30px;" alt="" title=""/></a></p>
			<?php } ?>
			</div>

		</div>

	</div>
	<!--End Content Container-->

<?php include "assets/footer.php"; ?>