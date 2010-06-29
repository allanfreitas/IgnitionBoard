<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The Config lib contains functions for loading/updating the configuration as well as some basic
 * error handling.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class IBB_Config extends CI_Config {
	/**
	 * Stores a reference to the global CI object.
	 */
	public $CI;
	/**
	 * The Load function reads data from the configuration table (assuming it exists) and populates
	 * this class with properties based on the settings found.
	 *
	 * If the table does not exist, the settings found in the files of the config folder are used.
	 */
	public final function initialize() {
		// Set up the CI reference.
		$this->CI =& get_instance();
		// Force defaults to be loaded.
		$this->_init_defaults();
		// If the database has been fully loaded, get the config from the database.
		if($this->CI->database->loaded == TRUE) {
			// Kay, now that's done...Let's populate baby! Get all config settings.
			$config = $this->CI->db->get('config');
			// Loopies.
			foreach($config->result() as $setting) {
				// Assign each setting as a new property of this model.
				// Add this setting to the array.
				if($setting->subcategory != "") {
					$this->{$setting->category}->{$setting->subcategory}->{$setting->setting} = $setting->value;
				} else {
					$this->{$setting->category}->{$setting->setting} = $setting->value;
				}
				// Done.
			}
		}
	}
	/**
	 * Populates the configuration properties with those found in the filesystem config files,
	 * located at "config/settings/".
	 *
	 * Only used if the config table doesn't exist, as not all settings are found here.
	 * Slow method, only used if needed.
	 */
	private final function _init_defaults() {
		// Load the config files.
		require APPPATH . '/config/settings/paths.php';
		require APPPATH . '/config/settings/board.php';
		// Translate arrays into properties. Set the resultant to their respective properties.
		$this->paths = new ConfigObject($paths);
		$this->board = new ConfigObject($board);
	}
}
/**
 * A ConfigObject is a class to allow for quick conversion of multidimensional arrays
 * into objects.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class ConfigObject {
	/**
	 * Override the constructor of the object and recursively loop to create enough objects
	 * to cover a multidimensional array.
	 *
	 * @param $array The array to convert into an object.
	 */
	function __construct(Array $array) {
		// Loop over the given array.
		foreach($array as $key => $val) {
			// If the value is an array, create a new self.
			if(is_array($val)) {
				// Make a new one of us.
				$val = new self($val);
			}
			// Once we have the object sorted, set it as a property of this one.
			$this->{$key} = $val;
		}
	}
}
/* End of file ibb_config.php */
/* Location: ./apps/libraries/ibb_config.php */