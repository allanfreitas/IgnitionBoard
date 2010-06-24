<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The Database library manages the loading of records and other critical functions.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class Database_Utility extends Database {
	/**
	 * Stores a reference to the global CI instance.
	 */
	public $CI;
	/**
	 * Stores a list of tables which are elligible for autoloading by the spl_autoload_register() callback.
	 */
	private $registered_tables = array();
	/**
	 * Constructor
	 */
	public function __construct() {
		// Inherit the database.
		parent::__construct();
		// Assign the _load_table() function as a candidate for an autoloader.
		spl_autoload_register(array($this, '_load_table'));
	}
	/**
	 * Checks the database configuration file for valid connection data.
	 */
	public final function check_config() {
		// Either return the database_check_config cache item or run _check_config();
		return $this->CI->cache->get('database_check_config', 'functions', array(&$this, "_check_config"), TRUE);
	}
	/**
	 * Cleans an input string
	 *
	 * @param <type> $string
	 * @param <type> $ruleset
	 */
	public final function clean($string, $ruleset = "STRICT") {
		$this->CI->error->show('function_not_yet_implemented', array(
			'%f' => __FUNCTION__,
			'%c' => __CLASS__
		));
	}
	/**
	 * Loads the contents of the models/database folder into the registered tables array.
	 */
	public final function load_tables() {
		// Load our master table class first.
		require APPPATH . "/models/database/model.php";
		// Initialise the master table class (this allows it to use the CI instance, needed later on).
		DB_Model_Abstract::initialize();
		// Go through the tables folder and pick things up from there.
		$files = array_diff(scandir(APPPATH . "/models/database/models/"), array(".", ".."));
		foreach($files as $file) {
			// The key is the basename without php, prefixed with DB_. It's the class name.
			$key = 'DB_' . ucfirst(basename($file, ".php"));
			// Add this file to the registered components array.
			$this->registered_tables[$key] = APPPATH . '/models/database/models/' . $file;
		}
	}
	/**
	 * Loads a specific table model and sets up the table's definition. If the table isn't already loaded,
	 * then this is called automatically.
	 *
	 * @param	string	$name	The name of the table to load, as a model name.
	 */
	private final function _load_table($name) {
		// Check our autoload registry.
		if(array_key_exists($name, $this->registered_tables)) {
			// It exists! Include it.
			require $this->registered_tables[$name];
			// Initialise it, pass a unique identifier along with it so it knows what to access.
			// The UID will be the class name.
			// Bugfix: You can't call static methods through variable functions in PHP 5.2. Use call_user_func();
			call_user_func(array($name, "initialize"), $name);
			// Set the table definition.
			call_user_func(array($name, "set_table_definition"));
			// And now wipe the UID.
			call_user_func(array($name, "uninitialize"));
			// May as well remove from the tables array.
			unset($this->registered_tables[$name]);
		} else {
			// Only error if this actually WAS a table. Tables are prefix with DB_, so base it on that.
			// Basically CI's stuff triggers this too, and we don't want it erroring out on that.
			if(substr($name, 0, 3) == "DB_") {
				// Not found. Error.
				$this->CI->error->show('loader_object_not_found', $name);
			}
		}
	}
	/**
	 * Loads all the unloaded but registered tables. Used in create/drop table functions. Not to be used
	 * elsewhere.
	 */
	public final function _load_all_tables() {
		// Go through the registered tables array.
		foreach($this->registered_tables as $class => $file) {
			// Load it.
			require $file;
			// Initialise it, pass a unique identifier along with it so it knows what to access.
			// The UID will be the class name.
			call_user_func(array($class, "initialize"), $class);
			// Set the table definition.
			call_user_func(array($class, "set_table_definition"));
			// And now wipe the UID.
			call_user_func(array($class, "uninitialize"));
		}
		// Empty the array.
		$this->registered_tables = array();
	}
	/**
	 * Does the actual 'checking' of the config file for valid connection settings.
	 */
	public final function _check_config() {
		// Load DB settings.
		require APPPATH . '/config/database.php';
		// Make sure the var exists.
		if(!isset($db)) {
			// Die!
			return FALSE;
		} else {
			// If any important stuff is empty, it's not set up.
			if($db['default']['username'] == '' || $db['default']['password'] == ''	||
			   $db['default']['database'] == '' || $db['default']['hostname'] == '' ) {
			return FALSE;
			} else {
				// Can we actually connect?
				// Attempt to make a connection.
				if(@mysql_connect($db['default']['hostname'], $db['default']['username'],
								  $db['default']['password'], TRUE) != FALSE) {
					// Good. Try switching to the db.
					if(@mysql_select_db($db['default']['database']) != FALSE) {
						// Good, they worked.
						mysql_close();
						return TRUE;
					} else {
						// Error!
						mysql_close();
						return FALSE;
					}
				} else {
					// Error!
					return FALSE;
				}
			}
		}
	}
}
/* End of file database.php */
/* Location: ./apps/libraries/database.php */