<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Profile Record model, for quick creation of the profile table - for use with install script only.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class DB_Profile extends DB_Model_Abstract {
	/**
	 * Called during table setup, this function should be used to set up the table's registered columns,
	 * relations, name, etc.
	 */
	public static final function set_table_definition() {
		// Set up columns.
		self::has_field('user_id', 'int', 9);
		self::has_field('first_name', 'varchar', 25, array('null' => TRUE));
		self::has_field('last_name', 'varchar', 25, array('null' => TRUE));
		self::has_field('dob', 'date', NULL);
		self::has_field('avatar', 'varchar', 120);
		// Set up table name.
		self::set_table_name('profile');
		// Add relations.
		self::has_relation('user_id', 'id', 'user');
	}
}
