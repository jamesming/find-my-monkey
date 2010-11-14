<?php
require_once("header.php");

$background_image_index = rand(1,4);
if(!empty($_GET['image_index'])) $background_image_index = $_GET['image_index'];
$background_image_src = "/images/background-image{$background_image_index}.jpg";


?>
<body id="sitebody">
<style type='text/css'>

body,html
{
	overflow: hidden;
	width: 100%;
	height: 100%;
	margin: 0;
	padding: 0;
	color: #ffffff !important;
}


img#registerBackgroundImage
{
	position: absolute;
	top: 50px;
	left: 50px;
	z-index: 10;
	width: 525px;
	height: 535x;

	filter:alpha(opacity=90);
	-moz-opacity:0.75;
	-khtml-opacity: 0.75;
	opacity: 0.75;
}

div#registerBox
{
	position: absolute;
	top: 60px;
	left: 60px;
	z-index: 20;
	width: 510px;
	height: 550x;
}

img#registerBoxHeader
{
	display: block;
	margin-bottom: 15px;
}


.modalLabel
{
	font-weight: bold;
	margin: 10px 0px 7px 0px;
	color: #000000;
}

input#email_address
{
	border: 1px solid #d8d8d8;
	font-size: 14pt;
	padding: 8px;
	width: 160px;
}

div#eac
{
	width: 214px;
	text-align: right;
	float: left;
	margin-top: 6px;
}

div#mbs
{
	float: left;
	margin-left: 12px;
	width: 280px;
}


div#registerBoxInputContainer
{
	margin-top: 25px;
	width: 510px;
	height: 75px;
}

</style>

<script type='text/javascript'>
//<![CDATA[

var wh = $(window).height();
var ww = $(window).width();
$("#sitebody").append("\n<center>\n<img id='splashBackground' src='<?php echo $background_image_src; ?>' alt='background image' style='height: "+String(wh)+"px; width: "+String(ww)+"px;' />\n</center>\n");


window.onresize = function(){
		var wh = $(window).height();
		var ww = $(window).width();

		$("#splashBackground").css("width",ww);
		$("#splashBackground").css("height",wh);
	};


var imagePreload = [];
window.onload = function(){
		var img;
		for(var i=1;i<6;i++)
		{
			img = new Image();
			img.src = "/images/background-image"+String(i)+".jpg";
			imagePreload.push(img.src);
		}
	};





function checkRegisterForm(tf)
{
	var e = tf.email_address.value;

	if( e == '' || !e.match(/^[0-9a-z_\-\.]+@[0-9a-z_\-\.]+\.[0-9a-z_\-\.]{2,3}$/i))
	{
		alert("Please enter a valid e-mail address");
		tf.email_address.focus();
		return false;
	}

	return true;
}


//[[>
</script>

<img src='/images/gray-rounded-box.png' alt='gray background image' id='registerBackgroundImage' />

<form action='/survey' method='post' onsubmit='return checkRegisterForm(this)'>
<input type='hidden' name='register' id='register' value='1' />

<div id='registerBox'>
	<img src='/images/registerBoxHeader.png' alt='FindMyMonkey - Local Monkeys, Go Bananas!' id='registerBoxHeader' />

	<div id="registerBoxInputContainer">
		<div id="eac"><input type='text' name='email_address' id='email_address' value='enter email here' onfocus='this.value="";' maxlength='100' /></div>
		<div id='mbs'><input type='image' src='/images/member-btn.png' id='memberBtn' alt='Become a Member'/></div>
	</div>

</div>

	</form>

</body>
</html>