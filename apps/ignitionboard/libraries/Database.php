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
	 * Stores our initialize state. This is static so once we're initialized once in ANY instance, we're done.
	 */
	public static $initialized = FALSE;
	/**
	 * Stores our assessment of the database. Set to TRUE if the configuration is correct and tables exist.
	 */
	public $loaded = FALSE;
	/**
	 * Constructor
	 *
	 * Loads the child classes and sorts out the CI instance.
	 */
	public function __construct() {
		// Sort out the CI reference.
		$this->CI =& get_instance();
		// Initialize if we should. Prevents us recursively loading child components (stupid thing).
		if(self::$initialized == FALSE) {
			// Now we have!
			self::$initialized = TRUE;
			// Load the child classes.
			require_once APPPATH . "libraries/database/Utility.php";
			require_once APPPATH . "libraries/database/Maintenance.php";
			require_once APPPATH . "libraries/database/Result.php";
			$this->{"utility"} = new Database_Utility();  // Utility (create tables, etc.)
			$this->{"maintenance"} = new Database_Maintenance(); // Maintenance tasks (dumps, etc.)
			$this->{"result"} = new Database_Result(); // Handles result caching.
			// Initialize the connection.
			$this->initialize();
		}
	}
	/**
	 * Initializes the database connection.
	 */
	protected function initialize() {
		// Check the database configuration.
		if($this->utility->check_config()) {
			// It worked. Proski. Kick some booty.
			$this->CI->load->database();
			// Load up the database models (sort of).
			$this->utility->load_tables();
			// Do any tables exist right now?
			if($this->CI->db->table_exists('config') == TRUE) {
				// They do, consider the database installed.
				$this->loaded = TRUE;
			}
		}
	}
}
/* End of file database.php */
/* Location: ./apps/libraries/database.php */