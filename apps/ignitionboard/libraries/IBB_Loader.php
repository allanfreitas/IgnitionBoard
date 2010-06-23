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
	public function IBB_Loader() {
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
				$this->library($name, NULL, $opt_name);
				// If there's a callback, fire it.
				if($callback != NULL) {
					// Fire!
					$this->CI->$opt_name->$callback();
				}
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
				$this->$type($name, NULL, $opt_name);
				// Fire the callback if one exists.
				if($callback != NULL) {
					$this->CI->$opt_name->$callback();
				}
			}
		}
	}
}
/* End of file ibb_loader.php */
/* Location: ./apps/libraries/ibb_loader.php */