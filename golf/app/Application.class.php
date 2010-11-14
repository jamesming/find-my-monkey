<?php
# Global Cookie Message
$message = stripslashes( stripslashes( $_COOKIE[ "msg" ] ) );
setcookie( "msg", "", time()-86400, "/" );

$message_type = $_COOKIE[ "msg_type" ];
setcookie( "msg_type", "", time()-86400, "/" );

$temp = $_COOKIE[ "temp" ];
setcookie( "temp", "", time()-86400, "/" );

# Prevent Default
ini_set('display_errors', 0);

# Include the Database Class
require("db-config.php");
require("app-config.php");
require("MysqlDatabase.class.php");
require("HtmlElement.class.php");

# Initiate a _SESSION
//session_name ( sessionName );
session_start ( );

/*
** Application Class
**  Description: Operates FMM's Back-End Application
**  Date: 03/31/2010
**  Version: 1
*/

class Application extends MysqlDatabase
{# Create the Application Class, extending the MySQL Database Class
	private $localData = array();
	private $rowsPerPage;
	public function __construct( $data )
	{# Initialize the Application Class, Establish a Database Connection
		parent::Connect( dbHost, dbUser, dbPass, dbName );

		# Set Local Array with _GET/_POST Data
		$this->localData = $data;

		# Paging Defaults
		$this->rowsPerPage = 15;
		if( !$this->localData["s"] ) { $this->localData["s"] = 0; }

		# Check for Referrer
		if( isset( $this->localData["ref_id"] ) )
		{// user has been referred
			setcookie("referrer", $this->localData["ref_id"], time()+(86400*3), "/");
			//
			$requestURI = rtrim(str_replace("ref_id={$this->localData["ref_id"]}", "", $_SERVER["REQUEST_URI"]), "&");
			header("Location: http://" . $_SERVER["SERVER_NAME"] . $requestURI);
		}

		# Check for Affiliate
		if( isset( $this->localData["aff_id"] ) )
		{// affiliate referrer set
			setcookie("affiliate", $this->localData["aff_id"], time()+(86400*3), "/");
			//
			$requestURI = rtrim(str_replace("aff_id={$this->localData["aff_id"]}", "", $_SERVER["REQUEST_URI"]), "&");
			header("Location: http://" . $_SERVER["SERVER_NAME"] . $requestURI);
		}

		if( isset( $this->localData[ 'form-data' ] ) )
		{# Temporarily Save Form Data into a $_SESSION
			unset( $_SESSION[ 'form-data' ] );
			$_SESSION[ 'form-data' ] = array();
			foreach( $this->localData as $sess_name => $sess_val )
			{# Loop Through Each Element
				if( !is_array( $sess_val ) )
				{# Not Array
					$_SESSION[ 'form-data' ][ $sess_name ] = $sess_val;
				}
				else
				{# Array
					foreach( $sess_val as $n => $v )
					{
						$_SESSION[ 'form-data' ][ $sess_name ][ $n ] = $v;
					}
				}
			}
		}

		if( isset( $this->localData[ 'method' ] ) && $this->localData[ 'b' ] == 1 )
		{// Call a Method
			$this->{ $this->localData[ 'method' ] }( );
		}
	}

/**
***
*** General Functions
***
**/

	public function GetData( $field )
	{// get local data
		return $this->localData[ $field ];
	}

	public function TimestampConverter( $string )
	{# Convert String to Timestamp
		return strtotime( $string );
	}

