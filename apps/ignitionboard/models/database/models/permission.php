<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Permission Record model, for quick creation of the permission table - for use with install script only.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class DB_Permission extends DB_Model_Abstract {
	/**
	 * Called during table setup, this function should be used to set up the table's registered columns,
	 * relations, name, etc.
	 */
	public static final function set_table_definition() {
		// Set up columns.
		self::has_field('name', 'varchar', 50, array('unique' => TRUE));
		// Set up table name.
		self::set_table_name('permission');
	}
}
