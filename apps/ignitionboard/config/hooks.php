<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/

/**
 * Hooks required for IBB forum functionality, do not modify!
 *
 * Hook #1: Initialise the board, load libs/models/etc.
 */
$hook['post_controller_constructor'] = array(
	'class' => 'Post_Controller_Constructor',
	'function' => 'initialize_board',
	'filename' => 'post_controller_constructor.php',
	'filepath' => 'hooks'
);
/**
 * Hook #2: Write user/session data to database at end of execution.
 */
$hook['post_controller'] = array(
	'class' => 'Post_Controller',
	'function' => 'flush_session',
	'filename' => 'post_controller.php',
	'filepath' => 'hooks'
);

/* End of file hooks.php */
/* Location: ./system/application/config/hooks.php */