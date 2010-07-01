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
	 * Adds a component to the super object via a loader delegate, causing it to load automatically.
	 *
	 * @param string $name			The name of the library to load.
	 * @param string $type			Type of dependancy, helper, library or model are accepted.
	 * @param string $opt_name		Optional name for this library, to be used when accessing via $this/$CI.
	 * @param string $callback		A function to run within the library when it has been loaded.
	 * @param array	 $controller	The names of the controllers to load this for. Doesn't load if not needed.
	 */
	public final function autoload($name, $type, $opt_name = "", $callback = NULL, $controller = NULL) {
		// Sort out the optional name.
		$opt_name = empty($opt_name) ? (string)$name : (string)$opt_name;
		// Only bother if needed. Do we have a list of controllers to load this for?
		// If this isn't in the list, don't load it.
		if((is_array($controller)) && in_array($this->CI->router->class, $controller) == FALSE) {
			return FALSE;
		}
		// Load it.
		if($type != "helper") {
			// Make a loader delegate for this object.
			$this->CI->{$opt_name} = new IBB_Loader_Delegate($name, $opt_name, $type, $callback);
		} else {
			// Load helpers immediately, as they don't go into the CI instance.
			$this->helper($name);
		}
	}
}
/**
 * Acts as an intermediary between components (libs/deps) and the CI object itself.
 * Allows us to load components on demand, rather than loading things we might never need.
 */
class IBB_Loader_Delegate extends IBB_Delegate {
	/**
	 * Stores the type of component this delegate works for.
	 */
	protected $_delegate_type = NULL;
	/**
	 * Constructor
	 * 
	 * Sets up the delegate class.
	 *
	 * @param string $name Name of the file to load.
	 * @param string $opt_name Optional name to pass along.
	 * @param string $type Type of component to load as.
	 * @param string $callback Function to run within the component after it is loaded. Optional.
	 */
	public function __construct($name, $opt_name, $type, $callback) {
		// Call parent constructor.
		parent::__construct($name, $opt_name, $callback);
		// Set properties.
		$this->_delegate_type = $type;
	}
	/**
	 * Initializes the assigned component and fires any callbacks. Only happens once.
	 */
	public final function _delegate_initialize() {
		// How's the component faring?
		if($this->_delegate_object == NULL) {
			// Get a reference of the CI super-object.
			$CI =& get_instance();
			// Oh dear oh dear. Load it.
			$name = $this->_delegate_name;
			$type = $this->_delegate_type;
			$callback = $this->_delegate_initialize_function;
			// Load. We pass along the name which this delegate is sitting as, so that it overwrites itself.
			$CI->load->$type($name, NULL, $this->_delegate_opt_name);
			// Make a shortcut to this object.
			$this->_delegate_object =& $CI->{$this->_delegate_opt_name};
			// Got a callback?
			if($callback != NULL) {
				// Fire it.
				if(is_array($callback)) {
					// You can pass a two parameter array with $this->etc. as the first param, and func name
					// as second part.
					$callback[0]->{$callback[1]}();
				} else {
					$this->_delegate_object->$callback();
				}
			}
		}
	}
}
/**
 * Abstracr class for all delegate classes. Defines methods, properties, and other stuff.
 */
abstract class IBB_Delegate {
	/**
	 * Stores the name of the component this delegate works for.
	 */
	protected $_delegate_name = NULL;
	/**
	 * Stores the name of the component this delegate works for.
	 */
	protected $_delegate_opt_name = NULL;
	/**
	 * Stores the loaded component inside of this delegate.
	 */
	protected $_delegate_object = NULL;
	/**
	 * Stores the name of a function to call when this component is initialized.
	 */
	protected $_delegate_initialize_function = NULL;
	/**
	 * Constructor
	 * 
	 * Sets up the delegate class.
	 *
	 * @param string $name Name of the file to load.
	 * @param string $opt_name Optional name to pass along.
	 * @param string $type Type of component to load as.
	 */
	public function  __construct($name, $opt_name, $callback) {
		// Set properties
		$this->_delegate_name = $name;
		$this->_delegate_opt_name = $opt_name;
		$this->_delegate_initialize_function = $callback;
	}
	/**
	 * Initializes the assigned component and fires any callbacks. Only happens once.
	 */
	abstract public function _delegate_initialize();
	/**
	 * -------------------------------------------------------------------------------------------------------
	 * PROPERTY OVERLOADS
	 * -------------------------------------------------------------------------------------------------------
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
		$this->_delegate_initialize();
		// Pass the call on.
		return $this->_delegate_object->$name;
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
		$this->_delegate_initialize();
		// Pass the call on.
		$this->_delegate_object->$name = $value;
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
		$this->_delegate_initialize();
		// Pass the call on.
		return isset($this->_delegate_object->$name);
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
		$this->_delegate_initialize();
		// Pass the call on.
		unset($this->_delegate_object->$name);
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
		$this->_delegate_initialize();
		// Pass call on.
		return call_user_func_array(array(&$this->_delegate_object, $name), $arguments);
	}
}
/**
 * Assigns all the publicly accessible properties/methods from the CI super object to the given object,
 * by reference.
 *
 * @param object The object to assign properties/methods to.
 */
function _assign_instance_properties(&$object) {
	// Get the CI instance.
	$CI =& get_instance();
	// Get all the object variables of the instance.
	foreach(get_object_vars($CI) as $name => $value) {
		// Assign them by ref to the object.
		if(property_exists($object, $name) == FALSE) {
			// Write it with a normal name.
			$object->{$name} =& $CI->{$name};
		} else {
			// Something with this name exists already, so prefix it with ci_.
			$object->{'ci_' . $name} =& $CI->{$name};
		}
	}
}
/* End of file ibb_loader.php */
/* Location: ./apps/libraries/ibb_loader.php */