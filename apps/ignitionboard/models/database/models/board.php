<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Board Record model, for quick creation of the board table - for use with install script only.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class DB_Board extends DB_Model_Abstract {
	/**
	 * Called during table setup, this function should be used to set up the table's registered columns,
	 * relations, name, etc.
	 */
	public static final function set_table_definition() {
		// Set up columns.
		self::has_field('category_id', 'int', 9);
		self::has_field('name', 'varchar', 50, array('unique' => TRUE));
		// Set up table name.
		self::set_table_name('board');
		// Add relations.
		self::has_relation('category_id', 'id', 'category');
		// Add mutators.
		self::has_mutator('name', '_salt_name');
		self::has_mutator('name', '_desalt_name', 'GET');
	}
	/**
	 * Test SET mutator.
	 */
	public final function _salt_name($value) {
		return "SALTLOL_" . $value;
	}
	/**
	 * Test GET mutator.
	 */
	public final function _desalt_name($value) {
		return substr($value, 8);
	}
}

/* End of file: Record.php */
/* Location: ./apps/models/ */
