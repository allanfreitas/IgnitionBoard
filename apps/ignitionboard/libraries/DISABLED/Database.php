<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The Database library is an override of CI's default database implementation. It retains all the
 * crucial functionality, but it's a bit nippier and is less bloated in general.
 * Also integrates well with the cache.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class Database {
	/**
	 * Stores a reference to the global CI instance.
	 */
	public $CI;
	/**
	 * Stores our assessment of the database. Set to TRUE if the configuration is correct and tables exist.
	 */
	public $loaded = FALSE;
	/**
	 * Stores our database connection. Seriously!
	 */
	public $connection = NULL;
	/**
	 * Constructor
	 *
	 * Loads the child classes and sorts out the CI instance.
	 */
	public function __construct() {
		// Sort out the CI reference.
		$this->CI =& get_instance();
	}
	/**
	 * Initializes the database connection.
	 */
	public function initialize() {
		// Load the child classes.
		$this->CI->load->autoload('database/Database_Utility', 'library', 'db_utility');
		$this->CI->load->autoload('database/Database_Loader', 'library', 'db_loader');
		$this->{"loader"} =& $this->CI->db_loader; // Loading tasks.
		$this->{"utility"} =& $this->CI->db_utility; // Maintenance tasks.
		// Load query/result files.
		require_once APPPATH . 'libraries/database/DB_Query.php';
		// Try to connect to the database.
		if($this->connect() == TRUE) {
			// Load the database models.
			$this->loader->load_tables();
			// Does the config table EXEEEEST?
			if($this->utility->table_exists('config') == TRUE) {
				// Consider DB loaded and installed.
				$this->loaded = TRUE;
			}
		} else {
			die("Not Connected.");
		}
		// Check the database configuration.
		//if($this->utility->check_config()) {
			// It worked. Proski. Kick some booty.
			//$this->CI->load->database();
			// Load up the database models (sort of).
			//$this->utility->load_tables();
			// Do any tables exist right now?
			//if($this->CI->db->table_exists('config') == TRUE) {
				// They do, consider the database installed.
				//$this->loaded = TRUE;
			//}
		//}
	}
	/**
	 * Attempts to connect to the database specified in the configuration.
	 *
	 * @return bool TRUE if connected, FALSE otherwise.
	 */
	protected function connect() {
		// Is there already a connection?
		if($this->connection == NULL) {
			// Make a new connection, with settings from the config file.
			$config =& $this->CI->config->database;
			// Make connection. Hide errors.
			$this->connection = @new mysqli($config->hostname, $config->username, $config->password, $config->database);
			if($this->connection->connect_errno) {
				// Connection failed. Is this a database error or permission error?
				if($this->connection->connect_errno == 1049) {
					// Auto-create DB?
					if($config->auto_create == TRUE) {
						// Reconnect without database.
						$this->connection = @new mysqli($config->hostname, $config->username, $config->password);
						// Fix charset.
						$this->connection->set_charset($config->charset);
						// It's a database not-exist error. Let's try to fix this by making a database.
						$this->connection->real_query(
								"CREATE DATABASE IF NOT EXISTS `" . $this->connection->real_escape_string($config->database) . "`
								 CHARACTER SET = `" . $this->connection->real_escape_string($config->charset) . "`
								 COLLATE = `" . $this->connection->real_escape_string($config->collate) . "`");
						if($this->connection->error) {
							// Making the database didn't work either. Fail.
							return FALSE;
						} else {
							// Excellent, we're done in this case.
							return TRUE;
						}
					} else {
						// No auto-creation enabled.
						return FALSE;
					}
				} else {
					return FALSE;
				}
			} else {
				// No error, done. Set charset and head home.
				$this->connection->set_charset($config->charset);
				return TRUE;
			}
		} else {
			// There is one. Ping it.
			return $this->connection->ping();
		}
	}
}
/* End of file database.php */
/* Location: ./apps/libraries/database.php */