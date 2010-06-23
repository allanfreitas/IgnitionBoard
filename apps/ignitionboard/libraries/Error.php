<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The Error library replaces a few error output functions and allows logging to a database table.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class Error {
	/**
	 * Stores a reference to the global CI object.
	 */
	public $CI;
	/**
	 * Constructor for the Error lib.
	 */
	function __construct() {
		// Get reference of the CI object.
		$this->CI =& get_instance();
		// Load the appropriate language file.
		$this->CI->lang->load('error', $this->CI->language->get());
	}
	/**
	 * Outputs an error message using CI's built in show_error() function. The difference is this takes an
	 * error code, which is language-agnostic. Eg. "function_not_yet_implemented".
	 *
	 * @param string		The error code to fetch.
	 * @param array/string	If a string, replaces any occurence of %s in the error with the value. Else, if an
	 *						array is passed then replaces any occurence of %key with the value in the array.
	 */
	function show($error, $vars = NULL) {
		// Get the error string to display.
		$str = $this->CI->lang->line('error_' . $error);
		// Did we get a result?
		if($str == "") {
			// Put in a generic error.
			$str = $this->CI->lang->line('error_generic');
		}
		// Parse it if needed.
		if(is_array($vars)) {
			// Parse as array. Go through things to replace.
			foreach($vars as $key => $val) {
				// Replace.
				$str = str_replace($key, $val, $str);
			}
		} else if($vars != NULL) {
			// Parse as string/int. Make it a string for safety.
			$vars = (string)$vars;
			// Replace any occurences of %s with the value.
			$str = str_replace("%s", $vars, $str);
		}
		// Show the error.
		show_error($str);
	}
	/**
	 * Outputs a message if we're in debug/dev mode.
	 */
	function debug($message, $indent = 0) {
		if($this->CI->uri->segment(1) == "dev" || get_cookie("dev") == TRUE) {
			echo str_repeat("\t", $indent) . $message . "<br />";
		}
	}
}
/* End of file error.php */
/* Location: ./apps/libraries/error.php */