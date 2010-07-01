<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * MySQLi driver for the database library.
 * This extension of the core provides common functions which can be accessed at runtime.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class MySQLi_Common extends MySQLi_Component {
	/**
	 * Checks to see if a table exists in the database by performing a really quick query.
	 * Prepends the table prefix automatically.
	 *
	 * @param string $table The table name to check for.
	 */
	public function table_exists($table) {
		// Go!
		$query = $this->connection->query("SHOW TABLES LIKE '" . $this->config->prefix . "config'");
		// If we had a result, TRUE. Else, FALSE.
		if($query->num_rows > 0) { return TRUE; } else { return FALSE; }
	}
}
/* End of file database.php */
/* Location: ./apps/libraries/database.php */