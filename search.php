<?php
# Require Application Class
require("app/Application.class.php");

$offerData = $Application->GetOffers();
$timer = $Application->GetOfferDetails( $offerData[0]["offer_code"] );

// offers available
$notAvailable = false;

if( sizeOf( $offerData[0] ) == 0 ){ $notAvailable = true; }

include "assets/header-top.php"; ?>

<!--Title-->
<title>Search Deals</title>

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

		<?=$Application->DoSearch();?>

	</div>
	<!--End Content Container-->

<?php include "assets/footer.php"; ?>