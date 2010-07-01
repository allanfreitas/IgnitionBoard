<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * MySQLi driver for the database library.
 * This extension of the core provides functions for managing the database models/records, as well
 * as data storage for them.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class MySQLi_Models extends MySQLi_Component {
	/**
	 * Contains a register of table data. This register includes assigned mutators, fields, relations and
	 * other stuff.
	 */
	public $tables = array();
}
/* End of file database.php */
/* Location: ./apps/libraries/database.php */