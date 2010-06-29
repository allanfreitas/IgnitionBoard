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
	public final function generate_string($max_length = 32, $allow_symbols = TRUE) {
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
	public final function generate_challenge($sess_prefix) {
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
	public final function check_challenge($post_key, $sess_prefix) {
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
	public final function unset_challenge($sess_prefix) {
		// Remove it bro!
		$this->CI->session->remove($sess_prefix . '_challenge');
	}
	/**
	 * Removes any existing challenge variable at the given index and makes a new one.
	 *
	 * @param	string	$sess_prefix	The key of the challenge string in the session data.
	 * @return	string	The challenge string that comes out of this generator's womb. Sort of.
	 */
	public final function regenerate_challenge($sess_prefix) {
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
	public final function revalidate($time) {
		// If the time + time to revalidate are < the current time, revalidate is TRUE.
		if($time + $this->ttr < $this->now) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
/* End of file security.php */
/* Location: ./apps/libraries/security.php */