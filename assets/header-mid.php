<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>

<!--Style-->
<link rel="StyleSheet" type="text/css" href="assets/css/global-style.css"/>
<link rel="StyleSheet" type="text/css" href="assets/css/application-style.css"/>

<!--RSS Feeds-->
<link rel="alternate" type="application/rss+xml" title="FindMyMonkey.com, All Recent Deals" href="http://www.findmymonkey.com/rss?feed_type=most_recent"/>

<!--Global JavaScripts-->
<script type="text/javascript" src="assets/js/lib/jquery-1.3.2.js"></script>
<script type="text/javascript" src="assets/js/Encoder.class.js"></script>
<script type="text/javascript" src="assets/js/Cookie.class.js"></script>
<script type="text/javascript" src="assets/js/Preload.class.js"></script>
<script type="text/javascript" src="assets/js/Hash.class.js"></script>
<script src="http://js.nicedit.com/nicEdit-latest.js" type="text/javascript"></script>

<script type="text/javascript">
<!--

function StatusMessage( id, message )
{
	this.id = id;
	this.message = message;
	this.dots = '..';
	this.loadingComplete = 0;
	this.Start = function()
	{// display loading status
		$( "#" + this.id ).html( this.message + this.dots );
	}

	this.AppendDots = function()
	{// 
		if( this.loadingComplete == 0 )
		{// not completed
			$( "#" + this.id ).append( this.dots );
		}
	}

	this.SetComplete = function()
	{// update status
		this.loadingComplete = 1;
	}
}

var message_2;

$(document).ready(function(){
	message_2 = new StatusMessage( 'first-city', 'Locating' );
	message_2.Start();
	setInterval(function(){message_2.AppendDots()}, 500);
});

	function AppCall( backend , params, callback )
	{// make a call to the back-end
		var methods = new Array( 'start', 'success', 'error', 'complete' );
		var defaults = new Array( 'AppStart', 'AppSuccess', 'AppError', 'AppComplete' );
		for( p in methods )
		{// loop through callBack methods
			if ( ! callback[ p ] )
			{// if method is not set, use default
				callback[ p ] = defaults[ p ];
			}
		}
		var query_string = [];
		for( i in params )
		{// loop to get the params
			query_string.push( i + '=' + params[ i ] );
		}
		// call the back-end
		$.ajax({
			url: 'app/' + backend + '.class.php?' + query_string.join( '&' ),
			dataType: 'json',
			encoding: 'UTF-8',
			beforeSend: callback[ 'start' ],
			success: callback[ 'success' ],
			error: callback[ 'error' ],
			complete: callback[ 'complete' ]
		});
	}

	function AppStart( xhrInstance )
	{// display loading dialog while processing request
		$( '#content' ).html( '<br/><br/><br/><br/><center><img src="assets/gfx/loading.gif"/>' );
	}

	function AppError( xhrInstance, message, optional )
	{// error occurred while processing, alert user
		alert( message );
	}

	function AppComplete( xhrInstance, status )
	{// completed
	}

	function DoFocus( id )
	{// focus on an element
		setTimeout(function(){
			document.getElementById( id ).focus();
		}, 200);
	}

	function LoadData( id, value )
	{// load data into an input element
		if( value!=false && document.getElementById( id ) )
		{// value not null/false
			document.getElementById( id ).value = value;
		}
	}

	function GetData( id )
	{// get data from an id
		if( document.getElementById( id ) )
		{
			return document.getElementById( id ).value;
		}
	}

	function CityDropdown()
	{// city dropdown
		var dropdown = document.getElementById("city-dropdown");
		switch( dropdown.style.display )
		{
			case 'block':
				dropdown.style.display = 'none';
				<?php
					if( $_GET["location_id"] != $Application->GetData("location_id") )
					{
						if( stristr( $_SERVER["REQUEST_URI"], "offers" ) )
						{// currently on the offers page
							$relocate = "offers";
						}
						else
						{// currently on the deals page
							$relocate = "deals";
						}
				?>
				location = "<?=$relocate;?>?location_id=<?=$Application->GetData("location_id");?>";
				<?php
					}
				?>
			break;
			default: dropdown.style.display = 'block'; break;
		}
	}

// Populate City Dropdown Menu
LoadCities();
function LoadCities()
{// load cities
	AppCall( 'Application', {'method' : 'GetLocations', 'b' : 1, 'location_id' : '<?=$_GET["location_id"];?>', 'current_page' : '<?php if( stristr( $_SERVER["REQUEST_URI"], "offers" ) ) { echo "offers"; } else { echo "deals"; } ?>'}, {'success' : AppLoadCities} );
}

function AppLoadCities( data, status )
{// finished fetching data, now process the data
	message_2.SetComplete();
	$("#city-dropdown").html( Encoder.htmlDecode( data["cities"] ) );
	$("#first-city").html( Encoder.htmlDecode( data["first_city"] ) );
	LoadData( 'city-alerts', data["first_city"] );
}

// add offer to cart
function AddToCart( offer )
{// add offer to cart
	AppCall( 'Application', {'method' : 'AddToCart', 'b' : 1, 'offer_code' : offer}, {'success' : AddToCartResponse} );
}

function AddToCartResponse( data, status )
{// response add to cart
	location = data["continue"];
}

// user functions
GetUserStatus();
function GetUserStatus()
{// check if user is logged in
	AppCall( 'Application', {'method' : 'IsUserLoggedIn', 'b' : 1}, {'success' : LoadUserStatus} );
}

function LoadUserStatus( data, status )
{// get user status
	//message_1.SetComplete();
	if( !data["offline"] )
	{// user logged in
		LoadData('alerts-email', data["email_address"]);
	}
}

//-->
</script>
