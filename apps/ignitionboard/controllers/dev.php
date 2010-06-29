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
	function create_category_board() {
		$category_1 = new DB_Category();
		$category_1->name = "Test Category #1";
		$category_1->description = "Test Category #1 Description goes here.";
		$category_1->save();
		$category_2 = new DB_Category();
		$category_2->name = "Test Category #2";
		$category_2->description = "Test Category #2 Description goes here.";
		$category_2->save();

		$board_1 = new DB_Board();
		$board_1->name = "Test Board #1";
		$board_1->category_id = $category_1->id;
		$board_1->save();
		$board_2 = new DB_Board();
		$board_2->name = "Test Board #2";
		$board_2->category_id = $category_1->id;
		$board_2->save();
		$board_3 = new DB_Board();
		$board_3->name = "Test Board #3";
		$board_3->category_id = $category_1->id;
		$board_3->save();
		$board_4 = new DB_Board();
		$board_4->name = "Test Board #4";
		$board_4->category_id = $category_2->id;
		$board_4->save();
	}
	function get_category_record() {
		echo "Retrieving category name for board ID #4:<br />";
		$item = new DB_Board();
		$item->get_by_id(4);
		echo $item->category_id->name;
	}
	function drop_db_tables() {
		$this->database->maintenance->drop_tables();
	}
	function cache_library() {
		$this->output->append_output("<pre>Cache Library Debugger.");
		if($this->config->board->cache->enabled) {
			$this->output->append_output("\n<span style=\"color:green;\">Caching is currently enabled.</span>");
		} else {
			$this->output->append_output("\n<span style=\"color:red;\">Warning: Caching is currently disabled.</span>");
		}
		$this->output->append_output("\nThe idea of this is to create a very long, exhaustive loop. First load will take a while, second will be returned from the cache near-instantly.");
		$this->output->append_output("\nNote: echo/print/die statements do NOT get cached. Templates/views do.");
		if($this->config->board->cache->enabled) {		
			for($i=0; $i<100000; $i++) {
				$this->output->append_output("\n\t" . $i);
			}
		} else {			
			$this->output->append_output("\n<span style=\"color:red;\">Caching is disabled, so this test will not be performed.</span>");
		}
		// Finish debug output.
		$this->output->append_output("\nController Execution Complete.</pre>");
	}
	function session_library() {
		// Write random values, retrieve random values. Cause some overhead.
		$values = array();
		for($i = 1; $i <= 10; $i++) {
			$values[] = array($i, mt_rand(1,500));
		}
		foreach($values as $value) {
			if($this->session->get($value[0]) == FALSE) {
			echo "Writing data: " . $value[0] . " => " . $value[1] . "<br />";
			$this->session->set($value[0], $value[1]);
			}
			echo "Data Read: " . $value[0] . " => " . $this->session->get($value[0]) . "<br />";
			if(mt_rand(1, 10) == 10) {
				echo "Data Removed: " . $value[0] . "<br />";
				$this->session->remove($value[0]);
			}
		}
	}
	function security_library() {
		$this->error->debug("<pre>Security Library Debugger.", 1);
		// Test password encryption/decryption.
		$real_password = $this->security->generate_string(mt_rand(8,40), FALSE);
		$variable_salt = $this->security->generate_string(mt_rand(8,40), FALSE);
		$salt_start =  strlen($variable_salt) - (floor(strlen($variable_salt) / 40) * 40);
		if($salt_start >= 37)
			$salt_start = 36;
		$password_salt = substr(hash('ripemd160', $variable_salt), $salt_start, 4);
		$password = substr(hash('ripemd160', $real_password), 0, -4) . $password_salt;
		// Output.
		$this->error->debug("\nSETTING UP TEST PARAMETERS");
		$this->error->debug("Attempting to encrypt password: <span style=\"color:red;\">" . $real_password .
				"</span> (Length: " . strlen($real_password) . ")", 2);
		$this->error->debug("Using variable salt: <span style=\"color:purple;\">" . $variable_salt .
				"</span> (Length: " . strlen($variable_salt) . ")", 2);
		$this->error->debug("Calculated Base-Password Salt: <span style=\"color:purple;\">" . $password_salt .
				"</span> (Length: " . strlen($password_salt) . ")", 2);
		$this->error->debug("Resulting Password (Unhashed): <span style=\"color:orange;\">" . $password .
				"</span> (Length: " . strlen($password) . ")", 2);
		// Hash passwords.
		$this->error->debug("\nPASSWORD HASH COMPARISON");
		$password = hash('ripemd160', $password);
		$real_password = hash('ripemd160', $password);
		// More output.
		$this->error->debug("Resulting Password (Hashed): <span style=\"color:green;\">" . $password .
				"</span> (Length: " . strlen($password) . ")", 2);
		$this->error->debug("Comparison with Original Password (Hashed): <span style=\"color:red;\">" . $real_password .
				"</span> (Length: " . strlen($real_password) . ")", 2);
		// Test client-server auth.
		$this->error->debug("\nSETTING UP CLIENT/SERVER AUTH TEST");
		$challenge = hash('ripemd160', $this->security->generate_string(8));
		$this->error->debug("Challenge: " . $challenge . " (Length: 40)", 2);
		$this->error->debug("\nCLIENT/SERVER AUTH COMPARISON");
		$this->error->debug("Transmitted Password: " . hash('ripemd160', $password . $challenge) . " (Length: 40)", 2);
		$this->error->debug("Server Password: " . hash('ripemd160', $password . $challenge) . " (Length: 40)", 2);
		if(hash('ripemd160', $password . $challenge) === hash('ripemd160', $password . $challenge))
			$result = "<span style=\"color:green;\">SUCCESS</span>";
		else
			$result = "<span style=\"color:red;\">FAILURE</span>";
		$this->error->debug("\nRESULT");
		$this->error->debug("If user were to send password with random challenge, authentication would result in: " . $result, 2);
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
