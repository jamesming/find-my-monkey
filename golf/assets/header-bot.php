</head>

<!--Body-->
<body>

<!--Start Container-->
<div id="container">

	<!--Start Header-->
	<div id="header">
		<div style="overflow: hidden;">
			<div style="float: left;">
				<a href="offers"><img src="<?=path;?>assets/gfx/misc/find-my-monkey-logo.png" alt="" title="" border="0" style="position: absolute;"/></a>
			</div>
			<div style="float: left; margin-top: 15px; width: 950px;">
				<div style="margin-left: 145px; overflow: hidden;">
					<!--Find My Monkey Text-->
					<div style="float: left;">
						<a href="offers"><img src="<?=path;?>assets/gfx/misc/golf-monkey-logo.png" alt="" title="" border="0"/></a>
					</div>
					<!--Float Right-->
					<div style="float: right; overflow: hidden; margin-right: 390px;">
						<div style="position: absolute; overflow: hidden; z-index: 999;">
						<!--Start Float Right-->
						<div style="float: right;">
						<div class="city-dropdown-large" style="cursor: pointer;" onclick="CityDropdown()">
							<span id="first-city"><!--Loads Dynamically-->Locating...</span>
						</div>
						<div id="city-dropdown" style="display: none; background-color: #4A2410;">
							<!--Loads Dynamically-->
						</div>
						</div>
						<!--End Float Right-->
						<!--Find Deals Intro-->
						<div style="float: right; padding-top: 6px; padding-right: 4px;" class="location-chooser-intro">
							Find Deals in Your City:
						</div>
						</div>
						<!--End City Dropdown-->
					</div>
				</div>
				<div id="navigation" style="overflow: hidden;">
					<div style="float: left;" id="user-status">
						<?php
						if( $_SESSION["logged-in"] == 1 )
						{// user logged in
							if ( $_SESSION["user-data"]["firstname"]!="" )
							{// user has a first name
								?>
								Welcome, <b><?=$_SESSION["user-data"]["firstname"];?></b> | <a href="account">My Account</a>, <a href="?method=DoUserLogout&b=1">Logout</a>
								<?php
							}
							else if( $_SESSION["user-data"]["email_address"] )
							{// user email address as name
								?>
								Welcome, <b><?=$_SESSION["user-data"]["email_address"];?></b> | <a href="account">My Account</a>, <a href="?method=DoUserLogout&b=1">Logout</a>
								<?php
							}
							else
							{// user not logged in
							?>
							Welcome, <b>Guest</b> | <a href="login">Login</a>, <a href="register">Register</a>
							<?php
							}
						}
						else
						{// user not logged in
						?>
						Welcome, <b>Guest</b> | <a href="login">Login</a>, <a href="register">Register</a>
						<?php
						}
						?>
					</div>
					<div style="float: right; text-align: right;">
						<a href="cart" style="padding-left: 8px; padding-right: 8px;">View Cart</a>
						<a href="about" style="padding-left: 8px; padding-right: 8px;">About</a>
						<a href="contact" style="padding-left: 8px; padding-right: 8px;">Contact</a>
						<a href="help" style="padding-left: 8px; padding-right: 8px;">Help</a>
						<!-- <a href="#"><img src="<?=path;?>assets/gfx/icons/fb-connect.png" border="0" alt="" title="" style="height: 17px;"/></a> -->
					</div>
				</div>
				<div style="text-align: center;" class="greeting-text">
					Be a Monkey in your city: eat, shop, and play; one amazing opportunity featured daily at 50% - 95% off!!
				</div>
			</div>
		</div>
	</div>
