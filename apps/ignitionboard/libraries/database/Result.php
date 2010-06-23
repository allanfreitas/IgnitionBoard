<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The Database library manages the loading of records and other critical functions.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class Database_Result extends Database {
	/**
	 * Stores a reference to the global CI instance.
	 */
	public $CI;
	/**
	 * Constructor
	 */
	public function Database_Result() {
		// Inherit the database.
		parent::Database();
	}
}
/* End of file database.php */
/* Location: ./apps/libraries/database.php */