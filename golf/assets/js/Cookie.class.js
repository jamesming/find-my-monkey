function Cookie(  )
{// sets, gets, deletes, prints cookies

	this.SetCookie = function( name, value, exp, path )
	{// set the cookie data
		var path;
		var exp;
		path == null ? path = "/" : path = path;
		exp == null ? exp = 30 : exp = exp;
		var date = new Date(  );
		date.setTime( date.getTime(  ) + ( exp * 24 * 60 * 60 * 1000 ) );
		expires = ";expires=" + date.toGMTString(  );
		document.cookie = name + "=" + escape( value ) + expires + ";path=" + path;
	}

	this.GetCookieData = function( name )
	{// retrieve the cookie data
		var start = document.cookie.indexOf( name + "=" );
		if( start != -1 )
		{// cookie was stored
			start = start + name.length+1;
			var end = document.cookie.indexOf( ';', start );
			if( end == -1 )
			{// find the end
				end = document.cookie.length;
			}
			return unescape( document.cookie.substring( start, end ) );
		}
		else
		{// cookie wasn't found
			return false;
		}
	}

	this.DeleteCookie = function( name, path )
	{// delete the cookie
		Cookie.SetCookie( name, "", -1, path );
	}

}

// initiate the cookie class for easy access
Cookie = new Cookie(  );

/*
	Usage

	Cookie.SetCookie( "testcookie", "testvalue", 10, "/" );
	Cookie.GetCookieData( "testcookie" );
	Cookie.DeleteCookie( "testcookie" );
*/