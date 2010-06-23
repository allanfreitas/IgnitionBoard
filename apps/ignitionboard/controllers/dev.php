<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Dev controller. [REMOVE SOON]
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0.0
 */
class Dev extends IBB_Controller {
	/**
	 * Main view of the Dev controller.
	 */
	function index() {
		$this->error->debug("<pre>Development Controller Loaded. Select a function below.", 1);
		// Make a list of blacklisted (inaccessible) methods.
		$blacklist = array('Controller', '_ci_initialize', '_ci_scaffolding', 'CI_Base', 'get_instance');
		// Get all methods in this class.
		foreach(get_class_methods($this) as $method) {
			// Only show if it it isn't on the blacklist.
			if(in_array($method, $blacklist) == FALSE) {
				// Write the link.
				$this->error->debug(anchor("/dev/" . $method, $method), 2);
			}
		}
		// Finish debug output.
		$this->error->debug("\nController Execution Complete.");
		echo "</pre>";
	}
	function create_db_tables() {
		$this->database->maintenance->create_tables();
	}
	function create_config_record() {
		$config = new DB_Config();
		$config->category = "awesome";
		$config->subcategory = "model";
		$config->setting = "Test";
		$config->value = "1";
		$config->save();
	}
	function drop_db_tables() {
		$this->database->maintenance->drop_tables();
	}
	function session_library() {
	}
	function security_library() {
		$this->error->debug("<pre>Security Library Debugger.", 1);
		// Need to make some salt?
		if($this->config->cryptography->salt->remote == "" || $this->config->cryptography->salt->local == "") {
			$this->error->debug("Generating salt keypair.", 2);
			$this->security->generate_salt_keypair();
		}
		// Test password encryption/decryption.
		$password = $this->security->generate_string(mt_rand(1,32), FALSE);
		$salt = $this->security->generate_string(mt_rand(1,32), FALSE);
		$this->error->debug("Attempting to encrypt password " . $password . 
				" (Length: " . strlen($password) . ") with salt " . $salt .
				" (Length: " . strlen($salt) . ").", 2);
		$hashed_original = hash('ripemd160', $password);
		$encrypted = $this->security->encrypt_password($hashed_original, $salt);
		$decrypted = $this->security->decrypt_password($encrypted, $salt);
		if($decrypted == $hashed_original)
			$decrypted = "<span style=\"color:green\">" . $decrypted . "</span>";
		else
			$decrypted = "<span style=\"color:red\">" . $decrypted . "</span>";
		$this->error->debug("Original Hashed Password: " . $hashed_original, 2);
		$this->error->debug("Encrypted Password: " . $encrypted, 2);
		$this->error->debug("Decrypted Password: " . $decrypted, 2);
		$this->error->debug("Final Password: " . hash('ripemd160', $encrypted), 2);
		// Finish debug output.
		$this->error->debug("\nController Execution Complete.");
		echo "</pre>";
	}
//	function auth_library() {
//
//	}
}

/* End of file: login.php */
/* Location: ./apps/controllers/ */
