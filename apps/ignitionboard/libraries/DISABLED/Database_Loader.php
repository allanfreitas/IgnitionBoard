<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The Database loader library handles the loading of things like table models.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class Database_Loader {
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
		// Sort out the CI reference.
		$this->CI =& get_instance();
		// Assign the _load_table() function as a candidate for an autoloader.
		spl_autoload_register(array($this, '_load_table'));
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
}
/* End of file database_loader.php */
/* Location: ./apps/libraries/database/database_loader.php */