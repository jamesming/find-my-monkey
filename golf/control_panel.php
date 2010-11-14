<?php
# Require Application Class
require("app/Application.class.php");

# Check that Administrator is Logged In
$Application->CheckIsAuth( false );

$methodSelector = $_GET[ "method" ];
if( !$methodSelector )
{# Select a Method
	$methodSelector = "AdminWelcomeScreen";
}

include "assets/header-top.php"; ?>

<!--Title-->
<title><?=appName;?> - Control Panel</title>

<!--Meta-->
<meta name="keywords" content=""/>
<meta name="description" content=""/>

<?php include "assets/header-mid.php"; ?>

<!--Page-Specific JavaScripts-->
<script type="text/javascript">
<!--
	var saveChanges = 0;

	function ChangesMade()
	{// changes were made
		saveChanges = 1;
	}

	function ChangeOverride()
	{// override pre-existing changes, don't display warning dialog
		saveChanges = 0;
	}

	function CheckForChanges()
	{// create onblur changes made
		var inputs = document.getElementsByTagName("input");
		var selects = document.getElementsByTagName("select");
		var buttons = document.getElementsByTagName("button");
		if( inputs.length > 0 )
		{// more than 0 inputs
			for( var i in inputs )
			{// check inputs for changes
				if( inputs[i].type == 'text' )
				{
					inputs[i].setAttribute("onkeyup", "ChangesMade();");
				}
				else if( inputs[i].type == 'checkbox' )
				{
					inputs[i].setAttribute("onchange", "ChangesMade();");
				}
			}
		}
		if( buttons.length > 0 )
		{// more than 0 buttons
			for( var i in buttons )
			{// if new data is submitted, do not display warning dialog to save changes
				if( buttons[i].type == 'submit' )
				{// submits
					buttons[i].setAttribute("onclick", "ChangeOverride();");
				}
				else if( buttons[i].type == 'button' )
				{// buttons
					//buttons[i].setAttribute("onclick", "ChangeOverride();");
				}
			}
		}
		if( selects.length > 0 )
		{// more than 0 select boxes
			for( var i in selects )
			{// check selects for changes
				selects[i].setAttribute("onchange", "ChangesMade();");
			}
		}
	}

	function ConfirmationMessage( message, url )
	{// confirmation popup (for confirming deletion)
		var ask = confirm( message + '\n\nPress "Ok" to Continue.\nPress "Cancel" to Return to the previous screen.' );
		if( ask )
		{// confirmed
			location = url;
		}
		else
		{// cancelled
			return;
		}
	}

function UnloadFunction()
{// unload function
	if( saveChanges )
	{// save changes
		return 'You have made changes on this page, navigating away from this page before saving will discard all of the changes that you\'ve made.';
	}
}

setTimeout(function(){CheckForChanges();}, 200);
window.onbeforeunload = UnloadFunction;

//-->
</script>

<?php include "assets/admin/header-bot.php"; ?>

<?=$Application->{ $methodSelector }( );?>

<?php include "assets/admin/footer.php"; ?>