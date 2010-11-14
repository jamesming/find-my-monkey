<?php
/*
** MySQL Database Class
**  Description: Establishes a database connection and allows you to process database queries.
**  Date: 08/15/2010
**  Version: 2
*/

class MysqlDatabase{// MySQL Database Class
	private $dbHost;
	private $dbUser;
	private $dbPass;
	private $dbName;

	private $dbLink;
	private $dbQuery;
	private $dbResults = array();

	public function SetDBName( $dbname ){// Set the Database Name
		$this->_dbname = $dbname;
	}

	public function SetQuery( $query ){// Set a Query
		unset( $this->dbResults );
		$this->dbQuery=$query;
	}

	public function PrintQuery(){// Print Query to Screen [used for debugging]
		print_r( $this->dbQuery ); exit;
	}

	public function GetDBName(){// Get the Currently Selected Database
		return $this->dbName;
	}

	public function CountDBResults(){// Count the Results from the Query
		try
		{# Try/Catch
			
			if( mysql_query( $this->dbQuery ) >= 1 )
			{# Query was Successful
				$results = mysql_num_rows( mysql_query( $this->dbQuery ) );
				return $results;
			}
			else
			{# Error with Query, Throw Exception
				throw new Exception('Error with MySQL Query : ' . $this->dbQuery. "\n<br>\n\n Error Number:". mysql_errno(). "\n<br>\n\nBad Query: ".mysql_error()."\n<br>\n\n" );
			}
		}
		catch(Exception $e)
		{# Catch Error, Echo Error
			echo $e->getMessage();
			exit;
		}
	}

	public function GetRows(){// Get the Database Results
		return $this->dbResults;
	}

	public function __construct( $dbHost, $dbUser, $dbPass, $dbName ){// Set Default Connection String
		$this->dbHost = $dbHost;
		$this->dbUser = $dbUser;
		$this->dbPass = $dbPass;
		$this->dbName = $dbName;

		$this->Connect( $this->dbHost, $this->dbUser, $this->dbPass, $this->dbName );
		/*
			Set Defaults for Variables
			based on constructor input.
		*/			
	}

	public function __destruct(){// Automatically Disconnect DBLink When Completed also Automatically Unlink Open Connections After Page Loads
		if( $this->dbLink )
		{# Link Exists, Close Link
			mysql_close( $this->dbLink );
			exit;
		}
		
	}
  
	public function Connect( $dbHost, $dbUser, $dbPass, $dbName ){
		try
		{# Try/Catch
			if( mysql_connect( $dbHost, $dbUser, $dbPass ) )
			{# Successfully Connected
				$this->dbLink = mysql_connect( $dbHost, $dbUser, $dbPass );
				try
				{# Try/Catch
					if( mysql_select_db( $dbName ) )
					{# Database Selected
						mysql_select_db( $dbName );
					}
					else
					{# Error Selecting Database
						throw new Exception('Error selecting Database : ' . $dbName);
					}
				}
				catch(Exception $e)
				{# Display Caught Error
					echo $e->getMessage();
					exit;
				}
			}
			else
			{# Error Connecting to Database
				throw new Exception('Error Connecting to MySQL Database ' . $dbUser . '@' . $dbHost. ' using password: ' . $dbPass );
			}
		}
		catch(Exception $e)
		{# Display Caught Error
			echo $e->getMessage();
			exit;
		}
		/*
			Create a Database Connection
			All information passed via __construct method
		*/
	}
	
	public function DoQuery(){
		try
		{# Try/Catch
			$q = mysql_query( $this->dbQuery );
			if( $q )
			{# Query Successful
				while( $resultRow = mysql_fetch_assoc( $q ) )
				{# Fetch Results, Store Them in an Array
					$this->dbResults[] = $resultRow;
				}
				return $this->dbResults;
			}
			else
			{# Error With Query
				throw new Exception('Error with MySQL Query : ' . $this->dbQuery. "\n<br>\n\n Error Number:". mysql_errno(). "\n<br>\n\nBad Query: ".mysql_error()."\n<br>\n\n" );
			}
		}
		catch(Exception $e)
		{# Display Caught Error
			echo $e->getMessage();
			exit;
		}
		/*
			Performs a DB Query
			Output results to an associative array
			And return.
		*/			
	}

	public function StripQuery( $input ){// Strip Query of Illegal Characters
		$output = html_entity_decode( $input, ENT_QUOTES );
		$output = strip_tags( $output );
		$output = stripslashes( $output );
		return $output;
	}

	public function SimpleQuery(){// Simple Query -- No Results Are Returned
		try
		{# Try/Catch
			$success = mysql_query( $this->dbQuery );
			if( $success )
			{# Query Successful
				return $success;
			}
			else
			{# Error With Query
				throw new Exception('Error with MySQL Query : ' . $this->dbQuery. "\n<br>\n\n Error Number:". mysql_errno(). "\n<br>\n\nBad Query: ".mysql_error()."\n<br>\n\n" );
			}
		}
		catch(Exception $e)
		{# Display Caught Error
			echo $e->getMessage();
			exit;
		}
	}
	
	public function InsertQuery(){// Insert Query -- Inserted primary key returned
		$result = mysql_query( $this->dbQuery ) or die("A MySQL error has occurred.<br /><br><br>Your Query: " . $this->dbQuery . "<br /><br><br> Error: (" . mysql_errno() . ") " . mysql_error());
		return mysql_insert_id();
	}
	
}
?>