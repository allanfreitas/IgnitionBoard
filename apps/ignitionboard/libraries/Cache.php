<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Handles the caching of data in use by other libraries. Can be used to store function results, or objects.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class Cache {
	/**
	 * Storage array containing each created cache "store", which itself is an array containing data.
	 *
	 * @var array Cache stores.
	 */
	private $cache_stores = array();
	/**
	 * Chance for the delete_output() function to be called to remove old cache files.
	 */
	private $divisor = 10;
	/**
	 * Constructor
	 * 
	 * Sets up CI reference.
	 */
	public final function __construct() {
		// Reference the properties of the CI super object.
		_assign_instance_properties($this);
	}
	/**
	 * Sets up needed cache stores and clears expired page caches.
	 */
	public final function initialize() {
		// Add in the function results store. This is used for storing results from intensive functions.
		$this->create("functions");
		// Clear up old cache outputs?
		if(mt_rand(1, $this->divisor) == 1) {
			$this->cache_clear();
		}
	}
	/**
	 * Caches the given output string into a file specific for the current controller and user.
	 * The cache file lasts as long as the cache duration config setting.
	 */
	public final function cache_output() {
		// Page Cache enabled?
		if($this->config->board->cache->enabled == TRUE) {
			// User need a cache dir?
			$this->cache_mkdir();
			// Get the output.
			$output = $this->output->get_output();
			// Get the user's session ID.
			$session = $this->session->get_id();
			// Write the output into a new file with this controller name.
			$page = $this->router->class . "." . $this->router->method;
			// Go!
			file_put_contents(APPPATH . '/output/cache/' . $session . '/' . $page . '.cache', $output);
		}
	}
	/**
	 * Checks if the current request should return previously-cached output. Returns FALSE if not, or the
	 * TRUE if so.
	 */
	public final function cache_request() {
		// Page Cache enabled?
		if($this->config->board->cache->enabled == TRUE) {
			// Get the user's session ID.
			$session = $this->session->get_id();
			// Get controller name.
			$page = $this->router->class . "." . $this->router->method;
			// Check to see if a cached file exists.
			if(file_exists(APPPATH . '/output/cache/' . $session . '/' . $page . '.cache')) {
				// When was it last modified?
				if(filemtime(APPPATH . '/output/cache/' . $session . '/' . $page . '.cache') + $this->config->board->cache->duration > time()) {
					// Modified not too long ago, return TRUE.
					return TRUE;
				} else {
					// Modified ages ago. Return FALSE.
					return FALSE;
				}
			} else {
				// Not cached.
				return FALSE;
			}
		} else {
			// Caching isn't even enabled.
			return FALSE;
		}
	}
	/**
	 * Returns the cached file for this request.
	 */
	public final function cache_retrieve() {
		// Page Cache enabled?
		if($this->config->board->cache->enabled == TRUE) {
			// Get the user's session ID.
			$session = $this->session->get_id();
			// Get controller name.
			$page = $this->router->class . "." . $this->router->method;
			// Return the cache file.
			return file_get_contents(APPPATH . '/output/cache/' . $session . '/' . $page . '.cache');
		} else {
			// Cache isn't enabled.
			return "";
		}
	}
	/**
	 * Updates the filesystem cache stores for a users session ID. Called when their ID changes.
	 *
	 * @param string		$old		The users old session ID.
	 * @param string		$new		The users new session ID.
	 */
	public final function cache_update($old, $new) {
		// Page Cache enabled?
		if($this->config->board->cache->enabled == TRUE) {
			// Does a folder exist for the old user?
			if(file_exists(APPPATH . '/output/cache/' . $old . '/')) {
				// Rename it.
				rename(APPPATH . '/output/cache/' . $old . '/', APPPATH . '/output/cache/' . $new . '/');
			}
		}
	}
	/**
	 * Clears the cache directories based on whether or not their assigned sessions exist.
	 */
	private final function cache_clear() {
		// Page Cache enabled?
		if($this->config->board->cache->enabled == TRUE) {
			// Get all of the sessions which still exist.
			$sessions = array_diff(scandir(APPPATH . '/output/sessions/'), array('.', '..'));
			// Get all of the session caches.
			$caches = array_diff(scandir(APPPATH . '/output/cache/'), array('.', '..'));
			// Go through cache dirs.
			foreach($caches as $cache) {
				// If there's no session for this cache...(Append .sess ext to cache name, bugfix).
				if(in_array($cache . ".sess", $sessions) == FALSE) {
					// Delete all the files inside of this dir, and then the dir itself.
					$files = array_diff(scandir(APPPATH . '/output/cache/' . $cache . '/'), array('.', '..'));
					foreach($files as $file) {
						// Delete file (stupid limitation of rmdir is to NOT delete file -.-)
						unlink(APPPATH . '/output/cache/' . $cache . '/' . $file);
					}
					// Remove dir.
					rmdir(APPPATH . '/output/cache/' . $cache . '/');
				}
			}
		}
	}
	/**
	 * Makes the user's cache directory if needed.
	 */
	private final function cache_mkdir() {
		// Page Cache enabled?
		if($this->config->board->cache->enabled == TRUE) {
			// Does this user need a cache dir?
			$session = $this->session->get_id();
			if(file_exists(APPPATH . '/output/cache/' . $session . '/') == FALSE) {
				// Make one.
				mkdir(APPPATH . '/output/cache/' . $session . '/');
			}
		}
	}
	/**
	 * Creates a new cache store, which can then be used to cache data of a particular type. For example you
	 * could make a cache store for called Users, in which you store user records.
	 *
	 * @param	string		$name		The name of the store you want to create.
	 * @param	bool		$delete		If TRUE, delete the old store if a store with this name exists.
	 * @return	bool					TRUE if the store was created, FALSE otherwise.
	 */
	public final function create($name, $delete = FALSE) {
		// Check our cache array. Any stores with this name exist?
		if($this->has_store($name)) {
			// Yep. Delete?
			if($delete) {
				// Go forth.
				$this->delete($name);
			} else {
				// Return FALSE.
				return FALSE;
			}
		}
		// Add the new store.
		$this->cache_stores[$name] = array();
		// Return.
		return TRUE;
	}
	/**
	 * Checks to see if a store with the given name exists.
	 *
	 * @param	string		$name		The name of the store to check for.
	 * @return	bool					TRUE if the store exists, FALSE otherwise.
	 */
	public final function has_store($name) {
		// Check existance.
		if(array_key_exists($name, $this->cache_stores)) {
			// Exists.
			return TRUE;
		} else {
			// Nope.
			return FALSE;
		}
	}
	/**
	 * Adds an item to a cache store.
	 *
	 * @param	object		$data		The data item you would like to add to the cache store.
	 * @param	string		$key		The key to assign to this item, for future access.
	 * @param	string		$store		The store to place this item in.
	 * @param	bool		$delete		If TRUE, delete any existing item at the same key.
	 * @return	bool					TRUE if the item was added, FALSE otherwise.
	 */
	public final function add($data, $key, $store, $delete = FALSE) {
		// Does an item with this key exist?
		if($this->has_item($key, $store)) {
			// Delete?
			if($delete) {
				// Delete.
				$this->remove($key, $store);
			} else {
				// Error.
				return FALSE;
			}
		}
		// Add item.
		$this->cache_stores[$store][$key] = $data;
		// Return.
		return TRUE;
	}
	/**
	 * Gets an item from a cache store.
	 *
	 * @param	string		$key		The key of the item to retrieve
	 * @param	string		$store		The store to retrieve this item from.
	 * @param	array/str	$default	The default value to return instead of FALSE.
	 * @param	bool		$func		If TRUE, execute $default as a function. PHP <= 5.2 support.
	 * @return	object					The item if successful, FALSE or a default if otherwise.
	 */
	public final function get($key, $store, $default = "", $func = FALSE) {
		// Item exist?
		if($this->has_item($key, $store)) {
			// Return it.
			return $this->cache_stores[$store][$key];
		} else {
			// Got a default?
			if($default != FALSE) {
				// Is it a function?
				if($func == TRUE) {
					// What is it, exactly?
					if(is_array($default)) {
						// Execute it differently.
						$this->cache_stores[$store][$key] = $default[0]->$default[1]();
					} else {
						// Execute it like any normal function.
						$this->cache_stores[$store][$key] = $default();
					}
				} else {
					// Return default, after setting it into the cache at this key.
					$this->cache_stores[$store][$key] = $default;
				}
				// Return the value.
				return $this->cache_stores[$store][$key];
			} else {
				// Return FALSE.
				return FALSE;
			}
		}
	}
	/**
	 * Updates an item in a cache store.
	 *
	 * @param	object		$data		The data item you would like to replace into the cache store.
	 * @param	string		$key		The key of the item to replace.
	 * @param	string		$store		The store to replace this item in.
	 * @return	bool					TRUE if the item was updated, FALSE otherwise.
	 */
	public final function update($data, $key, $store) {
		// Does this item NOT exist?
		if($this->has_item($key, $store) == FALSE) {
			// Add it.
			$this->add($data, $key, $store);
		} else {
			// Update.
			$this->cache_stores[$store][$key] = $data;
		}
	}
	/**
	 * Checks to see if an item with the given key exists in a store.
	 *
	 * @param	string		$key		The name of the item to check for.
	 * @param	string		$store		The name of the store to check in.
	 * @return	bool					TRUE if the item exists, FALSE otherwise.
	 */
	public final function has_item($key, $store) {
		// Store exist?
		if($this->has_store($store)) {
			// Check existance.
			if(array_key_exists($key, $this->cache_stores[$store])) {
				// Exists.
				return TRUE;
			} else {
				// Nope.
				return FALSE;
			}
		} else {
			// No store.
			return FALSE;
		}
	}
	/**
	 * Removes an item from a store.
	 *
	 * @param	string		$key		The name of the item to remove.
	 * @param	string		$store		The name of the store to remove from.
	 * @return	bool					TRUE if the item was removed, FALSE otherwise.
	 */
	public final function remove($key, $store) {
		// Item exist?
		if($this->has_item($key, $store)) {
			// Remove it.
			unset($this->cache_stores[$store][$key]);
			// Return.
			return TRUE;
		} else {
			// Fail.
			return FALSE;
		}
	}
	/**
	 * Deletes a cache store with the given name, if it exists.
	 *
	 * @param	string		$name		The name of the cache store to delete.
	 * @return	bool					TRUE if the store was deleted. FALSE otherwise.
	 */
	public final function delete($name) {
		// Store exist?
		if($this->has_store($name)) {
			// Good. Delete.
			unset($this->cache_stores[$name]);
		} else {
			// Error.
			return FALSE;
		}
	}
}
/* End of file cache.php */
/* Location: ./apps/libraries/cache.php */