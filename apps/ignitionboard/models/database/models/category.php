<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Category Record model, for quick creation of the category table - for use with install script only.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class DB_Category extends DB_Model_Abstract {
	/**
	 * Called during table setup, this function should be used to set up the table's registered columns,
	 * relations, name, etc.
	 */
	public static final function set_table_definition() {
		// Set up columns.
		self::has_field('parent_id', 'int', 9, array('null' => TRUE, 'default' => NULL));
		self::has_field('name', 'varchar', 50, array('unique' => TRUE));
		self::has_field('description', 'varchar', 150);
		// Set up table name.
		self::set_table_name('category');
		// Set up table relations.
		self::has_relation('parent_id', 'id', 'category');
		self::has_relation('id', 'category_id', 'board', TRUE);
	}
}

/* End of file: Record.php */
/* Location: ./apps/models/ */
