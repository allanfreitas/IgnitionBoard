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
		// Load components (phase 1)
		$this->_load_components(1);
		// Initialize the configuration.
		$this->CI->config->initialize();
		// Enable profiler?
		$this->CI->output->enable_profiler($this->CI->config->board->core->profiler);
		// Initialize the cache.
		$this->CI->cache->initialize();
		// Initialize the session.
		$this->CI->session->initialize();
		// Is there a cache file we can return?
		if($this->CI->cache->cache_request() == FALSE) {
			// No cache.
			// Set the view path so that it's in the public templates directory.
			$this->CI->load->_ci_view_path = $this->CI->config->paths->server->themes;
			// Check if the board isn't installed, and that we're NOT in the install dir.
			if($this->CI->config->board->core->installed == FALSE && $this->CI->uri->segment(1) != "install") {
				// Not installed, redirect to /install.
				//redirect('/install');
			} else {
				// Load components (phase 2)
				$this->_load_components(2);
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
	 * Loads components based on the loading phase given. Phase 1 is base (core) components needed for
	 * board initialization. Phase 2 is core components needed across all controllers.
	 *
	 * @param int $phase The loading phase to process.
	 */
	private final function _load_components($phase = 1) {
		// Go through possible phases.
		switch($phase) {
			case 1:
				// Load core components.
				$this->CI->load->add_dependancy('url', 'helper');
				$this->CI->load->add_dependancy('cookie', 'helper');
				$this->CI->load->add_library('language');
				$this->CI->load->add_library('error');
				$this->CI->load->add_library('cache');
				$this->CI->load->add_library('database');
				$this->CI->load->add_library('security');
				$this->CI->load->add_library('session');
				// Define BASE_URL as a constant here too.
				define("BASE_URL", $this->CI->config->slash_item('base_url'));
				break;
			case 2:
				// Load less-important components.
				$this->CI->load->add_dependancy('form', 'helper');
				$this->CI->load->add_library('parser');
				break;
			case 3:
				break;
		}
	}
}
