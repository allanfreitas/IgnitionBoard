<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The Database library is an override of CI's default database implementation. It retains all the
 * crucial functionality, but it's a bit nippier and is less bloated in general.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class Database_Core {
	/**
	 * Stores a reference to the active primary driver class.
	 */
	public $driver = NULL;
	/**
	 * Constructor
	 *
	 * Sorts out the CI instance.
	 */
	public function __construct() {
		// Reference the properties of the CI super object.
		_assign_instance_properties($this);
	}
	/**
	 * Loads the appropriate driver and initializes it for the database.
	 */
	public final function initialize() {
		// Load the primary connection.
		$primary =& $this->config->database->connection;
		// Go through all of the possible connections.
		foreach(get_object_vars($this->config->database) as $name => $value) {
			// Is the value a config_object? If so, it means it was an array originally.
			if($value instanceof Config_Object) {
				// Are we loading all or just primary?
				if($this->config->database->load_all == TRUE || $name == $primary) {
					// It's likely a driver specification, go.
					$this->{$name} = $this->load_driver($name);
					// Initialize it.
					$this->{$name}->initialize($name);
					// Is this also the primary connection?
					if($name == $primary) {
						// Link it to driver.
						$this->driver =& $this->{$name};
					}
				}
			}
		}
	}
	/**
	 * Loads a driver up and returns the object.
	 *
	 * @param string $driver The driver to load.
	 */
	protected final function load_driver($driver) {
		// Get the connection details.
		$driver =& $this->config->database->{$driver}->driver;
		// Go through possible drivers.
		$files = array_diff(scandir(APPPATH . 'libraries/database/drivers/'), array('.', '..'));
		foreach($files as $file) {
			// Does this driver match the found directory?
			if($file == $driver) {
				// Load it.
				return $this->_load_driver($driver);
			}
		}
		// We got this far, error out as the driver doesn't exist.
		$this->error->show('database_driver_not_found', $driver);
	}
	/**
	 * Loads the required files for the driver and initialises a new instance of the driver class.
	 *
	 * @param object $connection The connection data to use.
	 */
	protected final function _load_driver($driver) {
		// Make sure we correct the capitalisation of the driver name (seriously!).
		switch($driver) {
			case 'mysqli': $driver_class = 'MySQLi_Core'; break;
			case 'mysql': $driver_class = 'MySQL_Core'; break;
			default: $driver_class = ""; break;
		}
		// Kick some ass. Do we need to load the core driver?
		if(class_exists($driver_class) == FALSE) {
			// Load it.
			require APPPATH . 'libraries/database/drivers/' . strtolower($driver) . '/' . $driver_class . '.php';
		}
		// Initialise it.
		return new $driver_class();
	}
	/**
	 * Reroutes function calls to the appropriate driver class.
	 *
	 * @param string $name The function to call.
	 * @param array $arguments The arguments to supply to the driver.
	 */
	public function  __call($name, $arguments) {
		// Route it if we can.
		if($this->driver != NULL) {
			// Call the function with this name in the driver library.
			call_user_func_array(array($this->driver, $name), $arguments);
		} else {
			// Nothing exists.
			return NULL;
		}
	}
}
/**
 * Interface defining what methods drivers must implement in their core file.
 */
interface Database_Driver {
	/**
	 * Attempts to connect to the database specified in the configuration.
	 * If a connection has already been opened, this function pings it and re-connects.
	 *
	 * @return bool TRUE if connected, FALSE otherwise.
	 */
	public function connect();
	/**
	 * Closes any active connections. Called automatically at the end of script execution.
	 */
	public function close();
}
/* End of file database.php */
/* Location: ./apps/libraries/database.php */