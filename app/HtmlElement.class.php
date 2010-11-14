<?php
/*
** HTML Class
**  Description: Creates an HTML Element
**  Date: 03/18/2010
**  Version: 1
*/

class HtmlElement
{# HTML Class
	public $type;
	public $attributes;
	public $selfClosers;
	
	public function __construct( $type, $selfClosers = array( 'input', 'img', 'hr', 'br', 'meta' ) )
	{# Construct
		$this->type = strtolower( $type );
		$this->selfClosers = $selfClosers;
	}
	
	public function Get( $attribute )
	{# Get Attribute
		return $this->attributes[ $attribute ];
	}
	
	public function Set( $attribute, $value = '' )
	{# Set Attribute
		if( ! is_array( $attribute ) )
		{# Set one attribute value
			$this->attributes[ $attribute ] = $value;
		}
		else
		{# Attributes is in an array
			$this->attributes = array_merge( $this->attributes, $attribute );
		}
	}
	
	public function Remove( $att )
	{# Remove Attribute
		if( isset( $this->attributes[$att] ) )
		{# Check if attribute is set before removing it
			unset( $this->attributes[ $att ] );
		}
	}
	
	public function Clear()
	{# Clear All Attributes
		$this->attributes = array();
	}
	
	public function Inject( $object )
	{# Inject
		if( @get_class( $object ) == __class__ )
		{# Add an already existing HTML Element to this current Element
			$this->attributes[ 'text' ] .= $object->BuildHTML();
		}
	}
	
	public function BuildHTML()
	{# Build HTML
		$htmlData = '<' . $this->type;
		
		if( count( $this->attributes ) )
		{# Add Attributes
			foreach( $this->attributes as $key => $value )
			{# Loop through each attribute and add it
				if( $key != 'text' ) { $htmlData .= ' ' . $key . '="' . $value . '"'; }
			}
		}
		
		if( !in_array( $this->type, $this->selfClosers ) )
		{# Closing Tag
			$htmlData .= '>' . $this->attributes[ 'text' ] . '</' . $this->type . '>';
		}
		else
		{# Self Closer
			$htmlData .= ' />';
		}
		
		# Return HTML
		return $htmlData;
	}
	
	public function OutputData()
	{# Output HTML
		echo $this->BuildHTML();
	}

	public function ReturnData()
	{# Return HTML
		return htmlentities( $this->BuildHTML(), ENT_QUOTES );
	}
}
?>