	public function EncodeHtml( $input )
	{# Html Encode
		return htmlentities( $input, ENT_QUOTES );
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

	public function JsonOutput( $input )
	{# Output JSON Encoded Data to the Front-End
		$output = array();
		foreach( $input as $name => $value )
		{# Loop Through Data, Encoding Special Chars
			$output[ $name ] = $value;
		}
		echo json_encode( $output );
	}

	public function Pagination( $data )
	{# Pagination
		$data['totalPages'] = ceil( $data['totalRows'] / $this->rowsPerPage );
		if( $data['totalPages'] > 1 )
		{
			$output = array();
			$output[] = "<div style=\"margin-top: 15px; margin-bottom: 5px;\">";

			$strings = array();
			foreach( $this->localData as $n => $v ){ if( $n!="s") { $strings[]="{$n}={$v}"; } }

			for( $i = 0; $i < $data['totalPages']; $i++ )
			{# Create Pages
				if( $this->localData["s"] == ( $i * $this->rowsPerPage ) )
				{# Current Page
					$style = "class=\"paging-selected\"";
				}
				else
				{# Not Current Page
					$style = "class=\"paging\"";
				}
				$output[] = "<a href=\"?" . join( "&", $strings ) . "&s="
					. ( $i * $this->rowsPerPage ) . "\" {$style}>" . ($i + 1) . "</a>";
			}
			$output[] = "</div>";
			return join( " ", $output );
		}
		else
		{//
			//return true;
		}
	}

	public function GetTotalCoupons()
	{# Total Coupons
		parent::SetQuery("SELECT COUNT(offer_id) as coupons FROM `table_offers`");
		$total = parent::DoQuery();
		return number_format($total[0]["coupons"], 0, '.', ',');
	}

	public function GetTotalSaved()
	{# Total Dollars Saved
		parent::SetQuery("SELECT SUM(`table_offers`.`price`) as total_cost, SUM(`table_offers`.`value`) as total_value
		FROM `table_offers`, `table_purchased` WHERE `table_purchased`.`offer_id`=`table_offers`.`offer_id`");
		$totals = parent::DoQuery();
		return number_format($totals[0]["total_value"] - $totals[0]["total_cost"], 2, '.', ',');
	}

	public function GetMyLocation()
	{# Get My Location by IP Address
		$myIP = explode( ".", $_SERVER[ "REMOTE_ADDR" ] );
		$ipNum = 16777216*$myIP[0] + 65536*$myIP[1] + 256*$myIP[2] + $myIP[3];
		parent::SetQuery("SELECT * FROM table_ipmap WHERE '{$ipNum}' >= startIpNum
		&& '{$ipNum}' <= endIpNum");
		$myLoc = parent::DoQuery();
		parent::SetQuery("SELECT *,truncate(latitude,0),truncate(longitude,0) FROM table_ip2location WHERE locId='{$myLoc[0]["locId"]}'");
		$locationData = parent::DoQuery();
		return $locationData[0];
	}

	public function CurrentViewingLocationRSS()
	{//
		parent::SetQuery("SELECT * FROM `table_locations` WHERE location_id='{$this->localData["location_id"]}'");
		$currentLocation = parent::DoQuery();
		return array( "location_id" => $this->localData["location_id"], "location" => $currentLocation[0]["location"] );
	}

	public function CheckMyLocation()
	{# Check My Current Location
		$myLocation = $this->GetMyLocation();
		if( $myLocation["city"] != "" && !$this->localData["location_id"] )
		{//
			$longEnd = ceil($myLocation["longitude"]);
			$longStart = floor($myLocation["longitude"]);
			$latEnd = ceil($myLocation["latitude"]);
			$latStart = floor($myLocation["latitude"]);
			parent::SetQuery("SELECT * FROM `table_offerlocations`,`table_offers`,`table_locations` WHERE
			`table_offerlocations`.`offer_id`=`table_offers`.`offer_id`
			AND `table_offerlocations`.`location_id`=`table_locations`.`location_id`");
			$results = parent::DoQuery();
			foreach( $results as $result )
			{// loop through results
				$city = explode(", ", $result["location"] );
				parent::SetQuery("SELECT DISTINCT(city) as city_name,truncate(longitude,0),truncate(latitude,0) 
				FROM `table_ip2location` WHERE 
				longitude>={$longStart} 
				AND longitude<={$longEnd} 
				AND latitude>={$latStart} 
				AND latitude<={$latEnd}");
				//parent::PrintQuery();
				$exists = parent::CountDBResults();
				if( $exists )
				{
					$locations = parent::DoQuery();
					//print_r( $locations ); exit;
					foreach( $locations as $location )
					{// loop through each location
						if( $location["city_name"]!="" )
						{// make sure city name is not blank
							parent::SetQuery("SELECT * FROM `table_locations` WHERE location LIKE '%{$location["city_name"]}%'");
							//parent::PrintQuery();
							$locationExists = parent::CountDBResults();
							if( $locationExists )
							{// this location exists in our locations table
								$temp = parent::DoQuery();
								$this->localData["location_id"] = $temp[0]["location_id"];
								$found = true;
								break;
							}
						}
					}
					if( $found )
					{// location matched
						break;
					}
				}
				else
				{// cannot find a location in our database that matches the users location
					$noLocationFound = true;
				}
			}
		}
		else
		{// can't find users location
			$noLocationFound = true;
		}

		// Can't Find a Location By User, Default to Los Angeles, CA
		if( !$this->localData["location_id"] && $noLocationFound ) { $this->localData["location_id"] = '6'; }

		# Default Location
		if( !isset( $this->localData["location_id"] ) 
		|| $this->localData["location_id"]=='')
		{// set a default location
			parent::SetQuery("SELECT * FROM table_locations ORDER BY location ASC LIMIT 1");
			$defaultLocation = parent::DoQuery();
			$this->localData["location_id"] = $defaultLocation[0]["location_id"];
		}
	}

/**
***
*** Administration Functions
***
**/

	public function AdminWelcomeScreen()
	{# Administration Welcome Screen
		?>

			<h1>Admin Home</h1>

			<p>Welcome to the Administration Control Panel.</p>
			
			<p>From here you will be able to control various aspects of your web application.</p>

		<?php
	}

/**
*** START Comment Moderation
***
**/

	public function ViewComments()
	{# View Comments Pending Approval
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		# Create Paging
		parent::SetQuery( "SELECT * FROM `table_discussions`" );
		$totalComments = parent::CountDBResults();
		$paging = $this->Pagination( array( "totalRows" => $totalComments ) );
		parent::SetQuery( "SELECT * FROM `table_discussions` WHERE `status`='0' ORDER BY comment_id ASC LIMIT {$this->localData['s']},{$this->rowsPerPage}" );
		$comments = parent::CountDBResults();
		global $message, $message_type;
		?>
			<h1>Comments Awaiting Moderation</h1>

	<?php if( $message ) { ?>
		<div class="message-<?=$message_type;?>"><?=stripslashes($message);?></div>
	<?php } ?>

		<?php
			if( $comments > 0 )
			{# More than 0 comments awaiting approval
			?>
			<form name="comment[action]" method="post" action="?method=ViewComments">
			<input type="hidden" name="method" value="CommentsPerformAction"/>
			<input type="hidden" name="b" value="1"/>
			<table cellspacing="0" cellpadding="4" border="0" style="width: 100%; border: 1px solid gray; border-collapse: collapse;">
			<tr style="background-color: #C9C9C9; color: #FFFFFF; font-weight: bold;">
				<th style="text-align: center; width: 15%;">Action</th>
				<th style="width: 60%;">Comment</th>
				<th style="width: 25%;">Timestamp</th>
			</tr>
			<?php
				$comment_details = parent::DoQuery();
				foreach( $comment_details as $comment )
				{# Loop Through Each Comment
					parent::SetQuery("SELECT * FROM `table_users`,`table_userinfo`
					WHERE `table_users`.`user_id`=`table_userinfo`.`user_id`
					AND `table_users`.`user_id`='{$comment["user_id"]}'");
					$userData = parent::DoQuery();
				?>
					<tr onmouseover="this.style.backgroundColor='#F9F9F9';"
						onmouseout="this.style.backgroundColor='#FFFFFF';">
						<td style="text-align: center;">
							<div class="select-styling-div" style="width: 95px; margin: auto;">
							<select name="comment_id[<?=$comment["comment_id"];?>]" style="width: 95px;">
								<option></option>
								<option value="1">Approve</option>
								<option value="2">Delete</option>
							</select>
							</div>
						</td>
						<td><b><?php if( $userData[0]["firstname"]!= "" ) { echo $userData[0]["firstname"] . " " . strtoupper(substr($userData[0]["lastname"], 0, 1)); } else { echo $userData[0]["email_address"]; } ?></b> <i>said</i> &quot;<?=$comment["comment"];?>&quot;</td>
						<td><?=date("M-d-Y h:i a", $comment["timestamp"]);?></td>
					</tr>
					<!--<tr style="visibility: hidden;">
						<td colspan="3"><?=$comment["comment"];?></td>
					</tr>-->
				<?php
				}
			?>
			</table>
			<?=$paging;?>
			<div style="margin-top: 15px;">
				<button type="submit" class="button-special">Perform Actions</button>
			</div>
			</form>
			<?php
			}
			else
			{# No comments awaiting approval
		?>
				There are currently no comments pending approval.
		<?php
			}
		}
	}

	public function CommentsPerformAction()
	{# Perform Comment Actions
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		$approved = 0;
		$deleted = 0;
		foreach( $this->localData["comment_id"] as $comment_id => $value )
		{// loop through all comments selected
			if( $value == 1 )
			{// approve comment
				$approved+=1;
				parent::SetQuery("UPDATE `table_discussions` SET `status`='{$value}' WHERE 
				comment_id='{$comment_id}'");
				parent::SimpleQuery();
			}
			else if( $value == 2 )
			{// delete comment
				$deleted+=1;
				parent::SetQuery("DELETE FROM `table_discussions` WHERE comment_id='{$comment_id}' LIMIT 1");
				parent::SimpleQuery();
			}
		}
		if( $approved >= 1 || $deleted >= 1 )
		{// at least one comment has been approved or deleted
			setcookie("msg", "{$approved} comments approved, {$deleted} comments removed.", time()+300, "/");
		}
		else
		{// no messages were approved or deleted
			setcookie("msg", "No messages were selected.", time()+300, "/");
		}
		setcookie("msg_type", "success", time()+300, "/");
		header("Location: ?method=ViewComments");
		}
	}

/**
*** END Comment Moderation
***
**/

/**
*** START Charity
***
**/

	public function ViewCharities()
	{# Display Charities
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		# Create Paging
		parent::SetQuery( "SELECT * FROM `table_charities`" );
		$totalCharities = parent::CountDBResults();
		$paging = $this->Pagination( array( "totalRows" => $totalCharities ) );
		parent::SetQuery( "SELECT * FROM `table_charities` ORDER BY charity_name ASC LIMIT {$this->localData['s']},{$this->rowsPerPage}" );
		$charities = parent::CountDBResults();
		global $message, $message_type;
		?>
			<h1>View Charities</h1>

		<?php
			if( $charities > 0 )
			{# More than 0 Charities
			?>
			<table cellspacing="0" cellpadding="4" border="0" style="width: 100%; border: 1px solid gray; border-collapse: collapse;">
			<tr style="background-color: #C9C9C9; color: #FFFFFF; font-weight: bold;">
				<th style="text-align: center; width: 15%;">Action</th>
				<th style="width: 55%;">Charity Name</th>
				<th style="width: 30%;">Total Donated</th>
			</tr>
			<?php
				$charity_details = parent::DoQuery();
				foreach( $charity_details as $charity )
				{# Loop Through Each Charity
				?>
					<tr onmouseover="this.style.backgroundColor='#F9F9F9';"
						onmouseout="this.style.backgroundColor='#FFFFFF';">
						<td style="text-align: center;">
							<button type="button" class="button-default" style="overflow: hidden;" onclick="ConfirmationMessage('Are you sure you want to delete this Charity?', '?method=DoDeleteCharity&b=1&charity_id=<?=$charity["charity_id"];?>');"><img src="assets/gfx/icons/quantity-remove.gif" style="float: left;"/></button>
						</td>
						<td><?=$charity["charity_name"];?></td>
						<td>$<?=$charity["amt_donated"];?></td>
					</tr>
				<?php
				}
			?>
			</table>
			<?=$paging;?>
			<?php
			}
			else
			{# No charities
		?>
				There are currently no Charities to view.
		<?php
			}
		?>

		<div>
			<button type="button" onclick="window.location='control_panel?method=AddCharity';" class="button-default" style="overflow: auto; margin-top: 10px;"><img src="assets/gfx/icons/button-add-general.gif" alt="" title="" style="float: left;"/><span style="float: left; margin-left: 3px;">Add Charity</span></button>
		</div>

		<?php
		}
	}

	public function AddCharity()
	{# Add Charity Form
		global $message, $message_type;
		?>

			<h1>Add Charities</h1>

	<?php if( $message ) { ?>
		<div class="message-<?=$message_type;?>"><?=stripslashes($message);?></div>
	<?php } ?>

			<form action="?method=AddCharity" method="post" name="form-data">
				<input type="hidden" name="method" value="DoAddCharity">
				<input type="hidden" name="b" value="1"/>
				<div>Charity Name</div>
				<input type="text" name="charity[name]" size="35" class="text" value="<?=stripslashes($_SESSION['form-data']['charity']['name']);?>"/>
				<div style="margin-top: 7px;">Description</div>
				<input type="text" name="charity[description]" size="65" class="text" value="<?=$_SESSION['form-data']['charity']['description'];?>"/>

				<div style="margin-top: 5px;">
					<button type="submit" class="button-special" name="form-data">Add Charity</button>
				</div>
			</form>

		<?php
	}

	public function DoAddCharity()
	{// add charity to the database
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		parent::SetQuery("INSERT INTO `table_charities` VALUES ('','{$this->localData["charity"]["name"]}','{$this->localData["charity"]["description"]}','0.00')");
		parent::SimpleQuery();
		setcookie("msg", "Charity successfully created.", time()+300, "/");
		setcookie("msg_type", "success", time()+300, "/");
		unset( $_SESSION['form-data'] );
		header("Location: ?method=AddCharity");
		}
	}

	public function DoDeleteCharity()
	{# Delete Offer
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		parent::SetQuery("SELECT * FROM `table_charities` WHERE charity_id='{$this->localData['charity_id']}' LIMIT 1");
		$charityData = parent::DoQuery();
		parent::SetQuery("DELETE FROM `table_charities` WHERE charity_id='{$this->localData['charity_id']}' LIMIT 1");
		parent::SimpleQuery();
		setcookie("msg", "You have successfully deleted the Charity: &quot;{$charityData[0]["charity_name"]}&quot;", time()+300, "/");
		setcookie("msg_type", "success", time()+300, "/");
		header("Location: ?method=ViewCharities");
		}
	}

/**
*** END Charity
***
**/

/**
*** START Custom Page CMS
***
**/

	public function ViewCustomPages()
	{// edit custom CMS pages
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		global $message, $message_type;
		?>
		<h1>Edit Pages</h1>

	<?php if( $message ) { ?>
		<div class="message-<?=$message_type;?>"><?=stripslashes($message);?></div>
	<?php } ?>

	<?php if( ! isset( $this->localData["mode"] ) ) { ?>
		<form name="custom[pages]" action="?method=ViewCustomPages" method="post">
			<input type="hidden" name="mode" value="edit"/>
			<div style="overflow: hidden;">
			<div class="select-styling-div" style="width: 150px; float: left;">
			<select name="page_id" style="width: 150px;">
			<?php
				parent::SetQuery( "SELECT * FROM `table_cms`" );
				$customPages = parent::DoQuery();
				foreach( $customPages as $page )
				{// loop through custom pages
				?>
				<option value="<?=$page["page_id"];?>"><?=$page["page_title"];?></option>
				<?php
				}
			?>
			</select>
			</div>
			<div style="float: left; margin-left: 5px;">
				<button type="submit" name="custom[edit-btn]" class="button-default">Edit</button>
			</div>
			</div>
		</form>
	<?php } else {
		parent::SetQuery( "SELECT * FROM `table_cms` WHERE page_id='{$this->localData["page_id"]}'" );
		$customPages = parent::DoQuery();
	?>

		<form name="custom[pages]" action="?method=SaveCustomPages&b=1" method="post">
		<?php
			foreach( $customPages as $custom )
			{// get custom message
			?>
			<script type="text/javascript">
			<!--
				setTimeout(function(){new nicEditor({fullPanel : true}).panelInstance('<?=$custom["page_id"];?>-message');}, 50); 
			//-->
			</script>
			<div style="margin-bottom: 10px;">
			<div style="margin-bottom: 4px;"><b><?=$custom["page_title"];?></b></div>
				<textarea name="custom[page][<?=$custom["page_id"];?>]" id="<?=$custom["page_id"];?>-message" rows="7" cols="65"><?=html_entity_decode($custom["content"], ENT_QUOTES);?></textarea>
			</div>
			<?php
			}
	?>
		<div style="margin-top: 15px;">
			<button type="submit" name="custom[save-btn]" class="button-default">Save Page</button>
		</div>
		</form>

		<div style="margin-top: 15px;">
			<a href="?method=ViewCustomPages">Back to Custom Pages</a>
		</div>

	<?php } ?>

	<?php
		}
	}

	public function SaveCustomPages()
	{# Save Custom Messages
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		foreach( $this->localData["custom"]["page"] as $page_id => $page )
		{// loop through each message, and update it
			parent::SetQuery("UPDATE `table_cms` SET content='" . htmlentities( $page, ENT_QUOTES ) . "' 
			WHERE page_id='{$page_id}' LIMIT 1");
			parent::SimpleQuery();
		}
		setcookie("msg", "Custom page saved.", time()+300, "/");
		setcookie("msg_type", "success", time()+300, "/");
		header("Location: ?method=ViewCustomPages&mode=edit&page_id={$page_id}");
		}
	}

/**
*** END Custom Page CMS
***
**/

/**
*** START Commissions Table
***
**/

	public function ViewCommissions()
	{# View Affiliate Commissions
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		# Create Paging
		parent::SetQuery( "SELECT * FROM `table_commissions`" );
		$totalCommissions = parent::CountDBResults();
		$paging = $this->Pagination( array( "totalRows" => $totalCommissions ) );
		parent::SetQuery( "SELECT * FROM `table_commissions` WHERE status='1' OR status='0' ORDER BY commission_id DESC LIMIT {$this->localData['s']},{$this->rowsPerPage}" );
		$commissions = parent::CountDBResults();
		global $message, $message_type;
		?>
			<h1>Affiliate Commissions</h1>

	<?php if( $message ) { ?>
		<div class="message-<?=$message_type;?>"><?=stripslashes($message);?></div>
	<?php } ?>

		<?php
			if( $commissions > 0 )
			{# More than 0 commissions in database
			?>
			<form name="commissions[action]" method="post" action="?method=ViewCommissions">
			<input type="hidden" name="method" value="CommissionsPerformAction"/>
			<input type="hidden" name="b" value="1"/>
			<table cellspacing="0" cellpadding="4" border="0" style="width: 100%; border: 1px solid gray; border-collapse: collapse;">
			<tr style="background-color: #C9C9C9; color: #FFFFFF; font-weight: bold;">
				<th style="text-align: center; width: 15%;">Action</th>
				<th style="width: 40%;">Payable To</th>
				<th style="width: 15%;">Status</td>
				<th style="width: 15%;">Amount</th>
				<td style="width: 15%;">Ref#</td>
			</tr>
			<?php
				$commission_details = parent::DoQuery();
				foreach( $commission_details as $commission )
				{# Loop Through Each Commission Record
					parent::SetQuery("SELECT * FROM `table_users`,`table_userinfo`
					WHERE `table_users`.`user_id`=`table_userinfo`.`user_id`
					AND `table_users`.`user_id`='{$commission["paid_to_user_id"]}'");
					$userData = parent::DoQuery();
				?>
					<tr onmouseover="this.style.backgroundColor='#F9F9F9';"
						onmouseout="this.style.backgroundColor='#FFFFFF';">
						<td style="text-align: center;">
							<div class="select-styling-div" style="width: 95px; margin: auto;">
							<select name="commission_id[<?=$commission["commission_id"];?>]" style="width: 95px;">
								<option></option>
								<option value="1">Mark as Paid</option>
								<option value="0">Mark as Unpaid</option>
								<option value="2">Archive</option>
							</select>
							</div>
						</td>
						<td>
							<span style="padding-right: 4px;">
								<a href="?method=ViewCustomerDetails&user_id=<?=$userData[0]["user_id"];?>"><img src="assets/gfx/icons/button-edit.png" alt="" title="View/Edit Customer Details" border="0"/></a>
							</span>
							<?php if( $userData[0]["firstname"]!= "" ) { echo ucwords( $userData[0]["firstname"] ) . " " . ucwords( $userData[0]["lastname"] ); } else { echo $userData[0]["email_address"]; } ?>
						</td>
						<td><?php
						switch( $commission["status"] )
						{// select a status
							case 0 : echo "<i>Unpaid</i>"; break;
							case 1 : echo "<b>Paid</b>"; break;
							case 2 : echo "<b>Archived</b>"; break;
						}
						?></td>
						<td>$<?=$commission["amount"];?></td>
						<td><a href="?method=ViewTransactionDetails&transaction_id=<?=$commission["transaction_id"];?>"><?=$commission["transaction_id"];?></a></td>
					</tr>
				<?php
				}
			?>
			</table>
			<?=$paging;?>
			<div style="margin-top: 15px;">
				<button type="submit" class="button-special">Perform Actions</button>
			</div>
			</form>
			<?php
			}
			else
			{# No affiliate commissions in database
		?>
				There are currently no affiliate commissions recorded.
		<?php
			}
		}
	}

	public function CommissionsPerformAction()
	{# Perform Commission Actions
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		$paid = 0;
		$unpaid = 0;
		$archived = 0;
		foreach( $this->localData["commission_id"] as $commission_id => $value )
		{// loop through all comments selected
			if( $value!="" && $value == 0 )
			{
				$unpaid+=1;
				parent::SetQuery("UPDATE `table_commissions` SET `status`='{$value}' WHERE 
				commission_id='{$commission_id}'");
				parent::SimpleQuery();
			}
			else if( $value == 1 )
			{// approve comment
				$paid+=1;
				parent::SetQuery("UPDATE `table_commissions` SET `status`='{$value}' WHERE 
				commission_id='{$commission_id}'");
				parent::SimpleQuery();
			}
			else if( $value == 2 )
			{// delete comment
				$archived+=1;
				parent::SetQuery("UPDATE `table_commissions` SET `status`='{$value}' WHERE 
				commission_id='{$commission_id}'");
				parent::SimpleQuery();
			}
		}
		if( $unpaid >= 1 || $paid >= 1 || $archived >= 1 )
		{// at least one commission has changed state
			setcookie("msg", "{$unpaid} commissions marked as unpaid.<br/>"
			."{$paid} commissions marked as paid.<br/>"
			."{$archived} commissions marked as archived.", time()+300, "/");
		}
		else
		{// no commissions were selected
			setcookie("msg", "No commissions were selected.", time()+300, "/");
		}
		setcookie("msg_type", "success", time()+300, "/");
		header("Location: ?method=ViewCommissions");
		}
	}

/**
*** END Commissions Table
***
**/

/**
*** START Custom Messages
***
**/

	public function ViewCustomMessages()
	{# Custom Messages
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		global $message, $message_type;
		?>
		<h1>Edit Custom Messages</h1>

	<?php if( $message ) { ?>
		<div class="message-<?=$message_type;?>"><?=stripslashes($message);?></div>
	<?php } ?>

	<?php if( ! isset( $this->localData["mode"] ) ) { ?>
		<form name="custom[messages]" action="?method=ViewCustomMessages" method="post">
			<input type="hidden" name="mode" value="edit"/>
			<div style="overflow: hidden;">
			<div class="select-styling-div" style="width: 150px; float: left;">
			<select name="message_name" style="width: 150px;">
			<?php
				parent::SetQuery( "SELECT * FROM `table_messages`" );
				$customMessages = parent::DoQuery();
				foreach( $customMessages as $message )
				{// loop through custom messages
				?>
				<option value="<?=$message["message_name"];?>"><?=$message["message_title"];?></option>
				<?php
				}
			?>
			</select>
			</div>
			<div style="float: left; margin-left: 5px;">
				<button type="submit" name="custom[edit-btn]" class="button-default">Edit</button>
			</div>
			</div>
		</form>
	<?php } else {
		parent::SetQuery( "SELECT * FROM `table_messages` WHERE message_name='{$this->localData["message_name"]}'" );
		$customMessages = parent::DoQuery();
	?>

		<form name="custom[messages]" action="?method=SaveCustomMessages&b=1" method="post">
		<?php
			foreach( $customMessages as $custom )
			{// get custom message
				if( $custom["richtext"] == 1 )
				{// check if richtext editor is required
			?>
			<script type="text/javascript">
			<!--
				setTimeout(function(){new nicEditor({fullPanel : true}).panelInstance('<?=$custom["message_name"];?>-message');}, 50); 
			//-->
			</script>
			<?php } ?>
			<div style="margin-bottom: 10px;">
			<div style="margin-bottom: 4px;"><b><?=$custom["message_title"];?></b></div>
				<textarea name="custom[message][<?=$custom["message_name"];?>]" id="<?=$custom["message_name"];?>-message" rows="7" cols="65"><?=html_entity_decode($custom["message"], ENT_QUOTES);?></textarea>
			</div>
			<?php
			}
	?>
		<div style="margin-top: 15px;">
			<button type="submit" name="custom[save-btn]" class="button-default">Save Message</button>
		</div>
		</form>

		<div style="margin-top: 15px;">
			<a href="?method=ViewCustomMessages">Back to Custom Messages</a>
		</div>

	<?php } ?>

	<?php
		}
	}

	public function SaveCustomMessages()
	{# Save Custom Messages
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		foreach( $this->localData["custom"]["message"] as $message_name => $message )
		{// loop through each message, and update it
			parent::SetQuery("UPDATE `table_messages` SET message='" . htmlentities( $message, ENT_QUOTES ) . "' 
			WHERE message_name='{$message_name}' LIMIT 1");
			parent::SimpleQuery();
		}
		setcookie("msg", "Custom message saved.", time()+300, "/");
		setcookie("msg_type", "success", time()+300, "/");
		header("Location: ?method=ViewCustomMessages&mode=edit&message_name={$message_name}");
		}
	}

/**
*** END Custom Messages
***
**/

/**
*** START Transactions
***
**/

	public function ViewTransactions()
	{# Display Transactions
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		# Create Paging
		parent::SetQuery( "SELECT * FROM table_transactions" );
		$totalOrders = parent::CountDBResults();
		$paging = $this->Pagination( array( "totalRows" => $totalOrders ) );
		parent::SetQuery( "SELECT * FROM table_transactions ORDER BY timestamp DESC LIMIT {$this->localData['s']},{$this->rowsPerPage}" );
		$orders = parent::CountDBResults();
		global $message, $message_type;
		?>
			<h1>View Transactions</h1>

		<?php
			if( $orders > 0 )
			{# More than 0 Orders
			?>
			<table cellspacing="0" cellpadding="4" border="0" style="width: 100%; border: 1px solid gray; border-collapse: collapse;">
			<tr style="background-color: #C9C9C9; color: #FFFFFF; font-weight: bold;">
				<th style="text-align: center; width: 15%;">Action</th>
				<th style="width: 25%;">Customer</th>
				<th style="width: 30%;">Purchase Date</th>
				<th style="width: 10%;">Total</th>
			</tr>
			<?php
				$order_details = parent::DoQuery();
				foreach( $order_details as $order )
				{# Loop Through Each Order
					parent::SetQuery("SELECT * FROM table_users, table_userinfo WHERE
					table_users.user_id=table_userinfo.user_id AND table_users.user_id='{$order["user_id"]}' LIMIT 1");
					$userData = parent::DoQuery();
				?>
					<tr onmouseover="this.style.backgroundColor='#F9F9F9';"
						onmouseout="this.style.backgroundColor='#FFFFFF';">
						<td style="text-align: center;">
							<a href="?method=ViewTransactionDetails&transaction_id=<?=$order['transaction_id'];?>"><img src="assets/gfx/icons/table.png" alt="" title="View Transaction Details" border="0"/></a>
						</td>
						<td><?=$userData[0]["firstname"];?> <?=$userData[0]["lastname"];?></td>
						<td><?=date("M-d-Y h:i a", $order["timestamp"]);?></td>
						<td>$<?=$order["total"];?></td>
					</tr>
				<?php
				}
			?>
			</table>
			<?=$paging;?>
			<?php
			}
			else
			{# No transactions
		?>
				There are currently no Transactions to view.
		<?php
			}
		}
	}

	public function ViewTransactionDetails()
	{# Transaction Details
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		parent::SetQuery("SELECT * FROM table_transactions WHERE transaction_id='{$this->localData["transaction_id"]}'");
		$transactionData = parent::DoQuery();
		parent::SetQuery("SELECT * FROM table_users, table_userinfo WHERE
		table_users.user_id=table_userinfo.user_id AND table_users.user_id='{$transactionData[0]["user_id"]}'");
		$userData = parent::DoQuery();
		?>
		<h1>Transaction Details</h1>
		<table cellspacing="0" cellpadding="5">
		<tr>
			<td style="text-align: right;">Customer Name :</td>
			<td>
				<?=$userData[0]["firstname"];?> <?=$userData[0]["lastname"];?>
				<a href="?method=ViewCustomerDetails&user_id=<?=$userData[0]["user_id"];?>"><img src="assets/gfx/icons/button-edit.png" alt="" title="View/Edit Customer Details" border="0"/></a>
			</td>
		</tr>
		<tr>
			<td style="text-align: right;">Customer Email :</td>
			<td><a href="mailto:<?=$userData[0]["email_address"];?>"><?=$userData[0]["email_address"];?></a></td>
		</tr>
		<tr>
			<td style="text-align: right;">Transaction Date :</td>
			<td><?=date("F d, Y h:i a", $transactionData[0]["timestamp"]);?></td>
		</tr>
		<tr>
			<td style="text-align: right;">Total Cost :</td>
			<td>$<?=$transactionData[0]["total"];?></td>
		</tr>
		</table>

		<br/><br/>

		<h3>Order Contents</h3>
		<table cellspacing="0" cellpadding="5" border="0" style="width: 100%; border: 1px solid gray; border-collapse: collapse;">
			<tr style="background-color: #C9C9C9; color: #FFFFFF; font-weight: bold;">
				<th style="width: 55%;">Description</th>
				<th style="width: 15%; text-align: left;">$/ea</th>
				<td style="width: 15%; text-align: center;">Quantity</td>
				<th style="width: 15%; text-align: left;">Item Total</th>
			</tr>
	<?php
		// display order contents
		$items = simplexml_load_string( $transactionData[0]["contents"] );
		foreach( $items as $item )
		{// Loop Through Items in Cart
			parent::SetQuery("SELECT * FROM table_offers WHERE offer_code='{$item->offer_code}'");
			$data = parent::DoQuery();
			$data = $data[0];
			$subTotal += ( $item->quantity * $data["price"] );
			$subTotalSavings += ( $item->quantity * ( $data["value"] - $data["price"] ) );
		?>
			<tr>
				<td>
					<?=$data['one_liner'];?><br/>
					(a <i><b>$<?=$data['value'];?></b></i> value, Limit: <?=$data['limit'];?>)
				</td>
				<td style="text-align: left;">$<?=$data['price'];?></td>
				<td style="text-align: center;">
					<input type="text" readonly="readonly" maxlength="2" class="text" size="3" value="<?=$item->quantity;?>" style="text-align: center;"/>
				</td>
				<td style="text-align: left;">$<?=number_format( $item->quantity * $data['price'], 2, '.', '');?></td>
			</tr>
		<?php
		}
		?>
			</table>
		<?php
		}
	}

/**
*** END Transactions
***
**/

/**
*** START Aggregated Deals
***
**/

	public function ViewAggregatedDeals()
	{// View Aggregated Deals
		global $message, $message_type;

		$this->rowsPerPage = 10;

		# Create Paging
		parent::SetQuery( "SELECT * FROM `table_dealaggregator`" );
		$totalDeals = parent::CountDBResults();
		$paging = $this->Pagination( array( "totalRows" => $totalDeals ) );
		parent::SetQuery("SELECT * FROM `table_dealaggregator` LIMIT {$this->localData["s"]},{$this->rowsPerPage}");
		?>
		<h2>View Aggregated Deals</h2>

	<?php if( $message ) { ?>
		<div class="message-<?=$message_type;?>"><?=stripslashes($message);?></div>
	<?php } ?>

		<?php
		if( $totalDeals > 0 )
		{// deals found
			?>
			<table cellspacing="0" cellpadding="4" border="0" style="width: 100%; border: 1px solid gray; border-collapse: collapse;">
			<tr style="background-color: #C9C9C9; color: #FFFFFF; font-weight: bold;">
				<th style="text-align: center; width: 15%;">Action</th>
				<th style="width: 25%;">Title</th>
				<th style="width: 20%;">Location</th>
				<th style="width: 20%;">Category</th>
				<th style="width: 10%;">Price</th>
				<th style="width: 10%;">Value</th>
			</tr>
			<?php
			$deals = parent::DoQuery();
			foreach( $deals as $deal )
			{// loop through each deal
				?>
				<tr>
					<td style="text-align: center;">
						<a href="?method=EditAggregatedDeal&deal_id=<?=$deal["deal_id"];?>">edit</a> / 
						<button type="button" class="button-default" style="overflow: hidden;" onclick="ConfirmationMessage('Are you sure you want to delete this Deal: &quot;<?=$deal["title"];?>&quot;?', '?method=DoDeleteDeal&b=1&deal_id=<?=$deal["deal_id"];?>')"><img src="assets/gfx/icons/quantity-remove.gif" alt="" title="Delete Deal" style="float: left;"/></button>
					</td>
					<td><?=$deal["title"];?></td>
					<td><?php
						parent::SetQuery("SELECT * FROM `table_locations` WHERE 
						location_id='{$deal["deal_location"]}'");
						$locationInfo = parent::DoQuery();
						echo $locationInfo[0]["location"];
					?>
					</td>
					<td><?php
						parent::SetQuery("SELECT * FROM `table_aggregator_cat` WHERE cat_id='{$deal["deal_category"]}'");
						$categoryInfo = parent::DoQuery();
						echo $categoryInfo[0]["category_name"];
					?>
					</td>
					<td><?=$deal["price"];?></td>
					<td><?=$deal["value"];?></td>
				</tr>
				<?php
			}
			?>
			</table>
			<?=$paging;?>
			<?php
		}
		else
		{// no aggregated deals found
		?>
			<center>No aggregated deals were found.</center>
		<?php
		}
		?>
		<div style="margin-top: 15px; overflow: hidden;">
			<button type="button" class="button-default" style="float: left;" onclick="window.location='?method=AddAggregatedDeal';">Add Deal</button>
			<button type="button" class="button-default" style="float: left; margin-left: 4px;" onclick="window.location='?method=AddDealSource';">Add Source</button>
		</div>
		<?php
	}

	public function DoAddDeal()
	{// add a new deal
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
			if( $this->localData["deal"]["title"]!=""
			&& $this->localData["deal"]["price"]>0
			&& $this->localData["deal"]["value"]>0)
			{// success
				$filename = "";
				if( $_FILES["deal-img"]["tmp_name"] )
				{// a file was submitted
					$extension = explode(".", $_FILES["deal-img"]["name"]);
					$extension = strtolower( $extension[ sizeOf( $extension ) - 1 ] );
					if( $extension == "gif" )
					{// gif filetype allowed
						$new_filename = md5( microtime() . $_FILES["deal-img"]["name"] ) . '.' . $extension;
						$success = move_uploaded_file( $_FILES["deal-img"]["tmp_name"], 'gfx/logos/' . $new_filename );
						if( $success )
						{
							$filename = $new_filename;
						}
					}
				}
				parent::SetQuery("INSERT INTO `table_dealaggregator`
				VALUES ('','{$this->localData["deal"]["category"]}',
				'{$this->localData["deal"]["location"]}',
				'{$filename}',
				'".addslashes($this->localData["deal"]["address"])."',
				'".addslashes($this->localData["deal"]["title"])."',
				'".addslashes($this->localData["deal"]["description"])."',
				'{$this->localData["deal"]["url"]}',
				'{$this->localData["deal"]["price"]}',
				'{$this->localData["deal"]["value"]}',
				'{$this->localData["deal"]["source"]}')");
				parent::SimpleQuery();
				unset( $_SESSION["form-data"] );
				setcookie("msg", "Deal successfully added.", time()+300, "/");
				setcookie("msg_type", "success", time()+300, "/");
				header("Location: ?method=AddAggregatedDeal");
			}
			else
			{// missing info
				setcookie("msg", "You were missing required fields.", time()+300, "/");
				setcookie("msg_type", "error", time()+300, "/");
				header("Location: ?method=AddAggregatedDeal");
			}
		}
	}

	public function DoDeleteDeal()
	{// delete a deal
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
			parent::SetQuery("DELETE FROM `table_dealaggregator` WHERE deal_id='{$this->localData["deal_id"]}' LIMIT 1");
			parent::SimpleQuery();
			setcookie("msg", "Deal successfully deleted.", time()+300, "/");
			setcookie("msg_type", "success", time()+300, "/");
			header("Location: ?method=ViewAggregatedDeals");
		}
	}

	public function DoSaveDeal()
	{// save deal information
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
			if( $this->localData["deal"]["title"]!=""
			&& $this->localData["deal"]["price"]>0
			&& $this->localData["deal"]["value"]>0)
			{// success
				$filename = "";
				if( $_FILES["deal-img"]["tmp_name"] )
				{// a file was submitted
					$extension = explode(".", $_FILES["deal-img"]["name"]);
					$extension = strtolower( $extension[ sizeOf( $extension ) - 1 ] );
					if( $extension == "gif" )
					{// gif filetype allowed
						$new_filename = md5( microtime() . $_FILES["deal-img"]["name"] ) . '.' . $extension;
						$success = move_uploaded_file( $_FILES["deal-img"]["tmp_name"], 'gfx/logos/' . $new_filename );
						if( $success )
						{
							$filename = $new_filename;
						}
					}
				}
				parent::SetQuery("UPDATE `table_dealaggregator` SET
				deal_category='{$this->localData["deal"]["category"]}',
				deal_location='{$this->localData["deal"]["location"]}',
				logo_img='{$filename}',
				deal_address='".addslashes($this->localData["deal"]["address"])."',
				title='".addslashes($this->localData["deal"]["title"])."',
				description='".addslashes($this->localData["deal"]["description"])."',
				deal_url='{$this->localData["deal"]["url"]}',
				price='{$this->localData["deal"]["price"]}',
				value='{$this->localData["deal"]["value"]}',
				deal_source='{$this->localData["deal"]["source"]}' WHERE
				deal_id='{$this->localData["deal_id"]}' LIMIT 1");
				parent::SimpleQuery();
				setcookie("msg", "Deal successfully saved.", time()+300, "/");
				setcookie("msg_type", "success", time()+300, "/");
				header("Location: ?method=EditAggregatedDeal&deal_id=" . $this->localData["deal_id"]);
			}
			else
			{// missing info
				setcookie("msg", "You were missing required fields.", time()+300, "/");
				setcookie("msg_type", "error", time()+300, "/");
				header("Location: ?method=EditAggregatedDeal&deal_id=" . $this->localData["deal_id"]);
			}
		}
	}

	public function EditAggregatedDeal()
	{// edit aggregated deal form
		global $message, $message_type;
		parent::SetQuery("SELECT * FROM `table_dealaggregator` WHERE deal_id='{$this->localData["deal_id"]}'");
		$exists = parent::CountDBResults();
		?>
		<h2>Edit Aggregated Deal</h2>

		<?php if( $exists ) { // deal exists ?>
		<?php
		// get deal info
		$deal_info = parent::DoQuery();
		$deal_info = $deal_info[0];
		?>

	<?php if( $message ) { ?>
		<div class="message-<?=$message_type;?>"><?=stripslashes($message);?></div>
	<?php } ?>

		<form name="form[deals]" method="post" action="" enctype="multipart/form-data">
		<input type="hidden" name="method" value="DoSaveDeal"/>
		<input type="hidden" name="b" value="1"/>
		<div>Title</div>
		<input type="text" name="deal[title]" class="text" size="35" value="<?=stripslashes($deal_info["title"]);?>"/>
		<div style="margin-top: 7px;">Description</div>
		<textarea name="deal[description]" rows="3" cols="45"><?=stripslashes($deal_info["description"]);?></textarea>
		<div style="margin-top: 7px;">Deal URL</div>
		<input type="text" name="deal[url]" size="45" class="text" value="<?=$deal_info["deal_url"];?>"/>
		<div style="margin-top: 7px;">Location</div>
		<div class="select-styling-div" style="width: 250px; margin-top: 3px;">
		<select name="deal[location]" style="width: 250px;">
		<?php
			parent::SetQuery("SELECT * FROM `table_locations`");
			$locations = parent::DoQuery();
			foreach( $locations as $location )
			{// loop through each location
			?>
			<option value="<?=$location["location_id"];?>" <?php if( $deal_info["deal_location"] == $location["location_id"] ) { ?>selected="selected"<?php } ?>><?=$location["location"];?></option>
			<?php
			}
		?>
		</select>
		</div>
		<div style="margin-top: 7px;">Address</div>
		<textarea name="deal[address]" rows="4" cols="45"><?=stripslashes($deal_info["deal_address"]);?></textarea>
		<div style="margin-top: 7px;">Category</div>
		<div class="select-styling-div" style="width: 250px; margin-top: 3px;">
		<select name="deal[category]" style="width: 250px;">
		<?php
			parent::SetQuery("SELECT * FROM `table_aggregator_cat`");
			$categories = parent::DoQuery();
			foreach( $categories as $category )
			{// loop through each category
			?>
				<option value="<?=$category["cat_id"];?>" <?php if( $deal_info["deal_category"] == $category["cat_id"] ) { ?>selected="selected"<?php } ?>><?=$category["category_name"];?></option>
			<?php
			}
		?>
		</select>
		</div>
		<div style="margin-top: 7px;">Price</div>
		<input type="text" class="text" size="4" name="deal[price]" value="<?=number_format($deal_info["price"], 2, '.', '');?>"/>
		<div style="margin-top: 7px;">Value</div>
		<input type="text" class="text" size="4" name="deal[value]" value="<?=number_format($deal_info["value"], 2, '.', '');?>"/>
		<div style="margin-top: 7px;">Deal Source</div>
		<div class="select-styling-div" style="width: 250px; margin-top: 3px;">
		<select name="deal[source]" style="width: 250px;">
		<?php
			parent::SetQuery("SELECT * FROM `table_aggregator_source`");
			$sources = parent::DoQuery();
			foreach( $sources as $source )
			{// loop through each source
			?>
				<option value="<?=$source["source_id"];?>" <?php if( $deal_info["deal_source"] == $source["source_id"] ) { ?>selected="selected"<?php } ?>><?=$source["source_name"];?></option>
			<?php
			}
		?>
		</select>
		</div>
		<div style="margin-top: 8px; margin-bottom: 3px;">
			Deal Image (*.gif filetypes)
		</div>
		<div style="overflow: hidden; margin-bottom: 5px;">
			<div style="float: left; margin-top: 5px;"><input type="file" name="deal-img"/></div>
			<div style="float: left; width: 150px; margin-left: 25px; text-align: center;"><?php if( $deal_info["logo_img"] ) { ?>(<i>current image</i>)<br/><img src="gfx/logos/<?=$deal_info["logo_img"];?>"/><?php } ?></div>
		</div>
		<div style="margin-top: 15px;">
			<button type="submit" name="btn[add]" class="button-special">Save Deal</button>
		</div>
		</form>
		<?php
		}
		else
		{// deal id doesn't exist in the database
		?>
		<center>This deal could not be located.</center>
		<?php
		}
	}

	public function AddAggregatedDeal()
	{// add aggregated deal form
		global $message, $message_type;
		?>
		<h2>Add Aggregated Deal</h2>

	<?php if( $message ) { ?>
		<div class="message-<?=$message_type;?>"><?=stripslashes($message);?></div>
	<?php } ?>

		<form name="form[deals]" method="post" action="" enctype="multipart/form-data">
		<input type="hidden" name="method" value="DoAddDeal"/>
		<input type="hidden" name="b" value="1"/>
		<input type="hidden" name="form-data" value="1"/>
		<div>Title</div>
		<input type="text" name="deal[title]" class="text" size="35" value="<?=$_SESSION["form-data"]["deal"]["title"];?>"/>
		<div style="margin-top: 7px;">Description</div>
		<textarea name="deal[description]" rows="3" cols="45"><?=$_SESSION["form-data"]["deal"]["description"];?></textarea>
		<div style="margin-top: 7px;">Deal URL</div>
		<input type="text" name="deal[url]" size="45" class="text" value="<?=$_SESSION["form-data"]["deal"]["url"];?>"/>
		<div style="margin-top: 7px;">Location</div>
		<div class="select-styling-div" style="width: 250px; margin-top: 3px;">
		<select name="deal[location]" style="width: 250px;">
		<?php
			parent::SetQuery("SELECT * FROM `table_locations`");
			$locations = parent::DoQuery();
			foreach( $locations as $location )
			{// loop through each location
			?>
			<option value="<?=$location["location_id"];?>" <?php if( $_SESSION["form-data"]["deal"]["location"] == $location["location_id"] ) { ?>selected="selected"<?php } ?>><?=$location["location"];?></option>
			<?php
			}
		?>
		</select>
		</div>
		<div style="margin-top: 7px;">Address</div>
		<textarea name="deal[address]" rows="4" cols="45"><?=$_SESSION["form-data"]["deal"]["address"];?></textarea>
		<div style="margin-top: 7px;">Category</div>
		<div class="select-styling-div" style="width: 250px; margin-top: 3px;">
		<select name="deal[category]" style="width: 250px;">
		<?php
			parent::SetQuery("SELECT * FROM `table_aggregator_cat`");
			$categories = parent::DoQuery();
			foreach( $categories as $category )
			{// loop through each category
			?>
				<option value="<?=$category["cat_id"];?>" <?php if( $_SESSION["form-data"]["deal"]["category"] == $category["cat_id"] ) { ?>selected="selected"<?php } ?>><?=$category["category_name"];?></option>
			<?php
			}
		?>
		</select>
		</div>
		<div style="margin-top: 7px;">Price</div>
		<input type="text" class="text" size="4" name="deal[price]" value="<?=number_format($_SESSION["form-data"]["deal"]["price"], 2, '.', '');?>"/>
		<div style="margin-top: 7px;">Value</div>
		<input type="text" class="text" size="4" name="deal[value]" value="<?=number_format($_SESSION["form-data"]["deal"]["value"], 2, '.', '');?>"/>
		<div style="margin-top: 7px;">Deal Source</div>
		<div class="select-styling-div" style="width: 250px; margin-top: 3px;">
		<select name="deal[source]" style="width: 250px;">
		<?php
			parent::SetQuery("SELECT * FROM `table_aggregator_source`");
			$sources = parent::DoQuery();
			foreach( $sources as $source )
			{// loop through each source
			?>
				<option value="<?=$source["source_id"];?>" <?php if( $_SESSION["form-data"]["deal"]["source"] == $source["source_id"] ) { ?>selected="selected"<?php } ?>><?=$source["source_name"];?></option>
			<?php
			}
		?>
		</select>
		</div>
		<div style="margin-top: 8px; margin-bottom: 3px;">
			Deal Image (*.gif filetypes)
		</div>
		<input type="file" name="deal-img"/>
		<div style="margin-top: 15px;">
			<button type="submit" name="btn[add]" class="button-special">Add Deal</button>
		</div>
		</form>
		<?php
	}

	public function AddDealSource()
	{// add a deal source
	global $message, $message_type;
	?>
	<h2>Add Aggregator Deal Source</h2>

	<?php if( $message ) { ?>
		<div class="message-<?=$message_type;?>"><?=stripslashes($message);?></div>
	<?php } ?>

	<form name="form[sources]" method="post" action="">
	<input type="hidden" name="method" value="DoAddDealSource"/>
	<input type="hidden" name="b" value="1"/>
	<div>Source Name</div>
	<input type="text" class="text" name="source[name]" size="25"/>
	<div style="margin-top: 7px;">Source URL</div>
	<input type="text" class="text" size="45" name="source[url]" value="http://"/>
	<div style="margin-top: 15px;">
		<button type="submit" name="btn[add-source]" class="button-default">Add Source</button>
	</div>
	</form>
	<?php
	}

	public function DoAddDealSource()
	{// do add the deal source to the database
		if( $this->LocalCheckAuth() )
		{// admin is logged in
			parent::SetQuery("INSERT INTO `table_aggregator_source` VALUES ('','{$this->localData["source"]["name"]}','{$this->localData["source"]["url"]}')");
			parent::SimpleQuery();
			setcookie("msg", "Deal source successfully added.", time()+300, "/");
			setcookie("msg_type", "success", time()+300, "/");
			header("Location: ?method=AddDealSource");
		}
	}

	public function GetAggregatedDeals( $myLocation )
	{// retrieve aggregated deals

		$this->rowsPerPage = 10;

		# Create Paging
		parent::SetQuery( "SELECT * FROM `table_dealaggregator` WHERE deal_location='{$this->localData["location_id"]}' AND `price`>0" );
		$totalDeals = parent::CountDBResults();
		$paging = $this->Pagination( array( "totalRows" => $totalDeals ) );
		
		parent::SetQuery("SELECT * FROM `table_dealaggregator` WHERE deal_location='{$this->localData["location_id"]}' AND `price`>0 LIMIT {$this->localData["s"]},{$this->rowsPerPage}");
		if( $totalDeals > 0 )
		{// deals exist for this location
			$deals = parent::DoQuery();
			?>
			<script type="text/javascript">
			<!--
				function ToggleMap( map_id )
				{// toggle the google map for each listing
					var element = document.getElementById('map-' + map_id );
					var selector = document.getElementById('selector-' + map_id );
					/*switch( element.style.visibility )
					{
						case 'visible' :
							//element.style.visibility = 'collapse';
							selector.innerHTML = '+';
							//element.style.height = '0px';
							break;
						case 'collapse' :
							element.style.visibility = 'visible';
							selector.innerHTML = '-';
							//element.style.height = 'auto';
							break;
					}*/
					switch( element.style.display )
					{
						case 'block' :
							element.style.display = 'none';
							selector.innerHTML = '+';
							//element.style.height = '0px';
							break;
						case 'none' :
							element.style.display = 'block';
							selector.innerHTML = '-';
							//element.style.height = 'auto';
							break;
					}
				}
			//-->
			</script>
			<table cellspacing="0" cellpadding="5" border="1" style="width: 100%; border: 1px solid #FFFFFF; border-collapse: collapse;">
			<tr style="background-color: #777777; color: #FFFFFF; font-weight: bold;">
				<td style="width: 5%;"></td>
				<td></td>
				<td style="width: 10%;">Category</td>
				<td style="width: 40%;">Deal Info</td>
				<td style="width: 8%;">Price</td>
				<td style="width: 8%;">Value</td>
				<td style="width: 8%;">Discount</td>
				<td style="width: 8%;">Save</td>
				<td style="width: 10%;">Company</td>
			</tr>
			<?php
			foreach( $deals as $deal )
			{// loop through each category
				parent::SetQuery("SELECT * FROM `table_aggregator_cat` WHERE cat_id='{$deal["deal_category"]}'");
				$categoryInfo = parent::DoQuery();
				$pattern = "/src=[\"']?([^\"']?.*(png|jpg|gif))[\"']?/i";
				preg_match_all($pattern, $deal["description"], $images);
				?>
				<tr style="border-style: hidden;" bordercolor="#777777" onmouseover="this.style.backgroundColor='#F9F9F9';" onmouseout="this.style.backgroundColor='#FFFFFF';" style="border-top: 1px solid #777777;">
					<td style="border-style: hidden; text-align: center; cursor: pointer;" onclick="<?php if( $deal["deal_address"]!="" ) { ?>ToggleMap('<?=$deal["deal_id"];?>');<?php } ?>"><?php if( $deal["deal_address"]!="" ) { ?><a href="javascript:void(0);" style="font-weight: bold; text-decoration: none; text-align: center; width: 10px; height: 10px;" id="selector-<?=$deal["deal_id"];?>">+</a><?php } else { ?>&nbsp;<?php } ?></td>
					<td style="border-style: hidden;"><?php if( sizeOf( $images ) > 0 ) { ?><img <?=$images[0][0];?> style="width: 75px;"/><?php } ?></td>
					<td style="border-style: hidden;"><?php if( $deal["deal_category"] != 0 ) { ?><?=$categoryInfo[0]["category_name"];?><?php } else { ?>N/A<?php } ?></td>
					<td style="border-style: hidden;"><a href="<?=$deal["deal_url"];?>" target="_blank"><?=$deal["title"];?></a></td>
					<td style="border-style: hidden;">$<?=$deal["price"];?></td>
					<td style="border-style: hidden;">$<?=$deal["value"];?></td>
					<td style="border-style: hidden;"><?=round((($deal["value"] - $deal["price"]) / $deal["value"]) * 100);?>%</td>
					<td style="border-style: hidden;">$<?php $savings = number_format($deal["value"]-$deal["price"], 2, '.', ''); if( $savings > 0 ) { echo $savings; } else { echo "0.00"; } ?></td>
					<td style="border-style: hidden;"><?php
						parent::SetQuery("SELECT * FROM `table_aggregator_source`
						WHERE source_id='{$deal["deal_source"]}'");
						$companyInfo = parent::DoQuery();
						echo "<a href=\"{$companyInfo[0]["source_url"]}\" target=\"_blank\" onclick=\"return false;\">" . $companyInfo[0]["source_name"] . "</a>";
					?></td>
				</tr>
				<tr style="border-style: hidden;">
					<td colspan="9" style="border-style: hidden;">
						<img id="map-<?=$deal["deal_id"];?>" style="display: none;" src="http://maps.google.com/maps/api/staticmap?zoom=16&size=640x200&markers=<?php if( $deal["logo_img"] == "" ) { ?>color:blue<?php } else { ?>icon:http://www.findmymonkey.com/gfx/logos/<?=$deal["logo_img"];?><?php } ?>|shadow:false|<?=urlencode(stripslashes($deal["deal_address"]));?>&sensor=false"/>
					</td>
				</tr>
				<?php
			}
		?>
		</table>
		<?=$paging;?>
		<?php
		}
		else
		{// no deals for this location
			?>
				No deals were found for the selected location.
			<?php
		}
		?>
		<?php
	}

/**
*** END Aggregated Deals
***
**/

/**
*** START Offers
***
**/

	public function ViewOffers()
	{# View Offers
		# Create Paging
		parent::SetQuery( "SELECT * FROM table_offers" );
		$totalOffers = parent::CountDBResults();
		$paging = $this->Pagination( array( "totalRows" => $totalOffers ) );
		parent::SetQuery( "SELECT * FROM table_offers ORDER BY expiration DESC LIMIT {$this->localData['s']},{$this->rowsPerPage}" );
		$offers = parent::CountDBResults();
		global $message, $message_type;
		?>

			<h1>View Offers</h1>

	<?php if( $message ) { ?>
		<div class="message-<?=$message_type;?>"><?=stripslashes($message);?></div>
	<?php } ?>

		<?php
			if( $offers > 0 )
			{# More than 0 Offers
			?>
			<table cellspacing="0" cellpadding="4" border="0" style="width: 100%; border: 1px solid gray; border-collapse: collapse;">
			<tr style="background-color: #C9C9C9; color: #FFFFFF; font-weight: bold;">
				<th style="text-align: center; width: 15%;">Action</th>
				<th style="width: 25%;">Offer Name (Code)</th>
				<th style="width: 30%;">Expiration</th>
				<th style="text-align: center; width: 20%;">Status</th>
				<th style="width: 10%;">Price</th>
			</tr>
			<?php
				$offer_details = parent::DoQuery();
				foreach( $offer_details as $offer )
				{# Loop Through Each Offer
				?>
					<tr onmouseover="this.style.backgroundColor='#F9F9F9';"
						onmouseout="this.style.backgroundColor='#FFFFFF';">
						<td style="text-align: center;">
							<a href="?method=ViewOfferDetails&offer_code=<?=$offer['offer_code'];?>"><img src="assets/gfx/icons/button-edit.png" alt="" title="View/Edit Offer Details" border="0"/></a>
							<a href="?method=ViewOfferParticipants&offer_code=<?=$offer['offer_code'];?>"><img src="assets/gfx/icons/table.png" alt="" title="View Offer Participants" border="0"/></a>
						</td>
						<td><b><?=stripslashes($offer['name']);?></b> (<?=$offer['offer_code'];?>)</td>
						<td><?=date("M-d-Y h:i a", $offer['expiration']);?></td>
						<td style="text-align: center;"><?php if( $offer['expiration'] > time() ) { ?>Active<?php } else { ?><i>Expired</i><?php } ?></td>
						<td>$<?=$offer['price'];?></td>
					</tr>
				<?php
				}
			?>
			</table>
			<?=$paging;?>
			<?php
			}
			else
			{# No offers, display a message
		?>
				There are currently no Offers to display.
		<?php
			}
		?>

		<div>
			<button type="button" onclick="window.location='control_panel?method=AddOffers';" class="button-default" style="overflow: auto; margin-top: 10px;"><img src="assets/gfx/icons/button-add-general.gif" alt="" title="" style="float: left;"/><span style="float: left; margin-left: 3px;">Add Offers</span></button>
		</div>

		<?php
	}# End View Offers

	public function GetOfferDetails( $offerCode )
	{# Get Offer Details for Timer Script
		parent::SetQuery("SELECT * FROM table_offers WHERE offer_code='{$offerCode}' LIMIT 1");
		$data = parent::DoQuery();
		if( $data[0]["expiration"] > time() )
		{# Still Going
			return array( "timeLeft" => $data[0]["expiration"] - time() );
		}
		else
		{# Expired
			return array( "timeLeft" => 0 );
		}
	}

	public function ViewOfferDetails()
	{# View Offer Details & Update
		parent::SetQuery("SELECT * FROM table_offers WHERE offer_code='{$this->localData['offer_code']}' LIMIT 1");
		$offerDetails = parent::DoQuery();
		global $message, $message_type;
		$timer = $this->GetOfferDetails($offerDetails[0]["offer_code"]);
		?>

		<script type="text/javascript">
		<!--
			setTimeout(function(){new nicEditor({fullPanel : true}).panelInstance('offer-details');}, 200); 
			setTimeout(function(){new nicEditor({fullPanel : true}).panelInstance('offer-description');}, 200); 
		//-->
		</script>

			<h1>View Offer Details</h1>

	<?php if( $message ) { ?>
		<div class="message-<?=$message_type;?>"><?=stripslashes($message);?></div>
	<?php } ?>

	<!--Timer-->
	<script type="text/javascript">
	<!--
		var timeLeft = <?=$timer["timeLeft"]?>;
		var timer;
	//-->
	</script>
	<script type="text/javascript" src="assets/js/Timer.js"></script>
	<script type="text/javascript">
	<!--
		window.onload = Timer;
	//-->
	</script>

			<form action="?method=AddOffers" method="post" enctype="multipart/form-data">
				<input type="hidden" name="method" value="DoAddOffer">
				<input type="hidden" name="b" value="1"/>
				<div>Offer Name</div>
				<input type="text" name="offer[name]" size="30" class="text" value="<?=stripslashes($offerDetails[0]["name"]);?>"/>
				<div style="margin-top: 7px;">Offer Code</div>
				<input type="text" name="offer[code]" size="20" readonly="readonly" class="text" value="<?=$offerDetails[0]["offer_code"];?>"/>
				<div style="margin-top: 7px;">One-line Description</div>
				<input type="text" name="offer[oneliner]" size="65" class="text" value="<?=$offerDetails[0]["one_liner"];?>"/>
				<div style="margin-top: 7px; margin-bottom: 5px;">Offer Details</div>
				<textarea rows="6" cols="85" name="offer[details]" id="offer-details"><?=stripslashes(html_entity_decode($offerDetails[0]["details"]));?></textarea>
				<div style="margin-top: 7px; margin-bottom: 5px;">Offer Description</div>
				<textarea rows="6" cols="85" name="offer[description]" id="offer-description"><?=stripslashes(html_entity_decode($offerDetails[0]["description"]));?></textarea>
				<div style="margin-top: 7px; margin-bottom: 5px;">Merchant (<i>Who is Offering the Deal?</i>) <a href="?method=AddMerchants">Add Merchants</a></div>
					<div class="select-styling-div" style="width: 250px;">
						<select name="offer[company]" style="width: 250px;">
						<?php
						parent::SetQuery("SELECT * FROM table_merchants, table_merchantinfo WHERE table_merchants.merchant_id=table_merchantinfo.merchant_id");
						$companies = parent::DoQuery();
						foreach( $companies as $company )
						{// loop through merchants
							?>
							<option value="<?=$company["merchant_id"];?>" <?php if( $offerDetails[0]["company"] == $company["merchant_id"]) { ?>selected="selected"<?php } ?>><?=$company["company_name"];?></option>
							<?php
						}
						?>
						</select>
					</div>
				<div style="margin-top: 7px; margin-bottom: 5px;">Graphic (500x200)</div>
				<img src="<?=$offerDetails[0]["graphic"];?>" alt="" title="" border="0"/>
				<div style="margin-top: 7px;">Update Graphic (<i>will override existing, leave blank to keep current</i>)</div>
				<input type="file" class="text" name="offer-graphic"/>
				<div style="margin-top: 7px;">Offer Expiration (example: March 10, 2010 8pm)</div>
				<input type="text" name="offer[expiration]" size="35" class="text" value="<?=date("M-d-Y h:i a", $offerDetails[0]["expiration"]);?>"/>
					<div style="margin-top: 3px; margin-bottom: 7px;">
					<table cellspacing="0" cellpadding="2">
						<tr>
							<td><span id="days" class="timer-box">00</span></td>
							<td>:</td>
							<td><span id="hours" class="timer-box">00</span></td>
							<td>:</td>
							<td><span id="minutes" class="timer-box">00</span></td>
							<td>:</td>
							<td><span id="seconds" class="timer-box">00</span></td>
						</tr>
						<tr>
							<td style="text-align: center; font-size: 10px;">days</td>
							<td></td>
							<td style="text-align: center; font-size: 10px;">hours</td>
							<td></td>
							<td style="text-align: center; font-size: 10px;">mins</td>
							<td></td>
							<td style="text-align: center; font-size: 10px;">secs</td>
						</tr>
					</table>
					</div>
				<div style="margin-top: 7px;">Offer Price $(xx.xx)</div>
				<input type="text" name="offer[price]" size="15" class="text" value="<?=$offerDetails[0]["price"];?>"/>
				<div style="margin-top: 7px;">Offer Value $(xx.xx)</div>
				<input type="text" name="offer[value]" size="15" class="text" value="<?=$offerDetails[0]["value"];?>"/>
				<div style="margin-top: 7px;">Offer Discount (<b>%</b>) [leave blank to calculate]</div>
				<input type="text" name="offer[discount]" size="5" class="text" value="<?=$offerDetails[0]["discount"];?>"/> %
				<div style="margin-top: 7px;">Limit Per Customer</div>
				<input type="text" name="offer[limit]" size="2" maxlength="2" class="text" value="<?=$offerDetails[0]["limit"];?>"/>
				<div style="margin-top: 7px; margin-bottom: 3px;">Offer Locations (check all that apply)</div>
				<div>
				<?php
					parent::SetQuery("SELECT * FROM table_offerlocations WHERE offer_id='{$offerDetails[0]["offer_id"]}'");
					$countOfferLocations = parent::CountDBResults();
					$myOfferLocations = array();
					if( $countOfferLocations > 0 )
					{# More than 0 Offer Locations
						$locationsForOffer = parent::DoQuery();
						foreach( $locationsForOffer as $offerAvailLocations )
						{
							$myOfferLocations[] = $offerAvailLocations['location_id'];
						}
					}
					parent::SetQuery("SELECT * FROM table_locations");
					$offerLocations = parent::DoQuery();
					foreach( $offerLocations as $location )
					{# Loop Through Each Location
					?>
						<div style="overflow: hidden;"><input type="checkbox" <?php if( in_array( $location['location_id'], $myOfferLocations ) ) { ?>checked="checked"<?php } ?> style="float: left; cursor: pointer; margin-right: 3px;" name="location[<?=$location['location_id'];?>]" value="<?=$location['location_id'];?>" id="location[<?=$location['location_id'];?>]"/><label style="float: left; cursor: pointer;" for="location[<?=$location['location_id'];?>]"><?=$location['location'];?></label></div>
					<?php
					}
				?>
				</div>
				<div style="margin-top: 5px;">
					<button type="button" class="button-default" style="overflow: hidden;" onclick="ConfirmationMessage('Are you sure you want to delete this offer?', '?method=DoDeleteOffer&b=1&offer_id=<?=$offerDetails[0]["offer_id"];?>');"><img src="assets/gfx/icons/quantity-remove.gif" style="float: left;"/></button>
					<button type="submit" class="button-special">Save Changes</button>
				</div>
			</form>

	<?php
	}

	public function ClearSavedData()
	{# Clear Saved Data
		unset( $_SESSION['form-data'] );
		setcookie("msg", "Your saved form data has been erased.", time()+300, "/");
		setcookie("msg_type", "success", time()+300, "/");
		header("Location: ?method=ViewOffers");
	}

	public function UploadOfferPicture()
	{# Upload Offer Picture

		$target_dir = "uploads/"; 
		$image_file = stripslashes( $_FILES["offer-graphic"]["name"] );
		$md5_hash = md5( $image_file );

		$allowed_ext = "jpg,gif,png"; 
		$extension = pathinfo( $_FILES["offer-graphic"]["name"] );
		$extension = strtolower( $extension[ extension ] );
		$new_image = $target_dir . $md5_hash . '.' . $extension;
		$allowed_paths = explode( ",", $allowed_ext );
		for( $i=0; $i<sizeOf( $allowed_paths ); $i++ )
		{# Loop Extensions
			if( $allowed_paths[ $i ] == "$extension" )
			{# Allowed Extension
				$ok = 1;
			}
		}

		if($ok == 1)
		{# Extension Ok
			if( file_exists( $new_image ) ) { unlink( $new_image ); }
			if( move_uploaded_file( $_FILES[ "offer-graphic" ][ "tmp_name" ], $new_image ) )
			{# Moved New Image
				return array('status' => 1, 'image' => $new_image );
			}
			else
			{# Error Moving Image
				return array('status' => 0, 'reason' => 'Error moving file. ' . $new_image);
			}
		}
		else
		{
			return array('status' => 0, 'reason' => 'Invalid file extension. ' . $new_image);
		}
	}

	public function RemovePictureFile( $file )
	{# Delete Offer Picture
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
			if( file_exists( $file ) )
			{// if file exists, delete it
				unlink( $file );
			}
		}
	}

	public function AddOffers()
	{# Add Offer Form
		global $message, $message_type;
		?>

			<h1>Add Offers</h1>

<script type="text/javascript">
<!--
	setTimeout(function(){new nicEditor({fullPanel : true}).panelInstance('offer-details');}, 200); 
	setTimeout(function(){new nicEditor({fullPanel : true}).panelInstance('offer-description');}, 200); 
//-->
</script>

	<?php if( $message ) { ?>
		<div class="message-<?=$message_type;?>"><?=stripslashes($message);?></div>
	<?php } ?>

			<form action="?method=AddOffers" method="post" name="form-data" enctype="multipart/form-data">
				<input type="hidden" name="method" value="DoAddOffer">
				<input type="hidden" name="b" value="1"/>
				<div>Offer Name</div>
				<input type="text" name="offer[name]" size="30" class="text" value="<?=stripslashes($_SESSION['form-data']['offer']['name']);?>"/>
				<div style="margin-top: 7px;">Offer Code</div>
				<input type="text" name="offer[code]" size="20" class="text" value="<?=$_SESSION['form-data']['offer']['code'];?>"/>
				<div style="margin-top: 7px;">One-line Description</div>
				<input type="text" name="offer[oneliner]" size="65" class="text" value="<?=$_SESSION['form-data']['offer']['oneliner'];?>"/>
				<div style="margin-top: 7px; margin-bottom: 5px;">Offer Details</div>
				<textarea rows="6" cols="85" name="offer[details]" id="offer-details"><?=stripslashes($_SESSION['form-data']['offer']['details']);?></textarea>
				<div style="margin-top: 7px; margin-bottom: 5px;">Offer Description</div>
				<textarea rows="6" cols="85" name="offer[description]" id="offer-description"><?=stripslashes($_SESSION['form-data']['offer']['description']);?></textarea>
				<div style="margin-top: 7px; margin-bottom: 5px;">Merchant (<i>Who is Offering the Deal?</i>) <a href="?method=AddMerchants">Add Merchants</a></div>
					<div class="select-styling-div" style="width: 250px;">
						<select name="offer[company]" style="width: 250px;">
						<?php
						parent::SetQuery("SELECT * FROM table_merchants, table_merchantinfo WHERE table_merchants.merchant_id=table_merchantinfo.merchant_id");
						$companies = parent::DoQuery();
						foreach( $companies as $company )
						{// loop through merchants
							?>
							<option value="<?=$company["merchant_id"];?>" <?php if( $_SESSION['form-data']['offer']['company'] == $company["merchant_id"]) { ?>selected="selected"<?php } ?>><?=$company["company_name"];?></option>
							<?php
						}
						?>
						</select>
					</div>
				<div style="margin-top: 7px;">Offer Graphic (500 x 200)</div>
				<input type="file" class="text" name="offer-graphic"/>
				<div style="margin-top: 7px;">Offer Expiration (example: March 10, 2010 8pm)</div>
				<input type="text" name="offer[expiration]" size="35" class="text" value="<?=$_SESSION['form-data']['offer']['expiration'];?>"/>
				<div style="margin-top: 7px;">Offer Price $(xx.xx)</div>
				<input type="text" name="offer[price]" size="15" class="text" value="<?=$_SESSION['form-data']['offer']['price'];?>"/>
				<div style="margin-top: 7px;">Offer Value $(xx.xx)</div>
				<input type="text" name="offer[value]" size="15" class="text" value="<?=$_SESSION['form-data']['offer']['value'];?>"/>
				<div style="margin-top: 7px;">Offer Discount (<b>%</b>) [leave blank to calculate]</div>
				<input type="text" name="offer[discount]" size="5" class="text" value="<?=$_SESSION['form-data']['offer']['discount'];?>"/> %
				<div style="margin-top: 7px;">Limit Per Customer</div>
				<input type="text" name="offer[limit]" size="2" maxlength="2" class="text" value="<?=$_SESSION['form-data']['offer']['limit'];?>"/>
				<div style="margin-top: 7px; margin-bottom: 3px;">Offer Locations (check all that apply)</div>
				<div>
				<?php
					parent::SetQuery("SELECT * FROM table_locations");
					$offerLocations = parent::DoQuery();
					foreach( $offerLocations as $location )
					{# Loop Through Each Location
					?>
						<div style="overflow: hidden;"><input type="checkbox" style="float: left; cursor: pointer; margin-right: 3px;" name="location[<?=$location['location_id'];?>]" <?php if( $_SESSION['form-data']['location'][$location['location_id']] ) { ?>checked="checked"<?php } ?> value="<?=$location['location_id'];?>" id="location[<?=$location['location_id'];?>]"/><label style="float: left; cursor: pointer;" for="location[<?=$location['location_id'];?>]"><?=$location['location'];?></label></div>
					<?php
					}
				?>
				</div>
				<div style="margin-top: 5px;">
					<button type="button" class="button-default" style="overflow: hidden;" onclick="ConfirmationMessage('Are you sure you want to cancel without saving?', '?method=ClearSavedData&b=1');"><img src="assets/gfx/icons/quantity-remove.gif" style="float: left;"/></button>
					<button type="submit" class="button-special" name="form-data">Add Offer</button>
				</div>
			</form>

		<?php
	}

	public function ViewOfferParticipants()
	{# View Offer Participants
		parent::SetQuery("SELECT * FROM table_offers WHERE offer_code='{$this->localData['offer_code']}' LIMIT 1");
		$offerData = parent::DoQuery();
		$timer = $this->GetOfferDetails( $offerData[0]["offer_code"] );
		# Create Paging
		parent::SetQuery( "SELECT * FROM table_purchased WHERE offer_id='{$offerData[0]['offer_id']}'" );
		$totalParticipants = parent::CountDBResults();
		$paging = $this->Pagination( array( "totalRows" => $totalParticipants ) );
		parent::SetQuery("SELECT * FROM table_purchased WHERE offer_id='{$offerData[0]['offer_id']}' LIMIT {$this->localData['s']},{$this->rowsPerPage}");
		$participantCount = parent::CountDBResults();
	?>

	<!--Timer-->
	<script type="text/javascript">
	<!--
		var timeLeft = <?=$timer["timeLeft"]?>;
		var timer;
	//-->
	</script>
	<script type="text/javascript" src="assets/js/Timer.js"></script>
	<script type="text/javascript">
	<!--
		window.onload = Timer;
	//-->
	</script>

		<h1>Offer Details</h1>

		<div style="margin-bottom: 25px; padding-bottom: 25px; border-bottom: 1px dashed #C7C7C7; overflow: auto;">
			<div style="float: left;">
			<b><?=$offerData[0]["name"];?></b> (<?=$offerData[0]["offer_code"];?>)<br/>
			<p><?=html_entity_decode( $offerData[0]["description"], ENT_QUOTES );?></p>
			<table style="line-height: 30px;">
				<tr>
					<td style="text-align: right;" class="default-special">Price :</td>
					<td class="yellow-special">$<?=$offerData[0]["price"];?></td>
				</tr>
				<tr>
					<td style="text-align: right;" class="default-special">Value :</td>
					<td class="yellow-special">$<?=$offerData[0]["value"];?></td>
				</tr>
				<tr>
					<td style="text-align: right;" class="default-special">Discount :</td>
					<td class="yellow-special"><?=$offerData[0]["discount"];?>%</td>
				</tr>
			</table>
			</div>
			<div style="float: right;">

				<!--Timer-->
				<div style="margin-top: 3px; margin-bottom: 7px;">
					<p><img src="assets/gfx/misc/time-left-to-buy.png" alt="" title=""/></p>
					<table cellspacing="0" cellpadding="2" align="center">
						<tr>
							<td><span id="days" class="timer-box">00</span></td>
							<td>:</td>
							<td><span id="hours" class="timer-box">00</span></td>
							<td>:</td>
							<td><span id="minutes" class="timer-box">00</span></td>
							<td>:</td>
							<td><span id="seconds" class="timer-box">00</span></td>
						</tr>
						<tr>
							<td style="text-align: center; font-size: 10px;">days</td>
							<td></td>
							<td style="text-align: center; font-size: 10px;">hours</td>
							<td></td>
							<td style="text-align: center; font-size: 10px;">mins</td>
							<td></td>
							<td style="text-align: center; font-size: 10px;">secs</td>
						</tr>
					</table>
				</div>

			</div>
		</div>

		<h3>Offer Participants</h3>
		<?php
		if( $participantCount > 0 )
		{// Offer has participants
		?>
			<table cellspacing="0" cellpadding="4" border="0" style="width: 100%; border: 1px solid gray; border-collapse: collapse;">
			<tr style="background-color: #C9C9C9; color: #FFFFFF; font-weight: bold;">
				<th style="width: 15%; text-align: center;">Action</th>
				<th style="width: 45%;">Customer Name</th>
				<th style="width: 40%; text-align: right;">Email Address</th>
			</tr>
			<?php
				$participant_details = parent::DoQuery();
				foreach( $participant_details as $participant )
				{# Loop Through Each Customer
					parent::SetQuery("SELECT * FROM table_userinfo, table_users WHERE table_users.user_id=table_userinfo.user_id AND table_users.user_id='{$participant["user_id"]}'");
					$customerData = parent::DoQuery();
				?>
					<tr onmouseover="this.style.backgroundColor='#F9F9F9';"
						onmouseout="this.style.backgroundColor='#FFFFFF';">
						<td style="text-align: center;">
							<a href="?method=ViewCustomerDetails&user_id=<?=$customerData[0]["user_id"];?>"><img src="assets/gfx/icons/button-edit.png" alt="" title="View/Edit Customer Details" border="0"/></a>
						</td>
						<td><?=ucwords($customerData[0]["firstname"]);?> <?=ucwords($customerData[0]["lastname"]);?></td>
						<td style="text-align: right;"><a href="javascript:void(0);"><?=$customerData[0]["email_address"];?></a></td>
					</tr>
				<?php
				}
			?>
			</table>
			<?=$paging;?>
		<?php
		}
		else
		{// No participants
		?>
			There are no participants for this Offer.
		<?php
		}
		?>
		<div style="margin-top: 7px; overflow: hidden;">
			<input type="button" style="float: left;" onclick="location='?method=ViewOfferDetails&offer_code=<?=$offerData[0]["offer_code"];?>';" class="button-special" value="Edit Offer"/>
			<button type="button" class="button-special" style="float: left; margin-left: 5px;" onclick="window.open('app/ReportGenerator.php?method=ExportParticipantsCSV&offer_code=<?=$offerData[0]["offer_code"];?>&b=1');">Export Participants (CSV)</button>
		</div>
	<?php
	}

	public function ExportParticipantsCSV()
	{// export data to CSV, Available to Administration & Merchant
		if( $this->LocalCheckAuth() || $_SESSION["user"]["type"] == "merchant" )
		{// check if admin logged in
			parent::SetQuery("SELECT * FROM `table_offers` WHERE `offer_code`='{$this->localData["offer_code"]}'");
			$offerData = parent::DoQuery();
			parent::SetQuery("SELECT * FROM `table_purchased` WHERE `offer_id`='{$offerData[0]["offer_id"]}'");
			$participants = parent::CountDBResults();
			header("Content-type: application/csv");
			header("Content-Disposition: inline; filename=offer-{$offerData[0]["offer_code"]}-participants-report_" . date("M-d-Y") . ".csv"); 
			if( $participants > 0 )
			{// more than one participant
				$participants = parent::DoQuery();
				$count = 0;
				foreach( $participants as $participant )
				{// get each participant
					parent::SetQuery("SELECT * FROM `table_users`,`table_userinfo`
					WHERE `table_users`.`user_id`=`table_userinfo`.`user_id`
					AND `table_users`.`user_id`='{$participant["user_id"]}'");
					$participantInfo = parent::DoQuery();
					foreach( $participantInfo[0] as $name => $value )
					{//
						if( $name!="password" )
						{// not a password field
							$fieldNames[] = $name;
							$userFields[] = $value;
						}
					}
					if( $count == 0 ) { echo join(",", $fieldNames) . "\n"; }
					echo join(",", $userFields) . "\n";
					$count+=1;
				}
			}
		}
	}

	private function DoAddOffer()
	{# Add Offer to Database
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		$this->localData['offer']['discount'] = round( 100 * ( $this->localData['offer']['value'] - $this->localData['offer']['price'] ) / $this->localData['offer']['value']  );
		if( $this->localData['offer']['code']!='' && $this->localData['offer']['name']!=''
			&& $this->localData['offer']['expiration']!='' && $this->localData['offer']['price']!=''
			&& $this->localData['offer']['description']!=''
			&& $this->localData['offer']['value']!=''
			&& $this->localData['offer']['discount']!=''
			&& $this->localData['offer']['oneliner']!=''
			&& $this->localData['offer']['company']!=''
			&& $this->localData['offer']['details']!='')
		{
			parent::SetQuery("SELECT * FROM table_offers WHERE offer_code='{$this->localData['offer']['code']}' LIMIT 1");
			$doesExist = parent::CountDBResults();
			if (!$doesExist)
			{# Doesn't Exist, Add
				$uploadSuccess = $this->UploadOfferPicture();
				if( $uploadSuccess['status'] == 1 )
				{// upload successful
					parent::SetQuery("INSERT INTO table_offers VALUES ('','{$this->localData['offer']['code']}',
						'{$this->localData['offer']['name']}',
						'{$this->localData['offer']['oneliner']}',
						'{$this->EncodeHtml($this->localData['offer']['details'])}',
						'{$this->EncodeHtml($this->localData['offer']['description'])}',
						'{$this->EncodeHtml($this->localData['offer']['company'])}',
						'{$uploadSuccess['image']}',
						'{$this->TimestampConverter($this->localData['offer']['expiration'])}',
						'{$this->localData['offer']['price']}',
						'{$this->localData['offer']['value']}',
						'{$this->localData['offer']['discount']}',
						'active',
						'{$this->localData['offer']['limit']}')");
					parent::SimpleQuery();
					parent::SetQuery("SELECT * FROM table_offers WHERE offer_code='{$this->localData['offer']['code']}' LIMIT 1");
					$offerInfo = parent::DoQuery();
					// insert offer into the queue to send out emails to subscribers
					parent::SetQuery("INSERT INTO `table_queue` VALUES ('{$offerInfo[0]["offer_id"]}','0','0')");
					parent::SimpleQuery();

					foreach( $this->localData['location'] as $location )
					{# Loop Through Selected Offer Locations
						parent::SetQuery("INSERT INTO `table_offerlocations` VALUES ('','{$offerInfo[0]['offer_id']}','{$location}')");
						parent::SimpleQuery();
					}
					unset( $_SESSION['offer'] );
					unset( $_SESSION['location'] );
					unset( $_SESSION['form-data'] );
					setcookie("msg", "New Offer &quot;{$this->localData['offer']['name']}&quot; successfully created.", time()+300, "/");
					setcookie("msg_type", "success", time()+300, "/");
					$redirect = "AddOffers";
				}
				else
				{// error uploading picture
					setcookie("msg", $uploadSuccess['reason'], time()+300, "/");
					setcookie("msg_type", "error", time()+300, "/");
					$redirect = "AddOffers";
				}
			}
			else
			{# Does Exist, Update
				$uploadSuccess = $this->UploadOfferPicture();
				if( $uploadSuccess['status'] == 1 )
				{// upload successful
					$update_graphic = "graphic='{$uploadSuccess['image']}',";
					parent::SetQuery("SELECT * FROM `table_offers` WHERE offer_code='{$this->localData['offer']['code']}'");
					$exists = parent::CountDBResults();
					if( $exists )
					{// image exists
						$detailedOfferData = parent::DoQuery();
						$this->RemovePictureFile( $detailedOfferData[0]["graphic"] );
					}
				}
				else
				{// don't update graphic
					$update_graphic = '';
				}
				$offerData = parent::DoQuery();
				parent::SetQuery("UPDATE `table_offers` SET offer_code='{$this->localData['offer']['code']}', 
					name='{$this->localData['offer']['name']}',
					one_liner='{$this->localData['offer']['oneliner']}',
					details='{$this->EncodeHtml($this->localData['offer']['details'])}',
					description='{$this->EncodeHtml($this->localData['offer']['description'])}',
					company='{$this->EncodeHtml($this->localData['offer']['company'])}',
					expiration='{$this->TimestampConverter($this->localData['offer']['expiration'])}',
					price='{$this->localData['offer']['price']}',
					value='{$this->localData['offer']['value']}',
					discount='{$this->localData['offer']['discount']}',
					{$update_graphic}
					`limit`='{$this->localData['offer']['limit']}'
					WHERE offer_id='{$offerData[0]["offer_id"]}' LIMIT 1");
				parent::SimpleQuery();
				parent::SetQuery("SELECT * FROM table_offers WHERE offer_code='{$this->localData['offer']['code']}' LIMIT 1");
				$offerInfo = parent::DoQuery();

				parent::SetQuery("SELECT * FROM table_locations");
				$allLocations = parent::DoQuery();

				foreach( $allLocations as $location )
				{# Loop Through All Locations
					if( in_array( $location['location_id'], $this->localData['location'] ) )
					{# 
						parent::SetQuery("SELECT * FROM table_offerlocations WHERE offer_id='{$offerInfo[0]['offer_id']}' AND location_id='{$location['location_id']}'");
						$exists = parent::CountDBResults();
						if( !$exists )
						{
							parent::SetQuery("INSERT INTO table_offerlocations VALUES ('','{$offerInfo[0]['offer_id']}','{$location['location_id']}')");
							parent::SimpleQuery();
						}
					}
					else
					{
						parent::SetQuery("DELETE FROM table_offerlocations WHERE location_id='{$location['location_id']}' AND offer_id='{$offerInfo[0]['offer_id']}' LIMIT 1");
						parent::SimpleQuery();
					}
				}

				setcookie("msg", "Your updates have been saved.", time()+300, "/");
				setcookie("msg_type", "success", time()+300, "/");
				unset( $_SESSION['form-data'] );
				$redirect = "ViewOfferDetails&offer_code=" . $this->localData['offer']['code'];
			}
		}
		else
		{# Missing Information
			setcookie("msg", "You were missing required fields, all fields are required.", time()+300, "/");
			setcookie("msg_type", "error", time()+300, "/");
			$redirect = "AddOffers";
		}
		header("Location: ?method=" . $redirect);
		}
	}

	public function DoDeleteOffer()
	{# Delete Offer
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		parent::SetQuery("SELECT * FROM table_offers WHERE offer_id='{$this->localData['offer_id']}' LIMIT 1");
		$offerData = parent::DoQuery();
		$this->RemovePictureFile( $offerData[0]["graphic"] );
		parent::SetQuery("DELETE FROM table_offers WHERE offer_id='{$this->localData['offer_id']}' LIMIT 1");
		parent::SimpleQuery();
		parent::SetQuery("DELETE FROM table_offerlocations WHERE offer_id='{$this->localData['offer_id']}'");
		parent::SimpleQuery();
		setcookie("msg", "You have successfully deleted the Offer: &quot;{$offerData[0]["name"]}&quot;", time()+300, "/");
		setcookie("msg_type", "success", time()+300, "/");
		header("Location: ?method=ViewOffers");
		}
	}

/**
*** END Offers
***
**/

/**
*** START Subscribers
***
**/

	public function ViewSubscribers()
	{# View Subscribers
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		# Create Paging
		parent::SetQuery( "SELECT * FROM table_subscribers" );
		$totalSubscribers = parent::CountDBResults();
		$paging = $this->Pagination( array( "totalRows" => $totalSubscribers ) );
		parent::SetQuery( "SELECT * FROM table_subscribers LIMIT {$this->localData['s']},{$this->rowsPerPage}" );
		$subscribers = parent::CountDBResults();
		?>

			<h1>View Subscribers</h1>

	<?php if( $message ) { ?>
		<div class="message-<?=$message_type;?>"><?=stripslashes($message);?></div>
	<?php } ?>

		<?php
			if( $subscribers > 0 )
			{# More than 0 Subscribers
			?>
			<table cellspacing="0" cellpadding="4" border="0" style="width: 100%; border: 1px solid gray; border-collapse: collapse;">
			<tr style="background-color: #C9C9C9; color: #FFFFFF; font-weight: bold;">
				<th style="width: 15%;">Action</th>
				<th style="width: 35%;">Subscriber Email</th>
				<th style="width: 30%; text-align: right;">Location</th>
				<th style="width: 20%; text-align: center;">Member?</th>
			</tr>
			<?php
				$subscriber_details = parent::DoQuery();
				foreach( $subscriber_details as $subscriber )
				{# Loop Through Each Offer
					parent::SetQuery("SELECT * FROM table_locations WHERE location_id='{$subscriber["location_id"]}' LIMIT 1");
					$locationData = parent::DoQuery();
					parent::SetQuery("SELECT * FROM table_users WHERE email_address='{$subscriber["email_address"]}'");
					$isMember = parent::CountDBResults();
					if( $isMember )
					{// subscriber is a member
						$memberDetails = parent::DoQuery();
					}
				?>
					<tr onmouseover="this.style.backgroundColor='#F9F9F9';"
						onmouseout="this.style.backgroundColor='#FFFFFF';">
						<td style="text-align: center;">
							<button type="button" class="button-default" style="overflow: hidden;" onclick="ConfirmationMessage('Are you sure you want to delete this Subscriber: &quot;<?=$subscriber["email_address"];?>&quot;?', '?method=DoDeleteSubscriber&b=1&subscriber_id=<?=$subscriber["subscriber_id"];?>')"><img src="assets/gfx/icons/quantity-remove.gif" alt="" title="Remove Subscriber" style="float: left;"/></button>
						</td>
						<td><a href="mailto:<?=$subscriber["email_address"];?>"><?=$subscriber["email_address"];?></a></td>
						<td style="text-align: right;"><?=$locationData[0]["location"];?></td>
						<td style="text-align: center;">
							<?php
							if( $isMember )
							{// person is a member
							?>
							<a href="?method=ViewCustomerDetails&user_id=<?=$memberDetails[0]["user_id"];?>"><img src="assets/gfx/icons/account-person-green.gif" alt="" title="Account Holder" border="0"/></a>
							<?php
							}
							else
							{// person is not a member
							?>
							<img src="assets/gfx/icons/account-person-gray.gif" alt="" title="Not An Account Holder"/>
							<?php
							}
							?>
						</td>
					</tr>
				<?php
				}
			?>
			</table>
			<?=$paging;?>
			<?php
			}
			else
			{# No subscribers, display a message
		?>
				There are currently no subscribers to display.
		<?php
			}
		?>

		<?php
		}
	}

	public function DoDeleteSubscriber()
	{# Delete a Subscriber
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
			parent::SetQuery("DELETE FROM `table_subscribers` WHERE subscriber_id='{$this->localData["subscriber_id"]}' LIMIT 1");
			parent::SimpleQuery();
			setcookie("msg", "Subscriber successfully removed.", time()+300, "/");
			setcookie("msg_type", "success", time()+300, "/");
			header("Location: ?method=ViewSubscribers");
		}
	}

/**
*** END Subscribers
***
**/

/**
*** START Customers
***
**/

