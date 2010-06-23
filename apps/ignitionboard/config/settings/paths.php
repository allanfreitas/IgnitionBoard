<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| PATHS SETTINGS
| -------------------------------------------------------------------
| This file will contain the default settings for path-related
| information. These are overwritten when database settings are found.
|
| Settings inside of here are accessed through:
| $CI/$this->config->paths->route->type;
|
| Eg. $this->config->paths->server->themes;
|
| Some of these settings cannot be changed via the database.
| All of the settings here represent defaults.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|	--- BASE PATHS ---
|	['server']['root'] The absolute path to the forum. Given by FCPATH.
|	['public']['root'] The public URL to the forum. Given by base_url();
|	--- EXTENSION PATHS ---
|	['server']['themes'] The absolute path to the Themes dir.
|	['public']['themes'] The URL to the forum Themes dir.
|	['server']['plugins'] The absolute path to the Plugins dir.
|	['public']['plugins'] The URL to the plugins dir.
|	--- ATTACHMENT PATHS ---
|	['server']['avatars'] The absolute path to the Avatars dir.
|	['public']['avatars'] The URL to the Avatars dir.
|	['server']['attachments'] The absolute path to the Attachments dir.
|	['public']['attachments'] The url to the Attachments dir.
|	
*/
	// BASE PATHS
	$paths['server']['root'] 		= FCPATH;
	$paths['public']['root'] 		= BASE_URL;
	// EXTENSION PATHS
	$paths['server']['themes'] 		= FCPATH . "themes/";
	$paths['public']['themes'] 		= BASE_URL . "themes/";
	$paths['server']['plugins'] 	= FCPATH . "plugins/";
	$paths['public']['plugins'] 	= BASE_URL . "plugins/";
	// ATTACHMENT PATHS
	$paths['server']['avatars'] 	= FCPATH . "uploads/avatars/";
	$paths['public']['avatars'] 	= BASE_URL . "uploads/avatars/";
	$paths['server']['attachments'] = FCPATH . "uploads/attachments/";
	$paths['public']['attachments'] = BASE_URL . "uploads/attachments/";