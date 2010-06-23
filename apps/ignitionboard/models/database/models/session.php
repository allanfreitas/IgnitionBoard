<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Session Record model, for quick creation of the session table - for use with install script only.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class DB_Session extends DB_Model_Abstract {
	/**
	 * Called during table setup, this function should be used to set up the table's registered columns,
	 * relations, name, etc.
	 */
	public static final function set_table_definition() {
		// Set up columns.
		self::has_field('session_id', 'char', 40, array('unique' => TRUE, 'primary_key' => TRUE)); // ID.
		self::has_field('ip_address', 'varchar', 16);
		self::has_field('user_agent', 'varchar', 50);
		self::has_field('last_activity', 'varchar', 10);
		self::has_field('token', 'char', 40);
		self::has_field('data', 'varchar', 2500, array('null' => TRUE));
		// Set up table name.
		self::set_table_name('session');
		// Disable identifiers (session_id replaces it).
		self::has_identifer(FALSE);
	}
}

/* End of file: Session.php */
/* Location: ./apps/models/records/ */