	public function ViewCustomers()
	{# View Customers
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		# Create Paging
		parent::SetQuery( "SELECT * FROM table_users" );
		$totalCustomers = parent::CountDBResults();
		$paging = $this->Pagination( array( "totalRows" => $totalCustomers ) );
		parent::SetQuery( "SELECT * FROM table_users, table_userinfo WHERE table_users.user_id=table_userinfo.user_id ORDER BY lastname ASC LIMIT {$this->localData['s']},{$this->rowsPerPage}" );
		$customers = parent::CountDBResults();
		?>

			<h1>View Customers</h1>
		<?php
			if( $customers > 0 )
			{# More than 0 Offers
			?>
			<table cellspacing="0" cellpadding="4" border="0" style="width: 100%; border: 1px solid gray; border-collapse: collapse;">
			<tr style="background-color: #C9C9C9; color: #FFFFFF; font-weight: bold;">
				<th style="width: 15%; text-align: center;">Action</th>
				<th style="width: 30%;">Customer Name</th>
				<th style="width: 15%;">Value</th>
				<th style="width: 40%; text-align: right;">Email Address</th>
			</tr>
			<?php
				$customer_details = parent::DoQuery();
				foreach( $customer_details as $customer )
				{# Loop Through Each Customer
					parent::SetQuery("SELECT * FROM table_userinfo WHERE user_id='{$customer["user_id"]}' LIMIT 1");
					$customerData = parent::DoQuery();
					$names = array();
					$names[] = ucwords( $customerData[0]["firstname"] );
					$names[] = ucwords( $customerData[0]["lastname"] );
					$nameDisplay = join(" ", $names );
					if( strlen( $nameDisplay ) == 1 )
					{// names are empty
						$nameDisplay = "&lt;no name specified&gt;";
					}
				?>
					<tr onmouseover="this.style.backgroundColor='#F9F9F9';"
						onmouseout="this.style.backgroundColor='#FFFFFF';">
						<td style="text-align: center;">
							<!--<button type="button" class="button-default" style="overflow: hidden;" onclick="ConfirmationMessage('Are you sure you want to delete this Customer: <?=$nameDisplay;?>?', '?method=DoDeleteCustomers&b=1&user_id=<?=$customerData[0]["user_id"];?>')"><img src="assets/gfx/icons/quantity-remove.gif" alt="" title="Remove Customer" style="float: left;"/></button>
							--><a href="?method=ViewCustomerDetails&user_id=<?=$customerData[0]["user_id"];?>"><img src="assets/gfx/icons/button-edit.png" alt="" title="View/Edit Customer Details" border="0"/></a>
						</td>
						<td><?=$nameDisplay;?></td>
						<td>$<?php
							parent::SetQuery("SELECT SUM(total) as customer_value FROM table_transactions WHERE user_id='{$customerData[0]["user_id"]}'");
							$customerValue = parent::DoQuery();
							echo number_format( $customerValue[0]["customer_value"], 2, '.', '' );
						?></td>
						<td style="text-align: right;"><a href="mailto:<?=$customer["email_address"];?>"><?=$customer["email_address"];?></a></td>
					</tr>
				<?php
				}
			?>
			</table>
			<?=$paging;?>
			<?php
			}
			else
			{# No customers, display a message
		?>
				There are currently no customers to display.
		<?php
			}
		?>

		<?php
		}
	}

	public function DoDeleteCustomer()
	{# Delete Customer
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
			/*parent::SetQuery("DELETE FROM `table_users` WHERE user_id='{$this->localData["user_id"]}' LIMIT 1");
			parent::SimpleQuery();
			parent::SetQuery("DELETE FROM `table_userinfo` WHERE user_id='{$this->localData["user_id"]}' LIMIT 1");
			parent::SimpleQuery();
			parent::SetQuery("DELETE FROM `table_accountbalance` WHERE user_id='{$this->localData["user_id"]}' LIMIT 1");
			parent::SimpleQuery();
			parent::SetQuery("DELETE FROM `table_*/
			header("Location: ?method=ViewCustomers");
		}
	}

	public function ViewCustomerDetails()
	{# View Customer Details
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		parent::SetQuery("SELECT * FROM table_users, table_userinfo WHERE table_users.user_id=table_userinfo.user_id AND table_users.user_id='{$this->localData['user_id']}' LIMIT 1");
		$customer_details = parent::DoQuery();
		$customer_details = $customer_details[0];
		global $message, $message_type;
		?>
		<h1>Customer Details</h1>

	<?php if( $message ) { ?>
		<div class="message-<?=$message_type;?>"><?=stripslashes($message);?></div>
	<?php } ?>

		<form action="?method=ViewCustomerDetails&user_id=<?=$customer_details["user_id"];?>" method="post">
		<div style="overflow: hidden;">
			<div style="float: left; width: 50%;">
			<input type="hidden" name="method" value="SaveCustomerDetails"/>
			<input type="hidden" name="b" value="1"/>
			<div>First Name</div>
			<input type="text" class="text" size="30" name="user[firstname]" value="<?=$customer_details["firstname"];?>"/>
			<div style="margin-top: 7px;">Last Name</div>
			<input type="text" class="text" size="30" name="user[lastname]" value="<?=$customer_details["lastname"];?>"/>
			<div style="margin-top: 7px;">Email Address</div>
			<input type="text" class="text" size="35" name="user[email_address]" value="<?=$customer_details["email_address"];?>"/>
			<div style="margin-top: 7px;">Billing Address 1</div>
			<input type="text" class="text" size="45" name="user[billing_address1]" value="<?=$customer_details["billing_address1"];?>"/>
			<div style="margin-top: 7px;">Billing Address 2</div>
			<input type="text" class="text" size="45" name="user[billing_address2]" value="<?=$customer_details["billing_address2"];?>"/>
			<div style="margin-top: 7px;">Billing City</div>
			<input type="text" class="text" size="40" name="user[billing_city]" value="<?=$customer_details["billing_city"];?>"/>
			<div style="margin-top: 7px;">Billing State</div>
			<input type="text" class="text" size="2" name="user[billing_state]" value="<?=$customer_details["billing_state"];?>"/>
			<div style="margin-top: 7px;">Billing Zip</div>
			<input type="text" class="text" size="5" name="user[billing_zip]" value="<?=$customer_details["billing_zip"];?>"/>
			</div>
			
			<div style="float: right; min-height: 400px; width: 40%; border-left: 1px dashed gray; padding-left: 15px; margin-left: 15px;">
				<h1>Offers Purchased</h1>
				<?php
				parent::SetQuery("SELECT * FROM table_purchased WHERE user_id='{$customer_details["user_id"]}'");
				$offers = parent::CountDBResults();
				if( $offers > 0 )
				{// more than zero offers
					$offerInfo = parent::DoQuery();
					foreach( $offerInfo as $offer )
					{// loop through offers
					parent::SetQuery("SELECT * FROM table_offers WHERE offer_id='{$offer["offer_id"]}'");
					$offerDetails = parent::DoQuery();
						?>
						<a href="?method=ViewOfferParticipants&offer_code=<?=$offerDetails[0]["offer_code"];?>"><?=$offerDetails[0]["name"];?></a><br/>
						<?php
					}
				}
				else
				{// zero results
				?>
					This user has not yet purchased anything.
				<?php
				}
				?>
			</div>
		</div>
		<div>
			<button type="submit" class="button-special">Save Changes</button>
		</div>
		</form>
		<?php
		}
	}

	public function SaveCustomerDetails()
	{# Save Customer Details
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		parent::SetQuery("UPDATE table_users SET email_address='{$this->localData["user"]["email_address"]}' WHERE user_id='{$this->localData["user_id"]}' LIMIT 1");
		parent::SimpleQuery();
		parent::SetQuery("UPDATE table_userinfo SET firstname='{$this->localData["user"]["firstname"]}',
			lastname='{$this->localData["user"]["lastname"]}',
			billing_address1='{$this->localData["user"]["billing_address1"]}',
			billing_address2='{$this->localData["user"]["billing_address2"]}',
			billing_city='{$this->localData["user"]["billing_city"]}',
			billing_state='{$this->localData["user"]["billing_state"]}',
			billing_zip='{$this->localData["user"]["billing_zip"]}'
			WHERE user_id='{$this->localData["user_id"]}' LIMIT 1");
		parent::SimpleQuery();
		setcookie("msg", "Your changes have been saved.", time()+300, "/");
		setcookie("msg_type", "success", time()+300, "/");
		header("Location: ?method=ViewCustomerDetails&user_id={$this->localData["user_id"]}");
		}
	}

