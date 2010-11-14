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
			   //  $("#deals_list").load('_deals_list.php?randval='+ Math.random());
			  }, 1000);				
				
				// http://www.building58.com/examples/tabSlideOut.html
        $('.slide-out-div').tabSlideOut({
            tabHandle: '.handle',                     //class of the element that will become your tab
            pathToTabImage: 'http://www.building58.com/examples/images/contact_tab.gif', //path to the image for the tab //Optionally can be set using css
            imageHeight: '122px',                     //height of tab image           //Optionally can be set using css
            imageWidth: '40px',                       //width of tab image            //Optionally can be set using css
            tabLocation: 'right',                      //side of screen where tab lives, top, right, bottom, or left
            speed: 300,                               //speed of animation
            action: 'click',                          //options: 'click' or 'hover', action to trigger animation
            topPos: '200px',                          //position from the top/ use if tabLocation is left or right
            leftPos: '20px',                          //position from left/ use if tabLocation is bottom or top
            fixedPosition: false                      //options: true makes it stick(fixed position) on scroll
        });

				$('#about').click(function(event) {
						$("#deals_list").load('_about.php?randval='+ Math.random());
		    });	

				$('#blog').click(function(event) {
						$("#deals_list").load('_blog.php?randval='+ Math.random());
		    });	
		    
				$('#press').click(function(event) {
						$("#deals_list").load('_press.php?randval='+ Math.random());
		    });	

				$('#businesses').click(function(event) {
						$("#deals_list").load('_businesses.php?randval='+ Math.random());
		    });	

				$('#signUp').click(function(event) {
						$("#deals_list").load('_signUp.php?randval='+ Math.random());
		    });	

				$('#faq').click(function(event) {
						$("#deals_list").load('_faq.php?randval='+ Math.random());
		    });	
		    
				$('#contactUs').click(function(event) {
						$("#deals_list").load('_contactUs.php?randval='+ Math.random());
		    });	

				$('#whyFindMyMoney').click(function(event) {
						$("#deals_list").load('_whyFindMyMonkey.php?randval='+ Math.random());
		    });	
		    
		    
				$('#privacy').click(function(event) {
						$("#deals_list").load('_privacy.php?randval='+ Math.random());
		    });						    

//						$('.menu-inside_Table td').mouseenter(function(){
//							$(this).css({background:'white'});
//						}).mouseleave(function(){
//							$(this).css({background:'#F7F0E0'});
//						});	
					  
					  
			});
		</script>
</head>

<body>
	<div id="fb-root"></div>
<script>
window.fbAsyncInit = function() {

	FB.init({
	appId : '<?php  echo $appid ;   ?>',
	status : true, // check login status
	cookie : true, // enable cookies to allow the server to access the session
	xfbml : true // parse XFBML
	});
	
   FB.Event.subscribe('auth.login', function(response) {
  		onConnected();
   });
   
   FB.Event.subscribe('auth.logout', function(response) {
   	
			FB.logout(function(response) {
			});

   });
  
   FB.getLoginStatus(function(response) {
   	
       if (response.session) {
       	
       		 $('#isLoggedInToFB').val('1');

     		//		 onConnected();
       } else {

 					 $('#isLoggedInToFB').val('0');
       		 
      	//	 onNotConnected();
    	 }
   });
};

(function() {
var e = document.createElement('script');
e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
e.async = true;
document.getElementById('fb-root').appendChild(e);
}());


</script>


<div id='body_background'>
	
<div id='top' class=' container ' style='background:#FFD171;'     >


			<div   id='inside-left'  onClick="window.location.reload()"    >
				<img  src='../images/fmmlogo.png'  style='cursor:pointer;'  >
			</div>
			
			<div   id='inside-right' >
				<div  id='inside-right-0' >
					
					<table     >
						<tr>
							<td width=45%>
								<div id='findmymonkey'  onClick="window.location.reload()"    style='
									
									font-style: normal;
									color:#FE2E2E;
									font-size:16px;
									
									'    >
									<img src='../images/find-my-monkey-logo2.png'   style='cursor:pointer;'  ><br>
									All the best daily deal sites in one fantastic place
								</div>
								
							</td>
							<td align=right     >
								
								<div   style="
															margin:0px 30px 0px 0px;
															background: url('http://webimages.mailchimp.com/img/home/signup_login_bg.gif') top right no-repeat;
															text-indent:-999em;
															padding:0;
															display:block;
															height:35px;"  >
								</div>
					<div  style='
						padding:20px 40px 0px 0px;
						'  >
								
									<img   style='width:35px'  src='http://icons.iconarchive.com/icons/fasticon/web-2/48/Feed-icon.png'>
									<img   style='width:35px'  src='http://icons.iconarchive.com/icons/fasticon/web-2/48/FaceBook-icon.png'>
									<img   style='width:35px'  src='http://icons.iconarchive.com/icons/fasticon/web-2/48/Twitter-icon.png'>

					</div>	
								
