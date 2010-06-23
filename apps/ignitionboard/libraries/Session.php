<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * This Session lib fixes flaws inside of the CI Sessions library, in that the CI one stores all data
 * client side. We'll want the data to be stored using the database is possible, rather than client-side
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
	 */
	private $divisor = 10;
	/**
	 * Stores the user's session data in an array. This is serialized for storage.
	 */
	private $session_data;	
	/**
	 * Stores the user's session data in MD5 format. Used to determine whether flushing is needed or not.
	 */
	private $session_checksum;
	/**
	 * Stores the user's session key, and token identifer. This is read from the user's encrypted cookie.
	 */
	private $client_key_tokens;
	/**
	 * Stores the user's IP address and UA string. This is read from the request itself.
	 */
	private $client_key_source;
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
		// Generic checksum of empty session array.
		$this->session_checksum = md5('a:0:{}');
		// Only access database if we can.
		if($this->CI->database->loaded == TRUE) {
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
	 * Writes the user's session array into the database. Called once after controller execution, but
	 * can be called as needed.
	 */
	public final function flush($force_update = FALSE) {
		// Only write to database if we can.
		if($this->CI->database->loaded == TRUE) {
			// Update the session data.
			$session = (string) serialize(($this->session_data == NULL) ? array() : $this->session_data);
			// Only update if we actually NEED to, or if we're told to.
			if(md5($session) != $this->session_checksum || $force_update == TRUE) {
				// Overwrite the data in the database.
				$this->CI->db->where('session_id', $this->client_key_tokens['session_id'])
							 ->update('session', array('data' => $session));
			}
		}
	}
	/**
	 * Destroys the user's session, wiping their key and data.
	 */
	public final function destroy() {
		// Destroy cookies and database session.
		$this->destroy_database_session();
		delete_cookie('session');
		// Unset the local stuff.
		$this->session_data = array();
		$this->client_key_tokens = array();
		$this->client_key_source = array();
		// Make a new session.
		$this->initialize_session(TRUE);
	}
	/**
	 * Retrieves the user's session data from the database.
	 */
	private final function get_session_data() {
		// Only access database if we can.
		if($this->CI->database->loaded == TRUE) {
			// Get the session data.
			$session = $this->CI->db->select('data')
									->from('session')
									->where('session_id', $this->client_key_tokens['session_id']);
			$session = $this->CI->db->get();
			// Any results?
			if($session->num_rows() > 0) {
				// Got results. Done.
				$session = $session->row_array();
				// Do we have any data stored?
				if($session['data'] == NULL) {
					// Put in a blank array.
					$this->session_data = array();
					// Generic checksum of empty session array.
					$this->session_checksum = md5('a:0:{}');
				} else {
					// Unserialize the data. MD5 it and set the checksum too.
					$this->session_data = (array) unserialize($session['data']);
					$this->session_checksum = md5($session['data']);
				}
			} else {
				// Put in a blank array.
				$this->session_data = array();
				// Generic checksum of empty session array.
				$this->session_checksum = md5('a:0:{}');
			}
		}
	}
	/**
	 * Checks to see if the user has a registered session, and if not it'll make one.
	 */
	private final function initialize_session($force_new = FALSE) {
		// Sort out the keys based on the user's connection.
		$this->client_key_source = array(
			// User Agent string. Trimmed to 50 chars.
			'user_agent' => substr($this->CI->input->user_agent(), 0, 50),
			// IP address. Self-explanatory.
			'ip_address' => $this->CI->input->ip_address(),
		);
		// Does this user have a session cookie?
		if(get_cookie('session') == FALSE || $force_new == TRUE) {
			// They don't. Shucks. Make a new set of keys.
			$this->generate_client_keys();
			// Update the user's database session entry.
			$this->initialize_database_session();
		} else {
			// They do, sweet. Get the keys.
			$this->retrieve_client_keys();
			// Make sure the user has all the needed information.
			if(array_key_exists("session_id", $this->client_key_tokens) == FALSE
			|| array_key_exists("token", $this->client_key_tokens) == FALSE
			|| array_key_exists("last_activity", $this->client_key_tokens) == FALSE) {
				// Something is missing. Invalidate the session, and then re-initialize it.
				$this->destroy();
			} else {
				// All data appears to be here. Make sure this user has a registered session in the database.
				$this->initialize_database_session();
			}
		}
	}
	/**
	 * Checks to see if the user's session is in the database. Doesn't update the data, only inserts a new
	 * session record if needed.
	 */
	private final function initialize_database_session() {
		// Only access database if we can.
		if($this->CI->database->loaded == TRUE) {
			// Check the last_activity timer. Is it older than the time to live, or does it match now exactly?
			if((integer) $this->client_key_tokens['last_activity'] + $this->ttl < $this->now
			|| $this->now == $this->client_key_tokens['last_activity']) {
				// Check to see if anything for this session already exists.
				if($this->CI->db->where('session_id', $this->client_key_tokens['session_id'])->from('session')->count_all_results() == 0) {
					// No results. Add in a new session record.
					$session = new DB_Session();
					// Set up data.
					$session->import_from_array(array_merge($this->client_key_tokens, $this->client_key_source));
					// Save it.
					$session->save();
				}
			}
		}
	}
	/**
	 * Destroys a database session based on the user's session ID.
	 */
	private final function destroy_database_session() {
		// Only write to database if we can.
		if($this->CI->database->loaded == TRUE) {
			// Delete this session.
			$this->CI->db->where('session_id', $this->client_key_tokens['session_id'])
						 ->delete('session');
		}
	}
	/**
	 * Destroys sessions which are considered to be out of date/expired.
	 */
	private final function destroy_old_sessions() {
		// Only write to database if we can.
		if($this->CI->database->loaded == TRUE) {
			// Sessions older than time() - ttl are to be deleted.
			$time = $this->now - $this->ttl;
			// Delete.
			$this->CI->db->where('last_activity <', $time)->delete('session');
		}
	}
	/**
	 * Checks to see if the user's session record identifiers match the ones given by the user.
	 */
	private final function check_session_ownership() {
		// Only access database if we can.
		if($this->CI->database->loaded == TRUE) {
			// Only do this once every so often.
			if($this->CI->security->revalidate($this->client_key_tokens['last_activity']) == TRUE) {
				// Check the user's connection data with what the session has.
				$server = $this->CI->db->select('ip_address, user_agent, last_activity')
									   ->from('session')
									   ->where('session_id', $this->client_key_tokens['session_id']);
				$server = $this->CI->db->get();
				// Any results?
				if($server->num_rows() > 0) {
					// Got results. Coo'.
					$server = $server->row_array();
					// Do these things match?
					if(count(array_diff($this->client_key_source, $server)) > 0) {
						// Mismatch! Oh dear. Bye bye!
						return FALSE;
					} else {
						// All seems well in this world.
						return TRUE;
					}
					return TRUE;
				} else {
					// No matches for this session ID.
					return FALSE;
				}
			} else {
				// Throttling says don't bother checking.
				return TRUE;
			}
		}
	}
	/**
	 * Regenerates the user's session ID and updates the last_activity timestamp in the database.
	 * Performs this once every so often based on the time to update in the config.
	 */
	private final function update_session_keys() {
		// Get the user's last_activity string.
		$last_update = $this->client_key_tokens['last_activity'];
		// TIme to revalidate?
		if($this->CI->security->revalidate($last_update) == TRUE) {
			// It was ages ago. Alright. Update their keys, but store a copy of their current session ID.
			$session_id = $this->client_key_tokens['session_id'];
			// Update.
			$this->generate_client_keys();
			// Only write to database if we can.
			if($this->CI->database->loaded == TRUE) {
				// Overwrite the data in the database.
				$this->CI->db->where('session_id', $session_id)
							 ->update('session', array_merge($this->client_key_tokens, $this->client_key_source));
			}
		}
	}
	/**
	 * Generates a new set of client-keys and sends them to the user in an encrypted cookie.
	 */
	private final function generate_client_keys() {
		// Make up some awesome data.
		$this->client_key_tokens = array(
			// Session Identifer. Random + IP, hashed.
			'session_id' => hash('ripemd160',$this->CI->security->generate_string() . $this->CI->input->ip_address()),
			// Uniquely generated token. This remains constant throughout the user's visit.
			'token' => hash('ripemd160', $this->CI->security->generate_string()),
			// Last Activity token. Timestamp of last load.
			'last_activity' => $this->now
		);
		// Give this set of keys to the user. Make it a session cookie (expiry = 0)
		set_cookie('session', $this->CI->encrypt->encode(serialize($this->client_key_tokens)), 0);
	}
	/**
	 * Gets the user's keys from their cookie.
	 */
	private final function retrieve_client_keys() {
		// The cookie represents the user's key, so decrypt and unserialize it.
		$this->client_key_tokens = (array) unserialize($this->CI->encrypt->decode(get_cookie('session')));
	}
}
/* End of file session.php */
/* Location: ./apps/libraries/session.php */