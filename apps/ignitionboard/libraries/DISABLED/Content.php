<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The Content library handles the accessing of certain content areas, like users, and return models or
 * objects for the data requested. Also handles the cache of these objects.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class Content {
	/**
	 * Stores a reference to the global CI object.
	 */
	public $CI;
	/**
	 * Constructor
	 *
	 * Sets up the needed cache stores.
	 */
	public final function __construct() {
		// Get a reference of the CI object.
		$this->CI =& get_instance();
		// Set up cache stores for users.
		$this->CI->cache->create("users");
	}
	/**
	 * Creates and populates a user object, returning it and caching it for future usage in this load.
	 *
	 * @param	string		$id		The ID of the user, also used as the key for the cache.
	 * @param	bool		$force	If true, force-override the cache and fetch a new copy of the object.
	 */
	public function user($id, $force = FALSE) {
		// Anything with this ID in the cache? And is force false?
		if($this->CI->cache->has_item((string)$id, 'users') && $force == FALSE) {
			// Return it.
			return $this->CI->cache->get((string)$id, 'users');
		} else {
			// Make a new user.
			$user = new User((integer)$id);
			// Cache it.
			$this->CI->cache->add($user, (string)$id, 'users', TRUE);
			// Return it.
			return $user;
		}
	}
}
/* End of file content.php */
/* Location: ./apps/libraries/content.php */