<?php
session_start();

ini_set('display_errors', 0);

define('from_name', 'FindMyMonkey');
define('from_email', 'no-reply@findmymonkey.com');

require("app/db-config.php");
require("app/MysqlDatabase.class.php");

class Survey extends MysqlDatabase
{
	public $localData = array();
	public function __construct()
	{
		parent::Connect( dbHost, dbUser, dbPass, dbName );
		$this->localData = array_merge( $_GET, $_POST );
	}

	public function GetMyLocation()
	{// get my location
		$myIP = explode( ".", $_SERVER[ "REMOTE_ADDR" ] );
		$ipNum = intval( 16777216*$myIP[0] + 65536*$myIP[1] + 256*$myIP[2] + $myIP[3] );
		parent::SetQuery("SELECT locId FROM `table_ipmap` WHERE '{$ipNum}' >= startIpNum
		AND '{$ipNum}' <= endIpNum LIMIT 1");
		$myLoc = parent::DoQuery();
		parent::SetQuery("SELECT * FROM `table_ip2location` WHERE locId='{$myLoc[0]["locId"]}'");
		$locationData = parent::DoQuery();
		return $locationData[0];
	}

	public function StateDropDown()
	{// state drop-down
		parent::SetQuery("SELECT DISTINCT(region) FROM `table_ip2location` WHERE country='US' ORDER BY region ASC");
		$states = parent::DoQuery();
		$MyLocation = $this->GetMyLocation();
		?>
		<select name="user[state]">
		<?php
		foreach( $states as $state )
		{// loop through states
		?>
			<option value="<?=$state["region"];?>" <?php if( $state["region"] == $MyLocation["region"] ) { ?>selected="selected"<?php } ?>><?=$state["region"];?></option>
		<?php
		}
		?>
		</select>
		<?php
	}

	public function CreateAccount()
	{# Create Account
		if( $this->ValidateEmail( $_POST["email_address"] ) )
		{// valid email address
			parent::SetQuery("SELECT * FROM `table_users` WHERE email_address='{$_POST["email_address"]}'");
			$exists = parent::CountDBResults();
			if( !$exists )
			{
				// create account
				parent::SetQuery("INSERT INTO `table_users` VALUES ('','{$_POST["email_address"]}','{$this->GenerateRandomPassword()}')");
				parent::SimpleQuery();
				parent::SetQuery("SELECT * FROM `table_users` WHERE email_address='{$_POST["email_address"]}'");
				$userData = parent::DoQuery();
				// create profile
				parent::SetQuery("INSERT INTO `table_userinfo` VALUES ('{$userData[0]["user_id"]}',
				'',
				'',
				'',
				'',
				'',
				'',
				'')");
				parent::SimpleQuery();
				// create account balance
				parent::SetQuery("INSERT INTO `table_accountbalance` VALUES ('{$userData[0]["user_id"]}','10.00')");
				parent::SimpleQuery();
				// create survey
				parent::SetQuery("INSERT INTO `table_questionnaire` VALUES ('{$userData[0]["user_id"]}',
				'',
				'',
				'',
				'',
				'',
				'',
				'')");
				parent::SimpleQuery();
				$this->SendEmail( $userData[0]["email_address"], 'Welcome to FMM', $this->GetCustomMessage('welcome_email') );
				return array("error" => 0, "user_id" => $userData[0]["user_id"]);
			}
			else
			{// user already registered
				return array("error" => 2);
			}
		}
		else
		{// invalid email
			return array("error" => 1);
		}
	}

	public function GenerateRandomPassword()
	{# Random Password Generator
		$chars = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z",
		"A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
		"0","1","2","3","4","5","6","7","8","9");
		$specials = array("$","!","^","(",")","*","%","@","~");
		$choices = array_merge( $chars, $specials );
		for( $v=0; $v<=7; $v++ )
		{// random
			$char[] = $choices[ mt_rand( 0, sizeOf( $choices ) ) ];
		}
		return implode( $char );
	}

