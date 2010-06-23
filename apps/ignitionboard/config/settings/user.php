<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| USER SETTINGS
| -------------------------------------------------------------------
| This file will contain the default settings for user-related
| information. These are overwritten when database settings are found.
|
| Settings inside of here are accessed through:
| $CI/$this->config->user->data_category->data;
|
| Eg. $this->config->user->localization->language;
|
| Some of these settings cannot be changed via the database.
| All of the settings here represent defaults.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|	--- LOCALIZATION ---
|	['localization']['language'] 	The language of the board.
|	['localization']['date']		The format of date/time strings on the board.
|	
*/
	// CORE
	$user['localization']['language'] 	= 'english';
	$user['localization']['date']		= 'd/m/Y H:i:s';