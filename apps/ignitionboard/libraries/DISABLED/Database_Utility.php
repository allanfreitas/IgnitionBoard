<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The Database utility library contains utility functions for the database, like table creation/deletion,
 * and other stuff.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class Database_Utility {
	/**
	 * Stores a reference to the global CI instance.
	 */
	public $CI;
	/**
	 * Stores a reference to the database connection.
	 */
	protected $connection;
	/**
	 * Contains a register of table data. This register includes assigned mutators, fields, relations and
	 * other stuff.
	 */
	public $tables = array();
	/**
	 * Constructor
	 */
	public function __construct() {
		// Sort out the CI reference.
		$this->CI =& get_instance();
		// Reference the connection object.
		$this->connection =& $this->CI->database->connection;
	}
	/**
	 * Checks the database to see if a table with the given name exists.
	 *
	 * @param string $table The name of the table to check for.
	 */
	public final function table_exists($table) {
		// Execute a quick query.
		$result = $this->connection->query("SHOW TABLES LIKE '" . $this->CI->config->database->prefix . "config'");
		// Any results?
		if($result->num_rows > 0) {
			// A table named config exists, coolio.
			return TRUE;
		} else {
			// Nope!
			return FALSE;
		}
	}
	/**
	 * Creates tables and places them in the forum database, after loading all the definitions.
	 * Does not overwrite existing data.
	 */
	public final function create_tables() {
		// Attempt to load all unloaded tables.
		$this->CI->db_loader->_load_all_tables();
		// Disable FK checks for this database. Allows us to insert tables in any order we like.
		$this->CI->db->query("SET FOREIGN_KEY_CHECKS = 0");
		// Right, go through the tables array.
		foreach($this->tables as $table => $data) {
			// Does this table have fields?
			if(count($data['fields']) > 0) {
				// Start generating a query.
				$query = "";
				// Put in the basics.
				$query .= "CREATE TABLE IF NOT EXISTS `" . $data['name'][1] . "` (";
				// Loop through our fields.
				foreach($data['fields'] as $field => $field_data) {
					// Set up the name.
					$query .= "`" . $field . "` ";
					// Add the type of column.
					$query .= $field_data['type'];
					// Do we have a size?
					if($field_data['size'] != NULL) {
						// Add in our size too.
						$query .= "(" . $field_data['size'] . ")";
					}
					// Set up our constraints.
					// Primary key?
					if($field_data['primary'] == TRUE)
						$query .= " PRIMARY KEY";
					// Unique field?
					if($field_data['unique'] == TRUE)
						$query .= " UNIQUE";
					// Unsigned field?
					if($field_data['unsigned'] == TRUE)
						$query .= " UNSIGNED";
					// To null or not to null?
					if($field_data['null'] == TRUE)
						$query .= " NULL";
					else
						$query .= " NOT NULL";
					// Default value?
					if(empty($field_data['default']) == FALSE)
						$query .= " DEFAULT = ' . " . $field_data['default'] . "'";
					// Auto Increment field?
					if($field_data['auto_increment'] == TRUE)
						$query .= " AUTO_INCREMENT";
					// Add comma.
					$query .= ", ";
				}
				// Go through relationships.
				foreach($data['relations'] as $relation_data) {
					// Give the constraint (relation) a name.
					// Default name is <table>_<foreign_key>_FK.
					$constraint = $data['name'][0] . "_" . $relation_data['local'] . "_FK";
					$query .= "CONSTRAINT `" . $constraint . "` ";
					// Add in the foreign key bit. It's the field in THIS table.
					$query .= "FOREIGN KEY (`" . $relation_data['local'] . "`) ";
					// Add in the other table to reference.
					$query .= "REFERENCES `" . $relation_data['foreign_table'] . "` (`" . $relation_data['foreign'] . "`) ";
					// Determine the cascade type and append it.
					switch($relation_data['cascade']) {
						case "ALL":
							// Cascade on UPDATE and DELETE.
							$query .= "ON UPDATE CASCADE ON DELETE CASCADE";
							break;
						case "UPDATE":
							// Cascade on UPDATE.
							$query .= "ON UPDATE CASCADE";
							break;
						case "DELETE":
							// Cascade on DELETE.
							$query .= "ON DELETE CASCADE";
							break;
						default:
							// None by default.
							break;
					}
					// Add comma.
					$query .= ", ";
				}
				// Remove the last comma.
				$query = substr($query, 0, -2);
				// Close off our query string.
				$query .= ") DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB\n";
				// Run it.
				$this->CI->db->query($query);
				// Done!
			}
		}
		// Re-enable FK checks for this database.
		$this->CI->db->query("SET FOREIGN_KEY_CHECKS = 1");
	}
	/**
	 * Drops all the forum tables in the database. There's no going back after this though bro!
	 */
	public final function drop_tables() {
		// Attempt to load all unloaded tables.
		$this->CI->db_loader->_load_all_tables();
		// Disable FK checks for this database. Allows us to delete tables in any order we like.
		$this->CI->db->query("SET FOREIGN_KEY_CHECKS = 0");
		// Right, go through the tables array.
		foreach($this->tables as $table => $data) {
			// Drop this table.
			$this->CI->db->query("DROP TABLE IF EXISTS `" . $data['name'][1] . "`");
		}
		// Re-enable FK checks for this database.
		$this->CI->db->query("SET FOREIGN_KEY_CHECKS = 1");
		// Make sure the board is now considered uninstalled.
		$this->CI->database->loaded = FALSE;
	}
	/**
	 * Truncates all of the data in the database. Cannot be reversed.
	 */
	public final function empty_tables() {
		// Attempt to load all unloaded tables.
		$this->CI->db_loader->_load_all_tables();
		// Right, go through the tables array.
		foreach($this->tables as $table => $data) {
			// Empty this table.
			$this->CI->db->query("TRUNCATE `" . $data['name'][1] . "`");
		}
	}
}
/* End of file database_utility.php */
/* Location: ./apps/libraries/database/database_utility.php */