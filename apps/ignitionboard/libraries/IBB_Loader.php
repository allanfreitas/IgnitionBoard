<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The Loader library extends the CI one to perform autoloading components (libs, etc.) based on the current
 * controller.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class IBB_Loader extends CI_Loader {
	/**
	 * Stores a reference to the global CI object.
	 */
	public $CI;
	/**
	 * Constructor
	 *
	 * Sets up CI instance here.
	 */
	public function __construct() {
		// Fire parent constructor.
		parent::CI_Loader();
		// Set it up.
		$this->CI =& get_instance();
	}
	/**
	 * Adds an library to the libs array, causing it to load automatically.
	 *
	 * @param string $name		The name of the library to load.
	 * @param string $opt_name	Optional name for this library, to be used when accessing via this $this/$CI.
	 * @param array $controller	The name of the controller(s) to load this for. Does not load if not needed.
	 * @param string $callback	A function to run within the library when it has been loaded.
	 */
	public final function add_library($name, $opt_name = "", $controller = NULL, $callback = NULL) {
		// Sort out the optional name.
		$opt_name = empty($opt_name) ? (string)$name : (string)$opt_name;
		// Only bother if needed.
		if(is_array($controller) || $controller == NULL) {
			// Is this controller in the array, or were no controllers specified?
			if(($controller == NULL) || in_array($this->CI->router->class, $controller)) {
				// Load it.
				$this->CI->{$opt_name} = new IBB_Loader_Delegate($name, "library", $callback);
			}
		}
	}
	/**
	 * Adds a dependancy (helper/model) to the deps array, to be loaded before all libraries.
	 *
	 * @param string $name		Name of the dependancy, eg. url.
	 * @param string $type		Type of dependancy, helper or model are accepted.
	 * @param string $opt_name	Optional name for this dependancy, used when accessing via $this/$CI.
	 * @param array $controller	The name of the controller(s) to load this for. Does not load if not needed.
	 * @param string $callback	A function to run when the dependancy has been loaded.
	 */
	public final function add_dependancy($name, $type, $opt_name = "", $controller = NULL, $callback = NULL) {
		// Sort out the optional name.
		$opt_name = empty($opt_name) ? (string)$name : (string)$opt_name;
		// Only bother if needed.
		if(is_array($controller) || $controller == NULL) {
			// Is this controller in the array, or were no controllers specified?
			if(($controller == NULL) || in_array($this->CI->router->class, $controller)) {
				// Load it.
				if($type != "helper") {
					$this->CI->{$opt_name} = new IBB_Loader_Delegate($name, $type, $callback);
				} else {
					$this->$type($name, NULL, $opt_name);
				}
			}
		}
	}
}
/**
 * Acts as an intermediary between components (libs/deps) and the CI object itself.
 * Allows us to load components on demand, rather than loading things we might never need.
 */
class IBB_Loader_Delegate {
	/**
	 * Stores the name of the component this delegate works for.
	 */
	protected $_delegate_name = NULL;
	/**
	 * Stores the loaded component inside of this delegate.
	 */
	protected $_delegate_component = NULL;
	/**
	 * Stores the type of component this delegate works for.
	 */
	protected $_delegate_type = NULL;
	/**
	 * Stores the name of a function to call when this component is initialized.
	 */
	protected $_delegate_callback = NULL;
	/**
	 * Constructor
	 * 
	 * Sets up the delegate class.
	 *
	 * @param string $name Name of the file to load.
	 * @param string $type Type of component to load as.
	 * @param string $callback Function to run within the component after it is loaded. Optional.
	 */
	public function __construct($name, $type, $callback) {
		// Set properties.
		$this->_delegate_name = $name;
		$this->_delegate_type = $type;
		$this->_delegate_callback = $callback;
	}
	/**
	 * Initializes the assigned component and fires any callbacks. Only happens once.
	 */
	public final function initialize_component() {
		// How's the component faring?
		if($this->_delegate_component == NULL) {
			// Get a reference of the CI super-object.
			$CI =& get_instance();
			// Oh dear oh dear. Load it.
			$name = $this->_delegate_name;
			$type = $this->_delegate_type;
			$callback = $this->_delegate_callback;
			// Go.
			$CI->load->$type($name, NULL, "DELEGATE_" . $name);
			$this->_delegate_component =& $CI->{"DELEGATE_" . $name};
			// Got a callback?
			if($callback != NULL) {
				// Fire it.
				$this->_delegate_component->$callback();
			}
		}
	}
	/**
	 * -------------------------------------------------------------------------------------------------------
	 * PROPERTY OVERLOADS
	 * -------------------------------------------------------------------------------------------------------	 *
	 */
	/**
	 * Handles all the intermediary stuff. If a property of the assigned component is accessed, the name of
	 * the property is passed to this function.
	 * From here we check if the component needs loading, fire callbacks and then pass the property name on.
	 *
	 * @param string The name of the property to access.
	 */
	public function __get($name) {
		// Check initialization state.
		$this->initialize_component();
		// Pass the call on.
		return $this->_delegate_component->$name;
	}
	/**
	 * Handles all the intermediary stuff. If a property of the assigned component is accessed, the name of
	 * the property is passed to this function.
	 * From here we check if the component needs loading, fire callbacks and then pass the property name and
	 * value on.
	 *
	 * @param string The name of the property to access.
	 * @param string The value to set.
	 */
	public function __set($name, $value) {
		// Check initialization state.
		$this->initialize_component();
		// Pass the call on.
		$this->_delegate_component->$name = $value;
	}
	/**
	 * Handles all the intermediary stuff. If a property of the assigned component is accessed, the name of
	 * the property is passed to this function.
	 * From here we check if the component needs loading, fire callbacks and then check the value ourselves.
	 *
	 * @param string The name of the property to access.
	 */
	public function __isset($name) {
		// Check initialization state.
		$this->initialize_component();
		// Pass the call on.
		return isset($this->_delegate_component->$name);
	}
	/**
	 * Handles all the intermediary stuff. If a property of the assigned component is accessed, the name of
	 * the property is passed to this function.
	 * From here we check if the component needs loading, fire callbacks and then unset the property.
	 *
	 * @param string The name of the property to access.
	 */
	public function __unset($name) {
		// Check initialization state.
		$this->initialize_component();
		// Pass the call on.
		unset($this->_delegate_component->$name);
	}
	/**
	 * -------------------------------------------------------------------------------------------------------
	 * FUNCTION OVERLOADS
	 * -------------------------------------------------------------------------------------------------------
	 */
	/**
	 * Handles more intermediary stuff. If a function of the assigned component is accessed, the name of the
	 * function is passed on to the component itself after we load it.
	 *
	 * @param string $name The name of the function to call.
	 * @param $arguments
	 */
	public function  __call($name, $arguments) {
		// Check initialization state.
		$this->initialize_component();
		// Pass call on.
		return call_user_func_array(array(&$this->_delegate_component, $name), $arguments);
	}
}
/* End of file ibb_loader.php */
/* Location: ./apps/libraries/ibb_loader.php */