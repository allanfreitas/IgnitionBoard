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
	private $cache = array();
	/**
	 * Constructor
	 * 
	 * Sets up default cache stores for usage across all libraries/models.
	 */
	public final function Cache() {
		// Add in the function results store. This is used for storing results from intensive functions.
		$this->create("functions");
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
		$this->cache[$name] = array();
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
		if(array_key_exists($name, $this->cache)) {
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
		$this->cache[$store][$key] = $data;
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
			return $this->cache[$store][$key];
		} else {
			// Got a default?
			if($default != FALSE) {
				// Is it a function?
				if($func == TRUE) {
					// What is it, exactly?
					if(is_array($default)) {
						// Execute it differently.
						$this->cache[$store][$key] = $default[0]->$default[1]();
					} else {
						// Execute it like any normal function.
						$this->cache[$store][$key] = $default();
					}
				} else {
					// Return default, after setting it into the cache at this key.
					$this->cache[$store][$key] = $default;
				}
				// Return the value.
				return $this->cache[$store][$key];
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
			$this->cache[$store][$key] = $data;
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
			if(array_key_exists($key, $this->cache[$store])) {
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
			unset($this->cache[$store][$key]);
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
			unset($this->cache[$name]);
		} else {
			// Error.
			return FALSE;
		}
	}
}
/* End of file cache.php */
/* Location: ./apps/libraries/cache.php */