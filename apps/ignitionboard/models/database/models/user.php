<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * User Record model, for quick creation of the user table - for use with install script only.
 * Named DB_User to prevent a problem with the normal user model.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class DB_User extends DB_Model_Abstract {
	/**
	 * Called during table setup, this function should be used to set up the table's registered columns,
	 * relations, name, etc.
	 */
	public static final function set_table_definition() {
		// Set up columns.
		self::has_field('email', 'varchar', 60, array('unique' => TRUE));
		self::has_field('password', 'varchar', 40);
		self::has_field('display_name', 'varchar', 25, array('unique' => TRUE));
		// Set up table name.
		self::set_table_name('user');
	}
}
