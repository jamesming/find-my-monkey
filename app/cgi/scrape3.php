<?php
	require("/var/www/html/fmm/app/Application.class.php");
	include('/var/www/html/fmm/app/simplehtmldom/simple_html_dom.php');

	$html = file_get_html('http://www.socialbuy.com/affiliate/hit/?link_id=139&deal_id=1294');

  echo $html->find('title', 0)->innertext."<br>";

?>