	public function SaveAccount()
	{# Save Account Data
		if( strlen( $this->localData["user"]["password"] ) >=5
		&& $this->localData["user"]["password"] == $this->localData["user"]["password2"] )
		{
			// Update User Info
			parent::SetQuery("UPDATE `table_userinfo` SET
			firstname='{$this->localData["user"]["firstname"]}',
			lastname='{$this->localData["user"]["lastname"]}'
			WHERE user_id='{$_GET["user_id"]}' LIMIT 1");
			parent::SimpleQuery();
			// update password
			parent::SetQuery("UPDATE `table_users` SET
			password='{$this->localData["user"]["password"]}'
			WHERE user_id='{$_GET["user_id"]}' LIMIT 1");
			parent::SimpleQuery();
			unset( $_SESSION["saved-data"] );
			$_SESSION["step"] = 2;
			header("Location: survey?saved=true");
		}
		else
		{// passwords do not match or are too short
			$_SESSION["saved-data"]["user"]["firstname"] = $this->localData["user"]["firstname"];
			$_SESSION["saved-data"]["user"]["lastname"] = $this->localData["user"]["lastname"];
			header("Location: survey?start=true&e=4");
		}
	}

	public function SaveSurvey()
	{# Save Survey Data
		$_SESSION["step"] = 3;
		if( isset( $this->localData["user"]["survey-btn"] ) )
		{// save survey
			$interests = array();
			$interests[] = "<interests>";
			foreach( $_POST["user"]["deals"] as $interest_num => $value )
			{// loop through user interested deals
				$interests[] = "<{$interest_num}>{$value}</{$interest_num}>";
			}
			$interests[] = "</interests>";
			$interests = join( "\n", $interests );
			// Update Questionnaire
			parent::SetQuery("UPDATE `table_questionnaire` SET
			dob='" . strtotime( $_POST["user"]["dob_month"] . " " . $_POST["user"]["dob_day"] . " " . $_POST["user"]["dob_year"] ) . "',
			income_level='{$_POST["user"]["income"]}',
			location_city='{$_POST["user"]["city"]}',
			location_state='{$_POST["user"]["state"]}',
			education='{$_POST["user"]["education"]}',
			gender='{$_POST["user"]["gender"]}',
			interests='{$interests}' WHERE user_id='{$_GET["user_id"]}'");
			parent::SimpleQuery();
			setcookie( "user_id", "", time()-86400, "/" );
			$_SESSION[ "survey_complete" ] = 1;
			header("Location: survey?complete=true");
		}
		else if( isset( $this->localData["user"]["skip-btn"] ) )
		{// skip survey
			setcookie( "user_id", "", time()-86400, "/" );
			$_SESSION[ "survey_complete" ] = 1;
			header("Location: survey?skip=true");
		}
	}

	public function ValidateEmail( $email )
	{# Validate Email Address
		if( eregi( "^[a-zA-Z0-9_]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$]", $email ) ) 
		{// match email address against a regular expression
			return false;
		}

		list( $Username, $Domain ) = split( "@", $email );

		if( getmxrr( $Domain, $MXHost ) ) 
		{// get mail server mx record, see if it exists
			return true;
		}
		else 
		{// check if domain exists
			if( fsockopen( $Domain, 25, $errno, $errstr, 30 ) ) 
			{// check if domain exists
				return true; 
			}
			else 
			{// domain doesn't exist, invalid email address
				return false; 
			}
		}
	}

	public function GetCustomMessage( $message_name )
	{# Get Custom Message
		parent::SetQuery("SELECT * FROM `table_messages` WHERE message_name='{$message_name}'");
		$message = parent::DoQuery();
		return $message[0]["message"];
	}

	public function SendEmail( $to_email, $subject, $my_message )
	{# Send an Email
		// create a boundary string. It must be unique
		// so we use the MD5 algorithm to generate a random hash
		$random_hash = md5(date('r', time()));
		// define the headers we want passed. Note that they are separated with \r\n
		$headers = "From: " . from_name . "<" . from_email . ">\r\nReply-To: " . from_name . "<" . from_email . ">";
		// add boundary string and mime type specification
		$headers .= "\r\nContent-Type: multipart/alternative; boundary=\"PHP-alt-".$random_hash."\"";
		// define the body of the message.
		ob_start(); // Turn on output buffering
?>
--PHP-alt-<?php echo $random_hash; ?> 
Content-Type: text/plain; charset="iso-8859-1"
Content-Transfer-Encoding: 7bit

<?=strip_tags( html_entity_decode( $my_message, ENT_QUOTES ) );?>

--PHP-alt-<?php echo $random_hash; ?> 
Content-Type: text/html; charset="iso-8859-1"
Content-Transfer-Encoding: 7bit

<?=html_entity_decode( $my_message, ENT_QUOTES );?>

--PHP-alt-<?php echo $random_hash; ?>--
<?php
		// copy current buffer contents into $message variable and delete current output buffer
		$message = ob_get_clean();
		// send the email
		$mail_sent = @mail( $to_email, $subject, $message, $headers );
		// if the message is sent successfully print "Mail sent". Otherwise print "Mail failed" 
		return $mail_sent;
	}
}

$Survey = new Survey();
$LocationData = $Survey->GetMyLocation();

if( !isset( $_SESSION["step"] ) )
{// start with first step
	if( isset( $_POST[ "email_address" ] ) )
	{// create account, start survey
		$user_id = $Survey->CreateAccount();
		if( $user_id["error"] == 0 )
		{// user doesn't exist
			$_SESSION["step"] = 1;
			setcookie( "user_id", $user_id["user_id"], time()+300, "/" );
			header("Location: survey?start=true");
		}
	}
	else
	{// error, email address not set
		if( !isset( $_GET[ "complete" ] )
		&& !isset( $_GET[ "start" ] )
		&& !isset( $_GET[ "skip" ] ) )
		{// email address not set
			$user_id["error"] = 3;
		}
		else
		{// display success/failure
			if( isset( $_GET["skip"] ) || isset( $_GET["complete"] ) )
			{
				$_SESSION["step"] = 3;
				$_SESSION[ "survey_complete" ] = 1;
			}
		}
	}
}

if( $_GET[ "method" ] == "SubmitSurvey" )
{// save user survey
	$Survey->SaveSurvey();
}
else if( $_GET[ "method" ] == "SaveUser" )
{// save user account information
	$Survey->SaveAccount();
}

include "assets/header-top.php";
?>

	<title>Survey</title>
	<link rel="StyleSheet" type="text/css" href="http://www.findmymonkey.com/assets/css/application-style.css"/>
	<link rel="StyleSheet" type="text/css" href="http://www.findmymonkey.com/assets/css/global-style.css"/>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>

</head>

<body>

<!--Start Container-->
<div id="container">

	<!--Start Header-->
	<div id="header">
		<div style="overflow: hidden;">
			<div style="float: left;">
				<img src="assets/gfx/misc/find-my-monkey-logo.png" alt="" title="" border="0" style="position: absolute;"/>
			</div>
			<div style="float: left; margin-top: 15px; width: 950px;">
				<div style="margin-left: 145px; overflow: hidden;">
					<!--Find My Monkey Text-->
					<div style="float: left;">
						<img src="assets/gfx/misc/find-my-monkey-text.png" alt="" title="" border="0"/>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!--Start Content Container-->
	<div id="content-container" style="min-height: 400px;">

<?php
if( !$user_id["error"] )
{// no errors, survey not complete
if( $_SESSION["step"] == 1 )
{// step 1
?>



<div id="rw-account-details">


<h2>Please Enter Your Account Details to Complete Registration</h2>

<?php if( $_GET["e"] == 4 ) { ?>
<div class="message-error" style="margin-bottom: 5px;">Error: Your passwords must match and be at least 5 characters in length.</div>
<?php } ?>

		<form style="margin-top:40px; margin-left: 40px;" name="user[account]" action="?method=SaveUser&user_id=<?=$_COOKIE[ "user_id" ];?>" method="post">
		<div><b>First Name</b></div>
		<input type="text" name="user[firstname]" class="text" size="25" value="<?=$_SESSION["saved-data"]["user"]["firstname"];?>"/>

		<div style="margin-top: 7px;"><b>Last Name</b></div>
		<input type="text" name="user[lastname]" class="text" size="25" value="<?=$_SESSION["saved-data"]["user"]["lastname"];?>"/>

		<div style="margin-top: 7px;"><b>Password</b></div>
		<input type="password" name="user[password]" class="text" size="25"/>

		<div style="margin-top: 7px;"><b>Repeat Password</b></div>
		<input type="password" name="user[password2]" class="text" size="25"/>

		<div style="margin-top: 10px;">
			<button type="submit" name="user[save-btn]" class="button-special">Save Information</button>
		</div>
		</form>
		
</div>
		
		
		
<div id="rw-survey-right">

<h4 class="survey">Our Monkeys search high and low for the best places to eat, drink, and play in your city.</h4>

<ul><u>Membership Benefits</u>

<li>Each day, we will send you one exclusive deal only available to our monkey members</li>
<li>Our Deals Include: Spas, Salons, Restaurants, Theatre, Classes, Events, Bars, Museums, Concerts, Gyms, and much more!</li>
<li>FindMyMonkey gives 10% of all proceeds to a local charity</li>
<li>FindMyMonkey is a <span class="green">GREEN</span> company</li>

</ul>

</div>
		
		
		
		
		
<?php
}
else if( $_SESSION["step"] == 2 )
{// step 2 survey
?>


<div id="rw-thank-you">

		<h2>Thank's For Signing Up</h2>

<p>Thank you for joining FindMyMonkey.com. <br/> <br/>Please check your inbox shortly as you will be receiving a $10 coupon that can be redeemed towards any deal on findmymonkey.com. <br/> <br/>In the meantime, it would be a great help if you could complete the following survey. <br/> <br/>Also, please feel free to give us feedback on how you think we can better serve you, our fellow monkeys, as best possible!</p>

</div>

<div id="rw-survey-right">

		<h2>Please Complete the Following Survey</h2>

		<form name="user[survey]" action="?method=SubmitSurvey&user_id=<?=$_COOKIE[ "user_id" ];?>" method="post">

		<div style="margin-top: 7px;"><b>Date of Birth</b></div>
		<div style="overflow: hidden;">
			<div class="select-styling-div" style="float: left; margin-top: 5px;">
				<select name="user[dob_month]" style="padding-right: 3px;">
					<option>January</option>
					<option>February</option>
					<option>March</option>
					<option>April</option>
					<option>May</option>
					<option>June</option>
					<option>July</option>
					<option>August</option>
					<option>September</option>
					<option>October</option>
					<option>November</option>
					<option>December</option>
				</select>
			</div>
			<div class="select-styling-div" style="margin-left: 5px; float: left; margin-top: 5px;">
				<select name="user[dob_day]" style="padding-right: 3px;">
					<?php
						for($p=1; $p<=31; $p++)
						{
						?>
						<option><?=$p;?></option>
						<?php
						}
					?>
				</select>
			</div>
			<div class="select-styling-div" style="margin-left: 5px; float: left; margin-top: 5px;">
				<select name="user[dob_year]" style="padding-right: 3px;">
					<?php
						for($y=1999; $y>=1925; $y--)
						{
						?>
						<option><?=$y;?></option>
						<?php
						}
					?>
				</select>
			</div>
		</div>

		<div style="margin-top: 7px;"><b>Income Level</b></div>
		<div>
			<div class="select-styling-div" style="margin-top: 5px; width: 150px; text-align: right;">
				<select name="user[income]" style="margin-right: 3px; width: 150px;">
				<option value="Less Than 35k">Less Than $35,000</option>
				<option value="Between 35k and 42k">$35,000-$42,000</option>
				<option value="Between 42k and 60k">$42,000-$60,000</option>
				<option value="Between 60k and 75k">$60,000-$75,000</option>
				<option value="Between 75k and 95k">$75,000-$95,000</option>
				<option value="More Than 95k">More Than $95,000</option>
				</select>
			</div>
		</div>

		<div style="margin-top: 7px;"><b>Location</b></div>
		<div style="overflow: hidden;">
			<div style="float: left;">
				<input type="text" name="user[city]" size="30" value="<?=$LocationData["city"];?>" class="text"/>
			</div>
			<div class="select-styling-div" style="float: left; margin-top: 5px; margin-left: 5px;">
				<?=$Survey->StateDropDown();?>
			</div>
		</div>

		<div style="margin-top: 7px;"><b>Education</b></div>
		<div>
			<div class="select-styling-div" style="margin-top: 5px; width: 150px; text-align: right;">
				<select name="user[education]" style="margin-right: 3px; width: 150px;">
					<option>Some High School</option>
					<option>High School</option>
					<option>Some College</option>
					<option>College</option>
				</select>
			</div>
		</div>

		<div style="margin-top: 7px;"><b>Gender</b></div>
		<div>
			<input type="radio" name="user[gender]" id="user-gender-1" value="male"/><label for="user-gender-1">Male</label>
			<input type="radio" name="user[gender]" id="user-gender-2" value="female"/><label for="user-gender-2">Female</label>
		</div>

		<div style="margin-top: 7px;"><b>Deals of Interest</b></div>
		<div>
			<table cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td><input type="checkbox" name="user[deals][]" id="user-interest-1" value="spas"/><label for="user-interest-1">Spas</label></td>
					<td><input type="checkbox" name="user[deals][]" id="user-interest-2" value="salons"/><label for="user-interest-2">Salons</label></td>
					<td><input type="checkbox" name="user[deals][]" id="user-interest-3" value="restaurants"/><label for="user-interest-3">Restaurants</label></td>
					<td><input type="checkbox" name="user[deals][]" id="user-interest-4" value="bars"/><label for="user-interest-4">Bars</label></td>
				</tr>
				<tr>
					<td><input type="checkbox" name="user[deals][]" id="user-interest-5" value="theatre"/><label for="user-interest-5">Theatre</label></td>
					<td><input type="checkbox" name="user[deals][]" id="user-interest-6" value="classes"/><label for="user-interest-6">Classes</label></td>
					<td><input type="checkbox" name="user[deals][]" id="user-interest-7" value="events"/><label for="user-interest-7">Events</label></td>
					<td><input type="checkbox" name="user[deals][]" id="user-interest-8" value="museums"/><label for="user-interest-8">Museums</label></td>
				</tr>
				<tr>
					<td><input type="checkbox" name="user[deals][]" id="user-interest-9" value="concerts"/><label for="user-interest-9">Concerts</label></td>
					<td><input type="checkbox" name="user[deals][]" id="user-interest-10" value="gyms"/><label for="user-interest-10">Gyms</label></td>
					<td><input type="checkbox" name="user[deals][]" id="user-interest-11" value="outdoor activities"/><label for="user-interest-11">Outdoor Activities</label></td>
					<td></td>
				</tr>
			</table>
		</div>

		<div style="margin-top: 10px;">
			<button type="submit" name="user[survey-btn]" class="button-special">Submit Survey</button>
			<button type="submit" name="user[skip-btn]" class="button-default">Skip Survey</button>
		</div>
		</form>
		
		</div>
<?php
}
else if( $_SESSION["step"] == 3 )
{// step 3 display success or failure
	$_SESSION["destroy"] = 1;
	if( $_SESSION[ "survey_complete" ] == 1 )
	{// survey complete
		$_SESSION["destroy"] = 1;
		if( $_GET["complete"] )
		{// user completed survey
	?>
	
		<div id="rw-step-three-left">	
	
		<? /* =html_entity_decode($Survey->GetCustomMessage( 'survey_completed' ), ENT_QUOTES ); */ ?>
		
		
		
				<p>Thank you for Signing Up for FindMyMonkey.com, please click the button below to view the latest deals in your area.</p>
		<br/>
		<br/>
		<a href="http://www.findmymonkey.com/offers"><img style="margin-top:25px; margin-left: 160px; border: none;" src="/images/survey.png"/> </a>
		
		   </div>  
		
		
			<div id="rw-step-three-right">  <img style="margin-left: 75px;" src="/images/findmymonkey-325.jpg"/>  </div>  
		
		
		
		
		
		
		
		
	<?php
		}
		else if( $_GET["skip"] )
		{// user skipped survey
	?>
	
	<div id="rw-step-three-left">	
	

	
		<? /* =html_entity_decode( $Survey->GetCustomMessage( 'skip_survey' ), ENT_QUOTES ); */ ?>
		<p>Thank you for Signing Up for FindMyMonkey.com, please click the button below to view the latest deals in your area.</p>
		<br/>
		<br/>
		<a href="http://www.findmymonkey.com/offers"><img style="margin-top:25px; margin-left: 160px; border: none;" src="/images/survey.png"/> </a>
		
		   </div>  
		
		
			<div id="rw-step-three-right">  <img style="margin-left: 75px;" src="/images/findmymonkey-325.jpg"/>  </div>  
		
		
		
		
	<?php
		}
	}
}
}// no errors
else if( $user_id["error"] == 1 )
{// invalid email address
	$_SESSION["destroy"] = 1;
?>
	<center>You entered an invalid email address <a href="/">try again</a>.</center>
<?php } else if( $user_id["error"] == 2 ) { $_SESSION["destroy"] = 1; ?>
	<center>Oops! It appears that you've already signed up.</center>
<?php } else if( $user_id["error"] == 3 ) { $_SESSION["destroy"] = 1; ?>
	<center>You must start the signup process from <a href="/">here</a>.</center>
<?php } ?>

	</div>
	<!--End Content Container-->



</div>
<!--End Container-->

<!--Start Footer Container-->
<div id="footer-container">

<!--Start Footer-->
<div id="footer">
	<div id="footer-content">

	</div>
</div>

<!--Second Footer-->
<div id="second-footer">
Copyright &copy; 2010 FindMyMonkey, L.L.C. All Rights Reserved
</div>

</div>
<!--End Footer Container-->

</body>
</html>
<?php if( $_SESSION["destroy"] == 1 ) { session_destroy(); } ?>