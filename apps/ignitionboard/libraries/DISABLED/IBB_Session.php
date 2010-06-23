<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The IBB_Session lib fixes flaws inside of the CI Sessions library, in that the CI one stores all data
 * client side. In some cases, we'll want the data to be stored using PHP's built in server-side session
 * storage utilities.
 *
 * The goal is to emulate CI's session capabilities and extend them to server-side storage.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class IBB_Session extends CI_Session {
	/**
	 * Stores a reference to the global CI object.
	 */
	public $CI;
	/**
	 * Private storage variable for server-side flashdata.
	 *
	 * @var array Storage array for flashdata.
	 */
	private $flash = array();
	/**
	 * Stores the newly made client session ID for use in the update.
	 *
	 * @var string The new session ID.
	 */
	private $new_sessid = NULL;
	/**
	 * Constructor.
	 *
	 * Ref CI's instance here.
	 */
	public function __construct()
    {
		// Required line. Else all this goes to hell I say!
        parent::CI_Session();
		// Get a reference of the CI object.
		$this->CI =& get_instance();
    }
	/**
	 * Initialiser, called after this library is loaded. Prevents issues with security lib, as they need
	 * each other at the same time.
	 *
	 * Makes sessions, expires, validates and does flashdata stuff.
	 */
	public final function init() {
		// Check session status, create if need be.
		$this->_check_server_session();
		// Make sure this user owns the session.
		$this->_check_session_ownership();
		// The client cookie updates on a different timer, but the changed ID isn't stored in the DB.
		$this->_check_client_expiration();
		// Update the database validation stuff if we need to. Pass this library alongside, to prevent errors.
		if($this->CI->security->revalidate()) {
			// The time-to-update of this session, if you will, has expired or doesn't exist. Force check.
			$this->_check_database_validation();
		}
		// Finally, sort out the flashdata.
		$this->_parse_server_flashdata();
	}
	/**
	 * Similar to the userdata function, serverdata will return a value stored in the user's session on the
	 * server. Returns FALSE if the item does not exist.
	 *
	 * @param string $key The key/name of the item to return from the session data on the server.
	 * @return The data stored in the session, or FALSE if this item does not exist.
	 */
	public final function serverdata($key) {
		// Check session data for this item.
		if(isset($_SESSION[$key])) {
			// Session item exists. Return it.
			return $_SESSION[$key];
		} else {
			// Nothing exists here. FALSE.
			return FALSE;
		}
	}
	/**
	 * Reads flashdata created for server-only usage/communicated. Will return a value stored in the session
	 * which has been designated as flashdata - Data which exists only for the next request.
	 *
	 * Returns FALSE if the item does not exit.
	 *
	 * @param string $key The key of the item to return from the flashdata.
	 * @return The data stored in the session, or FALSE if this item does not exist.
	 */
	public final function server_flashdata($key) {
		// Check flashdata for this item.
		if(isset($this->flash['flash_' . $key])) {
			// Flash item exists. Return it.
			return $this->flash['flash_' . $key];
		} else {
			// Nothing exists here. FALSE.
			return FALSE;
		}
	}
	/**
	 * Stores given data into the user's session on the server. Takes an array of data, where the keys of the
	 * array represent the keys to the data. Can also be passed as a series of strings as seperate arguments
	 * in order of key => data. These two methods cannot be mixed and matched.
	 *
	 * @example $this->session->set_serverdata('username', 'bob', 'email', 'test@lol.com');
	 * @example $this->session->set_serverdata(array('username' => 'bob', 'email' => 'test@lol.com'));
	 */
	public final function set_serverdata() {
		// Get the data.
		$data = func_get_args();
		// Determine what mode we're doing, either array or string series.
		if(isset($data[0])) {
			// Data exists, what is it - array or string?
			if(is_array($data[0])) {
				// Array parsing mode.
				// Loop through all passed arguments.
				for($i = 0; $i < func_num_args(); $i++) {
					// Blow up each array and set data as needed.
					if(is_array($data[$i])) {
						// This data piece is an array, good.
						foreach($data[$i] as $key => $item) {
							// Set session data.
							$_SESSION[$key] = $item;
						}
					}
				}
			} else if(is_string($data[0])) {
				// String parsing mode.
				// Loop through all passed arguments.
				for($i = 0; $i < func_num_args(); $i+=2) {
					// Make sure both items at this pair of indices are strings.
					if(is_string($data[$i])) {
						// Good. Set data.
						$_SESSION[$data[$i]] = (string)$data[$i+1];
					}
				}
			}
		}
	}
	/**
	 * Stores an item as flashdata, data which exists only for the next request. Takes an array of data,
	 * where the keys of the array represent the keys to the data.
	 * Can also be passed as a series of strings as seperate arguments in order of key => data.
	 * These two methods cannot be mixed and matched.
	 *
	 * @example $this->session->set_server_flashdata('username', 'bob', 'email', 'test@lol.com');
	 * @example $this->session->set_server_flashdata(array('username' => 'bob', 'email' => 'test@lol.com'));
	 */
	public final function set_server_flashdata() {
		// Get the data.
		$data = func_get_args();
		// Determine what mode we're doing, either array or string series.
		if(isset($data[0])) {
			// Data exists, what is it - array or string?
			if(is_array($data[0])) {
				// Array parsing mode.
				// Loop through all passed arguments.
				for($i = 0; $i < func_num_args(); $i++) {
					// Blow up each array and set data as needed.
					if(is_array($data[$i])) {
						// This data piece is an array, good.
						foreach($data[$i] as $key => $item) {
							// Set session data.
							$_SESSION['flash_' . $key] = $item;
						}
					}
				}
			} else if(is_string($data[0])) {
				// String parsing mode.
				// Loop through all passed arguments.
				for($i = 0; $i < func_num_args(); $i+=2) {
					// Make sure the key is a string. Force-transform the value to a string.
					if(is_string($data[$i])) {
						// Good. Set data.
						$_SESSION['flash_' . $data[$i]] = (string)$data[$i+1];
					}
				}
			}
		}
	}
	/**
	 * Preserves an item of flashdata for an additional request. You can keep repeating this for as long
	 * as is needed.
	 *
	 * @param string $key The name of the flashdata item to extend the life of.
	 */
	public final function keep_server_flashdata($key) {
		// Check flashdata for this item.
		if(isset($this->flash['flash_' . $key])) {
			// Item exists. Set it for the next request.
			$this->set_server_flashdata($key, $this->flash['flash_' . $key]);
		}
	}
	/**
	 * Unsets a piece of data stored in the session array. Can be a singular key (string) or an array
	 * of items to unset. Array must have the item you wish to remove as the index.
	 *
	 * @param string $key The key of the item to remove.
	 */
	public final function unset_serverdata($key) {
		// Is the key a string or array?
		if(is_array($key)) {
			// Array parsing mode.
			// Loop through array.
			foreach($key as $item => $data) {
				// Remove at $item. unset_userdata uses this syntax, don't blame me for copying functionality.
				unset($_SESSION[$item]);
			}
		} else if(is_string($key)) {
			// String parsing mode.
			// Remove data. Done.
			unset($_SESSION[$key]);
		}
	}
	/**
	 * Mirrors the sess_destroy() function, and clears both the server AND client session data.
	 * Should be used instead of sess_destroy();
	 */
	public final function session_destroy() {
		// Wipe session (db).
		$this->_wipe_db_session();
		// Wipe session (server).
		session_regenerate_id();
		session_unset();
		session_destroy();
		// Now the client side.
		$this->sess_destroy();
	}
	/**
	 * Parses through the flashdata found in the session, and moves it to the flashdata array. Then removes
	 * it from the session.
	 */
	private final function _parse_server_flashdata() {
		// Loop over session data.
		foreach($_SESSION as $key => $item) {
			// Check $key length.
			if(strlen($key) >= 6) {
				// Possible flash item. Check string start.
				if(substr($key, 0, 6) == "flash_") {
					// Match found, add to flashdata array.
					$this->flash[$key] = $item;
					// Remove from session array.
					$this->unset_serverdata($key);
				}
			}
		}
	}
	/**
	 * Checks if the session exists, and if not initiates one. Also writes initial data, including IP, UA
	 * and last activity strings.
	 */
	private final function _check_server_session() {
		// Check SESSID, if not exist then make session.
		if(session_id() == "") {
			// Doesn't exist. Go.
			session_start();
			// Store some data too, if needed.
			// Store session ID.
			if($this->serverdata('session_id') == FALSE)
				$this->set_serverdata('session_id', session_id());
			// Store IP address.
			if($this->serverdata('ip_address') == FALSE)
				$this->set_serverdata('ip_address', $this->CI->input->ip_address());
			// Store UA.
			if($this->serverdata('user_agent') == FALSE)
				$this->set_serverdata('user_agent', substr($this->CI->input->user_agent(), 0, 50));
			// Store cookie session ID.
			if($this->serverdata('client_session_id') == FALSE)
				$this->set_serverdata('client_session_id', $this->userdata('session_id'));
			// Store last activity (invalidation thingy, timestamp)
			if($this->serverdata('last_activity') == FALSE)
				$this->set_serverdata('last_activity', (string)$this->userdata('last_activity'));
			// Is this a new session?
			if($this->_is_session_new() == TRUE) {
				$this->_create_db_session();
			}
		}
	}
	/**
	 * Checks the database to see if any rows exist with the server_session_id matching the stored ID.
	 *
	 * @return TRUE if session does not exist. FALSE otherwise.
	 */
	private final function _is_session_new() {
		// Check the last_activity sess var, is it > 1 hour old, or does the session_exists var not exist?
		if($this->serverdata('session_exists') == FALSE || (int)$this->serverdata('last_activity') + 3600 <= $this->now) {
			// Okies, the session var doesn't exist.
			// Is the board installed?
			if($this->CI->config->board->core->installed == TRUE) {
				// Board installed.
				$sessid = $this->serverdata('session_id');
				// Make a query.
				$this->CI->db->select('id')
						->from('session')
						->where('server_session_id', $sessid)
						->limit(1);
				// Total number of results > 0. Go!
				if($this->CI->db->count_all_results() > 0) {
					// Record exists, return FALSE.
					return FALSE;
				} else {
					// Record needs making. Return TRUE.
					return TRUE;
				}
			} else {
				// Board not installed. Return FALSE.
				return FALSE;
			}
		} else {
			// session_old exists. Return FALSE.
			return FALSE;
		}
	}
	/**
	 * Creates a session record in the database with the data written to the server-side session.
	 */
	private final function _create_db_session() {
		// Store data into an array.
		$data = array(
			'server_session_id' => $this->serverdata('session_id'),
			'client_session_id' => $this->serverdata('client_session_id'),
			'ip_address' => $this->serverdata('ip_address'),
			'user_agent' => $this->serverdata('user_agent'),
			'last_activity' => $this->serverdata('last_activity')
		);
		// Write all this into the DB.
		$this->CI->db->insert('session', $data);
		// Make sure the session is considered existing.
		$this->set_serverdata('session_exists', '1');
	}
	/**
	 * Wipes all session records from the database with the given server session ID.
	 */
	private final function _wipe_db_session() {
		// Board installed?
		if($this->CI->config->board->core->installed == TRUE) {
			// Get session id.
			$data = array('server_session_id' => $this->serverdata('session_id'));
			// Wipe from DB.
			$this->CI->db->delete('session', $data);
		}
	}
	/**
	 * Checks the user's IP and UA strings against those stored in the session variables.
	 * If they don't match, the session is destroyed.
	 */
	private final function _check_session_ownership() {
		// UA and IP must match for me to believe you own this session, mortal.
		if($this->serverdata('ip_address') != $this->CI->input->ip_address()
		|| $this->serverdata('user_agent') != substr($this->CI->input->user_agent(), 0, 50)) {
			// HAH! GOT YOU! Now gtfo plix <3
			$this->session_destroy();
			// Error.
			$this->CI->error->show('session_ownership');
		}
	}
	/**
	 * The client cookie's session ID is updated on an entirely different timer to the database validation
	 * mechanism below. As such the ID's may become unsynchronized despite the user being legitimate.
	 * 
	 * This is called in the constructor before validation and updates the ID as needed.
	 */
	private final function _check_client_expiration() {
		// Compare peni--Err, I mean cookie session ID's. We stored it as a session entry too, so...
		if($this->serverdata('client_session_id') != $this->userdata('session_id')
		&& $this->new_sessid != NULL) {
			// If they don't match, obviously something changed. Only continue if we're installed.
			if($this->CI->config->board->core->installed == TRUE) {
				// We're installed, so following on from that - We need to update the DB entry for the
				// old session ID.
				// Store the new data (new last_activity, new client_session_id) in an array.
				$data = array(
					'last_activity' => (string)$this->now,
					'client_session_id' => $this->new_sessid
				);
				// Get the old/new session ID's in a seperate array as WHERE clauses. For security, check
				// both session ID's. Less chance of spoofing a change.
				$session = array(
					'server_session_id' => $this->serverdata('session_id'),
					'client_session_id' => $this->serverdata('client_session_id')
				);
				// Update the session records.
				$this->CI->db->update('session', $data, $session);
				// Security: Only continue if there were affected rows. If not, something is up and
				// to be precautious - Destroy the session.
				if($this->CI->db->affected_rows() > 0) {
					// Update was good, update the server-side variables with the new data.
					$this->set_serverdata($data);
					// And as we're here, let's clear out the old records (> 2 hours old default).
					$old_time = (integer)$this->now - (integer)$this->sess_expiration;
					// Delete old records.
					$this->CI->db->where('last_activity <= \'' . (integer)$old_time . '\'')->delete('session');
				} else {
					$this->session_destroy();
					// Error.
					$this->CI->error->show('session_update');
				}
			} else {
				// No database available, update locally.
				// Store the new data (new last_activity, new client_session_id) in an array.
				$data = array(
					'last_activity' => $this->userdata('last_activity'),
					'client_session_id' => $this->userdata('session_id')
				);
				// Update.
				$this->set_serverdata($data);
			}
		} else if($this->serverdata('client_session_id') != $this->userdata('session_id')
				&& $this->new_sessid == NULL) {
			// Session ID's don't match, but the new ID is a null. Assume hack.
			$this->session_destroy();
			// Error.
			$this->CI->error->show('session_update');
		}
	}
	/**
	 * Checks the last_activity string against the current time and if needed, validates the client-side
	 * session data against that stored in the database. If validation fails, the session is destroyed.
	 */
	private final function _check_database_validation() {
		// First check, make sure all the data in both client/server are the same.
		// Even though it's not a database validation bit, it's an error nonetheless.
		// Store client data in a $client array.
		$client = array(
			'session_id' => $this->userdata('session_id'),
			'ip_address' => $this->userdata('ip_address'),
			'user_agent' => $this->userdata('user_agent'),
			'last_activity' => $this->userdata('last_activity')
		);
		// Store server data in $server array.
		$server = array(
			'client_session_id' => $this->serverdata('client_session_id'),
			'ip_address' => $this->serverdata('ip_address'),
			'user_agent' => $this->serverdata('user_agent'),
			'last_activity' => $this->serverdata('last_activity')
		);
		// If there's a single difference, invalidate the session.
		if(count(array_diff($client, $server)) > 0) {
			$this->session_destroy();
			// Error.
			$this->CI->error->show('session_ownership');
		}
		// Right, make sure we're an installed board.
		if($this->CI->config->board->core->installed == TRUE) {
			// We're installed, thus we can check. FIRE!
			// Match the data in the CLIENT array with that stored in the database.
			$server['server_session_id'] = $this->serverdata('session_id');
			// Make a query.
			$this->CI->db->select('id')
					->from('session')
					->where('server_session_id', $server['server_session_id'])
					->where('client_session_id', $server['client_session_id'])
					->where('ip_address', $server['ip_address'])
					->where('user_agent', $server['user_agent'])
					->where('last_activity', $server['last_activity'])
					->limit(1);
			// Total number of results = 0, invalidate.
			if($this->CI->db->count_all_results() == 0) {
				$this->session_destroy();
				// Error.
				$this->CI->error->show('session_ownership');
			}
		}
	}
	/**
	 * ----------------------------------------------------
	 * CI SESSION OVERWRITES
	 * ----------------------------------------------------
	 */
	/**
	 * Create a new session
	 *
	 * @access	public
	 * @return	void
	 */
	function sess_create() {
		// Make a random session ID.
		$sessid = '';
		while (strlen($sessid) < 32) {
			$sessid .= mt_rand(0, mt_getrandmax());
		}
		// To make the session ID even more secure we'll combine it with the user's IP
		$sessid .= $this->CI->input->ip_address();
		// Hash it.
		$sessid = md5(uniqid($sessid, TRUE));
		$this->userdata = array(
							'session_id' 	=> $sessid,
							'ip_address' 	=> $this->CI->input->ip_address(),
							'user_agent' 	=> substr($this->CI->input->user_agent(), 0, 50),
							'last_activity'	=> $this->now
							);
		// CHANGE: Log the new session ID to fix a bug where sessions die if they still exist in the DB, but
		// the user was inactive for an hour. It causes a mismatch between ID's.
		$this->new_sessid = $sessid;
		// Write the cookie
		$this->_set_cookie();
	}
	/**
	 * Update an existing session
	 *
	 * @access	public
	 * @return	void
	 */
	function sess_update() {
		// We only update the session every five minutes by default
		if (($this->userdata['last_activity'] + $this->sess_time_to_update) >= $this->now) {
			return;
		}
		// Save the old session id so we know which record to
		// update in the database if we need it
		$old_sessid = $this->userdata['session_id'];
		// Make a random session ID.
		$new_sessid = '';
		while (strlen($new_sessid) < 32) {
			$new_sessid .= mt_rand(0, mt_getrandmax());
		}
		// To make the session ID even more secure we'll combine it with the user's IP
		$new_sessid .= $this->CI->input->ip_address();
		// Turn it into a hash
		$new_sessid = md5(uniqid($new_sessid, TRUE));
		// Update the session data in the session data array
		$this->userdata['session_id'] = $new_sessid;
		$this->userdata['last_activity'] = $this->now;
		// CHANGE: Store the new session ID in the $new_sessid
		$this->new_sessid = $new_sessid;		
		// _set_cookie() will handle this for us if we aren't using database sessions
		// by pushing all userdata to the cookie.
		$cookie_data = NULL;
		// Write the cookie
		$this->_set_cookie($cookie_data);
	}
}
/* End of file ibb_session.php */
/* Location: ./apps/libraries/ibb_session.php */