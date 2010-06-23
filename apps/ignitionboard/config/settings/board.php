<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| BOARD SETTINGS
| -------------------------------------------------------------------
| This file will contain the default settings for board-related
| information. These are overwritten when database settings are found.
|
| Settings inside of here are accessed through:
| $CI/$this->config->board->data_category->data;
|
| Eg. $this->config->board->text->board_title;
|
| Some of these settings cannot be changed via the database.
| All of the settings here represent defaults.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|	--- CORE ---
|	['core']['installed'] 	Whether or not the board is considered installed.
|	--- TEXT ---
|	['text']['title'] 		The title of the forum.
|	--- THEMES ---
|	['themes']['name'] 		The name of the currently active theme. Overridden by user themes [NYI]
|	['themes']['url'] 		The URL to the active theme.
|	--- COMPRESSION ---
|	['compression']['css']	Whether or not to force GZIP compression via PHP on CSS files.
|	['compression']['js']	Whether or not to force GZIP compression via PHP on JS files.
|	
*/
	// CORE
	$board['core']['installed'] 	= FALSE;
	$board['core']['profiler']		= FALSE;
	// TEXT
	$board['text']['title'] 		= "IgniteBB";
	// THEMES
	$board['themes']['name'] 		= "Ignited";
	$board['themes']['url'] 		=  "ignited/";
	// COMPRESSION
	$board['compression']['css']	= FALSE;
	$board['compression']['js']		= FALSE;