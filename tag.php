<!DOCTYPE html>
<html xmlns:og="http://opengraphprotocol.org/schema/"
      xmlns:fb="http://www.facebook.com/2008/fbml">
<head>
<?php    
require("app/Application.class.php"); 
require('./assets/header_assets.php');
?>      
	<script type="text/javascript" language="Javascript">
			$(document).ready(function() { 
						setInterval(function() {
					     $("#main").load('_tag.php?randval='+ Math.random());
					  }, 1000);
					  
			});
		</script>
</head>

<body>
<div id='main'  class=' container' >test
</div>
</body>
	  