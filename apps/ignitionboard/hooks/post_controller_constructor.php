<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Events which are called by the CI Hooks system are defined in here. These are fired after
 * the controller is initialised, but before methods are called (thus allowing get_instance() to be useful)
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class Post_Controller_Constructor {
	/**
	 * Stores a reference to the global CI instance.
	 */
	public $CI;
	/**
	 * Initialises the config library, and replaces CI's autoload feature which loads things for all
	 * controllers, whereas this does it on a per-controller basis. Handles redirections, etc.
	 * All in all, this initializes the board.
	 */
	public final function initialize_board() {
		// Get an instance of CI.
		$this->CI =& get_instance();
		// Load components.
		$this->_load_components();
		// Initialize the configuration.
		$this->CI->config->initialize();
		// Enable profiler?
		$this->CI->output->enable_profiler($this->CI->config->board->core->profiler);
		// Force initialization of the session library.
		$this->CI->session->initialize();
		// Is there a cache file we can return?
		if($this->CI->cache->cache_request() == FALSE) {
			// No cache.
			// Set the view path so that it's in the public templates directory.
			$this->CI->load->_ci_view_path = $this->CI->config->paths->server->themes;
			// Check if the board isn't installed, and that we're NOT in the install dir.
			if($this->CI->config->board->core->installed == FALSE && $this->CI->router->class != "install") {
				// Not installed, redirect to /install.
				//redirect('/install');
			}
		} else {
			// We can! Coolio.
			$this->CI->output->set_output($this->CI->cache->cache_retrieve());
			// Send output.
			$this->CI->output->_display();
			// Kill script.
			exit;
		}
	}
	/**
	 * Registers components for autoloading.
	 */
	private final function _load_components() {
		// Load components.
		$this->CI->load->autoload('url', 'helper');
		$this->CI->load->autoload('cookie', 'helper');
		//$this->CI->load->autoload('form', 'helper');
		$this->CI->load->autoload('language', 'library');
		$this->CI->load->autoload('error', 'library');
		$this->CI->load->autoload('cache', 'library', '', 'initialize');
		$this->CI->load->autoload('database/Database_Core', 'library', 'db', 'initialize');
		$this->CI->load->autoload('security', 'library');
		$this->CI->load->autoload('session', 'library');
		$this->CI->load->autoload('parser', 'library');
		// Define BASE_URL as a constant here too.
		define("BASE_URL", $this->CI->config->slash_item('base_url'));
	}
}