<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Config Table model, for quick creation of the config table - for use with install script only.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class DB_Config extends DB_Model_Abstract {
	/**
	 * Called during table setup, this function should be used to set up the table's registered columns,
	 * relations, name, etc.
	 */
	public static final function set_table_definition() {
		// Set up columns.
		self::has_field('setting', 'varchar', '50', array('unique' => TRUE));
		self::has_field('value', 'varchar', 50);
		self::has_field('category', 'varchar', 50);
		self::has_field('subcategory', 'varchar', 50);
		// Set up table name.
		self::set_table_name('config');
	}
}

/* End of file: Record.php */
/* Location: ./apps/models/ */