/**
*** END Customers
***
**/

/**
*** START Merchants
***
**/

	public function ViewMerchants()
	{# View Customers
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		# Create Paging
		parent::SetQuery( "SELECT * FROM table_merchants" );
		$totalMerchants = parent::CountDBResults();
		$paging = $this->Pagination( array( "totalRows" => $totalMerchants ) );
		parent::SetQuery( "SELECT * FROM table_merchants, table_merchantinfo WHERE table_merchants.merchant_id=table_merchantinfo.merchant_id ORDER BY company_name ASC LIMIT {$this->localData['s']},{$this->rowsPerPage}" );
		$merchants = parent::CountDBResults();
		?>

			<h1>View Merchants</h1>
		<?php
			if( $merchants > 0 )
			{# More than 0 Offers
			?>
			<table cellspacing="0" cellpadding="4" border="0" style="width: 100%; border: 1px solid gray; border-collapse: collapse;">
			<tr style="background-color: #C9C9C9; color: #FFFFFF; font-weight: bold;">
				<th style="width: 15%; text-align: center;">Action</th>
				<th style="width: 35%;">Company</th>
				<th style="width: 25%;">Location</th>
				<th style="width: 25%; text-align: right;">Contact Email</th>
			</tr>
			<?php
				$merchant_details = parent::DoQuery();
				foreach( $merchant_details as $merchant )
				{# Loop Through Each Customer
					parent::SetQuery("SELECT * FROM table_merchantinfo WHERE merchant_id='{$merchant["merchant_id"]}' LIMIT 1");
					$merchantData = parent::DoQuery();
				?>
					<tr onmouseover="this.style.backgroundColor='#F9F9F9';"
						onmouseout="this.style.backgroundColor='#FFFFFF';">
						<td style="text-align: center;">
							<button type="button" class="button-default" style="overflow: hidden;" onclick="ConfirmationMessage('Are you sure you want to delete this Merchant: &quot;<?=$merchantData[0]['company_name'];?>&quot;?', '?method=DoDeleteMerchant&b=1&merchant_id=<?=$merchantData[0]['merchant_id'];?>')"><img src="assets/gfx/icons/quantity-remove.gif" alt="" title="Delete Merchant" style="float: left;"/></button>
							<a href="?method=ViewMerchantDetails&merchant_id=<?=$merchantData[0]["merchant_id"];?>"><img src="assets/gfx/icons/button-edit.png" alt="" title="View/Edit Merchant Details" border="0"/></a>
						</td>
						<td><?=ucwords($merchantData[0]["company_name"]);?></td>
						<td><?=$merchantData[0]["city"];?> <?=$merchantData[0]["state"];?></td>
						<td style="text-align: right;"><a href="mailto:<?=$merchant["email_address"];?>"><?=$merchant["email_address"];?></a></td>
					</tr>
				<?php
				}
			?>
			</table>
			<?=$paging;?>
			<?php
			}
			else
			{# No merchants, display a message
		?>
				There are currently no merchants to display.
		<?php
			}
		?>

		<div>
			<button type="button" onclick="window.location='control_panel?method=AddMerchants';" class="button-default" style="overflow: auto; margin-top: 10px;"><img src="assets/gfx/icons/button-add-general.gif" alt="" title="" style="float: left;"/><span style="float: left; margin-left: 3px;">Add Merchant</span></button>
		</div>

		<?php
		}
	}

	public function SaveMerchantDetails()
	{# Save Merchant Details
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		parent::SetQuery("UPDATE table_merchants SET 
		email_address='{$this->localData["merchant"]["email_address"]}',
			password='{$this->localData["merchant"]["password"]}'
			WHERE merchant_id='{$this->localData["merchant_id"]}' LIMIT 1");
		parent::SimpleQuery();
		parent::SetQuery("UPDATE table_merchantinfo SET 
			company_name='{$this->localData["merchant"]["company_name"]}',
			street_address='{$this->localData["merchant"]["street_address"]}',
			city='{$this->localData["merchant"]["city"]}',
			state='{$this->localData["merchant"]["state"]}',
			zipcode='{$this->localData["merchant"]["zipcode"]}',
			website_url='{$this->localData["merchant"]["website_url"]}'
			WHERE merchant_id='{$this->localData["merchant_id"]}' LIMIT 1");
		parent::SimpleQuery();
		setcookie("msg", "Your changes have been saved.", time()+300, "/");
		setcookie("msg_type", "success", time()+300, "/");
		header("Location: ?method=ViewMerchantDetails&merchant_id={$this->localData["merchant_id"]}");
		}
	}

	public function ViewMerchantDetails()
	{# Edit Merchant
		global $message, $message_type;
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		parent::SetQuery("SELECT * FROM `table_merchants`,`table_merchantinfo` WHERE
		`table_merchants`.`merchant_id`=`table_merchantinfo`.`merchant_id` AND
		`table_merchants`.`merchant_id`='{$this->localData["merchant_id"]}' LIMIT 1");
		$exists = parent::CountDBResults();
		if( $exists )
		{// merchant exists
			$merchantData = parent::DoQuery();
		?>

			<h1>Edit Merchant</h1>

	<?php if( $message ) { ?>
		<div class="message-<?=$message_type;?>"><?=stripslashes($message);?></div>
	<?php } ?>

			<form action="?method=SaveMerchantDetails&b=1&merchant_id=<?=$merchantData[0]["merchant_id"];?>" method="post">
				<div>Merchant Email</div>
				<input type="text" class="text" name="merchant[email_address]" value="<?=$merchantData[0]["email_address"];?>" size="30"/>

				<div style="margin-top: 7px;">Merchant Password</div>
				<input type="text" class="text" name="merchant[password]" value="<?=$merchantData[0]["password"];?>" size="25"/>

				<div style="margin-top: 7px;">Company Name</div>
				<input type="text" class="text" name="merchant[company_name]" value="<?=$merchantData[0]["company_name"];?>" size="45"/>

				<div style="margin-top: 7px;">Street Address</div>
				<input type="text" class="text" name="merchant[street_address]" value="<?=$merchantData[0]["street_address"];?>" size="55"/>

				<div style="margin-top: 7px;">City</div>
				<input type="text" class="text" name="merchant[city]" value="<?=$merchantData[0]["city"];?>" size="45"/>

				<div style="margin-top: 7px;">State</div>
				<input type="text" class="text" name="merchant[state]" maxlength="2" value="<?=$merchantData[0]["state"];?>" size="2"/>

				<div style="margin-top: 7px;">Zip Code</div>
				<input type="text" class="text" name="merchant[zipcode]" maxlength="5" value="<?=$merchantData[0]["zipcode"];?>" size="5"/>


				<div style="margin-top: 7px;">Website</div>
				<input type="text" class="text" name="merchant[website_url]" value="<?=$merchantData[0]["website_url"];?>" size="55"/>

				<div>
					<button type="submit" class="button-special">Save Merchant Details</button>
				</div>
			</form>

		<?php
		}
		else
		{// merchant not found
		?>
			<center>We could not locate this Merchant.</center>
		<?php
		}
		}
	}

	public function AddMerchants()
	{# Add Merchant
		global $message, $message_type;
		?>

			<h1>Add Merchants</h1>

	<?php if( $message ) { ?>
		<div class="message-<?=$message_type;?>"><?=stripslashes($message);?></div>
	<?php } ?>

			<form action="?method=AddMerchants" method="post">
				<input type="hidden" name="method" value="DoAddMerchant">
				<input type="hidden" name="b" value="1"/>
				<div>Merchant Email</div>
				<input type="text" class="text" name="merchant[email_address]" value="" size="30"/>

				<div style="margin-top: 7px;">Merchant Password</div>
				<input type="text" class="text" name="merchant[password]" value="" size="25"/>

				<div style="margin-top: 7px;">Company Name</div>
				<input type="text" class="text" name="merchant[company_name]" value="" size="45"/>

				<div style="margin-top: 7px;">Street Address</div>
				<input type="text" class="text" name="merchant[street_address]" value="" size="55"/>

				<div style="margin-top: 7px;">City</div>
				<input type="text" class="text" name="merchant[city]" value="" size="45"/>

				<div style="margin-top: 7px;">State</div>
				<input type="text" class="text" name="merchant[state]" maxlength="2" value="" size="2"/>

				<div style="margin-top: 7px;">Website</div>
				<input type="text" class="text" name="merchant[website_url]" value="http://" size="55"/>

				<div>
					<button type="submit" class="button-special">Add Merchant</button>
				</div>
			</form>

		<?php
	}

	public function DoAddMerchant()
	{# Add Merchant to the Database
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		if( $this->ValidateEmail( $this->localData["merchant"]["email_address"] ) )
		{// valid email
			if( $this->localData["merchant"]["email_address"]!=""
			&& $this->localData["merchant"]["password"]!=""
			&& $this->localData["merchant"]["company_name"]!="" )
			{// information is good add to the database
				parent::SetQuery("INSERT INTO table_merchants VALUES ('',
				'{$this->localData["merchant"]["email_address"]}',
				'{$this->localData["merchant"]["password"]}')");
				parent::SimpleQuery();
				parent::SetQuery("SELECT * FROM table_merchants WHERE email_address='{$this->localData["merchant"]["email_address"]}'");
				$merchantInfo = parent::DoQuery();
				parent::SetQuery("INSERT INTO table_merchantinfo VALUES ('{$merchantInfo[0]["merchant_id"]}',
				'{$this->localData["merchant"]["company_name"]}',
				'{$this->localData["merchant"]["street_address"]}',
				'{$this->localData["merchant"]["city"]}',
				'{$this->localData["merchant"]["state"]}',
				'{$this->localData["merchant"]["zipcode"]}',
				'{$this->localData["merchant"]["website_url"]}')");
				parent::SimpleQuery();
				
				setcookie("msg", "Merchant successfully added.", time()+300, "/");
				setcookie("msg_type", "success", time()+300, "/");
			}
			else
			{// missing required information
				setcookie("msg", "You are missing required information.", time()+300, "/");
				setcookie("msg_type", "error", time()+300, "/");
			}
		}
		else
		{// invalid email address
			setcookie("msg", "Invalid email address entered.", time()+300, "/");
			setcookie("msg_type", "error", time()+300, "/");
		}
		header("Location: control_panel?method=AddMerchants");
		}
	}

	public function DoDeleteMerchant()
	{# Delete Merchant
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		parent::SetQuery("DELETE FROM table_merchants WHERE merchant_id='{$this->localData["merchant_id"]}' LIMIT 1");
		parent::SimpleQuery();
		parent::SetQuery("DELETE FROM table_merchantinfo WHERE merchant_id='{$this->localData["merchant_id"]}' LIMIT 1");
		parent::SimpleQuery();
		setcookie("msg", "Merchant successfully deleted.", time()+300, "/");
		setcookie("msg_type", "success", time()+300, "/");
		header("Location: control_panel?method=ViewMerchants");
		}
	}

/**
*** END Merchants
***
**/

