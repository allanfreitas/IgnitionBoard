<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The Database library manages the loading of records and other critical functions.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class Database_Result {
	/**
	 * Stores a reference to the global CI instance.
	 */
	public $CI;
	/**
	 * Constructor
	 */
	public function __construct() {
		// Sort out the CI reference.
		$this->CI =& get_instance();
	}
}
/* End of file database_result.php */
/* Location: ./apps/libraries/database/database_result.php */