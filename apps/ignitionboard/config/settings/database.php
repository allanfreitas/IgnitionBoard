<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['prefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['charset'] The charset to use when connecting to the database.
|	['collate'] The collation to use for tables/database(s).
|	['auto_create'] Creates the database if it doesn't already exist. Doesn't always work, but no harm in trying.
*/
$db['connection']				= "primary"; // Default connection set.
$db['load_all']					= FALSE; // Load all connection sets?

$db['primary']['driver']		= "mysqli";
$db['primary']['hostname']		= "localhost";
$db['primary']['username']		= "";
$db['primary']['password']		= "";
$db['primary']['database']		= "ignitionboard";
$db['primary']['prefix']		= "ibb_";
$db['primary']['charset']		= "utf8";
$db['primary']['collate']		= "utf8_general_ci";
$db['primary']['auto_create']	= TRUE;

/* End of file database.php */
/* Location: ./system/application/config/settings/database.php */