/**
*** START Locations
***
**/

	public function ViewLocations()
	{# View Locations
		global $message, $message_type;
		# Create Paging
		parent::SetQuery( "SELECT * FROM `table_locations`" );
		$totalLocations = parent::CountDBResults();
		$paging = $this->Pagination( array( "totalRows" => $totalLocations ) );
		parent::SetQuery( "SELECT * FROM table_locations ORDER BY location ASC LIMIT {$this->localData['s']},{$this->rowsPerPage}" );
		$locations = parent::CountDBResults();
		?>

			<h1>View Locations</h1>

	<?php if( $message ) { ?>
		<div class="message-<?=$message_type;?>"><?=stripslashes($message);?></div>
	<?php } ?>

		<?php
			if( $locations > 0 )
			{# More than 0 Offers
			?>
			<table cellspacing="0" cellpadding="4" border="0" style="width: 100%; border: 1px solid gray; border-collapse: collapse;">
			<tr style="background-color: #C9C9C9; color: #FFFFFF; font-weight: bold;">
				<th style="width: 15%; text-align: center;">Action</th>
				<th style="width: 65%;">Location</th>
				<th style="width: 20%; text-align: center;">(<b># of</b>) Subscribers</th>
			</tr>
			<?php
				$location_details = parent::DoQuery();
				foreach( $location_details as $location )
				{# Loop Through Each Offer
					parent::SetQuery("SELECT * FROM table_subscribers WHERE location_id='{$location["location_id"]}'");
					$subscriberCount = parent::CountDBResults();
				?>
					<tr onmouseover="this.style.backgroundColor='#F9F9F9';"
						onmouseout="this.style.backgroundColor='#FFFFFF';">
						<td style="text-align: center;">
							<button type="button" class="button-default" style="overflow: hidden;" onclick="ConfirmationMessage('Are you sure you want to delete this location: &quot;<?=$location['location'];?>&quot;?', '?method=DoDeleteLocation&b=1&location_id=<?=$location['location_id'];?>')"><img src="assets/gfx/icons/quantity-remove.gif" alt="" title="Remove Location" style="float: left;"/></button>
						</td>
						<td><?=$location['location'];?></td>
						<td style="text-align: center;"><?=number_format( $subscriberCount, 0, '.', ',' );?></td>
					</tr>
				<?php
				}
			?>
			</table>
			<?=$paging;?>
			<?php
			}
			else
			{# No offers, display a message
		?>
				There are currently no locations in the database.
		<?php
			}
		?>

		<div>
			<button type="button" onclick="window.location='control_panel?method=AddLocations';" class="button-default" style="overflow: auto; margin-top: 10px;"><img src="assets/gfx/icons/button-add-general.gif" alt="" title="" style="float: left;"/><span style="float: left; margin-left: 3px;">Add Locations</span></button>
		</div>

		<?php
	}

	public function AddLocations()
	{# Add Locations Form
		global $message, $message_type;
		?>

			<h1>Add Locations</h1>

	<?php if( $message ) { ?>
		<div class="message-<?=$message_type;?>"><?=stripslashes($message);?></div>
	<?php } ?>

			<form action="?method=AddLocations" method="post">
				<input type="hidden" name="method" value="DoAddLocation">
				<input type="hidden" name="b" value="1"/>
				<div>Location</div>
				<input type="text" class="text" name="location" value="" size="45"/>
				<div><button type="submit" class="button-special">Add</button></div>
			</form>

		<?php
	}

	public function DoAddLocation()
	{# Process Add Location Form
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		if( $this->localData['location']!='' )
		{
			parent::SetQuery("INSERT INTO table_locations VALUES ('','{$this->localData['location']}')");
			$outcome = parent::SimpleQuery();
			if( $outcome )
			{# Success
				setcookie("msg", "You have successfully added the Location: &quot;{$this->localData['location']}&quot;", time()+300, "/");
				setcookie("msg_type", "success", time()+300, "/");
			}
			else
			{# Error
				setcookie("msg", "Error adding a location to the database.", time()+300, "/");
				setcookie("msg_type", "error", time()+300, "/");
			}
		}
		else
		{
			setcookie("msg", "Please specify a location name.", time()+300, "/");
			setcookie("msg_type", "error", time()+300, "/");
		}
		header("Location: control_panel?method=AddLocations");
		}
	}

	private function DoDeleteLocation()
	{# Remove Location
		if( $this->LocalCheckAuth() )
		{// check if admin logged in
		parent::SetQuery("SELECT * FROM table_locations WHERE location_id='{$this->localData['location_id']}' LIMIT 1");
		$locationData = parent::DoQuery();
		// Remove Location from Location Table
		parent::SetQuery("DELETE FROM table_locations WHERE location_id='{$this->localData['location_id']}' LIMIT 1");
		parent::SimpleQuery();
		// Remove Location Information from Offers that used this location
		parent::SetQuery("DELETE FROM table_offerlocations WHERE location_id='{$this->localData['location_id']}'");
		parent::SimpleQuery();
		// Remove Subscribers to this Location
		parent::SetQuery("DELETE FROM table_subscribers WHERE location_id='{$this->localData['location_id']}'");
		parent::SimpleQuery();
		setcookie("msg", "You have successfully deleted the Location: &quot;{$locationData[0]['location']}&quot;", time()+300, "/");
		setcookie("msg_type", "success", time()+300, "/");
		header("Location: ?method=ViewLocations");
		}
	}

/**
*** END Locations
***
**/

/**
*** START Administration Login
***
**/

	private function DoAdminLogin()
	{# Administrator Login
		if( $this->localData[ 'admin' ][ 'username' ] == adminUser )
		{# Valid Administrator
			setcookie("admin[username]", adminUser, time()+86400, "/");
			if( $this->localData[ 'admin' ][ 'password' ] == adminPass )
			{# Valid Password
				$_SESSION[ 'admin' ] = $this->localData[ 'admin' ];
				$_SESSION[ 'logged-in' ] = 1;
				$ref = $_COOKIE[ "ref" ];
				if( !$ref )
				{# A Ref Redirect is NOT Set
					header("Location: control_panel");
				}
				else
				{# A Ref Redirect is Set
					setcookie("ref", "", time()-86400, "/");
					header("Location: http://" . urldecode( $ref ));
				}
			}
			else
			{# Invalid Password
				setcookie( "msg", "Invalid administrator password.", time()+300, "/" );
				setcookie( "msg_type", "error", time()+300, "/" );
				header("Location: admin");
			}
		}
		else
		{# Invalid Administrator
			setcookie( "msg", "Invalid administrator.", time()+300, "/" );
			setcookie( "msg_type", "error", time()+300, "/" );
			header("Location: admin");
		}
	}

	public function DoAdminLogout()
	{# Admin Logout
		//session_destroy();
		$_SESSION[ 'logged-in' ] = 0;
		unset( $_SESSION['admin'] );
		setcookie("msg", "You are now logged out.", time()+300, "/");
		setcookie("msg_type", "success", time()+300, "/");
		setcookie("temp", 1, time()+300, "/");
		header("Location: admin");
	}

	public function LocalCheckAuth()
	{# Protect Admin Functions
		if( isset( $_SESSION[ 'admin' ] ) )
		{// admin logged in
			return true;
		}
		else
		{// admin not logged in
			return false;
		}
	}

	public function CheckIsAuth( $loginPage )
	{# Check if Administrator is Logged In
		if( !$loginPage )
		{# Not on the Login Page
			if( ( $_SESSION[ 'logged-in' ] == 0 || !isset( $_SESSION[ 'logged-in' ] ) || !isset( $_SESSION['admin'] )  ) && !$temp )
			{# Admin Not Logged In, Redirect to Login
				setcookie( "msg", "You must login first.", time()+300, "/" );
				setcookie( "msg_type", "error", time()+300, "/" );
				setcookie( "ref", $_SERVER[ 'SERVER_NAME' ].$_SERVER[ 'REQUEST_URI' ], time()+86400, "/" );
				header("Location: admin");
			}
		}
		else
		{# On the Login Page
			if( $_SESSION[ 'logged-in' ] == 1 && $_SESSION['admin'] )
			{# Admin Logged In
				header("Location: control_panel");
			}
		}
	}

/**
***
*** Front-End Page Functions
***
**/

	public function GetPageContent( $page_id )
	{# Get Page Content from CMS Table
		parent::SetQuery("SELECT * FROM `table_cms` WHERE page_id='{$page_id}'");
		$pageContent = parent::DoQuery();
		return $pageContent;
	}

	public function GetCharities()
	{# Page for Charities
	parent::SetQuery("SELECT * FROM `table_charities`");
	$total_charities = parent::CountDBResults();
		if( $total_charities > 0 )
		{// charities greater than 0
		?>
			<table cellspacing="0" cellpadding="5" border="0" style="width: 100%;">
			<tr style="background-color: #C7C7C7; color: #FFFFFF; font-weight: bold;">
				<td style="width: 70%; text-align:">Charity</td>
				<td style="width: 15%; text-align: center;">Amount</td>
				<td style="width: 15%; text-align: center;"></td>
			</tr>
		<?php
			$charities = parent::DoQuery();
			foreach( $charities as $charity )
			{// loop through each charity
			?>
			<tr>
				<td style="vertical-align: top;">
					<b><?=$charity["charity_name"];?></b><br/>
					<?=$charity["charity_description"];?>
				</td>
				<td style="vertical-align: top; text-align: center;">
					<form name="donate" action="cart?method=AddToCart&b=1" method="post">
					<input type="hidden" name="offer_code" value="<?=$charity["charity_id"];?>"/>
					<input type="hidden" name="donation[charity_name]" value="<?=$charity["charity_name"];?>"/>
					<input type="hidden" name="donation[charity_description]" value="<?=$charity["charity_description"];?>"/>
					$ <input type="text" class="text" name="donation[amt]" value="" size="6" maxlength="10"/>
				</td>
				<td style="vertical-align: top; text-align: center;">
					<button type="submit" name="donation[btn]" class="button-special">Donate</button>
					</form>
				</td>
			</tr>
			<?php
			}
		?>
			</table>
		<?php
		}
	}

