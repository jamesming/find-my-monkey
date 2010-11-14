function Preload(  )
{// preload images

	var images = [  ];
	var preload = [  ];

	this.New = function( name, extension, dir )
	{// add an image to preload
		var name;
		var extension;
		var dir;
		dir == null ? dir = "gfx" : dir = dir;
		extension == null ? extension = "jpg" : extension = extension;
		images.push( dir + "/" + name + "." + extension );
	}

	this.Initiate = function(  )
	{// start preloading
		for( i = 0; i< images.length; i++ )
		{// loop through images
			preload[ i ] = new Image(  );
			preload[ i ].src = images[ i ];
		}
	}

}

var Preload = new Preload(  );

/*
	Usage

	Preload.New( imgName, imgExtension, imgDirectory );
	Preload.Initiate( imgName, imgExtension, imgDirectory );
*/