<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The Record class extends the model and acts as a doctrine-style Record for easy table creation and
 * data manipulation. Any models which also act as tables for the database should extend this class.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class DB_Topic extends DB_Model_Abstract {
	/**
	 * Called during table setup, this function should be used to set up the table's registered columns,
	 * relations, name, etc.
	 */
	public static final function set_table_definition() {
		// Set up columns.
		self::has_field('board_id', 'int', 9);
		self::has_field('post_id', 'int', 9, array('unique' => TRUE));
		self::has_field('user_id', 'int', 9);
		// Set up table name.
		self::set_table_name('topic');
		// Add relations.
		self::has_relation('board_id', 'id', 'board');
		self::has_relation('user_id', 'id', 'user');
	}
}

/* End of file: Record.php */
/* Location: ./apps/models/ */
