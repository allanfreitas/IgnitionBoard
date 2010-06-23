<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Group_Permission Record model, for quick creation of the group table - for use with install script only.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class DB_Group_Permission extends DB_Model_Abstract {
	/**
	 * Called during table setup, this function should be used to set up the table's registered columns,
	 * relations, name, etc.
	 */
	public static final function set_table_definition() {
		// Set up columns.
		self::has_field('group_id', 'int', 9);
		self::has_field('permission_id', 'int', 9);
		// Set up table name.
		self::set_table_name('group_permission');
		// Set up table relations.
		self::has_relation('group_id', 'id', 'group');
		self::has_relation('permission_id', 'id', 'permission');
	}
}
