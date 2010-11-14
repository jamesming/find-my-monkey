function Timer()
	{// countdown timer;

		clearTimeout( timer );

		timeLeft--;
		//alert(timeLeft);
		UpdateTimer();

		timer = setTimeout(function(){
			Timer()
		}, 1000);
	}

function CheckVal( val )
	{
		if( val < 10 )
		{
			val = '0' + val;
		}
		return val;
	}

function UpdateTimer()
	{// update timer
		// temporarily store the timeleft locally
		var tempTime = timeLeft;
		if( tempTime > 0 )
		{// not expired
			// check how many days left
			var daysLeft = Math.floor( tempTime / 86400 );
			tempTime -= Math.round( daysLeft * 86400 );
			// update days output
			$("#days").html( CheckVal( daysLeft ) );
			// check how many hours left
			var hoursLeft = Math.floor( tempTime / 3600 );
			tempTime -= Math.round( hoursLeft * 3600 );
			// update hours output
			$("#hours").html( CheckVal( hoursLeft ) );
			// check out how many minutes left
			var minutesLeft = Math.floor( tempTime / 60 );
			tempTime -= Math.round( minutesLeft * 60 );
			// update minutes output
			$("#minutes").html( CheckVal( minutesLeft ) );
			// update seconds output
			$("#seconds").html( CheckVal( tempTime ) );
		}
		else if( tempTime == 0 )
		{// no time left
			$("#seconds").html( "00" );
		}
	}