<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Events which are called by the CI Hooks system are defined in here. These are fired after
 * the controller is initialised, but before methods are called (thus allowing get_instance() to be useful)
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class Post_Controller {
	/**
	 * Stores a reference to the global CI instance.
	 */
	public $CI;
	/**
	 * Writes the user's session data to the database after the script has executed. Prevents excessive
	 * writes to the database during execution.
	 */
	public final function uninitialize_board() {
		// Get an instance of CI.
		$this->CI =& get_instance();
		// Call the flush() function in the session lib.
		$this->CI->session->flush();
		// Cache output.
		$this->CI->cache->cache_output();
	}
}
