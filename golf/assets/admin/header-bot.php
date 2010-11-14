</head>

<!--Body-->
<body>

<!--Start Container-->
<div id="container">

	<!--Start Header-->
	<div id="header">
		<div style="overflow: hidden;">
			<div style="float: left;">
				<a href="deals"><img src="assets/gfx/misc/find-my-monkey-logo.png" alt="" title="" border="0" style="position: absolute;"/></a>
			</div>
			<div style="float: left; margin-top: 15px; width: 950px;">
				<div style="margin-left: 145px; overflow: hidden;">
					<!--Find My Monkey Text-->
					<div style="float: left;">
						<a href="deals"><img src="assets/gfx/misc/find-my-monkey-text.png" alt="" title="" border="0"/></a>
					</div>
					<!--Float Right-->
					<div style="float: right; overflow: hidden; margin-right: 390px;">
						<div style="position: absolute; overflow: hidden;">
						<!--Start Float Right-->
						<div style="float: right;">
						<div class="city-dropdown-large" style="cursor: pointer;" onclick="CityDropdown()">
							<span id="first-city"><!--Loads Dynamically--></span>
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
					<div style="float: left;">
						Administration Control Panel
					</div>
					<div style="float: right; text-align: right;">
						<a href="cart" style="padding-left: 8px; padding-right: 8px;">View Cart</a>
						<a href="about" style="padding-left: 8px; padding-right: 8px;">About</a>
						<a href="contact" style="padding-left: 8px; padding-right: 8px;">Contact</a>
						<a href="help" style="padding-left: 8px; padding-right: 8px;">Help</a>
						<a href="#"><img src="assets/gfx/icons/fb-connect.png" border="0" alt="" title="" style="height: 17px;"/></a>
					</div>
				</div>
				<div style="text-align: center;" class="greeting-text">
					Be a Monkey in your city: eat, shop, and play; one amazing opportunity featured daily at 50% - 95% off!!
				</div>
			</div>
		</div>
	</div>

	<!--Start Top Navigation-->
	<div id="navigation-top">
		<div class="nav-tab-top"><a href="control_panel"><img src="assets/gfx/icons/navigation-home.gif" alt="" border="0"/><span>Home</span></a></div>
		<div class="nav-tab-top"><a href="admin"><img src="assets/gfx/icons/navigation-admin.gif" alt="" border="0"/><span>Admin</span></a></div>
	</div>

	<!--Start Content Container-->
	<div id="content-container" style="padding-left: 15px; width: 920px;">

<?php if( !$hideLeftNavigation ) { // Don't Hide Left Navigation ?>
	<!--Start Left Navigation-->
	<div id="navigation-left">
		<div id="navigation-left-header">
			<div>Control Panel</div>
		</div>
		<div class="nav-tab-left"><a href="control_panel?method=ViewCustomMessages">Messages</a></div>
		<div class="nav-tab-left"><a href="control_panel?method=ViewComments">Comments</a></div>
		<div class="nav-tab-left"><a href="control_panel?method=ViewCharities">Charities</a></div>
		<div class="nav-tab-left"><a href="control_panel?method=ViewCustomPages">Pages</a></div>
		<div class="nav-tab-left"><a href="control_panel?method=ViewCommissions">Commissions</a></div>
		<div class="nav-tab-left"><a href="control_panel?method=ViewTransactions">Orders</a></div>
		<div class="nav-tab-left"><a href="control_panel?method=ViewOffers">Offers</a></div>
		<div class="nav-tab-left"><a href="control_panel?method=ViewAggregatedDeals">Aggregated Deals</a></div>
		<div class="nav-tab-left"><a href="control_panel?method=ViewSubscribers">Subscribers</a></div>
		<div class="nav-tab-left"><a href="control_panel?method=ViewCustomers">Customers</a></div>
		<div class="nav-tab-left"><a href="control_panel?method=ViewMerchants">Merchants</a></div>
		<div class="nav-tab-left"><a href="control_panel?method=ViewLocations">Locations</a></div>
		<div class="nav-tab-left"><a href="admin?method=DoAdminLogout&b=1">Logout</a></div>
	</div>
<?php } ?>

	<!--Start Content DIV-->
	<div id="content" style="min-height: 450px; width: 705px; <?php if( $hideLeftNavigation ) { ?> width: auto; float: none;<?php } ?>">
