<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * This Session lib fixes flaws inside of the CI Sessions library, in that the CI one stores all data
 * client side. We'll want the data to be stored using the file system rather than client-side
 * which can be tampered with potentially.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class CI_Session {
	/**
	 * Stores a reference to the global CI object.
	 */
	public $CI;
	/**
	 * Stores the timestamp as of now. Set during initialization.
	 */
	private $now;
	/**
	 * The time-to-live of the user's session in general. Sessions older than 1 hour (3600 secs) are destroyed
	 * by default.
	 */
	private $ttl = 3600;
	/**
	 * Rolls a random number between 1 and this variable. If it matches 1, we delete old sessions.
	 * Why call it a divisor? Because the chance is 1/this :p
	 */
	private $divisor = 10;
	/**
	 * Stores the user's session data in an array. This is serialized for storage.
	 */
	private $session_data;
	/**
	 * Stores the keys/identifiers needed to validate that whoever accesses this session data next owns it.
	 */
	private $session_keys;	
	/**
	 * Stores the user's session data in MD5 format. Used to determine whether flushing is needed or not.
	 */
	private $session_checksum;
	/**
	 * Stores the user's session key, and token identifer. This is read from the user's encrypted cookie.
	 */
	private $client_keys;
	/**
	 * Constructor.
	 *
	 * Sets up the reference to the CI super object.
	 */
	public final function __construct() {
		// Set up the CI reference.
		$this->CI =& get_instance();
	}
	/**
	 * Called during board initialization. Validates the user's session key, making new ones if needed.
	 * Validates other security credentials too.
	 */
	public final function initialize() {
		// Set up some variables.
		$this->now = time();
		// Should we wipe old sessions?
		if(mt_rand(1, $this->divisor) == 1) {
			// Delete old sessions.
			$this->destroy_old_sessions();
		}
		// First up, check if the session exists in the database.
		$this->initialize_session();
		// Right, does the user own this session?
		if($this->check_session_ownership()) {
			// Update the session keys if needed.
			$this->update_session_keys();
		} else {
			// Invalidate the session.
			$this->destroy();
		}
	}
	/**
	 * Retrieves an item from the the user's session data. Returns FALSE if no item was found.
	 *
	 * @param string $key The key of the item to retrieve.
	 */
	public final function get($key, $force_update = FALSE) {
		// Force a session data update, or do we still need to get it anyways?
		if($force_update == TRUE || $this->session_data == NULL)
			$this->get_session_data();
		// Does an item with this key exist?
		if(array_key_exists($key, $this->session_data) == TRUE) {
			// Return it.
			return $this->session_data[$key];
		} else {
			// FALSE.
			return FALSE;
		}
	}
	/**
	 * Writes data into the user's session array. The arguments can be either a single array, or
	 * a series of strings representing keys and data.
	 */
	public final function set() {
		// Did we ever actually GET the session data?
		if($this->session_data == NULL)
			$this->get_session_data();
		// Do we have an array or some strings?
		if(func_num_args() == 1) {
			// Array?
			if(is_array(func_get_arg(0))) {
				// Go go go.
				$this->session_data = array_merge($this->session_data, func_get_arg(0));
			}
		} else if(func_num_args() >= 2) {
			// Are there an even number of arguments?
			if(ceil(func_num_args() / 2) == func_num_args() / 2) {
				// Even number. Go through the arguments.
				for($i = 0; $i < func_num_args(); $i+=2) {
					// Set them.
					$this->session_data[(string) func_get_arg($i)] = (string) func_get_arg($i+1);
				}
			}
		}
	}
	/**
	 * Removes data from the user's session array.
	 */
	public final function remove($key) {
		// Did we ever actually GET the session data?
		if($this->session_data == NULL)
			$this->get_session_data();
		// Unset anything like this.
		unset($this->session_data[$key]);
	}
	/**
	 * Writes the user's session data and keys into their session file. Called after controller execution
	 * automatically.
	 */
	public final function flush($force_update = FALSE) {
		// Update the session data.
		$session = $this->parse_session_data();
		// Only update if we actually NEED to, or if we're told to.
		if(md5($session) != $this->session_checksum || $force_update == TRUE) {
			// Write the session data to a file in "output/sessions/<session_id>.sess".
			file_put_contents(APPPATH . '/output/sessions/' . $this->session_keys['session_id'] . '.sess', $session);
		}
	}
	/**
	 * Destroys the user's session, wiping their key and data.
	 */
	public final function destroy() {
		// Delete the user's session cookie.
		delete_cookie('session');
		// Delete the user's session file.
		$this->destroy_session_file();
		// Make a new session.
		$this->initialize_session(TRUE);
	}
	/**
	 * Returns the user's session ID.
	 */
	public final function get_id() {
		// Return.
		return $this->session_keys['session_id'];
	}
	/**
	 * Checks to see if the user has a registered session, and if not it'll make one.
	 */
	private final function initialize_session($force_new = FALSE) {
		// Does this user have a session cookie?
		if(get_cookie('session') == FALSE || $force_new == TRUE) {
			// They don't. Shucks. Make a new set of keys.
			$this->generate_client_keys();
		} else {
			// They do, sweet. Get the keys.
			$this->retrieve_client_keys();
		}
		// Include the user's IP/UA in the keys.
		$this->client_keys = array_merge(
				$this->client_keys,
				array(
					// User-Agent string, clipped to 50 chars.
					'user_agent' => substr($this->CI->input->user_agent(), 0, 50),
					// IP address. Self-explanatory.
					'ip_address' => $this->CI->input->ip_address(),
				)
			);
		// Is anything missing from the session keys?
		if(array_key_exists("session_id", $this->client_keys) == FALSE
		|| array_key_exists("token", $this->client_keys) == FALSE
		|| array_key_exists("last_activity", $this->client_keys) == FALSE) {
			// Something is missing. Invalidate the session, and then re-initialize it.
			$this->destroy();
		}
		// Right, has this session just been created, or did it expire a while ago?
		if((integer) $this->client_keys['last_activity'] + $this->ttl < $this->now
		|| $this->now == $this->client_keys['last_activity']) {
			// It's either new or expired. Overwrite the user's session data file, anyways..
			$this->generate_session_file();
		}
		// Get the user's session data.
		$this->get_session_data();
	}
	/**
	 * Retrieves the user's session data from the database.
	 */
	private final function get_session_data() {
		// Get the user's session data.
		$session = file_get_contents(APPPATH . '/output/sessions/' . $this->client_keys['session_id'] . '.sess');
		// If it exists, unserialize it/update checksum.
		if($session == FALSE) {
			// But it doesn't.
			// Put in a blank array.
			$this->session_data = array();
			$this->session_keys = array();
			// Put in a blank checksum, should force a write.
			$this->session_checksum = '';
		} else {
			// Oh, but it does!
			$session_data = (array) unserialize($session);
			$this->session_data = $session_data['data'];
			$this->session_keys = $session_data['keys'];
			$this->session_checksum = md5($session);
		}
	}
	/**
	 * Parses session data and returns either an unserialized set of arrays or a serialized string.
	 *
	 * @param string $session The serialized session data to parse.
	 */
	private final function parse_session_data($session = NULL) {
		// Is the session arg null?
		if($session == NULL) {
			// We're serializing the session data then.
			$session_data = array(
				'keys' => &$this->session_keys,
				'data' => &$this->session_data
			);
			// Return this array, serialized.
			return (string) serialize($session_data);
		} else {
			// We've got a serialized string to parse.
			$session_data = (array) unserialize($session);
			// Return the arrays.
			return $session_data;
		}
	}
	/**
	 * Destroys the current user's session file.
	 */
	private final function destroy_session_file() {
		// Make sure a file exists with this name.
		if(file_exists(APPPATH . '/output/sessions/' . $this->client_keys['session_id'] . '.sess')) {
			// Delete it.
			unlink(APPPATH . '/output/sessions/' . $this->client_keys['session_id'] . '.sess');
		}
	}
	/**
	 * Destroys sessions which are considered to be out of date/expired.
	 */
	private final function destroy_old_sessions() {
		// Get a list of all the files in the session output folder.
		$files = array_diff(scandir(APPPATH . '/output/sessions/'), array(".", ".."));
		foreach($files as $file) {
			// Is the last-modified time of this file longer than the TTL?
			if(filemtime(APPPATH . '/output/sessions/' . $file) + $this->ttl < $this->now) {
				// Delete it.
				unlink(APPPATH . '/output/sessions/' . $file);
			}
		}
	}
	/**
	 * Checks to see if the user's session record identifiers match the ones given by the user.
	 */
	private final function check_session_ownership() {
		// Any disparities with the client/server keys?
		if(count(array_diff($this->client_keys, $this->session_keys)) > 0) {
			// Mismatch! Oh dear. Bye bye!
			return FALSE;
		} else {
			// All seems well in this world.
			return TRUE;
		}
	}
	/**
	 * Regenerates the user's session ID and updates the last_activity timestamp in the database.
	 * Performs this once every so often based on the time to update in the config.
	 */
	private final function update_session_keys() {
		// Get the user's last_activity string.
		$last_update = $this->client_keys['last_activity'];
		// TIme to revalidate?
		if($this->CI->security->revalidate($last_update) == TRUE) {
			// It was ages ago. Alright. Update their keys, but store a copy of their current session ID.
			$session_id = $this->client_keys['session_id'];
			// Update.
			$this->generate_client_keys();
			// Update their session keys.
			$this->session_keys['session_id'] = $this->client_keys['session_id'];
			$this->session_keys['token'] = $this->client_keys['token'];
			$this->session_keys['last_activity'] = $this->client_keys['last_activity'];
			// Rename the old session file they had.
			rename(APPPATH . '/output/sessions/' . $session_id . '.sess', APPPATH . '/output/sessions/' . $this->get_id() . '.sess');
			// Update their cache stores.
			$this->CI->cache->cache_update($session_id, $this->get_id());
		}
	}
	/**
	 * Creates a new server-side session file/array/whatever.
	 */
	private final function generate_session_file() {
		// Sort out user session keys.
		$this->session_data = array();
		$this->session_keys = array();
		$this->session_checksum = '';
		$this->session_keys['session_id'] = $this->client_keys['session_id'];
		$this->session_keys['token'] = $this->client_keys['token'];
		$this->session_keys['last_activity'] = $this->client_keys['last_activity'];
		$this->session_keys['user_agent'] = $this->client_keys['user_agent'];
		$this->session_keys['ip_address'] = $this->client_keys['ip_address'];
		// Force-write data to file.
		$this->flush(TRUE);
	}
	/**
	 * Generates a new set of client-keys and sends them to the user in an encrypted cookie.
	 */
	private final function generate_client_keys() {
		// Make up some awesome data.
		$this->client_keys = array(
			// Session Identifer. Random + IP, hashed.
			'session_id' => hash('ripemd160',$this->CI->security->generate_string() . $this->CI->input->ip_address()),
			// Uniquely generated token. This remains constant throughout the user's visit.
			'token' => hash('ripemd160', $this->CI->security->generate_string()),
			// Last Activity token. Timestamp of last load.
			'last_activity' => $this->now
		);
		// Give this set of keys to the user. Make it a session cookie (expiry = 0)
		set_cookie('session', $this->CI->encrypt->encode(serialize($this->client_keys)), 0);
	}
	/**
	 * Gets the user's keys from their cookie.
	 */
	private final function retrieve_client_keys() {
		// The cookie represents the user's public keys, so decrypt and unserialize it.
		$this->client_keys = (array) unserialize($this->CI->encrypt->decode(get_cookie('session')));
	}
}
/* End of file session.php */
/* Location: ./apps/libraries/session.php */