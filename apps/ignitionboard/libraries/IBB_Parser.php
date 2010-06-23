<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Extends the Parser library to make parsing even more fun! Replaces a lot of innards to work properly.
 * Supports method chaining for the add function.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class IBB_Parser extends CI_Parser {
	/**
	 * Stores a reference to the global CI object.
	 */
	public $CI;
	/**
	 * Stores a list of templates to parse in order of which they go.
	 *
	 * @var array The templates to parse.
	 */
	private $templates = array();
	/**
	 * Constructor.
	 */
	public function __construct() {
		// DON'T set up parent, as the parser lib has no constructor. PHP error.
		// parent::CI_Parser();
		// Set up the CI reference.
		$this->CI =& get_instance();
	}
	/**
	 * Starts the parser, going through all included files for this page and sending them to the output stuff.
	 *
	 * @param bool $frames		Whether or not to include the header or footer in this round of parsing.
	 * @param string $template	The path to a specific template file to parse immediately.
	 * @param array $opt_data	Data to send with the specific template file in the previous param.
	 */
	public function parse($frames = TRUE, $template = "", $opt_data = NULL) {
		// Get default data.
		$data = $this->_get_template_data();
		// Kick some ass! Seriously.
		// $this->CI->body->actions->hostile->kick($this->CI->targets[0]->ass);
		// Ok not so seriously. Including frames?
		if($frames) {
			// Right. BUT DON'T YOU EVER LET ME CATCH YOU INCLUDING FRAMES AGAIN. Parse the header.
			$this->_parse($this->_get_template_path('board/header'), $data);
		}
		// Now go through the things we need to parse and do it. DO IT.
		// Is this a specific template?
		if($template != "") {
			// Yep. Parse it, assume path is all well and good.
			if(is_array($opt_data)) {
				// Merge opt/def data.
				$data = array_merge($data, $opt_data);
			}
			// Force parse.
			$this->_parse($template, $opt_data);
		} else {
			// Go to our template array.
			foreach($this->templates as $template) {
				// Merge the data arrays. If needed.
				if(is_array($template->data)) {
					// Merge.
					$template->data = array_merge($template->data, $data);
				} else {
					// Replace the nothingness.
					$template->data = $data;
				}
				// Parse this template.
				$this->_parse($template->path, $template->data);
			}
		}
		// Again. Including frames?
		if($frames) {
			// Footer time.
			$this->_parse($this->_get_template_path('board/footer'), $data);
		}
		// Clear the templates array.
		$this->templates = array();
	}
	/**
	 * Actually does the parsin'. All data, etc. should be done and passed here for the parser to parse it.
	 *
	 * @param string $template The path to the template file.
	 * @param array $data The template data to include. 
	 */
	private function _parse($template, $data) {
		// Load the template file as a view, don't append to output. Return as string.
		$template = $this->CI->load->view($template, $data, TRUE);
		// Make sure the template existed.
		if ($template == '') {
			$this->CI->error->show('template_not_found', $template);
		}
		// Go through our data array.
		if(is_array($data)) {
			foreach ($data as $key => $val)	{
				// If this is an array, it's a value pair.
				if (is_array($val))	{
					// So parse it as one.
					$template = $this->_parse_pair($key, $val, $template);
				} else {
					// Else, it's a single value. So parse it as that.
					$template = $this->_parse_single($key, (string)$val, $template);
				}
			}
		}
		// Append this to our output.
		$this->CI->output->append_output($template);
	}
	/**
	 * Parses a template, replacing pseudo-variables inside of the template with the real deal.
	 * Note that this doesn't force the parser to immediately output the contents unless the third param
	 * is set to TRUE.
	 *
	 * @param string $template_path		The template file to parse. Attempts to load from the current theme.
	 * @param array	$opt_data			The optional data to supply to this template file, in an array.
	 * @param bool  $output				Forces the parser library to immediately send this as output.
	 */
	public function add($template_path, $opt_data = NULL, $output = FALSE) {
		// Make a new template object.
		$template = new Template();
		// Set things as we go.
		// Convert the template path to an absolute path.
		$template->path = $this->_get_template_path($template_path);
		// Do we have optional data?
		if(is_array($opt_data)) {
			$template->data = $opt_data;
		}
		// Output if we should.
		if($output) {
			// Call parse, but don't include frames. Give this template file's path.
			$this->parse(FALSE, $template->path, $template->data);
		} else {
			// Don't output, put in array.
			$this->templates[] = $template;
		}
		// Allow method chaining.
		return $this;
	}
	/**
	 * Builds an array of generic template data to pass along with templates.
	 */
	private function _get_template_data() {
		// Make an array and put data in it! I LIKE THIS IDEA.
		$template_data = array(
			"BOARD_THEME" => $this->CI->config->board->themes->name,
			"BOARD_THEME_URL" =>  $this->_get_theme_url(),
			"BOARD_THEME_CSS" => $this->_get_theme_css(),
			"BOARD_THEME_JS" => $this->_get_theme_js(),
			"BOARD_THEME_IMG" => $this->_get_theme_url() . "img/",
			"BOARD_TITLE" => $this->CI->config->board->text->title,
			"BOARD_PAGE_NAME" => $this->_get_template_name()
		);
		// Return the array.
		return $template_data;
	}
	/**
	 * Returns a string pointing to the correct CSS file to include, depending on settings.
	 */
	private function _get_theme_css() {
		// Get the path to the CSS folder. Store both public (0) and server (1) paths for error checking.
		$path[0] = $this->_get_theme_url() . "css/";
		// Server path.
		$path[1] = $this->_get_theme_url(TRUE) . "css/";
		// Compression enabled?
		if($this->CI->config->board->compression->css) {
			// Compression enabled, we'll be using the master.php file. If it exists.
			// Does it?
			if(file_exists($path[1] . "master.php")) {
				// Return the public path.
				return $path[0] . "master.php";
			} else {
				// Error out. This file can't be downgraded to default theme.
				$this->CI->error->show('template_css_not_compressible', $this->CI->config->board->themes->name);
			}
		} else {
			// Compression disabled, use the normal master.css file.
			return $path[0] . "master.css";
		}
	}
	/**
	 * Returns an array of all the JS files to load for this page, depending on settings.
	 */
	private function _get_theme_js() {
		// Get the path to the JS folder. Store both public (0) and server (1) paths for error checking.
		$path[0] = $this->_get_theme_url() . "js/";
		// Server path.
		$path[1] = $this->_get_theme_url(TRUE) . "js/";
		// Compression enabled?
		if($this->CI->config->board->compression->js) {
			// Compression enabled, we'll be using the master.php file. If it exists.
			// Does it?
			if(file_exists($path[1] . "master.php")) {
				// Return the public path in an array.
				return array(array("BOARD_THEME_JS.URL" => $path[0] . "master.php"));
			} else {
				// Error out. This file can't be downgraded to default theme.
				$this->CI->error->show('template_js_not_compressible', $this->CI->config->board->themes->name);
			}
		} else {
			// Compression disabled, use the normal javascript files file.
			// PENDING: Fix it so that it detects plugins.
			return array(
				array("BOARD_THEME_JS.URL" => $path[0] . "jquery.js"),
				array("BOARD_THEME_JS.URL" => $path[0] . "plugins/jquery.login.js")
			);
		}
	}
	/**
	 * Takes the name of the current controller and returns a name for the page, to be used in the
	 * {BOARD_PAGE_NAME} template variable.
	 *
	 * @return string The name of the page, or blank if the page isn't in the array.
	 */
	private function _get_template_name() {
		// Here be penguins. And an array of possible page names.
		$names = array(
			'home' => '', // Home page should be blank.
			'login' => 'Login',
			'register' => 'Register'
		);
		// Let's see if this page exists shall we?
		if(array_key_exists($this->CI->router->class, $names)) {
			// Good. Is the entry at this key an array or string?
			if(is_array($names[$this->CI->router->class])) {
				// Array, so the keys in here represent methods in the controller. Does this method exist?
				if(array_key_exists($this->CI->router->method, $names[$this->CI->router->class])) {
					// It does. Return it.
					return $names[$this->CI->router->class][$this->CI->router->method];
				} else {
					// Nope. Empty handed!
					return '';
				}
			} else {
				// Not array. Return whatever it is.
				return $names[$this->CI->router->class];
			}
		} else {
			// Nope. Go back empty handed.
			return '';
		}
	}
	/**
	 * Returns the public or server URL to the users current theme.
	 *
	 * @param $server Whether to use the server path, or public path.
	 * @param $theme Optional theme to force for URL retrieval.
	 */
	private function _get_theme_url($server = false, $theme = "") {
		// Return the path.
		return	(($server) ? $this->CI->config->paths->server->themes : $this->CI->config->paths->public->themes) .
				(empty($theme) ? $this->CI->config->board->themes->url : $theme);
	}
	/**
	 * Prepends the absolute path to the given template file path. Checks to see if it exists and defaults
	 * to the default theme if needed too.
	 */
	private function _get_template_path($template) {
		// Make a full path to the view.
		$template_path = $this->CI->config->board->themes->url . 'templates/' . $template;
		// Check if the view we have doesn't exist.
		if(!file_exists($this->CI->config->paths->server->themes . $template_path . '.php')) {
			// File doesn't exist, bad. Fix it. Use default theme instead.
			$template_path = 'ignited/' . $template;
			// NOW does it exist?
			if(!file_exists($this->CI->config->paths->server->themes . $template_path . '.php')) {
				// Still doesn't exist, in that case I suggest an error is in order.
				$this->CI->error->show('template_not_found',
						$this->CI->config->paths->public->themes . $template_path . '.php');
				die();
			}
		}
		return $template_path;
	}
}
/**
 * Template object stores information on the template to parse, such as passed data and the path.
 */
class Template {
	/**
	 * The path to the template, relative to the themes dir.
	 *
	 * @var string The path of the template file.
	 */
	public $path = "";
	/**
	 * Optional data to merge with the default array when parsing this template.
	 *
	 * @var array/null Extra data to use during parsing.
	 */
	public $data = NULL;
}
/* End of file ibb_parser.php */
/* Location: ./apps/libraries/ibb_parser.php */