<!--									
								<div id='findDealsInYourCity'    >
									
									Find deals in your city:&nbsp;&nbsp;
										<div  style='text-align:center;cursor:pointer;float:right;width:215px;height:50px;
											background:url(http://www.findmymonkey.com/assets/gfx/bg/city-dropdown-bg.png) no-repeat'  >
											Los Angeles
										</div>
	
								</div>
								
	-->							

								
							</td>
						</tr>
					</table>
					
					
				</div>
				<div  id='inside-right-1'   >
	<!--				
					
					<table>
						<tr>
							<td width:50%>
								Welcome, Guest&nbsp;&nbsp;|&nbsp;&nbsp;Login, Register
							</td>
							<td align=right>
								<div    >
									View Cart, Account, Help
								</div>
							</td>
						</tr>
					</table>
					
					-->
				</div>

			</div>	
	



</div>
	
<style>
#menu{
#height:auto;
}
#menu-inside{
font-size:18px;
color:white;
}
#menu-inside table{
margin:0px 0px 0px 20px;
width:819px;
}
#menu-inside td{
text-align:center;
vertical-align:bottom;
width:24%;
height:40px;
cursor:pointer;	
background:url(../images/tabPurple.png) no-repeat;
background-position: bottom center;
}
#menu-inside div{
padding:0px 0px 3px 0px;	
}
.bottomLink{
	cursor:pointer;	
}
.slide-out-div {
    padding: 20px;
    width: 250px;
    background: #ccc;
    border: 1px solid #29216d;
}    
</style>
	
<div  class=' container '    style='background:#FFD171;
<?php

if( $Application->browserIs_ie()){
	echo "margin-top:-5px";
};

?>'	>

<!--<div style='background:url(../images/orangeEnd.png) bottom left repeat-x'  >-->
	<div id='menu'         >
		<div id='menu-inside'   >
			<table class='menu-inside_Table'  >
				<tr>
<!--					
					<td   style='background:none;width:8%'  >
					</td>			
					-->
								
					<td   >

						<div>
							Today's Deal
						</div>
					</td>
					<td >
						<div id='whyFindMyMoney'>Why FindMyMonkey
						</div>
						
					</td>
					<td >
						<div>Charity
						</div>
					</td>
					<td >
						<div>Bananna Corner
						</div>
						
					</td>
				
				</tr>
			</table>
		</div>
	</div>
<!--</div>-->
</div>


<style>
#middle{
min-height: 190px;
}
</style>
<div id='middle' class=' container '    style='border:0px solid gray; background:white;
	
	<?php
	
	if( $Application->browserIs_ie()){
		echo "margin-top:-7px";
	};
	
	?>'       >

	<div id='deals_list'      >
		<?php  include('_deals_list.php');   ?>
	</div>
	
</div>

<style>
#bottom{
padding:50px 0px 40px 0px;
height:140px;
background:#FFD171;
}
#bottom td{
	color:purple;
	font-size:14px;
	text-align:center;
	padding:30px 0px 30px 0px;
}
</style>
	<div id='bottom' class=' container '     >
		<table>
			<tr>
				<td>
					<div id='about'  class=' bottomLink' >
						About
					</div>
				</td>
				<td>
					<div id='blog'  class=' bottomLink' >
						Blog
					</div>
				</td>
				<td>
					<div id='press'  class=' bottomLink' >
						Press
					</div>
				</td>
				<td>
					<div id='businesses'  class=' bottomLink' >
						Businesses
					</div>
				</td>
				<td>
					<div id='faq'  class=' bottomLink' >
						FAQ
					</div>
				</td>
				<td>
					<div id='contactUs'  class=' bottomLink' >
						contactUs
					</div>
				</td>
			</tr>
			<tr>
				<td colspan=6>
					©2010 FindMyMonkey, Inc.   All rights reserved                                   Terms of Use  |  <span id='privacy' class='bottomLink' >Privacy Policy</a>
				</td>
			</tr>
		</table>
	</div>
</div>



<div class="slide-out-div">
    <a class="handle" href="http://link-for-non-js-users.html">Content</a>
    <h3>Contact me</h3>
    <p>Thanks for checking out my jQuery plugin, I hope you find this useful.
    </p>
    <p>This can be a form to submit feedback, or contact info</p>
</div>



<form target="_blank" id='referTo' name='referTo' method='POST'>
</body>
	  