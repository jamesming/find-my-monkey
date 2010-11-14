
<script type="text/javascript" language="Javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript" language="Javascript" >
		  google.load("jquery", "1.4.2");
</script>

<script type="text/javascript" language="Javascript">
$(document).ready(function() { 
						setInterval(function() {
						//	$('#theDiv').load('runAPI.php?randval='+ Math.random());
					  }, 1000);
					  
					  $('#theDiv').load('scrape3.php?randval='+ Math.random());
					  
});
</script>
<div id='theDiv'     >
</div>


  