function Hash()
{// get location url hash data
	this.timerID;
	this.timeInt = 200;
	this.GetHash = function()
	{// get the hash content
		var hash = location.hash;
		var hashData = [];
		if( hash )
		{// hash key is set
			hash = hash.replace( '#', '' );
			var parts = hash.split( '&' );
			for( i in parts )
			{// get hash content parts
				if ( parts[ i ] )
				{// this part is not empty/null
					var bits = parts[ i ].split( '=' );
					hashData[ bits[0] ] = bits[1];
				}
			}
			this.ProcessData( hashData );
		}
	}

	this.ProcessData = function( data )
	{// process hash into an application call
		clearTimeout( this.timerID );
		this.timerID = setTimeout(function(){
			AppCall( 'Application', data, {'success' : AppSuccess} );
		}, this.timerInt);
	}
}