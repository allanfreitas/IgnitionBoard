<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The Authentication library handles user logins, logouts, validation, and other important things.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class Authentication {
	/**
	 * Stores a reference to the global CI object.
	 */
	public $CI;
	// User login status constants.
	const GUEST		= 0;
	const DORMANT	= 1;
	const LOGGED	= 2;
	/**
	 * Constructor
	 * 
	 * Validates the user's credentials and updates things as needed. Gets a copy of the user's details from
	 * the Content library, extends it and puts it in the cache.
	 */
	public final function __construct() {
		// Set the CI reference up.
		$this->CI = get_instance();
		// Extend the user's information to include authentication stuff.
		$this->_extend_user();
		// Validate the user's login details.
		$this->revalidate();
		// If the user is logged out, try a cookie login.
		if($this->user->login->status == self::GUEST) {
			// Cookie login?
			$this->login('cookie');
		}
	}
	/**
	 * Gets the user's details from the database via the content library. Extends them and caches the added
	 * information in the auth store under the SELF key.
	 */
	private final function _extend_user() {
		// Get the user's ID - stored in the session's user_id variable. If this doesn't exist, set it to -1.
		if($this->CI->session->serverdata('auth_user_id') === FALSE) {
			// In that case, make it -1.
			$this->CI->session->set_serverdata('auth_user_id', '-1');
		}
		// Get the user's details.
		$user = $this->CI->content->user($this->CI->session->serverdata('auth_user_id'));
		// Cache it in an alternative location.
		$this->CI->cache->add($user, 'SELF', 'users', TRUE);
		// Create a shortcut.
		$this->user =& $this->CI->cache->get('SELF', 'users');
		// Extend it with login stuff.
		$this->_set_login();
	}
	/**
	 * Updates the user's login credentials in their object with the ones from the given source.
	 *
	 * @param	string		$source		The source to get the data from. Defaults to session data.
	 */
	private final function _set_login($source = 'session') {
		// Populate with defaults (session/ip)
		$this->user->login->session = session_id();
		$this->user->login->session_record_id = $this->_session_record_id();
		$this->user->login->ip = $this->CI->input->ip_address();
		$this->user->login->status = self::GUEST;
		// Go through the sources.
		switch($source) {
			case 'post':
				// Populate with POST data.
				$this->user->login->email = $this->CI->input->post('login_email', TRUE);
				$this->user->login->password = $this->CI->input->post('login_password', TRUE);
				$this->user->login->challenge = $this->CI->input->post('login_challenge', TRUE);
				$this->user->login->remember = $this->CI->input->post('login_remember', TRUE);
				break;
			case 'cookie':
				// Populate with COOKIE data.
				$this->user->login->email = $this->CI->encrypt->decode(get_cookie('auth_remember', TRUE));
				$this->user->login->password = '';
				$this->user->login->challenge = '';
				$this->user->login->remember = '1';
				break;
			default:
				// Populate with SESSION data.
				$this->user->login->email = $this->CI->session->serverdata('auth_login_email');
				$this->user->login->password = $this->CI->session->serverdata('auth_login_password');
				$this->user->login->challenge = $this->CI->session->serverdata('auth_login_challenge');
				$this->user->login->remember = $this->CI->session->serverdata('auth_login_remember');
				$this->user->login->status = $this->CI->session->serverdata('auth_login_status');
				break;
		}
	}
	/**
	 * Logs in a user from a given source of data.
	 *
	 * @param	string		$source		The source to load data from. Valid entries are POST and COOKIE,
	 *									or an array of valid login credentials.
	 * @return	bool					TRUE if the login was successful, FALSE otherwise.
	 */
	public final function login($source) {
		// Go through possible sources.
		switch($source) {
			case 'post':
				// Log in via POST data.
				$this->_set_login('post');
				return $this->_process();
			case 'cookie':
				// Log in via COOKIE data.
				$this->_set_login('cookie');
				return $this->_process();
			default:
				// Log in via the given data.
				$this->_set_login($source);
				return $this->_process();
		}
	}
	/**
	 * Logs a user out, destroying their session and cookies.
	 *
	 * @param bool	$redirect	If TRUE, the user will be redirect to the board index, or to the location in
	 *							the AUTH_REDIRECT session variable.
	 */
	public final function logout($redirect = FALSE) {
		// Store the redirect destination, before destroying the session.
		$destination = $this->CI->session->serverdata('auth_redirect');
		// Unset session.
		$this->CI->session->session_destroy();
		// Destroy cookies.
		delete_cookie('remember');
		// Redirect user if we should.
		if($redirect) {
			// Check it.
			if($destination == FALSE) {
				// Fix it. Board index.
				$destination = '/';
			}
			// Redirect.
			redirect($destination);
		}
	}
	/**
	 * Revalidates a login from data in the session, or from a cookie. Doesn't error out either!
	 */
	public final function revalidate() {
		// Only revalidate if we actually have an email (which comes from the session or cookie ONLY).
		if($this->user->login->email != "") {
			// Time to revalidate?
			if($this->CI->security->revalidate()) {
				// Validate the login.
				$result = $this->_validate(TRUE);
				// Perform a strict validation.
				if($result == self::GUEST) {
					// Validation didn't work. Log user out.
					$this->logout();
				} else {
					// Update login status.
					$this->user->login->status = $result;
					// Login validation throttling.
					$this->CI->session->set_serverdata('auth_login_status', $result);
				}
			} else {
				// Give the user the benefit of the doubt.
				$this->CI->session->set_serverdata('auth_login_status', $this->user->login->status);
			}
		}
	}
	/**
	 * Validates and processes a login based on information stored in the user object.
	 */
	public final function _process() {
		// Validate the login.
		$result = $this->_validate();
		// Update user status.
		$this->user->login->status = $result;
		// Check it.
		if($result == self::DORMANT) {
			// User can be logged in as DORMANT (password needs confirmation, else is fine).
			// Store their credentials in the session data, from the user object.
			$this->CI->session->set_serverdata('auth_login_email', $this->user->login->email);
			$this->CI->session->set_serverdata('auth_login_remember', $this->user->login->remember);
		} else if($result == self::LOGGED) {
			// All user creds could be validated. Full login.
			// Store all their creds in the session. This keeps them logged in.
			$this->CI->session->set_serverdata('auth_login_email', $this->user->login->email);
			$this->CI->session->set_serverdata('auth_login_password', $this->user->login->password);
			$this->CI->session->set_serverdata('auth_login_challenge', $this->user->login->challenge);
			$this->CI->session->set_serverdata('auth_login_remember', $this->user->login->remember);
		} else {
			// User should be considered a guest. This is a login attempt, so don't kill their session.
			// The data in their object won't propagate to the next session either, so just exit.
			return FALSE;
		}
		// User is logged in now.
		// Get their user ID number, we'll need this to update the user object.
		$id = $this->user->get_id_from_email($this->user->login->email);
		// Set the ID.
		$this->CI->session->set_serverdata('auth_user_id', $id);
		// Login validation throttling (and bugfix for login state not updating in time).
		$this->CI->session->set_serverdata('auth_login_status', $result);
		// Rebuild the user object.
		$this->_extend_user();
		// Update the database with the user's IP and session ID.
		$this->user->update(array(
			'last_logged_ip' => $this->user->login->ip,
			'session_id' => $this->user->login->session_record_id
			));
		// Remember this user?
		if($this->user->login->remember == '1') {
			// Set the remember cookie. Expires in a year.
			set_cookie('auth_remember', $this->CI->encrypt->encode($this->user->login->email), 31536000);
		}
		// return true, user logged in.
		return TRUE;
	}
	/**
	 * Validates the information in a user object, returning the state that the user should be.
	 *
	 * @param bool		$strict		If TRUE, validate the IP and session ID too. Only do this on revalidate.
	 */
	public final function _validate($strict = FALSE) {
		// Determine what the result is going to be (before we even start!).
		// Is the email empty?
		if($this->user->login->email == "") {
			// Well this failed quick.
			return self::GUEST;
		} else {
			// Is the password blank?
			if($this->user->login->password == "") {
				// Right, so validate a dormant login. Dormant logins are forcefully half-strict.
				$this->CI->db->select('id')
					   ->from('user')
					   ->where('email', $this->user->login->email)
					   ->where('last_logged_ip', $this->user->login->ip)
					   ->limit(1);
				// Strict?
				if($strict) {
					// Validate session ID.
					$this->CI->db->where('session_id', $this->user->login->session_record_id);
				}
				// Execute.
				$query = $this->CI->db->get();
				// Result count.
				if($query->num_rows() > 0) {
					// Good to go.
					return self::DORMANT;
				} else {
					// Fail.
					return self::GUEST;
				}
			} else {
				// Full login attempt.
				$this->CI->db->select('password')
					   ->from('user')
					   ->where('email', $this->user->login->email)
					   ->limit(1);
				// Strict?
				if($strict) {
					// Validate session ID and IP.
					$this->CI->db->where('last_logged_ip', $this->user->login->ip);
					$this->CI->db->where('session_id', $this->user->login->session_record_id);
				}
				// Execute.
				$query = $this->CI->db->get();
				// Result count.
				if($query->num_rows() > 0) {
					// Good to go. Compare passwords.
					$real_password = $query->row();
					$real_password = $real_password->password;
					// Decode it.
					$real_password = 
							$this->CI->security->decode_password($real_password, $this->user->login->email);
					// The password on the user's end is the hashed password, prefixed with challenge, hashed.
					$real_password = hash('ripemd160', $this->user->login->challenge . $real_password);
					// Do the passwords match?
					if($real_password === $this->user->login->password) {
						// Yep!
						return self::LOGGED;
					} else {
						// No.
						return self::GUEST;
					}
				} else {
					// Fail.
					return self::GUEST;
				}
			}
		}
	}
	/**
	 * Retrieves a session record ID from the session table based on the ID in the user object.
	 * Returns NULL if a record doesn't exist.
	 */
	private final function _session_record_id() {
		// Got a session ID?
		if($this->user->login->session != "") {
			// Have an ID stored in the session data?
			if($this->CI->session->serverdata('auth_session_id')) {
				// Return this.
				return $this->CI->session->serverdata('auth_session_id');
			}
			// Get the ID.
			$this->CI->db->select('id')
				   ->from('session')
				   ->where('server_session_id', $this->user->login->session)
				   ->limit(1);
			// Execute.
			$query = $this->CI->db->get();
			// Results?
			if($query->num_rows() > 0) {
				// Return the ID, after setting it to the auth_session_id var.
				$id = $query->row();
				// Set ID. Improves performance.
				$this->CI->session->set_serverdata('auth_session_id', $id->id);
				return $id->id;
			} else {
				// Error.
				return NULL;
			}
		} else {
			// Error.
			return NULL;
		}
	}
}
/* End of file authentication.php */
/* Location: ./apps/libraries/authentication.php */