<?php
# Require Application Class
require("app/Application.class.php");

$pageInfo = $Application->GetPageContent( 'tou' );

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
	<div id="content-container" style="min-height: 400px; padding-left: 15px; width: 920px;">

		<?=html_entity_decode( $pageInfo[0]["content"] , ENT_QUOTES );?>

	</div>
	<!--End Content Container-->

<?php include "assets/footer.php"; ?>