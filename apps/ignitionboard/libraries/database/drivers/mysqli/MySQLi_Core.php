<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * MySQLi driver for the database library.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class MySQLi_Core implements Database_Driver {
	/**
	 * Stores our database connection. Seriously!
	 */
	protected $connection = NULL;
	/**
	 * Stores our database connection name, or the name of the driver instance in the database_core class.
	 * It's the name used to access this driver via $this->db->{name}. Also the name of this connection
	 * in the config file.
	 */
	protected $name = NULL;
	/**
	 * Stores a reference to our connections' configuration.
	 * This prevents normal quick access to the config library, so instead use $this->ci_config to access
	 * said library.
	 */
	protected $config = NULL;
	/**
	 * Stores a list of loaded components, which we can query as needed.
	 */
	private $components = array();
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
	 * Initializes the driver and connects to the database.
	 */
	public function initialize($connection_name) {
		// Set our connection name.
		$this->name = $connection_name;
		// Try to connect to the database.
		if($this->connect() == TRUE) {
			// Load the sub-classes of the core.
			$this->_load_classes();
			// Load the database models.
			//$this->utility->load_tables();
		} else {
			die("Not Connected.");
		}
	}
	/**
	 * Loads extensions and sub-classes into the driver object.
	 */
	private final function _load_classes() {
		// Here's a list of components to load.
		$components = array_diff(scandir(APPPATH . 'libraries/database/drivers/mysqli/components/'), array('.', '..'));
		foreach($components as $component) {
			// Load each component.
			require_once APPPATH . 'libraries/database/drivers/mysqli/components/' . $component;
			// Load it.
			$component_class = basename($component, '.php');
			$this->components[$component] = new $component_class($this->name);
		}
	}
	/**
	 * Attempts to connect to the database specified in the configuration.
	 *
	 * @return bool TRUE if connected, FALSE otherwise.
	 */
	public final function connect() {
		// Is there already a connection?
		if($this->connection == NULL) {
			// Make a new connection, with settings from the config file.
			$config =& $this->ci_config->database->{$this->name};
			// Store a copy of the configuration globally.
			$this->config =& $config;
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
	/**
	 * Closes any active connections. Called automatically at the end of script execution.
	 */
	public final function close() {
		// Close the connection if it exists.
		if($this->connection != NULL)
			$this->connection->close();
	}
	/**
	 * Reroutes any function calls to their appropriate locations. Prevents needing to specify a component
	 * name when calling a function like table_exists();
	 *
	 * @param string $name The name of the function to call.
	 * @param array $arguments The arguments to pass to said function.
	 */
	public function  __call($name, $arguments) {
		// Go through our components.
		foreach($this->components as $component) {
			// Does a function exist in this component?
			if(method_exists($component, $name) == TRUE) {
				// Call it and quit.
				call_user_func_array(array($component, $name), $arguments);
				break;
			}
		}
	}
}
/**
 * The component class is extended by driver components. Sets some stuff up initially.
 */
class MySQLi_Component extends MySQLi_Core {
	/**
	 * Constructor. Sorts things out.
	 */
	public function __construct($name) {
		// Reference the properties of the CI super object.
		_assign_instance_properties($this);
		// Reference the connection.
		$this->connection =& $this->db->{$name}->connection;
		// Sort out the rest of the properties.
		$this->name = $name;
		$this->config =& $this->db->{$name}->config;
	}
}
/* End of file database.php */
/* Location: ./apps/libraries/database.php */