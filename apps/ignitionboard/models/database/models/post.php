<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Post Record model, for quick creation of the post table - for use with install script only.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class DB_Post extends DB_Model_Abstract {
	/**
	 * Called during table setup, this function should be used to set up the table's registered columns,
	 * relations, name, etc.
	 */
	public static final function set_table_definition() {
		// Set up columns.
		self::has_field('topic_id', 'int', 9);
		self::has_field('board_id', 'int', 9);
		self::has_field('user_id', 'int', 9);
		self::has_field('subject', 'varchar', 50);
		self::has_field('post', 'text', 65535);
		self::has_field('hidden', 'tinyint', 1);
		// Timestamp fields.
		self::has_timestamps(TRUE);
		// Set up table name.
		self::set_table_name('post');
		// Add relations.
		self::has_relation('topic_id', 'id', 'topic');
		self::has_relation('board_id', 'id', 'board');
		self::has_relation('user_id', 'id', 'user');
	}
}

/* End of file: Record.php */
/* Location: ./apps/models/ */
