<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The Security library contains useful functions and other tidbits to quicken access to basic security
 * methods, and to manage other things. I really thought this one through, as you can tell.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class Security {
	/**
	 * Stores a reference to the global CI object.
	 */
	public $CI;
	/**
	 * Stores the timestamp as of now. Set during initialization.
	 */
	private $now;
	/**
	 * Stores the time-to-revalidation, used by the revalidate() function.
	 * By default, is set to 1 minute (60 secs).
	 */
	private $ttr = 60;
	/**
	 * Constructor.
	 */
	public function __construct() {
		// Set up the CI reference.
		$this->CI =& get_instance();
		// Set up some variables.
		$this->now = time();
	}
	/**
	 * Generates a random string of alphanumeric characters.
	 *
	 * @param	int		$max_length		The maximum length of the generated string.
	 * @param	bool	$allow_symbols	If TRUE, allow symbols to be added into the mix.
	 * @return	string	The randomly generated string.
	 */
	public function generate_string($max_length = 32, $allow_symbols = TRUE) {
		// Store possible characters.
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		// Allowing symbols?
		if($allow_symbols) {
			// Append.
			// Don't allow anything which could break the system. Eg. ' " \ `, or non-ASCII chars.
			$chars .= "!<>%$^*()_+-=[]{};#:@~,./?";
		}
		// Store a result.
		$result = "";
		// Loop until the length of our result is the max length allowed.
		while(strlen($result) < $max_length) {
			// Step one, make a random number.
			$char_index = mt_rand(0, strlen($chars) - 1);
			// Step two, get a character from that random number.
			$char = $chars[$char_index];
			// Step three, if that character is a letter...
			if($char_index <= 25) {
				// Then randomly decide to make it upper or lowercase.
				$case = mt_rand(0, 1);
				switch ($case) {
					case 1:
						$char = strtoupper($char);
						break;
				}
			}
			// And finally append to result.
			$result .= $char;
		}
		// Return result.
		return $result;
	}
	/**
	 * Generates a random string, and then places it in the user's session. Returns the challenge string too.
	 *
	 * @param	string	$sess_prefix	The key of the challenge string in the session data.
	 * @return	string	The challenge string that comes out of this generator's womb. Sort of.
	 */
	function generate_challenge($sess_prefix) {
		// Only generate a new challenge if one doesn't exist with this name. Fixes a bug with Chrome.
		if($this->CI->session->get($sess_prefix . '_challenge') == FALSE) {
			// Make a stwing!
			$challenge = $this->generate_string(32, FALSE);
			// Set to sess!
			$this->CI->session->set($sess_prefix . '_challenge', $challenge);
			// Go home, challenge!
			return $challenge;
		} else {
			// Return the one we have.
			return $this->CI->session->get($sess_prefix . '_challenge');
		}
	}
	/**
	 * Compares a given challenge string to the one stored in a session at the given location.
	 *
	 * @param	string	$post_key		The key to get the challenge string from in the post array.
	 * @param	string	$sess_prefix	The key of the challenge string in the session data.
	 * @return	bool	Whether or not the challenge data matched up.
	 */
	function check_challenge($post_key, $sess_prefix) {
		// Do something AWESOME. Like...Get the super-duper challenge string from the session! If we can.
		$challenge = $this->CI->session->get($sess_prefix . '_challenge');
		// Did it exist? Really?
		if($challenge != FALSE) {
			// YAY!
			// Do the two strings match?
			if($challenge == $this->CI->input->post($post_key)) {
				// YAY!
				return TRUE;
			} else {
				// Fail.
				return FALSE;
			}
		} else {
			// NOOOOOO!
			return FALSE;
		}
	}
	/**
	 * Removes a stored challenge variable at the given location.
	 *
	 * @param	string	$sess_prefix	The key of the challenge string in the session data.
	 */
	function unset_challenge($sess_prefix) {
		// Remove it bro!
		$this->CI->session->remove($sess_prefix . '_challenge');
	}
	/**
	 * Removes any existing challenge variable at the given index and makes a new one.
	 *
	 * @param	string	$sess_prefix	The key of the challenge string in the session data.
	 * @return	string	The challenge string that comes out of this generator's womb. Sort of.
	 */
	function regenerate_challenge($sess_prefix) {
		// Kill old one.
		$this->unset_challenge($sess_prefix);
		// Return new one.
		return $this->generate_challenge($sess_prefix);
	}
	/**
	 * Checks the time to update, shared by the session library. If the TTR has expired, then we
	 * return TRUE. Else, FALSE. Acts to throttle the amount of revalidation we do.
	 *
	 * @param	int		$time			The time to compare the TTR to.
	 */
	function revalidate($time) {
		// If the time + time to revalidate are < the current time, revalidate is TRUE.
		if($time + $this->ttr < $this->now) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	/**
	 * Takes an input and returns a salted version, suitable for storing as a password. The salt is a 
	 * combination of a static and variable salt. The static salt is determined by the database AND by
	 * part stored in a file. The variable salt is based on the value given.
	 *
	 * @param	string	$value			The value to salt.
	 * @param	string	$variable_salt	A piece of variable user data to aid in creating the salted password.
	 * @return	string	The salted password.
	 */
	function encrypt_password($value, $variable_salt) {
		// Get the salt. Key fragment #1 is in the database.
		$key[0] = $this->CI->config->cryptography->salt->remote;
		// Key fragment #2 is in the config files.
		$key[1] = $this->CI->config->cryptography->salt->local;
		// Make sure we've a key fragment.
		if($key[1] == "" || $key[0] = "") {
			// Error out.
			$this->CI->error->show('security_missing_salt');
		}
		// Combine the two. Salt formed.
		$salt[0][0] = hash('ripemd160', $key[0] . $key[1]);
		// Split that hash in half. Prepend the second half, append the second half.
		$salt[0][1] = substr($salt[0][0], 0, 20);
		$salt[0][2] = substr($salt[0][0], 20);
		// Now for our variable salt. We want the length of the value * 2.2. Ceil that value.
		// If the length of our value is more than 36, trim it to be 36.
		if(strlen($variable_salt) > 36) { $variable_salt = substr($variable_salt, 0, 36); }
		// Calculate it.
		$salt[1][0] = ceil(strlen($variable_salt) * 2.2);
		// Take the calculation and SHA1 it, along with the first 12 characters of the variable salt appended
		// to the end.
		$salt[1][1] = sha1($salt[1][0] . substr($variable_salt, 0, 12));
		// Put ths static salt on the value. Second half prepend, first half append.
		$result = $salt[0][2] . $value . $salt[0][1];
		// Put our variable salt in the position calculated.
		$result = (substr($result, 0, -$salt[1][0]) . $salt[1][1] . substr($result, -$salt[1][0]));
		// Done.
		return $result;
	}
	/**
	 * Decodes a string created with the salt() function.
	 *
	 * @param	string	$value			The value to de-salt.
	 * @param	string	$variable_salt	A piece of variable user data to aid in decoding the salted password.
	 * @return	string	The unsalted password.
	 */
	function decrypt_password($value, $variable_salt) {
		// Check the value length.
		if(strlen($value) < 120) {
			// Error out.
			$this->CI->error->show('security_password_length');
		}
		// We only need the second salt value.
		// Key fragment #2 is in the config files.
		$key[1] = $this->CI->config->cryptography->salt->local;
		// Make sure we've a key fragment.
		if($key[1] == "") {
			// Error out.
			$this->CI->error->show('security_missing_salt');
		}
		// Now for our variable salt. We want the length of the value * 2.2. Ceil that value.
		// If the length of our value is more than 36, trim it to be 36.
		if(strlen($variable_salt) > 36) { $variable_salt = substr($variable_salt, 0, 36); }
		// Calculate it.
		$salt[1][0] = ceil(strlen($variable_salt) * 2.2);
		// Take the calculation and SHA1 it, along with the first 12 characters of the variable salt appended
		// to the end.
		$salt[1][1] = sha1($salt[1][0] . substr($variable_salt, 0, 12));
		// Remove the variable salt at the calculated position. It's 40 characters long by design.
		$result = substr_replace($value, "", - $salt[1][0] - 40, 40);
		// Remove the static salt on the value.
		$result = substr($result, 20, 40);
		// Done.
		return $result;
	}
	/**
	 * Generates a new static salt keypair. Any user credentials will become inaccessible after using this, as
	 * the salt key would have changed. This is only called on install, and should never ever ever ever ever
	 * be called afterwards.
	 */
	function generate_salt_keypair() {
		// I guess the user is insane. Okies, let's do this. Make a new salt keypair. 20 char limit each.
		$key[0] = $this->generate_string(20); // This part is going into the database.
		$key[1] = $this->generate_string(20); // This part is going into a file.
		// Make a new row.
		$row = array(
			'setting' => 'remote',
			'value' => $key[0],
			'category' => 'cryptography',
			'subcategory' => 'salt'
		);
		// Put it into the config table. Half done.
		$this->CI->db->insert('config', $row);
		// Write the next half into the cryptography settings file.
		$file = <<<EOF
<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| CRYPTOGRAPHY SETTINGS
| -------------------------------------------------------------------
| This file contains important keys used in encrypting and securing your forum data.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|	--- HASH ---
|	['salt']['local'] 	Half of the salt used in hashing passwords.
|
*/
	// HASH
	\$cryptography['salt']['local'] = '$key[1]';
EOF;
		// Write this file.
		if(is_writeable(APPPATH . 'config/settings/cryptography.php')) {
			// Write!
			file_put_contents(APPPATH . 'config/settings/cryptography.php', $file);
		} else {
			// Uh-oh. Error.
			$this->CI->error->show('security_file_not_writeable');
		}
	}
}
/* End of file security.php */
/* Location: ./apps/libraries/security.php */