/**
***
*** Offer Page Functions
***
**/

	public function GetRecentDeals()
	{// Recent Deals Page
		$this->CheckMyLocation();
		parent::SetQuery("SELECT * FROM `table_locations` WHERE location_id='{$this->localData["location_id"]}'");
		$userLocation = parent::DoQuery();
		?>
		<h2>Recent Deals for <?=$userLocation[0]["location"];?></h2>
		<?php
		parent::SetQuery("SELECT * FROM `table_offers`,`table_offerlocations`
		WHERE `table_offers`.`offer_id`=`table_offerlocations`.`offer_id`
		AND `table_offerlocations`.`location_id`='{$this->localData["location_id"]}'");
		$exists = parent::CountDBResults();
		if( $exists )
		{// recent deals for user's current location exist
			?>
			<div style="overflow: hidden;">
			<?php
			$recentDeals = parent::DoQuery();
			foreach( $recentDeals as $deal )
			{// loop through each recent deal
				parent::SetQuery("SELECT * FROM `table_purchased` WHERE offer_id='{$deal["offer_id"]}'");
				$participants = parent::CountDBResults();
			?>
				<div style="float: left; border: 2px dashed #E8E8E8; padding: 10px; margin-bottom: 15px;">
				<b><?=$deal["one_liner"];?></b><br/>
				<img src="<?=$deal["graphic"];?>" border="0" alt="" title="<?=$deal["one_liner"];?>" style="margin-top: 5px;"/>

					<div style="overflow: hidden; margin-top: 5px;">
						<div style="float: left;">Participants: <b><?=$participants;?></b></div>
						<div style="float: right;">
							<?php
							if( $deal["expiration"] > time() )
							{// deal not expired
							?>
								Expires: <?=date("F jS, Y", $deal["expiration"]) . " at " . date(" g:i a", $deal["expiration"]);?>
							<?php
							}
							else
							{// deal expired
							?>
								<i>Past Expiration</i>
							<?php
							}
							?>
						</div>
					</div>
					<div style="overflow: hidden; margin-top: 5px;">
						<div style="float: left;">Price: <b>$<?=$deal["price"];?></b> (<?=$deal["discount"];?>% off)</div>
						<div style="float: right;">
							<a href="offer-details?offer_id=<?=$deal["offer_id"];?>">More Information &amp; Comments</a>
						</div>
					</div>
				</div>
			<?php
			}
			?>
			</div>
			<?php
		}
		else
		{// there are currently no recent deals for the user's current location
		?>
				<center>There are currently no recent deals found for your location.</center>
		<?php
		}
	}

	public function CheckForParameters()
	{// no parameters set, redirect to main offers page
		if( !$this->localData["offer_id"] )
		{// redirect to main offers page
			header("Location: offers");
		}
	}

	public function GetLocationsAvailableForOffer()
	{// get offer locations
		parent::SetQuery("SELECT * FROM `table_offerlocations`,`table_locations` WHERE 
		`table_offerlocations`.`location_id`=`table_locations`.`location_id`
		AND
		`table_offerlocations`.`offer_id`='{$this->localData["offer_id"]}'");
		$exists = parent::CountDBResults();
		if( $exists )
		{// locations exist for this location
			$locations = parent::DoQuery();
			return $locations;
		}
		else
		{// no locations exist for this location
			return false;
		}
	}

	public function GetDetailedOffer()
	{# Get Detailed Offer
		parent::SetQuery("SELECT * FROM `table_offers` WHERE offer_id='{$this->localData["offer_id"]}'");
		$exists = parent::CountDBResults();
		if( $exists )
		{// offer exists
			$data = parent::DoQuery();
			return $data;
		}
		else
		{// offer not available
			return false;
		}
	}

	public function GetOffers()
	{# Get Offers
		$extendedQuery = array();
		if( isset( $this->localData["location_id"] ) )
		{
			$extendedQuery[] = " AND location_id='{$this->localData["location_id"]}'";
		}
		# Create Paging
		parent::SetQuery( "SELECT * FROM table_offers, table_offerlocations WHERE 
		table_offerlocations.offer_id=table_offers.offer_id " . join(" AND ", $extendedQuery ) );
		//parent::PrintQuery();
		$totalOffers = parent::CountDBResults();
		$this->rowsPerPage = 1;
		$paging = $this->Pagination( array( "totalRows" => $totalOffers ) );
		parent::SetQuery( "SELECT * FROM table_offers, table_offerlocations 
		WHERE table_offerlocations.offer_id=table_offers.offer_id " . join(" AND ", $extendedQuery )
		. " ORDER BY expiration DESC LIMIT {$this->localData['s']},{$this->rowsPerPage}" );
		$offerData = parent::DoQuery();
		$offerData["paging"] = $paging;
		return $offerData;
	}

	public function GetCompanyInfo( $merchant_id )
	{# Company Info
		parent::SetQuery("SELECT * FROM `table_merchantinfo` WHERE merchant_id='{$merchant_id}' LIMIT 1");
		$companyInfo = parent::DoQuery();
		return $companyInfo[0];
	}

	public function GetLocations()
	{# Create Location Drop-Down
		$this->CheckMyLocation();
		parent::SetQuery("SELECT * FROM `table_locations` ORDER BY location ASC");
		$locations = parent::DoQuery();
		$locationLinks = array();

		parent::SetQuery("SELECT * FROM `table_locations` WHERE location_id='{$this->localData["location_id"]}'");
		$cityInfo = parent::DoQuery();
		$first_city = $cityInfo[0]["location"];

		foreach( $locations as $location )
		{// location dropdowns
			$link_div = new HtmlElement('div');
			$link_div->Set('style', 'padding: 8px; padding-left: 20px;');
			$link_div->Set('onmouseover', 'this.style.backgroundColor=\'#794933\';');
			$link_div->Set('onmouseout', 'this.style.backgroundColor=\'transparent\';');
			$link = new HtmlElement('a');
			$link->Set('href', $this->localData["current_page"] . '?location_id=' . $location["location_id"] );
			$link->Set('class', 'city-dropdown-link');
			$link->Set('text', $location["location"]);
			$link_div->Inject( $link );
			if( $location["location"] != $first_city )
			{// don't display the currently selected city
				$locationLinks[] = $link_div->ReturnData();
			}
		}

		$this->JsonOutput( array( "first_city" => $first_city, "cities" => join( "", $locationLinks ) ) );
	}

/**
***
*** User Functions
***
**/

	public function IsUserLoggedIn()
	{# Check if User is Logged In
		if( $_SESSION["logged-in"] == 1 && isset($_SESSION["user-data"]) )
		{// user is logged in return user information
			$this->JsonOutput( $_SESSION["user-data"] );
		}
		else
		{// user is not logged in
			$this->JsonOutput( array( "offline" => 1 ) );
		}
	}

	public function CheckUserAuth()
	{# Check if User Authenticated
		if( !$_SESSION["logged-in"] || !isset($_SESSION["user-data"]) )
		{// not logged in
			setcookie("msg", "You must login first.", time()+300, "/");
			setcookie("msg_type", "error", time()+300, "/");
			header("Location: login");
		}
	}

	public function CheckUserAuthLocal()
	{# Check if User is Authenticated, Do Not Redirect
		if( $_SESSION["logged-in"] && isset( $_SESSION["user-data"] ) )
		{// user logged in
			return true;
		}
		else
		{// user not logged in
			return false;
		}
	}

	public function AccountRegistration()
	{# Account Registration Form
		if( !isset( $_SESSION["user-data"] ) || !isset( $_SESSION["logged-in"] ) ) { unset( $_SESSION["registration-step"] ); }
		switch( $_SESSION["registration-step"] )
		{// select a registration step
			case 2 : $this->SurveyForm(); break;
			default : $this->RegistrationForm(); break;
		}
	}

	public function SubmitSurvey()
	{# Submit the Survey
		if( isset( $this->localData["user"]["skip-btn"] ) )
		{// skip survey
			unset( $_SESSION["registration-step"] );
			setcookie("msg", "You have skipped the survey.", time()+300, "/");
			setcookie("msg_type", "success", time()+300, "/");
			header("Location: account");
		}
		if( isset( $this->localData["user"]["survey-btn"] ) )
		{// submit survey
			unset( $_SESSION["registration-step"] );
			parent::SetQuery("SELECT * FROM `table_questionnaire`
			WHERE user_id='{$_SESSION["user-data"]["user_id"]}'");
			$exists = parent::CountDBResults();
			if( $exists )
			{// survey exists, update it
				$interests = array();
				$interests[] = "<interests>";
				foreach( $this->localData["user"]["deals"] as $interest_num => $value )
				{// loop through user interested deals
					$interests[] = "<{$interest_num}>{$value}</{$interest_num}>";
				}
				$interests[] = "</interests>";
				$interests = join( "\n", $interests );
				// Update Questionnaire
				parent::SetQuery("UPDATE `table_questionnaire` SET
				dob='" . strtotime( $this->localData["user"]["dob_month"] . " " . $this->localData["user"]["dob_day"] . " " . $this->localData["user"]["dob_year"] ) . "',
				income_level='{$this->localData["user"]["income"]}',
				location_city='{$this->localData["user"]["city"]}',
				location_state='{$this->localData["user"]["state"]}',
				education='{$this->localData["user"]["education"]}',
				gender='{$this->localData["user"]["gender"]}',
				interests='{$interests}' WHERE user_id='{$_SESSION["user-data"]["user_id"]}'");
				parent::SimpleQuery();
				setcookie("msg", "Thank you for completing the survey!", time()+300, "/");
				setcookie("msg_type", "success", time()+300, "/");
				header("Location: account");
			}
		}
	}

	public function SurveyForm()
	{# Survey Form
	?>
		<h1>Account Survey</h1>

		<form name="user[survey]" action="?method=SubmitSurvey&b=1" method="post">

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
			<?php
			$MyLocation = $this->GetMyLocation();
			?>
				<input type="text" name="user[city]" size="30" value="<?=$MyLocation["city"];?>" class="text"/>
			</div>
			<div class="select-styling-div" style="float: left; margin-top: 5px; margin-left: 5px;">
				<?php
				parent::SetQuery("SELECT DISTINCT(region) FROM `table_ip2location` WHERE country='US' ORDER BY region ASC");
				$states = parent::DoQuery();
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
	<?php
	}

	public function GetCustomMessage( $message_name )
	{# Get Custom Message
		parent::SetQuery("SELECT * FROM `table_messages` WHERE message_name='{$message_name}'");
		$message = parent::DoQuery();
		return $message[0]["message"];
	}

	public function DoRegister()
	{// register an account
		if( $this->ValidateEmail( $this->localData["user"]["email_address"] ) )
		{// valid email
			if( $this->localData["user"]["firstname"]!=""
			&& $this->localData["user"]["lastname"]!="")
			{// valid first and last anem
				if( $this->localData["user"]["password"] == $this->localData["user"]["password2"] )
				{// passwords match
					if( strlen( $this->localData["user"]["password"] ) >=5 )
					{// password length is okay
						parent::SetQuery("SELECT * FROM `table_users` WHERE email_address='{$this->localData["user"]["email_address"]}'");
						$exists = parent::CountDBResults();
						if( !$exists )
						{// email address/account doesn't exist
							// create user account
							parent::SetQuery("INSERT INTO `table_users` VALUES ('','{$this->localData["user"]["email_address"]}','{$this->localData["user"]["password"]}')");
							parent::SimpleQuery();
							parent::SetQuery("SELECT * FROM `table_users` WHERE email_address='{$this->localData["user"]["email_address"]}'");
							$user = parent::DoQuery();
							// create user profile
							parent::SetQuery("INSERT INTO `table_userinfo` VALUES ('{$user[0]["user_id"]}',
							'{$this->localData["user"]["firstname"]}',
							'{$this->localData["user"]["lastname"]}',
							'',
							'',
							'',
							'',
							'')");
							parent::SimpleQuery();
							// create account balance
							parent::SetQuery("INSERT INTO `table_accountbalance` VALUES ('{$user[0]["user_id"]}','10.00')");
							parent::SimpleQuery();
							// create survey
							parent::SetQuery("INSERT INTO `table_questionnaire` VALUES ('{$user[0]["user_id"]}',
							'',
							'',
							'',
							'',
							'',
							'',
							'')");
							parent::SimpleQuery();
							$this->SendEmail( $user[0]["email_address"], 'Welcome to FMM', $this->GetCustomMessage('welcome_email') );
							// log the user in when completed with registration
							parent::SetQuery("SELECT * FROM `table_users`,`table_userinfo`
							WHERE `table_users`.`user_id`=`table_userinfo`.`user_id` 
							AND `table_users`.`user_id`='{$user[0]["user_id"]}'");
							$userData = parent::DoQuery();
							$_SESSION["user-data"] = $userData[0];
							$_SESSION["logged-in"] = 1;
							$_SESSION["user"]["type"] = "customer";
							$_SESSION["registration-step"] = 2;
							unset( $_SESSION["form-data"] );
							header("Location: register");
						}
						else
						{// account already exists
							setcookie("msg", "There is already an account associated with this email address.", time()+300, "/");
							setcookie("msg_type", "error", time()+300, "/");
							header("Location: register");
						}
					}
					else
					{// password too short
						setcookie("msg", "Your password is too short.", time()+300, "/");
						setcookie("msg_type", "error", time()+300, "/");
						header("Location: register");
					}
				}
				else
				{// passwords don't match
					setcookie("msg", "Your passwords do not match.", time()+300, "/");
					setcookie("msg_type", "error", time()+300, "/");
					header("Location: register");
				}
			}
			else
			{// missing first or last name
				setcookie("msg", "You must enter a first and last name.", time()+300, "/");
				setcookie("msg_type", "error", time()+300, "/");
				header("Location: register");
			}
		}
		else
		{// invalid email address
			setcookie("msg", "You entered an invalid email address.", time()+300, "/");
			setcookie("msg_type", "error", time()+300, "/");
			header("Location: register");
		}
	}

	public function SendEmail( $to_email, $subject, $my_message )
	{# Send an Email
		// create a boundary string. It must be unique
		// so we use the MD5 algorithm to generate a random hash
		$random_hash = md5(date('r', time()));
		// define the headers we want passed. Note that they are separated with \r\n
		$headers = "From: " . adminEmail . "\r\nReply-To: " . adminEmail;
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

	public function RegistrationForm()
	{# Registration Form
		global $message, $message_type;
	?>
		<h1>Member Registration</h1>

		<?php if($message) { ?>
			<div class="message-<?=$message_type;?>"><?=$message;?></div>
		<?php } ?>

		<form style="margin-top: 20px; margin-left: 40px;" name="user[account]" action="?method=DoRegister&b=1" method="post">
		<input type="hidden" name="form-data" value="1"/>
		<div><b>First Name</b></div>
		<input type="text" name="user[firstname]" class="text" size="25" value="<?=$_SESSION["form-data"]["user"]["firstname"];?>"/>

		<div style="margin-top: 7px;"><b>Last Name</b></div>
		<input type="text" name="user[lastname]" class="text" size="25" value="<?=$_SESSION["form-data"]["user"]["lastname"];?>"/>

		<div style="margin-top: 7px;"><b>Email Address</b></div>
		<input type="text" name="user[email_address]" class="text" size="35" value="<?=$_SESSION["form-data"]["user"]["email_address"];?>"/>

		<div style="margin-top: 7px;"><b>Password</b></div>
		<input type="password" name="user[password]" class="text" size="25"/>

		<div style="margin-top: 7px;"><b>Repeat Password</b></div>
		<input type="password" name="user[password2]" class="text" size="25"/>

		<div style="margin-top: 10px;">
			<button type="submit" name="user[register-btn]" class="button-special">Create Account</button>
		</div>
		</form>
	<?php
	}

	public function DiscussionForm( $offer )
	{# Discussion Form
		global $message, $message_type;
		if( $this->CheckUserAuthLocal() )
		{// check if user is logged in
	?>
		<div style="margin-top: 15px;">
		<?php if($message) { ?>
			<div class="message-<?=$message_type;?>"><?=$message;?></div>
		<?php } ?>
		<form method="post" action="?method=PostDiscussionMessage&b=1&offer_id=<?=$offer;?>&location_id=<?=$this->localData["location_id"];?>">
			<div style="margin-bottom: 5px;">You are posting as <b><?=$_SESSION["user-data"]["firstname"];?> <?=strtoupper(substr($_SESSION["user-data"]["lastname"], 0, 1));?></b> (<a href="javascript:void(0);"><?=$_SESSION["user-data"]["email_address"];?></a>) (<a href="?method=DoUserLogout&b=1">This Isn't Me?</a>)</div>
			<textarea name="discussion[message]" rows="4" cols="65" class="text"></textarea>
			<div style="margin-top: 5px;"><button type="submit" class="button-special">Post Message</button></div>
		</form>
		</div>
	<?php
		}
		else
		{// user not logged in
		?>
		<div style="margin-top: 15px;"><a href="login" title="Account Login">Login</a> to post a message.</div>
		<?php
		}
	}

	public function PostDiscussionMessage()
	{# Post Discussion Message
		if( $this->CheckUserAuthLocal() )
		{// user is logged in
			$message = $this->localData["discussion"]["message"];
			if( strlen( $message ) > 0 )
			{// not too short
				parent::SetQuery("INSERT INTO table_discussions VALUES('','{$this->localData["offer_id"]}','" . time() . "','" . htmlentities( $message, ENT_QUOTES ) . "','{$_SESSION["user-data"]["user_id"]}','')");
				parent::SimpleQuery();
				setcookie("msg", "Your message has been submitted and is awaiting approval, thank you.", time()+300, "/");
				setcookie("msg_type", "success", time()+300, "/");
			}
			else
			{// too short
				setcookie("msg", "Your message cannot be blank.", time()+300, "/");
				setcookie("msg_type", "error", time()+300, "/");
			}
			header("Location: offer-details?offer_id={$this->localData["offer_id"]}");
		}
	}

	public function Discussions( $offer )
	{# Display Discussion Messages for an Offer
		parent::SetQuery("SELECT * FROM table_discussions WHERE offer_id='{$offer}' AND status='1' ORDER BY timestamp DESC");
		$messages = parent::CountDBResults();
		?>
		<h1>Discussion</h1>
		<?php
		if( $messages > 0 )
		{// more than zero messages
			$message_data = parent::DoQuery();
			foreach( $message_data as $message )
			{// loop through messages
				parent::SetQuery("SELECT * FROM table_users, table_userinfo WHERE
				table_users.user_id=table_userinfo.user_id AND table_users.user_id='{$message["user_id"]}'");
				$userData = parent::DoQuery();
			?>
				<div style="margin-bottom: 8px; padding-bottom: 8px; border-bottom: 2px dashed #E8E8E8;">on <i><?=date("F d, Y", $message["timestamp"]);?></i> at <i><?=date("h:i a", $message["timestamp"]);?></i> <b><?=$userData[0]["firstname"];?> <?=strtoupper(substr($userData[0]["lastname"], 0, 1));?></b> said ..<br/>
					<p style="letter-spacing: .05em; line-height: 16px; font-size: 13px;">
					<b>&quot;</b>
					<?=html_entity_decode( $message["comment"], ENT_QUOTES );?>
					<b>&quot;</b>
					</p>
				</div>
			<?php
			}
		}
		else
		{// zero messages
			?>
				There are currently no messages regarding this offer.
			<?php
		}
	}

	public function LoginForm()
	{# Login Form
	?>
		<table cellspacing="0" cellpadding="10" border="0" align="center">
		<tr>
		<td style="vertical-align: top;">
		<h1>Login</h1>
		<form name="account-login" action="?method=DoUserLogin&b=1&type=login" method="post">
			<input type="hidden" name="form-data" value="1"/>
			<div>Email Address</div>
			<input type="text" name="user[email_address]" class="text" size="25" value="<?php if( $_SESSION["form-data"]["type"]=="login" ) { echo $_SESSION["form-data"]["user"]["email_address"]; } ?>"/>
			<div style="margin-top: 7px;">Password</div>
			<input type="password" name="user[password]" class="text"/><br/>
			<div style="margin-top: 7px; margin-bottom: 5px;">Account Type</div>
			<div class="select-styling-div" style="width: 100px; margin-bottom: 7px;">
				<select name="user[type]" style="width: 100px;">
					<option value="customer" <?php if( !$_SESSION["form-data"]["user"]["type"] || $_SESSION["form-data"]["user"]["type"]=="customer" ) { ?>selected="selected"<?php } ?>>Customer</option>
					<option value="merchant" <?php if( $_SESSION["form-data"]["user"]["type"]=="merchant" ) { ?>selected="selected"<?php } ?>>Merchant</option>
				</select>
			</div>
			<input type="submit" value="Continue" class="button-default"/>
		</form>
		</td>
		<td><b>OR</b></td>
		<td style="vertical-align: top;">
		<h1>Forgot Password</h1>
		<form name="forgot-pass" action="?method=ForgotPassword&b=1" method="post">
			<input type="hidden" name="form-data" value="1"/>
			<input type="hidden" name="forgot-pass" value="1"/>
			<div>Email Address</div>
			<input type="text" name="user[email_address]" class="text" size="25" value="<?php if( $_SESSION["form-data"]["forgot-pass"] == 1 ) { echo $_SESSION["form-data"]["user"]["email_address"]; } ?>"/><br/>
			<div style="margin-top: 7px; margin-bottom: 5px;">Account Type</div>
			<div class="select-styling-div" style="width: 100px; margin-bottom: 7px;">
				<select name="user[type]" style="width: 100px;">
					<option value="customer" <?php if( !$_SESSION["form-data"]["user"]["type"] || $_SESSION["form-data"]["user"]["type"]=="customer" ) { ?>selected="selected"<?php } ?>>Customer</option>
					<option value="merchant" <?php if( $_SESSION["form-data"]["user"]["type"]=="merchant" ) { ?>selected="selected"<?php } ?>>Merchant</option>
				</select>
			</div>
			<input type="submit" value="Send My Password" class="button-default"/>
		</form>
		</td>
		</tr>
		</table>
	<?php
	}

	public function DoUserLogin()
	{# Process Login
		switch( $this->localData["user"]["type"] )
		{
			case "customer" : $query = "FROM table_users, table_userinfo WHERE table_users.user_id=table_userinfo.user_id"; break;
			case "merchant" : $query = "FROM table_merchants, table_merchantinfo WHERE table_merchants.merchant_id=table_merchantinfo.merchant_id"; break;
		}
		parent::SetQuery("SELECT * {$query} AND email_address='{$this->localData["user"]["email_address"]}'");
		$exists = parent::CountDBResults();
		if( $exists )
		{
			$data = parent::DoQuery();
			if( $data[0]["email_address"] == $this->localData["user"]["email_address"] )
			{
				if( $data[0]["password"] == $this->localData["user"]["password"] )
				{// valid password
					unset( $_SESSION['oauth_request_token'] );
					$_SESSION["checkout-step"] = 2;
					$_SESSION["user-data"] = $data[0];
					$_SESSION["logged-in"] = 1;
					$_SESSION["user"]["type"] = $this->localData["user"]["type"];
					header("Location: offers");
				}
				else
				{// invalid password
					setcookie("msg", "Invalid " . ucwords($this->localData["user"]["type"]) . " password.", time()+300, "/");
					setcookie("msg_type", "error", time()+300, "/");
					header("Location: login");
				}
			}
			else
			{// invalid email address
				setcookie("msg", "Invalid " . ucwords($this->localData["user"]["type"]) . " email address.", time()+300, "/");
				setcookie("msg_type", "error", time()+300, "/");
				header("Location: login");
			}
		}
		else
		{// user doesn't exist
			setcookie("msg", "Invalid " . ucwords($this->localData["user"]["type"]) . " email address.", time()+300, "/");
			setcookie("msg_type", "error", time()+300, "/");
			header("Location: login");
		}
	}

	public function DoUserLogout()
	{# Logout
		unset( $_SESSION["user-data"] );
		unset( $_SESSION["logged-in"] );
		unset( $_SESSION["user"]["type"] );
		unset( $_SESSION["registration-step"] );
		unset( $_SESSION['oauth_request_token'] );
		setcookie("msg", "You are now logged out.", time()+300, "/");
		setcookie("msg_type", "success", time()+300, "/");
		header("Location: login");
	}

	public function DoAlertSignup()
	{# Signup for Location Alerts
		$response = array();
		$validEmail = $this->ValidateEmail( $this->localData["email_address"] );
		if( $validEmail )
		{// valid email
			parent::SetQuery("SELECT * FROM table_subscribers WHERE email_address='{$this->localData["email_address"]}' AND location_id='{$this->localData["location_id"]}'");
			$exists = parent::CountDBResults();
			if( !$exists )
			{// not yet signed up
				parent::SetQuery("INSERT INTO table_subscribers VALUES('','{$this->localData["email_address"]}','{$this->localData["location_id"]}')");
				parent::SimpleQuery();
				$response[ "message" ] = "You are now signed up to receive Offer alerts for this Location.";
			}
			else
			{// already signed up for this location
				$response[ "message" ] = "You are already signed up to receive Offer alerts for this Location.";
			}
		}
		else
		{// invalid email
			$response[ "message" ] = "Oops, You entered an invalid email address.";
		}
		$this->JsonOutput( $response );
	}

	public function MyAccount()
	{# My Account Screen
		switch( $this->localData["page"] )
		{
			case 'edit_profile' : $this->EditProfile(); break;
			case 'edit_alerts' : $this->EditAlerts(); break;
			case 'view_offers' : $this->MerchantViewOffers(); break;
			case 'view_participants' : $this->MerchantViewOfferParticipants(); break;
			default: $this->AccountMaintenance(); break;
		}
	}

	public function GetShareMessage()
	{# Get the Share with 5 Friends for Credit, Message
		parent::SetQuery("SELECT * FROM `table_messages` WHERE message_name='share_deal_email'");
		$messageData = parent::DoQuery();
		return $messageData[0]["message"];
	}

	public function AccountMaintenance()
	{# Account Maintenance
		global $message, $message_type;
		if( $_SESSION["user"]["type"] == "customer" )
		{
			global $twitter_oauth_link;
			// get user account balance
			parent::SetQuery("SELECT * FROM table_accountbalance WHERE user_id='{$_SESSION["user-data"]["user_id"]}' LIMIT 1");
			$accountBalance = parent::DoQuery();
			// check if user received $10 credit for sending a message on Twitter
			parent::SetQuery("SELECT * FROM `table_credits` WHERE user_id_credited='{$_SESSION["user-data"]["user_id"]}'");
			$creditReceived = parent::CountDBResults();
			// get user commissions
			parent::SetQuery("SELECT SUM(amount) as amt FROM `table_commissions` WHERE paid_to_user_id='{$_SESSION["user-data"]["user_id"]}'");
			$commission_earned = parent::DoQuery();
			parent::SetQuery("SELECT SUM(amount) as amt FROM `table_commissions` WHERE paid_to_user_id='{$_SESSION["user-data"]["user_id"]}' AND status>='1'");
			$commission_paid = parent::DoQuery();
			parent::SetQuery("SELECT SUM(amount) as amt FROM `table_commissions` WHERE paid_to_user_id='{$_SESSION["user-data"]["user_id"]}' AND status='0'");
			$commission_unpaid = parent::DoQuery();
			?>

				<h3>Customer Account Options</h3>
				<table cellspacing="0" cellpadding="4" border="1" style="border: 2px solid #E8E8E8; border-collapse: collapse;">
				<tr>
					<td style="text-align: right;">Your unique Referral link :</td>
					<td><a target="_blank" href="http://<?=$_SERVER["SERVER_NAME"];?>/offers?ref_id=<?=$_SESSION["user-data"]["user_id"];?>">http://<?=$_SERVER["SERVER_NAME"];?>/offers?ref_id=<?=$_SESSION["user-data"]["user_id"];?></a></td>
				</tr>
				<tr>
					<td style="text-align: right;">Your unique Affiliate link :</td>
					<td><a target="_blank" href="http://<?=$_SERVER["SERVER_NAME"];?>/offers?aff_id=<?=$_SESSION["user-data"]["user_id"];?>">http://<?=$_SERVER["SERVER_NAME"];?>/offers?aff_id=<?=$_SESSION["user-data"]["user_id"];?></a></td>
				</tr>
				<tr>
					<td style="text-align: right;">Your Affiliate RSS Feed link :</td>
					<td><a target="_blank" href="http://<?=$_SERVER["SERVER_NAME"];?>/rss?feed_type=most_recent&amp;aff_id=<?=$_SESSION["user-data"]["user_id"];?>">http://<?=$_SERVER["SERVER_NAME"];?>/rss?feed_type=most_recent&amp;aff_id=<?=$_SESSION["user-data"]["user_id"];?></a></td>
				</tr>
				<tr>
					<td style="text-align: right;">Your Account Balance :</td>
					<td>$<?=$accountBalance[0]["balance"];?></td>
				</tr>
				<tr>
					<td style="text-align: right;">Affiliate Commissions Earned :</td>
					<td>$<?=number_format($commission_earned[0]["amt"], 2, '.', '');?></td>
				</tr>
				<tr>
					<td style="text-align: right;">Affiliate Commissions (<i>Paid</i>) :</td>
					<td>$<?=number_format($commission_paid[0]["amt"], 2, '.', '');?></td>
				</tr>
				<tr>
					<td style="text-align: right;">Affiliate Commissions (<i>Not Paid</i>) :</td>
					<td>$<?=number_format($commission_unpaid[0]["amt"], 2, '.', '');?></td>
				</tr>
				</table>

				<?php if( !$creditReceived ) { ?>

				<div style="margin-top: 15px; border: 2px dotted #E8E8E8; padding: 15px;">
					<b>Share This Site and get a $10 Credit to your Account Balance!</b> (<i>Can only be claimed once</i>)<br/><br/>
					
					<div style="overflow: hidden;">
					<div style="float: left;">
						<a href="<?=$twitter_oauth_link;?>"><img src="assets/gfx/twitter-connect.gif" title="Sign in With Twitter to Share FMM" border="0" alt=""/></a>
					</div>
					<div style="margin-top: 5px; float: left; font-weight: bold; margin-left: 5px; margin-right: 5px;">
						-OR-
					</div>
					<div style="float: left; margin-top: 5px;">
						<a href="#" onclick="DoSharePopup();">Share with 5 Friends</a>
					</div>
					</div>
				</div>
				<?php } ?>

				<div style="overflow: hidden; margin-top: 15px;">
					<p style="float: left; margin-right: 10px;"><input type="button" onclick="location='?page=edit_profile';" class="button-default" value="Edit Profile"/></p>
					<p style="float: left; margin-right: 10px;"><input type="button" onclick="location='?page=edit_alerts';" class="button-default" value="Edit Alerts"/></p>
					<p style="float: left;"><input type="button" onclick="location='login?method=DoUserLogout&b=1';" class="button-default" value="Logout"/></p>
				</div>
			<?php
		}
		else if( $_SESSION["user"]["type"] == "merchant" )
		{// merchant logged in
		?>
				<h3>Merchant Account Options</h3>
				<div style="overflow: hidden;">
					<p style="float: left; margin-right: 10px;"><input type="button" onclick="location='?page=edit_profile';" class="button-default" value="Edit Profile"/></p>
					<p style="float: left; margin-right: 10px;"><input type="button" onclick="location='?page=view_offers';" class="button-default" value="View Past Offers"/></p>
					<p style="float: left;"><input type="button" onclick="location='login?method=DoUserLogout&b=1';" class="button-default" value="Logout"/></p>
				</div>
		<?php
		}
	}

	public function MerchantViewOffers()
	{# View Offers
		# Create Paging
		parent::SetQuery( "SELECT * FROM `table_offers` WHERE company='{$_SESSION["user-data"]["merchant_id"]}'" );
		$totalOffers = parent::CountDBResults();
		$paging = $this->Pagination( array( "totalRows" => $totalOffers ) );
		parent::SetQuery( "SELECT * FROM table_offers WHERE company='{$_SESSION["user-data"]["merchant_id"]}' ORDER BY expiration DESC LIMIT {$this->localData['s']},{$this->rowsPerPage}" );
		$offers = parent::CountDBResults();
		global $message, $message_type;
		?>

			<h1>View Offers</h1>

	<?php if( $message ) { ?>
		<div class="message-<?=$message_type;?>"><?=stripslashes($message);?></div>
	<?php } ?>

		<?php
			if( $offers > 0 )
			{# More than 0 Offers
			?>
			<table cellspacing="0" cellpadding="4" border="0" style="width: 100%; border: 1px solid gray; border-collapse: collapse;">
			<tr style="background-color: #C9C9C9; color: #FFFFFF; font-weight: bold;">
				<th style="text-align: center; width: 15%;">Action</th>
				<th style="width: 25%;">Offer Name (Code)</th>
				<th style="width: 30%;">Expiration</th>
				<th style="text-align: center; width: 20%;">Status</th>
				<th style="width: 10%;">Price</th>
			</tr>
			<?php
				$offer_details = parent::DoQuery();
				foreach( $offer_details as $offer )
				{# Loop Through Each Offer
				?>
					<tr onmouseover="this.style.backgroundColor='#F9F9F9';"
						onmouseout="this.style.backgroundColor='#FFFFFF';">
						<td style="text-align: center;">
							<a href="?page=view_participants&offer_code=<?=$offer['offer_code'];?>"><img src="assets/gfx/icons/table.png" alt="" title="View Offer Participants" border="0"/></a>
						</td>
						<td><b><?=stripslashes($offer['name']);?></b> (<?=$offer['offer_code'];?>)</td>
						<td><?=date("M-d-Y h:i a", $offer['expiration']);?></td>
						<td style="text-align: center;"><?php if( $offer['expiration'] > time() ) { ?>Active<?php } else { ?><i>Expired</i><?php } ?></td>
						<td>$<?=$offer['price'];?></td>
					</tr>
				<?php
				}
			?>
			</table>
			<?=$paging;?>
			<?php
			}
			else
			{# No offers, display a message
		?>
				You do not currently have a history of Offers.
		<?php
			}
	}# End View Offers

	public function MerchantViewOfferParticipants()
	{# View Merchant Offer Participants
		parent::SetQuery("SELECT * FROM table_offers WHERE offer_code='{$this->localData['offer_code']}' AND company='{$_SESSION["user-data"]["merchant_id"]}' LIMIT 1");
		$offerData = parent::DoQuery();
		$timer = $this->GetOfferDetails( $offerData[0]["offer_code"] );
		# Create Paging
		parent::SetQuery( "SELECT * FROM table_purchased WHERE offer_id='{$offerData[0]['offer_id']}'" );
		$totalParticipants = parent::CountDBResults();
		$paging = $this->Pagination( array( "totalRows" => $totalParticipants ) );
		parent::SetQuery("SELECT * FROM table_purchased WHERE offer_id='{$offerData[0]['offer_id']}' LIMIT {$this->localData['s']},{$this->rowsPerPage}");
		$participantCount = parent::CountDBResults();
	?>

	<!--Timer-->
	<script type="text/javascript">
	<!--
		var timeLeft = <?=$timer["timeLeft"]?>;
		var timer;
	//-->
	</script>
	<script type="text/javascript" src="assets/js/Timer.js"></script>
	<script type="text/javascript">
	<!--
		window.onload = Timer;
	//-->
	</script>

		<h1>Offer Details</h1>

		<div style="margin-bottom: 25px; padding-bottom: 25px; border-bottom: 1px dashed #C7C7C7; overflow: auto;">
			<div style="float: left;">
			<b><?=$offerData[0]["name"];?></b> (<?=$offerData[0]["offer_code"];?>)<br/>
			<p><?=html_entity_decode( $offerData[0]["description"], ENT_QUOTES );?></p>
			<table style="line-height: 30px;">
				<tr>
					<td style="text-align: right;" class="default-special">Price :</td>
					<td class="yellow-special">$<?=$offerData[0]["price"];?></td>
				</tr>
				<tr>
					<td style="text-align: right;" class="default-special">Value :</td>
					<td class="yellow-special">$<?=$offerData[0]["value"];?></td>
				</tr>
				<tr>
					<td style="text-align: right;" class="default-special">Discount :</td>
					<td class="yellow-special"><?=$offerData[0]["discount"];?>%</td>
				</tr>
			</table>
			</div>
			<div style="float: right;">

				<!--Timer-->
				<div style="margin-top: 3px; margin-bottom: 7px;">
					<p><img src="assets/gfx/misc/time-left-to-buy.png" alt="" title=""/></p>
					<table cellspacing="0" cellpadding="2" align="center">
						<tr>
							<td><span id="days" class="timer-box">00</span></td>
							<td>:</td>
							<td><span id="hours" class="timer-box">00</span></td>
							<td>:</td>
							<td><span id="minutes" class="timer-box">00</span></td>
							<td>:</td>
							<td><span id="seconds" class="timer-box">00</span></td>
						</tr>
						<tr>
							<td style="text-align: center; font-size: 10px;">days</td>
							<td></td>
							<td style="text-align: center; font-size: 10px;">hours</td>
							<td></td>
							<td style="text-align: center; font-size: 10px;">mins</td>
							<td></td>
							<td style="text-align: center; font-size: 10px;">secs</td>
						</tr>
					</table>
				</div>

			</div>
		</div>

		<h3>Offer Participants</h3>
		<?php
		if( $participantCount > 0 )
		{// Offer has participants
		?>
			<table cellspacing="0" cellpadding="4" border="0" style="width: 100%; border: 1px solid gray; border-collapse: collapse;">
			<tr style="background-color: #C9C9C9; color: #FFFFFF; font-weight: bold;">
				<th style="width: 50%;">Customer Name</th>
				<th style="width: 50%; text-align: right;">Email Address</th>
			</tr>
			<?php
				$participant_details = parent::DoQuery();
				foreach( $participant_details as $participant )
				{# Loop Through Each Customer
					parent::SetQuery("SELECT * FROM table_userinfo, table_users WHERE table_users.user_id=table_userinfo.user_id AND table_users.user_id='{$participant["user_id"]}'");
					$customerData = parent::DoQuery();
				?>
					<tr onmouseover="this.style.backgroundColor='#F9F9F9';"
						onmouseout="this.style.backgroundColor='#FFFFFF';">
						<td><?=ucwords($customerData[0]["firstname"]);?> <?=ucwords($customerData[0]["lastname"]);?></td>
						<td style="text-align: right;"><a href="mailto:<?=$customerData[0]["email_address"];?>"><?=$customerData[0]["email_address"];?></a></td>
					</tr>
				<?php
				}
			?>
			</table>
			<?=$paging;?>
			<div style="margin-top: 15px;">
				<button type="button" class="button-special" onclick="window.open('app/ReportGenerator.php?method=ExportParticipantsCSV&offer_code=<?=$offerData[0]["offer_code"];?>&b=1');">Export Participants (CSV)</button>
			</div>
		<?php
		}
		else
		{// No participants
		?>
			There are no participants for this Offer.
		<?php
		}
		?>
		
		<?php
	}

	public function EditProfile()
	{# Edit Profile Page
	?>
		<h3>Edit <?=ucwords($_SESSION["user"]["type"]);?> Profile</h3>
		<?php if($message) { ?>
			<div class="message-<?=$message_type;?>"><?=$message;?></div>
		<?php } ?>
		<?php if( $_SESSION["user"]["type"] == "customer" ) { ?>
		<form method="post" action="?method=SaveMyProfile&b=1&user_id=<?=$_SESSION["user-data"]["user_id"];?>">
			<div>First Name</div>
			<input type="text" size="25" name="user[firstname]" class="text" value="<?=$_SESSION["user-data"]["firstname"];?>"/>

			<div style="margin-top: 7px;">Last Name</div>
			<input type="text" size="25" name="user[lastname]" class="text" value="<?=$_SESSION["user-data"]["lastname"];?>"/>

			<div style="margin-top: 7px;">Email Address</div>
			<input type="text" size="30" name="user[email_address]" class="text" value="<?=$_SESSION["user-data"]["email_address"];?>"/>

			<div style="margin-top: 7px;">Billing Address 1</div>
			<input type="text" size="45" name="user[billing_address1]" class="text" value="<?=$_SESSION["user-data"]["billing_address1"];?>"/>

			<div style="margin-top: 7px;">Billing Address 2</div>
			<input type="text" size="45" name="user[billing_address2]" class="text" value="<?=$_SESSION["user-data"]["billing_address2"];?>"/>

			<div style="margin-top: 7px;">Billing City</div>
			<input type="text" size="35" name="user[billing_city]" class="text" value="<?=$_SESSION["user-data"]["billing_city"];?>"/>

			<div style="margin-top: 7px;">Billing State</div>
			<input type="text" size="2" name="user[billing_state]" maxlength="2" class="text" value="<?=$_SESSION["user-data"]["billing_state"];?>"/>

			<div style="margin-top: 7px;">Billing Zip</div>
			<input type="text" size="5" name="user[billing_zip]" maxlength="5" class="text" value="<?=$_SESSION["user-data"]["billing_zip"];?>"/>

			<p><input type="submit" value="Save My Profile" class="button-default"/></p>
		</form>
		<?php } else if( $_SESSION["user"]["type"] == "merchant" ) { ?>
		<form method="post" action="?method=SaveMyProfile&b=1&merchant_id=<?=$_SESSION["user-data"]["merchant_id"];?>">
			<div>Company Name</div>
			<input type="text" size="35" name="merchant[company_name]" class="text" value="<?=$_SESSION["user-data"]["company_name"];?>"/>

			<div style="margin-top: 7px;">Email Address</div>
			<input type="text" size="30" name="merchant[email_address]" class="text" value="<?=$_SESSION["user-data"]["email_address"];?>"/>

			<div style="margin-top: 7px;">Street Address</div>
			<input type="text" size="45" name="merchant[street_address]" class="text" value="<?=$_SESSION["user-data"]["street_address"];?>"/>

			<div style="margin-top: 7px;">City</div>
			<input type="text" size="35" name="merchant[city]" class="text" value="<?=$_SESSION["user-data"]["city"];?>"/>

			<div style="margin-top: 7px;">State</div>
			<input type="text" size="2" name="merchant[state]" maxlength="2" class="text" value="<?=$_SESSION["user-data"]["state"];?>"/>

			<div style="margin-top: 7px;">Billing Zip</div>
			<input type="text" size="5" name="merchant[zipcode]" maxlength="5" class="text" value="<?=$_SESSION["user-data"]["zipcode"];?>"/>

			<div style="margin-top: 7px;">Website</div>
			<input type="text" size="45" name="merchant[website_url]" class="text" value="<?=$_SESSION["user-data"]["website_url"];?>"/>

			<p><input type="submit" value="Save Merchant Profile" class="button-default"/></p>
		</form>
		<?php } ?>
	<?php
	}

	public function SaveMyProfile()
	{# Update My Profile
		if( $_SESSION["user"]["type"] == "customer" )
		{// customer
			if( $this->ValidateEmail( $this->localData["user"]["email_address"] ) )
			{// valid email
				if( $this->localData["user"]["firstname"]!=""
				&& $this->localData["user"]["lastname"]!="" )
				{// not missing requirements
					parent::SetQuery("UPDATE table_subscribers SET email_address='{$this->localData['user']['email_address']}' 
					WHERE email_address='{$_SESSION['user-data']['email_address']}' LIMIT 1");
					parent::SimpleQuery();
					parent::SetQuery("UPDATE table_users SET email_address='{$this->localData["user"]["email_address"]}' WHERE user_id='{$this->localData["user_id"]}'");
					parent::SimpleQuery();
					parent::SetQuery("UPDATE table_userinfo SET 
						firstname='{$this->localData["user"]["firstname"]}',
						lastname='{$this->localData["user"]["lastname"]}',
						billing_address1='{$this->localData["user"]["billing_address1"]}',
						billing_address2='{$this->localData["user"]["billing_address2"]}',
						billing_city='{$this->localData["user"]["billing_city"]}',
						billing_state='{$this->localData["user"]["billing_state"]}',
						billing_zip='{$this->localData["user"]["billing_zip"]}' 
						WHERE user_id='{$this->localData["user_id"]}' LIMIT 1");
					parent::SimpleQuery();
					parent::SetQuery("SELECT * FROM table_users, table_userinfo WHERE table_users.user_id=table_userinfo.user_id AND table_users.user_id='{$this->localData["user_id"]}'");
					$userData = parent::DoQuery();
					$_SESSION["user-data"] = $userData[0];
					setcookie("msg", "Your profile has been saved.", time()+300, "/");
					setcookie("msg_type", "success", time()+300, "/");
				}
				else
				{// missing required fields
					setcookie("msg", "You are missing required fields.", time()+300, "/");
					setcookie("msg_type", "error", time()+300, "/");
				}
			}
			else
			{// invalid email address
				setcookie("msg", "Oops, You entered an invalid email address.", time()+300, "/");
				setcookie("msg_type", "error", time()+300, "/");
			}
		}
		else if ( $_SESSION["user"]["type"] == "merchant" )
		{// merchant
			if( $this->ValidateEmail( $this->localData["merchant"]["email_address"] ) )
			{// valid email
				parent::SetQuery("UPDATE `table_merchantinfo` SET
				company_name='{$this->localData["merchant"]["company_name"]}',
				street_address='{$this->localData["merchant"]["street_address"]}',
				city='{$this->localData["merchant"]["city"]}',
				state='{$this->localData["merchant"]["state"]}',
				zipcode='{$this->localData["merchant"]["zipcode"]}',
				website_url='{$this->localData["merchant"]["website_url"]}'
				WHERE merchant_id='{$this->localData["merchant_id"]}' LIMIT 1");
				parent::SimpleQuery();
				parent::SetQuery("SELECT * FROM table_merchants, table_merchantinfo WHERE table_merchants.merchant_id=table_merchantinfo.merchant_id AND table_merchants.merchant_id='{$this->localData["merchant_id"]}'");
				$userData = parent::DoQuery();
				$_SESSION["user-data"] = $userData[0];
				setcookie("msg", "Your company profile has been saved.", time()+300, "/");
				setcookie("msg_type", "success", time()+300, "/");
			}
			else
			{// invalid email address
				setcookie("msg", "Oops, You entered an invalid email address.", time()+300, "/");
				setcookie("msg_type", "error", time()+300, "/");
			}
		}

		header("Location: account?page=edit_profile");
	}

	public function EditAlerts()
	{# Display Currently Active Alerts
		parent::SetQuery("SELECT * FROM `table_locations`");
		$locations = parent::DoQuery();
		?>

			<h3>Location Alerts</h3>

			<form name="alert[form]" method="post" action="?page=edit_alerts">
			<input type="hidden" name="method" value="UpdateAccountAlerts"/>
			<input type="hidden" name="b" value="1"/>

			<table cellspacing="0" cellpadding="4" border="0" style="width: 100%; border: 1px solid gray; border-collapse: collapse;">
			<tr style="background-color: #C9C9C9; color: #FFFFFF; font-weight: bold;">
				<th style="width: 15%; text-align: center;">Action</th>
				<th style="width: 70%;">Location</th>
				<th style="width: 15%; text-align: center;">Status</th>
			</tr>
		<?php
			foreach( $locations as $location )
			{// loop through all locations
				parent::SetQuery( "SELECT * FROM `table_subscribers` WHERE email_address='{$_SESSION['user-data']["email_address"]}'
				AND location_id='{$location["location_id"]}'" );
				$subscription = parent::CountDBResults();
				?>
				<tr>
					<td style="text-align: center;">
						<div class="select-styling-div" style="width: 95px; margin: auto;">
						<select name="location_id[<?=$location["location_id"];?>]" style="width: 95px;">
							<option></option>
							<option value="1">Activate</option>
							<option value="2">Deactivate</option>
						</select>
					</td>
					<td><?=$location["location"];?></td>
					<td><?php
						switch( $subscription )
						{
							case 1 : echo "Active"; break;
							case 0 : echo "<i>Not Active</i>"; break;
						}
					?></td>
				</tr>
				<?php
			}
		?>
			</table>

			<div style="margin-top: 15px;">
				<button type="submit" class="button-special">Save Alerts</button>
			</div>

			</form>
	<?php
	}

	public function UpdateAccountAlerts()
	{# Update Account Alerts
		if( $_SESSION["user-data"]["email_address"] )
		{// check if admin logged in
		$activated = 0;
		$deactivated = 0;
		foreach( $this->localData["location_id"] as $location_id => $value )
		{// loop through all locations
			if( $value == 1 )
			{// activate location alert
				parent::SetQuery("SELECT * FROM `table_subscribers`
				WHERE email_address='{$_SESSION["user-data"]["email_address"]}'
				AND location_id='{$location_id}'");
				$exists = parent::CountDBResults();
				if( !$exists )
				{// location alert doesn't exist
					$activated+=1;
					parent::SetQuery("INSERT INTO `table_subscribers` 
					VALUES('','{$_SESSION["user-data"]["email_address"]}','{$location_id}')");
					parent::SimpleQuery();
				}
				else
				{//
				
				}
			}
			else if( $value == 2 )
			{// delete account alert
				$deactivated+=1;
				parent::SetQuery("DELETE FROM `table_subscribers` WHERE location_id='{$location_id}' 
				AND email_address='{$_SESSION["user-data"]["email_address"]}' LIMIT 1");
				parent::SimpleQuery();
			}
		}
		if( $activated >= 1 || $deactivated >= 1 )
		{// at least one location alert has been activated or deactivated
			setcookie("msg", "{$activated} location alerts activated, {$deactivated} location alerts deactivated.", time()+300, "/");
		}
		else
		{// no locations alerts were activated or deactivated
			setcookie("msg", "No locations were selected.", time()+300, "/");
		}
		setcookie("msg_type", "success", time()+300, "/");
		header("Location: ?page=edit_alerts");
		}
	}

	public function ForgotPassword()
	{// resend password
		switch( $this->localData["user"]["type"] )
		{// switch based on the user type
			case "customer" : $query = "FROM table_users, table_userinfo WHERE table_users.user_id=table_userinfo.user_id"; break;
			case "merchant" : $query = "FROM table_merchants, table_merchantinfo WHERE table_merchants.merchant_id=table_merchantinfo.merchant_id"; break;
		}
		if( $this->ValidateEmail( $this->localData["user"]["email_address"] ) )
		{// check for a valid email address
			parent::SetQuery("SELECT * {$query} AND 
			email_address='{$this->localData["user"]["email_address"]}'");
			$exists = parent::CountDBResults();
			if( $exists )
			{// email address is in our system
				$userData = parent::DoQuery();
				$subject = "Lost Password Reminder";
				$message = "The password you requested for your FMM " . ucwords($this->localData["user"]["type"]) . " Account is listed below:\n\n"
						."Email Address: " . $userData[0]["email_address"] . "\n"
						."Password: " . $userData[0]["password"] . "\n\n"
						."Thank You,\n"
						."The Management";
				mail( $userData[0]["email_address"], $subject, $message, "From: Admin <" . adminEmail . ">" );
				setcookie("msg", "We have sent you a password reminder.", time()+300, "/");
				setcookie("msg_type", "success", time()+300, "/");
			}
			else
			{// email address isn't in our system
				setcookie("msg", "We could not locate a " . ucwords($this->localData["user"]["type"]) . " Account with this email address in our system.", time()+300, "/");
				setcookie("msg_type", "error", time()+300, "/");
			}
		}
		else
		{// please specify an email address
			setcookie("msg", "Oops, You entered an invalid email address.", time()+300, "/");
			setcookie("msg_type", "error", time()+300, "/");
		}
		header("location: login");
	}

	public function DoShareSite()
	{# Share Site, Offers Page
		if( $this->localData["friend"]["name"]!=""
		&& $this->ValidateEmail( $this->localData["friend"]["email"] )
		&& $this->localData["your"]["name"]!=""
		&& $this->ValidateEmail( $this->localData["your"]["email"] )
		&& $this->localData["share"]["message"]!="")
		{//
			mail("{$this->localData["friend"]["name"]} <{$this->localData["friend"]["email"]}>",
			"FindMyMonkey.com, Check it Out!",
			"{$this->localData["share"]["message"]}",
			"From: {$this->localData["your"]["name"]} <{$this->localData["your"]["email"]}>");
			setcookie("msg", "Thanks for sharing the site!", time()+300, "/");
			setcookie("msg_type", "success", time()+300, "/");
			unset( $_SESSION['form-data'] );
		}
		else
		{// missing information
			setcookie("msg", "Invalid email address and/or missing required information.", time()+300, "/");
			setcookie("msg_type", "error", time()+300, "/");
		}
		header("Location: offer-details?offer_id={$this->localData["offer_id"]}&share-post=true");
	}

	public function DoShareSite_Credit()
	{# Share Site for $10 Credit
		$valid_referrals = 0;
		$friendData = array();
		foreach( $this->localData["friend"] as $friend )
		{//
			if( $friend["name"]!="" && $this->ValidateEmail( $friend["email"] ) )
			{// count
				$friendData[] = $friend;
				$valid_referrals+=1;
			}
		}
		if( $valid_referrals == 5 )
		{// successfully referred 5 people
			foreach( $friendData as $friend )
			{// loop through each friend and send them an email
				mail("{$friend["name"]} <{$friend["email"]}>", "FindMyMonkey.com, Great Deals",
				$this->localData["share"]["message"], "From: {$_SESSION["user-data"]["firstname"]} <{$_SESSION["user-data"]["email_address"]}>");
			}
			parent::SetQuery("SELECT * FROM `table_credits` WHERE user_id_credited='{$_SESSION["user-data"]["user_id"]}'");
			$check = parent::CountDBResults();
			if( !$check )
			{// user has not been credited $10 yet
				parent::SetQuery("INSERT INTO `table_credits` VALUES ('','{$_SESSION["user-data"]["user_id"]}')");
				parent::SimpleQuery();
				parent::SetQuery("SELECT * FROM `table_accountbalance` WHERE user_id='{$_SESSION["user-data"]["user_id"]}'");
				$currentBalance = parent::DoQuery();
				$newBalance = $currentBalance[0]["balance"] += 10.00;
				parent::SetQuery("UPDATE `table_accountbalance` SET balance='{$newBalance}' WHERE
				user_id='{$_SESSION["user-data"]["user_id"]}'");
				parent::SimpleQuery();
				setcookie("msg", "Your account has been credited $10, thanks for spreading the word about FMM!", time()+300, "/");
				setcookie("msg_type", "success", time()+300, "/");
				header("Location: account");
			}
			else
			{// user has already completed the offer to receive $10 credit
				setcookie("msg", "You have already received a $10 credit.", time()+300, "/");
				setcookie("msg_type", "error", time()+300, "/");
				header("Location: account");
			}
		}
		else
		{// there aren't 5 valid referrals
			setcookie("msg", "You must enter 5 valid referrals to be credited $10.", time()+300, "/");
			setcookie("msg_type", "error", time()+300, "/");
			header("Location: account");
		}
	}

/**
***
*** Search Functionality
***
**/

	public function DoSearch()
	{# Perform a Search
		?>
		<h2>Deal Search</h2>

		<form action="search" method="get" style="margin: 0px; padding: 0px; overflow: hidden; margin-bottom: 15px;">
			<input type="text" class="search-box" name="q" style="float: left; width: 225px; padding: 3px;" value="<?=$this->localData["q"];?>" onfocus="if(this.value=='<?=$this->localData["q"];?>'){this.value='';}" onblur="if(this.value==''){this.value='<?=$this->localData["q"];?>';}"/>
			<input type="submit" class="search-btn" value="" style="float: left;"/>
		</form>

		<?php
		if( $this->localData["q"] !="" )
		{// query is set
			parent::SetQuery("SELECT *, MATCH (offer_code,name,one_liner,details,description) AGAINST ('{$this->localData["q"]}' 
			IN BOOLEAN MODE) AS score FROM `table_offers` WHERE MATCH (offer_code,name,one_liner,details,description) AGAINST ('{$this->localData["q"]}' 
			IN BOOLEAN MODE) ORDER BY score DESC");
			$results = parent::CountDBResults();
			?>
			<div style="overflow: hidden;">
			<?php
			if( $results )
			{// results found
				$recentDeals = parent::DoQuery();
				foreach( $recentDeals as $deal )
				{// loop through each recent deal
					parent::SetQuery("SELECT * FROM `table_purchased` WHERE offer_id='{$deal["offer_id"]}'");
					$participants = parent::CountDBResults();
				?>
					<div style="float: left; border: 2px dashed #E8E8E8; padding: 10px; margin-bottom: 15px;">
					<b><?=$deal["one_liner"];?></b><br/>
					<img src="<?=$deal["graphic"];?>" border="0" alt="" title="<?=$deal["one_liner"];?>" style="margin-top: 5px;"/>

						<div style="overflow: hidden; margin-top: 5px;">
							<div style="float: left;">Participants: <b><?=$participants;?></b></div>
							<div style="float: right;">
								<?php
								if( $deal["expiration"] > time() )
								{// deal not expired
								?>
									Expires: <?=date("F jS, Y", $deal["expiration"]) . " at " . date(" g:i a", $deal["expiration"]);?>
								<?php
								}
								else
								{// deal expired
								?>
									<i>Past Expiration</i>
								<?php
								}
								?>
							</div>
						</div>
						<div style="overflow: hidden; margin-top: 5px;">
							<div style="float: left;">Price: <b>$<?=$deal["price"];?></b> (<?=$deal["discount"];?>% off)</div>
							<div style="float: right;">
								<a href="offer-details?offer_id=<?=$deal["offer_id"];?>">More Information &amp; Comments</a>
							</div>
						</div>
					</div>
				<?php
				}
			}
			else
			{// no results found
			?>
				<div class="message-error">We couldn't locate any deals for these search terms.  <a href="offers">Try again</a>.</div>
			<?php
			}
			?>
			</div>
			<?php
			/*if( $results )
			{// results found
				$resultData = parent::DoQuery();
				$number = 1;
				foreach( $resultData as $result )
				{// loop through each offer found for these keywords
				?>
					<div style="overflow: hidden; border-bottom: 1px dashed #E8E8E8;">
					<div style="float: left; width: 10%; text-align: right; margin-bottom: 10px;">
						<div style="padding-top: 10px; padding-right: 5px;"><?=$number;?>.</div>
					</div>
					<div style="float: left; width: 90%; margin-bottom: 10px;">
						<div style="padding: 10px;"><a href="offer-details?offer_id=<?=$result["offer_id"];?>"><?=$result["one_liner"];?></a> <b>...</b> <?php if( $result["expiration"] > time() ) { ?><?php echo "Expires: " . date("F d, Y h:i a", $result["expiration"] ); ?><?php } else { ?><i>Offer Expired</i><?php } ?></div>
					</div>
					</div>
				<?php
				$number+=1;
				}
			}
			else
			{// no results found
			?>
				<div class="message-error">We couldn't locate any deals for these search terms.  <a href="offers">Try again</a>.</div>
			<?php
			}*/

		}
		else
		{// no search terms set
		?>
			<div class="message-error">Please enter a search term.</div>
		<?php
		}
	}

/**
***
*** Contact Form
***
**/

	public function DoSendContact()
	{# Submit Contact Form
		if( $this->localData["contact"]["name"]!="" )
		{// name not blank
			if( $this->ValidateEmail( $this->localData["contact"]["email"] ) )
			{// valid email
				if( $this->localData["contact"]["message"]!="" )
				{// message not blank
					parent::SetQuery("SELECT * FROM `table_messages` WHERE message_name='contact_autoresponder'");
					$customMessage = parent::DoQuery();
					// Send To FMM Administrator
					mail(adminEmail, "Contact Form", 
					$this->localData["contact"]["message"], "From: {$this->localData["contact"]["name"]} <{$this->localData["contact"]["email"]}>");
					// Send to Contact Initiator
					mail("{$this->localData["contact"]["name"]} <{$this->localData["contact"]["email"]}>", "Your Contact Inquiry", $customMessage[0]["message"], "no-reply@findmymonkey.com");
					setcookie("msg_type", "success", time()+300, "/");
					setcookie("msg", "Your message has been sent, Thank you.", time()+300, "/");
					unset( $_SESSION["form-data"] );
				}
				else
				{// message blank
					setcookie("msg_type", "error", time()+300, "/");
					setcookie("msg", "Your message cannot be blank.", time()+300, "/");
				}
			}
			else
			{// invalid email
				unset( $_SESSION["form-data"]["contact"]["email"] );
				setcookie("msg_type", "error", time()+300, "/");
				setcookie("msg", "Your e-mail address was invalid.", time()+300, "/");
			}
		}
		else
		{// name blank
			setcookie("msg_type", "error", time()+300, "/");
			setcookie("msg", "Please enter a name.", time()+300, "/");
		}
		header("Location: contact");
	}

/**
***
*** Shopping Cart Functions
***
**/

	public function AddToCart()
	{# Add Item to Cart
		$offerCode = $this->localData['offer_code'];
		if( !isset( $_SESSION[ 'shopping-cart' ] ) )
		{// shopping cart session not started
			$_SESSION[ 'shopping-cart' ] = array();
		}
		$exists = false;
		foreach( $_SESSION[ 'shopping-cart' ] as $name => $value )
		{// loop through shopping cart
			if( $name == $offerCode)
			{// found
				$exists = true;
				break;
			}
		}
		if( !$exists )
		{// not in cart
			if( !$this->localData["donation"] )
			{// not a donation
				parent::SetQuery("SELECT * FROM table_offers WHERE offer_code='{$offerCode}' LIMIT 1");
				$offerDetails = parent::DoQuery();
				if( $offerDetails[0]["expiration"] > time() )
				{// offer still good
					$_SESSION[ 'shopping-cart' ][ $offerCode ][ "quantity" ] = 1;
					foreach( $offerDetails[0] as $name => $value )
					{
						$_SESSION[ 'shopping-cart' ][ $offerCode ][ $name ] = $value;
					}
					setcookie("msg", "Offer successfully added to your Cart.", time()+300, "/");
					setcookie("msg_type", "success", time()+300, "/");
				}
				else
				{// offer expired
					setcookie("msg", "Oops, This Offer has expired.", time()+300, "/");
					setcookie("msg_type", "error", time()+300, "/");
				}
			}
			else
			{// this is a donation
				if( ctype_digit( str_replace( ".", "", $this->localData["donation"]["amt"] ) ) )
				{// valid donation amount
					$_SESSION[ 'shopping-cart' ][ $offerCode ][ "donation"] = true;
					$_SESSION[ 'shopping-cart' ][ $offerCode ][ "offer_code"] = $offerCode;
					$_SESSION[ 'shopping-cart' ][ $offerCode ][ "quantity" ] = 1;
					$_SESSION[ 'shopping-cart' ][ $offerCode ][ "name" ] = $this->localData["donation"]["charity_name"];
					$_SESSION[ 'shopping-cart' ][ $offerCode ][ "description" ] = $this->localData["donation"]["charity_description"];
					$_SESSION[ 'shopping-cart' ][ $offerCode ][ "price" ] = number_format($this->localData["donation"]["amt"], 2, '.', '');
					setcookie("msg", "Your donation has been added to your Shopping Cart.", time()+300, "/");
					setcookie("msg_type", "success", time()+300, "/");
				}
				else
				{// invalid donation amount
					setcookie("msg", "You entered an invalid donation amount.", time()+300, "/");
					setcookie("msg_type", "error", time()+300, "/");
				}
				header("Location: cart");
			}
		}
		else
		{// already in cart
			setcookie("msg", "This item is already in your Cart.", time()+300, "/");
			setcookie("msg_type", "error", time()+300, "/");
		}
		$this->JsonOutput( array( "continue" => "cart" ) );
	}

	public function RemoveFromCart()
	{# Remove Item from Cart
		unset( $_SESSION['shopping-cart'][ $this->localData['offer_code'] ] );
		setcookie("msg", "Item successfully removed from your Cart.", time()+300, "/");
		setcookie("msg_type", "success", time()+300, "/");
		header("Location: cart");
	}

	public function UpdateCart()
	{# Update Cart Contents
		foreach( $this->localData['offer'] as $offer => $value )
		{// loop through each offer
			if( $offer )
			{
				parent::SetQuery("SELECT * FROM table_offers WHERE offer_code='{$offer}'");
				$offerExists = parent::CountDBResults();
				if( $offerExists )
				{// update offer quantity
					$offerData = parent::DoQuery();
					if( $value == 0 )
					{// value is empty, delete from cart
						unset( $_SESSION["shopping-cart"][ $offer ] );
					}
					else if( $value <= $offerData[0]["limit"] )
					{// good value
						$_SESSION['shopping-cart'][ $offer ][ "quantity" ] = $value;
					}
					else
					{// over the limit
						$_SESSION['shopping-cart'][ $offer ][ "quantity" ] = $offerData[0]["limit"];
					}
				}
				setcookie("msg", "Your Cart has been updated.", time()+300, "/");
				setcookie("msg_type", "success", time()+300, "/");
			}
		}
		header("Location: cart");
	}

	public function DoEmptyCart()
	{# Empty Cart Contents
		unset( $_SESSION['shopping-cart'] );
		setcookie("msg", "Your Cart has been emptied.", time()+300, "/");
		setcookie("msg_type", "success", time()+300, "/");
		header("Location: cart");
	}

	public function DisplayCart()
	{# Display Cart
		//session_regenerate_id();
		unset( $_SESSION["form-data"] );
		$_SESSION["checkout-step"] = 1;
	?>
		<form action="?method=UpdateCart&b=1" method="post">
		<table cellspacing="0" cellpadding="5" border="0" style="width: 100%; border: 1px solid gray; border-collapse: collapse;">
			<tr style="background-color: #C9C9C9; color: #FFFFFF; font-weight: bold;">
				<th style="width: 15%; text-align: center;">Action</th>
				<th style="width: 50%;">Description</th>
				<th style="width: 10%; text-align: left;">$/ea</th>
				<td style="width: 10%; text-align: center;">Quantity</td>
				<th style="width: 15%; text-align: left;">Item Total</th>
			</tr>
	<?php
		$subTotal = 0.00;
		$subTotalSavings = 0.00;
		if( sizeOf( $_SESSION['shopping-cart'] ) > 0 )
		{// more than 0 items in cart
			foreach( $_SESSION['shopping-cart'] as $name => $data )
			{// Loop Through Items in Cart
				$subTotal += ( $data["quantity"] * $data["price"] );
				if( !$data["donation"] )
				{// this isn't a donation
				$subTotalSavings += ( $data["quantity"] * ( $data["value"] - $data["price"] ) );
			?>
				<tr>
					<td style="text-align: center;">
						<button type="button" class="button-default" style="overflow: hidden;" onclick="location='?method=RemoveFromCart&b=1&offer_code=<?=$data["offer_code"];?>';"><img src="assets/gfx/icons/quantity-remove.gif" alt="" title="Remove Location" style="float: left;"/></button>
					</td>
					<td>
						<?=$data['one_liner'];?><br/>
						(a <i><b>$<?=$data['value'];?></b></i> value, Limit: <?=$data['limit'];?>)
					</td>
					<td style="text-align: left;">$<?=$data['price'];?></td>
					<td style="text-align: center;">
						<input type="text" maxlength="2" class="text" size="3" name="offer[<?=$data['offer_code'];?>]" value="<?=$data["quantity"];?>" style="text-align: center;"/>
					</td>
					<td style="text-align: left;">$<?=number_format( $data["quantity"] * $data['price'], 2, '.', '');?></td>
				</tr>
			<?php
				}
				else
				{// this is a donation
				?>
				<tr>
					<td style="text-align: center;">
						<button type="button" class="button-default" style="overflow: hidden;" onclick="location='?method=RemoveFromCart&b=1&offer_code=<?=$data["offer_code"];?>';"><img src="assets/gfx/icons/quantity-remove.gif" alt="" title="Remove Location" style="float: left;"/></button>
					</td>
					<td>
						Donation To: <?=$data['description'];?>
					</td>
					<td style="text-align: left;">$<?=$data['price'];?></td>
					<td style="text-align: center;">
						<input type="text" maxlength="2" class="text" size="3" name="offer[<?=$data['offer_code'];?>]" value="<?=$data["quantity"];?>" style="text-align: center;" readonly="readonly"/>
					</td>
					<td style="text-align: left;">$<?=number_format( $data["quantity"] * $data['price'], 2, '.', '');?></td>
				</tr>
				<?php
				}
			}
		}
		else
		{// shopping cart is empty
		?>
			<tr>
				<td colspan="5" style="text-align: center;">You have no Items in your Cart.</td>
			</tr>
		<?php
		}
	?>
		</table>
		<?php if( sizeOf( $_SESSION['shopping-cart'] ) > 0 ) { ?>
		<table cellspacing="0" cellpadding="5" style="margin-top: 15px; width: 100%;">
			<tr>
				<td style="width: 70%;"></td>
				<td style="text-align: right; width: 15%;">
					<b>Total Savings</b> :
				</td>
				<td style="width: 15%;">
					$<?=number_format( $subTotalSavings, 2, '.', '' );?>
				</td>
			</tr>
			<tr>
				<td style="width: 70%;"></td>
				<td style="text-align: right; width: 15%;">
					<b>Total Cost</b> :
				</td>
				<td style="width: 15%;">
					$<?=number_format( $subTotal, 2, '.', '' );?>
				</td>
			</tr>
			<tr>
				<td></td>
				<td colspan="2" style="text-align: right; padding-top: 15px;">
					<input type="button" class="button-default" value="Empty Cart" onclick="ConfirmationMessage('Are you sure you want to empty your Cart?', '?method=DoEmptyCart&b=1');"/>
					<input type="submit" class="button-default" name="update-cart" value="Update Cart"/><br/><br/>
					<input type="button" class="button-special" value="Secure Checkout" onclick="location='checkout?SESSION_ID=<?=session_id();?>';"/>
				</td>
			</tr>
		</table>
		</form>
	<?php
		}
	}

	public function BillingForm()
	{# Billing Form
		if( $this->localData[ "SESSION_ID" ] == session_id() )
		{# Valid Session Identifier
			if( isset( $_SESSION["user-data"] ) && $_SESSION["checkout-step"] == 1 )
			{// skip first step
				$_SESSION['checkout-step'] = 2;
			}
			switch( $_SESSION['checkout-step'] )
			{// Select a Checkout Step
				case 3: $this->Checkout_Step3(); break;
				case 2: $this->Checkout_Step2(); break;
				default: $this->Checkout_Step1(); break;
			}
		}
		else
		{# Invalid Session Identifier
			?>
			<div class="message-error">Error: Please start the Checkout process from <a href="cart">Cart</a> page.</div>
			<?php
		}
	}

/**
***
*** Checkout Functions
***
**/

	public function Checkout_Step1()
	{# Step 1 - Login, or Create an Account
	?>
		<table cellspacing="0" cellpadding="10" align="center">
			<tr>
				<td style="width: 47%; vertical-align: top;">
				<h1>Login</h1>
				<form action="?SESSION_ID=<?=session_id();?>&method=Process_Step1&b=1&type=login" method="post">
					<div>Email Address</div>
					<input type="text" name="user[email_address]" class="text" size="25"/>
					<div style="margin-top: 7px;">Password</div>
					<input type="password" name="user[password]" class="text"/><br/>
					<input type="submit" value="Continue" class="button-default"/>
					<p><a href="login">Forgot Your Password?</a></p>
				</form>
				</td>

				<td style="text-align: center;"><b>OR</b></td>

				<td style="width: 47%; vertical-align: top;">
				<h1>Register</h1>
				<form action="?SESSION_ID=<?=session_id();?>&method=Process_Step1&b=1&type=register" method="post">
					<div>First Name</div>
					<input type="text" name="user[firstname]" class="text"/>
					<div style="margin-top: 7px;">Last Name</div>
					<input type="text" name="user[lastname]" class="text"/>
					<div style="margin-top: 7px;">Email Address</div>
					<input type="text" name="user[email_address]" class="text" size="25"/>
					<div style="margin-top: 7px;">Password</div>
					<input type="password" name="user[password]" class="text"/><br/>
					<input type="submit" value="Continue" class="button-default"/>
				</form>
				</td>
			</tr>
		</table>
	<?php
	}

	public function Process_Step1()
	{# Step 1 of Checkout Process
		if( $this->localData["type"] == "login" )
		{# Login
			parent::SetQuery("SELECT * FROM table_users, table_userinfo WHERE table_users.user_id=table_userinfo.user_id AND email_address='{$this->localData["user"]["email_address"]}'");
			$exists = parent::CountDBResults();
			if( $exists )
			{// user exists
				$data = parent::DoQuery();
				if( $data[0]["email_address"] == $this->localData["user"]["email_address"] )
				{// email address matches an account
					if( $data[0]["password"] == $this->localData["user"]["password"] )
					{// valid password
						$_SESSION["checkout-step"] = 2;
						$_SESSION["user-data"] = $data[0];
						$_SESSION["logged-in"] = 1;
						$_SESSION["user"]["type"] = "customer";
						header("Location: checkout?SESSION_ID=". session_id());
					}
					else
					{// invalid password
						setcookie("msg", "LOGIN: Invalid password.", time()+300, "/");
						setcookie("msg_type", "error", time()+300, "/");
						header("Location: checkout?SESSION_ID=" . session_id());
					}
				}
				else
				{// invalid email address
					setcookie("msg", "LOGIN: Invalid email address.", time()+300, "/");
					setcookie("msg_type", "error", time()+300, "/");
					header("Location: checkout?SESSION_ID=" . session_id());
				}
			}
			else
			{// user doesn't exist
				setcookie("msg", "LOGIN: Invalid email address.", time()+300, "/");
				setcookie("msg_type", "error", time()+300, "/");
				header("Location: checkout?SESSION_ID=" . session_id());
			}
		}
		else if( $this->localData["type"] == "register" )
		{# Register
			if( $this->ValidateEmail( $this->localData["user"]["email_address"] ) )
			{// valid email provided
				parent::SetQuery("SELECT * FROM table_users WHERE email_address='{$this->localData["user"]["email_address"]}'");
				$exists = parent::CountDBResults();
				if( $exists )
				{// error email address already in use
					setcookie("msg", "REGISTER: Email address already in use.", time()+300, "/");
					setcookie("msg_type", "error", time()+300, "/");
					header("Location: checkout?SESSION_ID=" . session_id());
				}
				else
				{// user doesn't exist
					if( $this->localData["user"]["email_address"]!=""
					&& $this->localData["user"]["firstname"]!=""
					&& $this->localData["user"]["lastname"]!=""
					&& $this->localData["user"]["password"]!="")
					{// Successfully created an account
						parent::SetQuery("INSERT INTO table_users VALUES ('','{$this->localData["user"]["email_address"]}',
						'{$this->localData["user"]["password"]}')");
						parent::SimpleQuery();
						parent::SetQuery("SELECT * FROM table_users WHERE email_address='{$this->localData["user"]["email_address"]}'");
						$userData = parent::DoQuery();
						// check if this user was referred
						if( isset( $_COOKIE["referrer"] ) )
						{// user was referred
							parent::SetQuery("INSERT INTO table_referrals VALUES('','{$userData[0]["user_id"]}','{$_COOKIE["referrer"]}','0')");
							parent::SimpleQuery();
						}
						// create user info table record
						parent::SetQuery("INSERT INTO table_userinfo VALUES ('{$userData[0]["user_id"]}',
						'{$this->localData["user"]["firstname"]}','{$this->localData["user"]["lastname"]}','','','','','')");
						parent::SimpleQuery();
						// create account balance table record
						parent::SetQuery("INSERT INTO table_accountbalance VALUES('{$userData[0]["user_id"]}','0.00')");
						parent::SimpleQuery();
						parent::SetQuery("SELECT * FROM table_users, table_userinfo WHERE table_users.user_id=table_userinfo.user_id
						AND email_address='{$this->localData["user"]["email_address"]}'");
						$userData = parent::DoQuery();
						$_SESSION["logged-in"] = 1;
						$_SESSION["user-data"] = $userData[0];
						$_SESSION["checkout-step"] = 2;
						$_SESSION["user"]["type"] = "customer";
						header("Location: checkout?SESSION_ID=" . session_id());
					}
					else
					{// missing information
						setcookie("msg", "REGISTER: Email address already in use.", time()+300, "/");
						setcookie("msg_type", "error", time()+300, "/");
						header("Location: checkout?SESSION_ID=" . session_id());
					}
				}
			}
			else
			{// invalid email address provided
				setcookie("msg", "REGISTER: Invalid email address.", time()+300, "/");
				setcookie("msg_type", "error", time()+300, "/");
				header("Location: checkout?SESSION_ID=" . session_id());
			}
		}
	}

	public function Checkout_Step2()
	{# Enter Credit Card & Billing Information
	?>
		<form method="post" name="form-data" action="?SESSION_ID=<?=session_id();?>&method=Process_Step2&b=1">
		<table cellspacing="0" cellpadding="5" border="0" align="center" style="width: 100%;">
		<tr>
		<td style="width: 10%;"></td>
		<td style="vertical-align: top; width: 30%;">
			<h2>Billing Information</h2>
			<div>First Name</div>
			<input type="text" class="<?php if($_SESSION["fix-field"]["user"]["firstname"]) { ?>missing-info<?php } else { ?>text<?php } ?>" name="user[firstname]" size="25" value="<?php if(!is_array($_SESSION["form-data"])) { echo $_SESSION["user-data"]["firstname"]; } else { echo $_SESSION["form-data"]["user"]["firstname"]; } ?>"/>
			<div style="margin-top: 7px;">Last Name</div>
			<input type="text" class="<?php if($_SESSION["fix-field"]["user"]["lastname"]) { ?>missing-info<?php } else { ?>text<?php } ?>" name="user[lastname]" size="25" value="<?php if(!$_SESSION["form-data"]) { echo $_SESSION["user-data"]["lastname"]; } else { echo $_SESSION["form-data"]["user"]["lastname"]; } ?>"/>
			<div style="margin-top: 7px;">Email Address</div>
			<input type="text" class="<?php if($_SESSION["fix-field"]["user"]["email_address"]) { ?>missing-info<?php } else { ?>text<?php } ?>" name="user[email_address]" size="35" value="<?php if(!$_SESSION["form-data"]) { echo $_SESSION["user-data"]["email_address"]; } else { echo $_SESSION["form-data"]["user"]["email_address"]; } ?>"/>

			<div style="margin-top: 7px;">Address 1</div>
			<input type="text" class="<?php if($_SESSION["fix-field"]["user"]["billing_address1"]) { ?>missing-info<?php } else { ?>text<?php } ?>" name="user[billing_address1]" size="45" value="<?php if(!$_SESSION["form-data"]) { echo $_SESSION["user-data"]["billing_address1"]; } else { echo $_SESSION["form-data"]["user"]["billing_address1"]; } ?>"/>
			<div style="margin-top: 7px;">Address 2</div>
			<input type="text" class="text" name="user[billing_address2]" size="45" value="<?php if(!$_SESSION["form-data"]) { echo $_SESSION["user-data"]["billing_address2"]; } else { echo $_SESSION["form-data"]["user"]["billing_address2"]; } ?>"/>

			<div style="margin-top: 7px;">City</div>
			<input type="text" class="<?php if($_SESSION["fix-field"]["user"]["billing_city"]) { ?>missing-info<?php } else { ?>text<?php } ?>" name="user[billing_city]" size="30" value="<?php if(!$_SESSION["form-data"]) { echo $_SESSION["user-data"]["billing_city"]; } else { echo $_SESSION["form-data"]["user"]["billing_city"]; } ?>"/>

			<div style="margin-top: 7px;">State</div>
			<input type="text" class="<?php if($_SESSION["fix-field"]["user"]["billing_state"]) { ?>missing-info<?php } else { ?>text<?php } ?>" name="user[billing_state]" size="2" value="<?php if(!$_SESSION["form-data"]) { echo $_SESSION["user-data"]["billing_state"]; } else { echo $_SESSION["form-data"]["user"]["billing_state"]; } ?>"/>

			<div style="margin-top: 7px;">Zip Code</div>
			<input type="text" class="<?php if($_SESSION["fix-field"]["user"]["billing_zip"]) { ?>missing-info<?php } else { ?>text<?php } ?>" name="user[billing_zip]" size="5" value="<?php if(!$_SESSION["form-data"]) { echo $_SESSION["user-data"]["billing_zip"]; } else { echo $_SESSION["form-data"]["user"]["billing_zip"]; } ?>"/>

		</td>
		<td style="width: 10%;"></td>
		<td style="vertical-align: top; width: 30%;">
			<h2>Payment Information</h2>
			<input type="radio" name="payment[type]" value="creditcard" id="payment-type-1" onchange="if(this.checked){document.getElementById('type-cc').style.display='block';document.getElementById('type-balance').style.display='none';}" <?php if(!$_SESSION["form-data"]["payment"]["type"] || $_SESSION["form-data"]["payment"]["type"]=="creditcard") { $cc_field = 'block'; ?>checked="checked"<?php } else { $cc_field = 'none'; } ?>/><label for="payment-type-1">My Credit Card</label>
			<br/>
			<input type="radio" name="payment[type]" value="balance" id="payment-type-2" onchange="if(this.checked){document.getElementById('type-cc').style.display='none';document.getElementById('type-balance').style.display='block';}" <?php if($_SESSION["form-data"]["payment"]["type"]=="balance") { $balance_field = 'block'; ?>checked="checked"<?php } else { $balance_field = 'none'; } ?>/><label for="payment-type-2">My Account Balance</label>
			<div style="margin-top: 8px;">

			<?php
				// account balance info
				parent::SetQuery("SELECT * FROM table_accountbalance WHERE user_id='{$_SESSION["user-data"]["user_id"]}' LIMIT 1");
				$accountBalance = parent::DoQuery();
			?>

			<!--Start Payment Type CC-->
			<div id="type-cc" style="display: <?=$cc_field;?>;">
			<div style="margin-bottom: 10px; <?php if( $accountBalance[0]["balance"] == 0 ) { ?>display: none;<?php } ?>">
				<input type="checkbox" id="apply-credit" name="user[apply_credit]" <?php if( $_SESSION["form-data"]["user"]["apply_credit"] ) { ?>checked="checked"<?php } ?>/>
				<label for="apply-credit">Use My Account Balance as a Credit on my Order (Current Balance: $<?=$accountBalance[0]["balance"];?>)</label>
			</div>
			<div>Card Number</div>
			<input type="text" name="user[credit_card]" class="<?php if($_SESSION["fix-field"]["user"]["credit_card"]) { ?>missing-info<?php } else { ?>text<?php } ?>" size="30" maxlength="16" value="<?php echo $_SESSION["form-data"]["user"]["credit_card"]; ?>"/>
			<div style="margin-top: 7px;">Card Expiration</div>
			<input type="text" name="user[card_expmonth]" class="<?php if($_SESSION["fix-field"]["user"]["card_expmonth"]) { ?>missing-info<?php } else { ?>text<?php } ?>" size="2" maxlength="2" value="<?php if( $_SESSION["form-data"]["user"]["card_expmonth"]!="mm") { echo $_SESSION["form-data"]["user"]["card_expmonth"]; } else { echo "mm"; } ?>" 
				onfocus="if(this.value=='mm'){this.value='';}" 
				onblur="if(this.value==''){this.value='mm';}"/> /
			<input type="text" name="user[card_expyear]" class="<?php if($_SESSION["fix-field"]["user"]["card_expyear"]) { ?>missing-info<?php } else { ?>text<?php } ?>" size="4" maxlength="4" value="<?php if( $_SESSION["form-data"]["user"]["card_expyear"]!="yyyy") { echo $_SESSION["form-data"]["user"]["card_expyear"]; } else { echo "yyyy"; } ?>"
				onfocus="if(this.value=='yyyy'){this.value='';}" 
				onblur="if(this.value==''){this.value='yyyy';}"/>
			</div>
			<!--End Payment Type CC-->
			<!--Start Payment Type Balance-->
			<div id="type-balance" style="display: <?=$balance_field;?>;">
			<table cellspacing="0" cellpadding="0">
				<td style="text-align: right;">Current Account Balance :</td>
				<td style="padding-left: 2px;">$<?=$accountBalance[0]["balance"];?></td>
			</table>
			</div>
			<!--End Payment Type Balance-->
			</div>
			<p>
				You will review your order in the next step.<br/><br/>
				<input type="submit" name="form-data" value=" Continue Checkout " class="button-special"/>
			</p>
		</td>
		<td style="width: 10%;"></td>
		</tr>
		</table>
		</form>
	<?php
	}

	public function Process_Step2()
	{# Preliminary Check for Valid Data
		if( $this->localData["user"]["firstname"]!=""
		&& $this->localData["user"]["lastname"]!=""
		&& $this->localData["user"]["email_address"]!=""
		&& $this->localData["user"]["billing_address1"]!=""
		&& $this->localData["user"]["billing_city"]!=""
		&& $this->localData["user"]["billing_state"]!=""
		&& $this->localData["user"]["billing_zip"]!=""
		&& 
			(
				($this->localData["payment"]["type"]=="creditcard"
				&& $this->localData["user"]["credit_card"]!=""
				&& $this->localData["user"]["card_expmonth"]!="mm"
				&& $this->localData["user"]["card_expmonth"]!=""
				&& $this->localData["user"]["card_expyear"]!="yyyy"
				&& $this->localData["user"]["card_expyear"]!="")
				||
				( $this->localData["payment"]["type"]=="balance" )
			)
		)
		{//
			foreach( $this->localData as $name => $value )
			{# Checkout Values
				$_SESSION["checkout-values"][ $name ] = $value;
			}
			$_SESSION["checkout-step"] = 3;
		}
		else
		{// missing required fields
			unset( $_SESSION["fix_field"] );
			$_SESSION[ "fix-field" ][ "user" ] = array();
			$fields = array("firstname", "lastname", "billing_address1", "billing_city", "billing_state",
			"billing_zip", "email_address");
			foreach( $fields as $field )
			{//
				if( $this->localData["user"][$field] == "" )
				{// missing info
					$_SESSION["fix-field"]["user"][$field] = true;
				}
			}
			if( $this->localData["payment"]["type"] == "creditcard" )
			{//
				if( $this->localData["user"]["credit_card"] == "" ) { $_SESSION["fix-field"]["user"]["credit_card"] = true; }
				if( $this->localData["user"]["card_expmonth"] == "" ||
				$this->localData["user"]["card_expmonth"] == "mm" ) { $_SESSION["fix-field"]["user"]["card_expmonth"] = true; }
				if( $this->localData["user"]["card_expyear"] == "" ||
				$this->localData["user"]["card_expyear"] == "yyyy" ) { $_SESSION["fix-field"]["user"]["card_expyear"] = true; }
			}
			setcookie("msg", "You are missing required field(s).", time()+300, "/");
			setcookie("msg_type", "error", time()+300, "/");
		}
		header("Location: checkout?SESSION_ID=" . session_id());
	}

	public function Checkout_Step3()
	{# Review Order
	?>
		<form action="?method=CompleteCheckout&b=1" method="post">
		<center>Your order will not be completed until you click the &quot;<b>Submit My Order</b>&quot; button below.
		<table cellspacing="0" cellpadding="5" border="0" style="width: 100%; border: 1px solid gray; margin-top: 15px; border-collapse: collapse;">
			<tr style="background-color: #C9C9C9; color: #FFFFFF; font-weight: bold;">
				<th style="width: 65%;">Description</th>
				<th style="width: 10%; text-align: left;">$/ea</th>
				<td style="width: 10%; text-align: center;">Quantity</td>
				<th style="width: 15%; text-align: left;">Item Total</th>
			</tr>
	<?php
		$subTotal = 0.00;
		$subTotalSavings = 0.00;
		if( sizeOf( $_SESSION['shopping-cart'] ) > 0 )
		{// more than 0 items in cart
			// empty shopping-cart-info (Authorize.NET Order Info)
			foreach( $_SESSION['shopping-cart'] as $name => $data )
			{// Loop Through Items in Cart
				$subTotal += ( $data["quantity"] * $data["price"] );
				if( !$data["donation"] )
				{// this is not a donation
				$subTotalSavings += ( $data["quantity"] * ( $data["value"] - $data["price"] ) );
			?>
				<tr>
					<td>
						<?=$data['one_liner'];?><br/>
						(a <i><b>$<?=$data['value'];?></b></i> value, Limit: <?=$data['limit'];?>)
					</td>
					<td style="text-align: left;">$<?=$data['price'];?></td>
					<td style="text-align: center;">
						<input type="text" maxlength="2" class="text" size="3" name="offer[<?=$data['offer_code'];?>]" value="<?=$data["quantity"];?>" readonly="readonly" style="text-align: center;"/>
					</td>
					<td style="text-align: left;">$<?=number_format( $data["quantity"] * $data['price'], 2, '.', '');?></td>
				</tr>
			<?php
				}
				else
				{// this is a donation item
				?>
				<tr>
					<td>
						Donation To: <?=$data['description'];?>
					</td>
					<td style="text-align: left;">$<?=$data['price'];?></td>
					<td style="text-align: center;">
						<input type="text" maxlength="2" class="text" size="3" name="offer[<?=$data['offer_code'];?>]" value="<?=$data["quantity"];?>" readonly="readonly" style="text-align: center;"/>
					</td>
					<td style="text-align: left;">$<?=number_format( $data["quantity"] * $data['price'], 2, '.', '');?></td>
				</tr>
				<?php
				}
			}
		}
	?>
		</table>
		<div style="overflow: auto;">
		<table align="right" cellspacing="0" cellpadding="5" style="border: 1px solid gray; margin-top: 15px; border-collapse: collapse;">
			<tr>
				<td style="background-color: #999999; color: #FFFFFF;"><b>Bill To</b></td>
			</tr>
			<tr>
				<td><span class="input"><?=$_SESSION["checkout-values"]["user"]["firstname"];?> <?=$_SESSION["checkout-values"]["user"]["lastname"];?></span> (<span class="input"><a href="javascript:void(0);"><?=$_SESSION["checkout-values"]["user"]["email_address"];?></a></span>)</td>
			</tr>
			<tr><td><span class="input"><?=$_SESSION["checkout-values"]["user"]["billing_address1"];?></span></td></tr>
			<?php if($_SESSION["checkout-values"]["user"]["billing_address2"]) { ?><tr><td><?=$_SESSION["checkout-values"]["user"]["billing_address2"];?></td></tr><?php } ?>
			<tr>
				<td>
					<span class="input"><?=$_SESSION["checkout-values"]["user"]["billing_city"];?></span>,
					<span class="input"><?=$_SESSION["checkout-values"]["user"]["billing_state"];?></span>
					<span class="input"><?=$_SESSION["checkout-values"]["user"]["billing_zip"];?></span>
				</td>
			</tr>
		</table>
		<table align="right" cellspacing="0" cellpadding="5" style="border: 1px solid gray; margin-top: 15px; border-collapse: collapse;">
			<tr>
				<td colspan="2" style="background-color: #999999; color: #FFFFFF;"><b>Payment Information</b></td>
			</tr>
			<?php
				// account balance information
				parent::SetQuery("SELECT * FROM table_accountbalance WHERE user_id='{$_SESSION["user-data"]["user_id"]}' LIMIT 1");
				$accountBalance = parent::DoQuery();
			?>
			<?php if($_SESSION["checkout-values"]["payment"]["type"] == "creditcard" ) { ?>
			<tr>
				<td style="background-color: #ABABAB; color: #FFFFFF; text-align: right;">Card Number :</td>
				<td><?=$_SESSION["checkout-values"]["user"]["credit_card"];?></td></tr>
			<tr>
				<td style="background-color: #ABABAB; color: #FFFFFF; text-align: right;">Expiration :</td>
				<td>
					<?=$_SESSION["checkout-values"]["user"]["card_expmonth"];?>
					/
					<?=$_SESSION["checkout-values"]["user"]["card_expyear"];?>
				</td>
			</tr>
			<?php } elseif( $_SESSION["checkout-values"]["payment"]["type"] == "balance") { ?>
			<tr>
				<td colspan="2" style="font-weight: bold;">Use Account Balance</td>
			</tr>
			<tr>
				<td style="text-align: right;">Current Balance :</td>
				<td>$<?=$accountBalance[0]["balance"];?></td>
			</tr>
			<?php } ?>
		</table>
		</div>
		<table cellspacing="0" cellpadding="5" style="margin-top: 15px; width: 100%;">
			<?php
				$_SESSION["cart-total"] = number_format($subTotal, 2, '.', '');
				$_SESSION["original-cart-total"] = number_format($subTotal, 2, '.', '');
			?>
			<?php
			if( $_SESSION["checkout-values"]["user"]["apply_credit"] == true )
			{// apply balance as a credit
				$_SESSION["cart-total"] -= $accountBalance[0]["balance"];
			?>
			<tr>
				<td></td>
				<td style="text-align: right;"><b>Sub-Total</b> :</td>
				<td>$<?=number_format($subTotal, 2, '.', '');?></td>
			</tr>
			<tr>
				<td></td>
				<td style="text-align: right;"><b>Discount</b> :</td>
				<td>-$<?=$accountBalance[0]["balance"];?></td>
			</tr>
			<?php
			}
			?>
			<tr>
				<td style="width: 70%;"></td>
				<td style="text-align: right; width: 15%;">
					<b>Total</b> :
				</td>
				<td style="width: 15%;">
				
					$<?=number_format( $_SESSION["cart-total"], 2, '.', '' );?>
				</td>
			</tr>
			<tr>
				<td></td>
				<td colspan="2" style="text-align: center; padding-top: 15px;">
					<input type="submit" class="button-special" value="Submit My Order"/>
				</td>
			</tr>
		</table>
		</form>
	<?php
	}

	public function CompleteCheckout()
	{# Process Checkout Form, Authorize.net

		$itemsArray = array();
		$cartContents = array();
		$cartContents[] = "<items>";
		foreach( $_SESSION['shopping-cart'] as $name => $data )
		{// Loop Through Items in Cart
			// item info
			/*
				$data["quantity"]
				$data["price"]
				$data["offer_code"]
				$data["one_liner"]
				$data["name"]
			*/
			if( !$data["donation"] )
			{// not a donation item
				$item = array( $data["offer_code"],
					$data["name"],
					$data["one_liner"],
					$data["quantity"],
					$data["price"],
					"N");
				$cartContents[] = "<item>";
				$cartContents[] = "<offer_code>{$data["offer_code"]}</offer_code>";
				$cartContents[] = "<quantity>{$data["quantity"]}</quantity>";
				$cartContents[] = "</item>";
			}
			else
			{// donation item
				$item = array( $data["offer_code"],
					$data["name"],
					"Donation To: " . $data["description"],
					$data["quantity"],
					$data["price"],
					"N");
			}
			$itemsArray[] = "x_line_item=" . join( "<|>", $item );
		}
		$cartContents[] = "</items>";
		$cartContentsXML = join("\n", $cartContents);

		$success = false;
		// local sale total amount
		$sale_total = $_SESSION["cart-total"];
		if( $_SESSION["checkout-values"]["payment"]["type"] == "creditcard" )
		{// use credit card
			$authorize = $this->AuthorizeNETCharge(
				array(
					"amount" => $_SESSION["cart-total"],
					"description" => "FMM Purchase",
					"firstname" => $_SESSION["checkout-values"]["user"]["firstname"],
					"lastname" => $_SESSION["checkout-values"]["user"]["lastname"],
					"email_address" => $_SESSION["checkout-values"]["user"]["email_address"],
					"card_number" => $_SESSION["checkout-values"]["user"]["credit_card"],
					"expiration" => $_SESSION["checkout-values"]["user"]["card_expmonth"].substr($_SESSION["checkout-values"]["user"]["card_expyear"], -2),
					"billing_zip" => $_SESSION["checkout-values"]["user"]["billing_zip"],
					"billing_city" => $_SESSION["checkout-values"]["user"]["billing_city"],
					"billing_state" => $_SESSION["checkout-values"]["user"]["billing_state"]
				),
				$itemsArray
			);
			if( $authorize[0] == "1" )
			{// order approved
				// create transaction in database
				parent::SetQuery("INSERT INTO `table_transactions` VALUES ('','{$_SESSION["user-data"]["user_id"]}',
				'{$_SESSION["cart-total"]}',
				'{$cartContentsXML}',
				'" . time() . "')");
				parent::SimpleQuery();
				// get transaction info
				parent::SetQuery("SELECT * FROM `table_transactions` WHERE user_id='{$_SESSION["user-data"]["user_id"]}' ORDER BY transaction_id DESC LIMIT 1");
				$txn_info = parent::DoQuery();
				if( $_SESSION["checkout-values"]["user"]["apply_credit"] == true )
				{// update account balance
					parent::SetQuery("SELECT * FROM `table_accountbalance` WHERE user_id='{$_SESSION["user-data"]["user_id"]}'");
					$accountBalance = parent::DoQuery();
					$afterDiscount = $_SESSION["original-cart-total"] - $accountBalance[0]["balance"];
					$difference = $accountBalance[0]["balance"] - ( $_SESSION["original-cart-total"] - $afterDiscount );
					parent::SetQuery("UPDATE `table_accountbalance` SET balance='{$difference}' WHERE user_id='{$_SESSION["user-data"]["user_id"]}' LIMIT 1");
					parent::SimpleQuery();
				}
				$success = true;
				$message = "Your order was successful, Thank you.";
				$location = "cart";
			}
			else
			{// error
				$success = false;
				$message = $authorize[3];
				$_SESSION["checkout-step"] = 2;
				$location = "checkout?SESSION_ID=" . session_id();
			}
		}
		else if( $_SESSION["checkout-values"]["payment"]["type"] == "balance")
		{// use account balance
			parent::SetQuery("SELECT * FROM `table_accountbalance` WHERE user_id='{$_SESSION["user-data"]["user_id"]}' LIMIT 1");
			$accountBalance = parent::DoQuery();
			if( $accountBalance[0]["balance"] > $_SESSION["cart-total"] )
			{// account balance is sufficient for this order
				// create transaction in database
				parent::SetQuery("INSERT INTO `table_transactions` VALUES ('','{$_SESSION["user-data"]["user_id"]}',
				'{$_SESSION["cart-total"]}',
				'{$cartContentsXML}',
				'" . time() . "')");
				parent::SimpleQuery();
				// get transaction info
				parent::SetQuery("SELECT * FROM `table_transactions` WHERE user_id='{$_SESSION["user-data"]["user_id"]}' ORDER BY transaction_id DESC LIMIT 1");
				$txn_info = parent::DoQuery();
				// update account balance
				$newBalance = number_format($accountBalance[0]["balance"] - $_SESSION["cart-total"], 2, '.', '');
				parent::SetQuery("UPDATE `table_accountbalance` SET balance='{$newBalance}' WHERE user_id='{$_SESSION["user-data"]["user_id"]}' LIMIT 1");
				parent::SimpleQuery();
				$success = true;
				$message = "Your order was successful, Thank you.";
				$location = "cart";
			}
			else
			{// account balance is too low, cannot continue with order, please use credit card
				$success = false;
				$message = "Your account balance is insuficient to complete this transaction. To complete this order, please use your Credit Card.";
				$_SESSION["checkout-step"] = 2;
				$location = "checkout?SESSION_ID=" . session_id();
			}
		}

		if( $success )
		{// success
			if( isset( $_COOKIE["referrer"] ) && $_COOKIE["referrer"]!=$_SESSION["user-data"]["user_id"] )
			{// user was referred
				// get referrer current account balance
				parent::SetQuery("SELECT * FROM table_accountbalance WHERE user_id='{$_COOKIE["referrer"]}' LIMIT 1");
				$referrerData = parent::DoQuery();
				$newBalance = number_format($referrerData[0]["balance"]+10, 2, '.', '');
				// update referrer account balance
				parent::SetQuery("UPDATE `table_accountbalance` SET balance='{$newBalance}' WHERE user_id='{$referrerData[0]["user_id"]}' LIMIT 1");
				parent::SimpleQuery();
				// update referral status to be active
				parent::SetQuery("UPDATE `table_referrals` SET status='1' WHERE user_id_referred='{$_SESSION["user-data"]["user_id"]}' LIMIT 1");
				parent::SimpleQuery();
				setcookie("referrer", "", time()-(86400*3), "/");
			}
			if( isset( $_COOKIE["affiliate"] ) && $_COOKIE["affiliate"]!=$_SESSION["user-data"]["user_id"] )
			{// affiliate id was set
				$percentage_commission = 3 / 100;
				$commission = $percentage_commission * $sale_total;
				parent::SetQuery("INSERT INTO `table_commissions` VALUES('','{$_COOKIE["affiliate"]}','{$txn_info[0]["transaction_id"]}','{$commission}','0')");
				parent::SimpleQuery();
				setcookie("affiliate", "", time()-(86400*3), "/");
			}
			foreach( $_SESSION["shopping-cart"] as $name => $data )
			{// insert into purchased table
				if( !$data["donation"] )
				{// this is not a donation
					parent::SetQuery("INSERT INTO `table_purchased` VALUES ('','{$_SESSION["user-data"]["user_id"]}','{$data["offer_id"]}')");
					parent::SimpleQuery();
				}
				else
				{// this is a donation item, update the donation amount for the charity
					parent::SetQuery("SELECT * FROM `table_charities` WHERE charity_id='{$data["offer_code"]}' LIMIT 1");
					$charityInfo = parent::DoQuery();
					$newCharityBalance = number_format($charityInfo[0]["amt_donated"] + $data["price"], 2, '.', '');
					parent::SetQuery("UPDATE `table_charities` SET amt_donated='{$newCharityBalance}' WHERE charity_id='{$data["offer_code"]}' LIMIT 1");
					parent::SimpleQuery();
				}
			}
			unset( $_SESSION["shopping-cart"] );
			setcookie("msg", $message, time()+300, "/");
			setcookie("msg_type", "success", time()+300, "/");
		}
		else
		{// error
			setcookie("msg", $message, time()+300, "/");
			setcookie("msg_type", "error", time()+300, "/");
		}
		header("Location: " . $location );
	}

	private function AuthorizeNETCharge( $data, $items )
	{# Authorize.Net API
		// By default, this sample code is designed to post to our test server for
		// developer accounts: https://test.authorize.net/gateway/transact.dll
		// for real accounts (even in test mode), please make sure that you are
		// posting to: https://secure.authorize.net/gateway/transact.dll
		$post_url = "https://secure.authorize.net/gateway/transact.dll";
		$post_values = array(
			// the API Login ID and Transaction Key must be replaced with valid values
			"x_login" => "2N5Fqn24",
			"x_tran_key" => "265HMnyQPf6232CQ",

			"x_version" => "3.1",
			"x_delim_data" => "TRUE",
			"x_delim_char" => "|",
			"x_relay_response" => "FALSE",

			"x_type" => "AUTH_CAPTURE",
			"x_method" => "CC",
			"x_card_num" => $data["card_number"],
			"x_exp_date" => $data["expiration"] ,
			// mm/yy

			"x_amount" => $data["amount"],
			"x_description" => $data["description"],

			"x_first_name" => $data["firstname"],
			"x_last_name" => $data["lastname"],
			"x_email" => $data["email_address"],
			"x_city" => $data["billing_city"],
			"x_state" => $data["billing_state"],
			"x_zip" => $data["billing_zip"]
			// Additional fields can be added here as outlined in the AIM integration
			// guide at: http://developer.authorize.net
		);

		// This section takes the input fields and converts them to the proper format
		// for an http post.  For example: "x_login=username&x_tran_key=a1B2c3D4"
		$post_string = "";
		foreach( $post_values as $key => $value )
		{// loop through post values
			$post_string .= "$key=" . urlencode( $value ) . "&";
		}
		$post_string .= join("&", $items);
		$post_string = rtrim( $post_string, "& " );

		// This sample code uses the CURL library for php to establish a connection,
		// submit the post, and record the response.
		// If you receive an error, you may want to ensure that you have the curl
		// library enabled in your php configuration
		$request = curl_init($post_url); // initiate curl object
		curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($request, CURLOPT_POSTFIELDS, $post_string); // use HTTP POST to send form data
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response.
		$post_response = curl_exec($request); // execute curl post and store results in $post_response
		// additional options may be required depending upon your server configuration
		// you can find documentation on curl options at http://www.php.net/curl_setopt
		curl_close ($request); // close curl object

		// authorize.net response
		$response_array = explode($post_values["x_delim_char"],$post_response);
		return $response_array;
	}

/***
**** Offer Notification Queue, Cronjob
****
***/
	public function ProcessQueue()
	{// empty the queue
		parent::SetQuery("SELECT * FROM `table_queue`,`table_offers` WHERE 
		`table_queue`.`offer_id`=`table_offers`.`offer_id` 
		AND 
		`table_queue`.`status`='0' LIMIT 1");
		$offerData = parent::DoQuery();
		$offerData = $offerData[0];
		parent::SetQuery("SELECT * FROM `table_offerlocations` WHERE 
		offer_id='{$offerData["offer_id"]}'");
		$offerLocations = parent::DoQuery();
		$offer_alert_message = "{$offerData["one_liner"]} .. for only \${$offerData["price"]} (a \${$offerData["value"]} value!)\n\n"
			."Expires: " . date("F jS, Y", $offerData["expiration"]) . " at " . date("g:i a") . "\n\n"
			."More Information: http://www.findmymonkey.com/offer-details?offer_id={$offerData["offer_id"]}\n\n"
			."-FMM";
		foreach( $offerLocations as $location )
		{// loop through each offer location
			parent::SetQuery("SELECT * FROM `table_locations` WHERE location_id='{$location["location_id"]}'");
			$locationData = parent::DoQuery();
			parent::SetQuery("SELECT * FROM `table_subscribers` WHERE 
			location_id='{$location["location_id"]}'");
			$locationSubscribers = parent::CountDBResults();
			if( $locationSubscribers > 0 )
			{// location has subscribers
				$subscribers = parent::DoQuery();
				foreach( $subscribers as $subscriber )
				{// loop through each location subscriber, sending them an email about the offer
					mail("{$subscriber["email_address"]}", "FMM Offer Alerts for {$locationData[0]["location"]}!",
					$offer_alert_message, "From: no-reply@findmymonkey.com");
				}
			}
		}
		// update the queue
		parent::SetQuery("UPDATE `table_queue` SET `status`='1', `timestamp`='" . time() . "' WHERE 
		offer_id='{$offerData["offer_id"]}'");
		parent::SimpleQuery();
	}
}

// Send All Requests to the Application
$Application = new Application( array_merge( $_GET, $_POST ) );
?>