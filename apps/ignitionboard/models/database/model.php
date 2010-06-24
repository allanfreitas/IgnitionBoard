<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The DB Model class is an abstract class with methods for derived database models to inherit. The key to
 * this is that the model acts as both a table in static form, and as a record in instance form.
 *
 * So say DB_Config extended this,
 * DB_Config::set_table_definition(); would set up the config's table definition. Only ever needs to be called
 * once.
 *
 * But: $conf = new DB_Config(), and then $conf->save() would save the new record to the database.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
abstract class DB_Model_Abstract {
	/**
	 * ----------------------------------------------------
	 * INSTANCED METHODS & PROPERTIES (FIELD)
	 * ----------------------------------------------------
	 */
	/**
	 * Stores the data for this instance of the record. Accessed by $model->{field name}.
	 * We don't actually STORE the data at $model->{field name}, as then __set/__get doesn't work.
	 */
	protected $data = array();
	/**
	 * Stores the name of the class of this instance of the model. Determined by get_class(), for reasons
	 * explained in the constructor.
	 */
	public $class = "";
	/**
	 * Stores a reference to the table that this model belongs to.
	 */
	public $table;
	/**
	 * Constructor.
	 *
	 * Called when a DERIVED CLASS is initialized. Use this to set up the properties the model has.
	 */
	public function __construct() {
		// Determine the UID this class has. Using __CLASS__ doesn't work, as it always returns the
		// base class, which is DB_Model_Abstract. We want the inherited class, so DB_Config or DB_User.
		$this->class = get_class($this);
		// Store the table definition this model owns.
		$this->table =& self::$CI->database->maintenance->tables[$this->class];
		// Set our local table definition using the one from the DB library.
		foreach(array_keys($this->table['fields']) as $field) {
			// Add this field to the data array.
			$this->data[$field] = NULL;
		}
	}
	/**
	 * Saves this record instance into the table, either by way of an UPDATE or INSERT query.
	 * An INSERT query is used if the id field is NULL, otherwise an UPDATE query is used assuming the row
	 * with the given ID exists.
	 */
	public function save() {
		// Is the ID null?
		if($this->id == NULL) {
			// Insert.
			self::$CI->db->insert($this->table['name'][0], $this->data);
		} else {
			// Update if row exists.
			if(self::$CI->db->where($this->table['id'], $this->id)->from($this->table['name'][0])->count_all_results() > 0) {
				// Record found, update.
				self::$CI->db->where($this->table['id'], $this->id)->update($this->table['name'][0], $this->data);
			} else {
				// Not found, insert.
				self::$CI->db->insert($this->table['name'][0], $this->data);
			}
		}
	}
	/**
	 * Deletes a record instance from the table, matching the given ID.
	 */
	public function delete() {
		// Delete the row where the ID is the one we have.
		self::$CI->db->where($this->table['id'], $this->data['id'])->delete($this->table['name'][0]);
	}
	/**
	 * Takes a given array and sets up the object with the present data.
	 */
	public function import_from_array($array) {
		// Go through the array.
		foreach($array as $field => $data) {
			// If we have a field with this name, set the value.
			if(array_key_exists($field, $this->table['fields']) == TRUE) {
				// Set it.
				$this->{$field} = $data;
			}
		}
	}
	/**
	 * Called automatically when data is set to an inaccessible property.
	 * Re-routes it to the data array, and calls any assigned mutators.
	 *
	 * @param string $name The field name to access.
	 * @param string $value The value to set in this field.
	 */
	public function __set($name, $value) {
		// Does this field name exist?
		if(array_key_exists($name, $this->data)) {
			// Set it.
			$this->data[$name] = $this->_call_set_mutator($name, $value);
		} else {
			// Error out.
			self::$CI->error->show("database_field_not_found", array(
				'%f' => $name,
				'%c' => get_class($this)
			));
		}
	}
	/**
	 * Called automatically when data is retrieved from an inaccessible property.
	 * Re-routes it to the data array, and calls any assigned mutators.
	 *
	 * @param string $name The field name to access.
	 */
	public function __get($name) {
		// Does this field name exist?
		if(array_key_exists($name, $this->data)) {
			// Get it.
			return $this->_call_get_mutator($name);
		} else {
			// Is this the ID field we're on about?
			if($name == "id") {
				// Don't error out, instead go through our fields and find the field which is now
				// acting as a primary key IF identifiers are disabled.
				if($this->table['identifier'] == FALSE) {
					// Call the get mutator on the data at the ID field.
					return $this->_call_get_mutator($this->table['id']);
				} else {
					// Okies, NOW you can error out.
					self::$CI->error->show("database_field_not_found", array(
						'%f' => $name,
						'%c' => get_class($this)
					));
				}
			} else {
				// Error out.
				self::$CI->error->show("database_field_not_found", array(
					'%f' => $name,
					'%c' => get_class($this)
				));
			}
		}
	}
	/**
	 * Calls any assigned 'mutators' (functions which alter data) on this field.
	 * This is the SET variant, so calls a mutator which alters data as it's being set.
	 *
	 * @param string $name The field name to access.
	 * @param string $value The value to set in this field.
	 */
	protected function _call_set_mutator($field, $value) {
		// Does a mutator for this field exist?
		if(array_key_exists($field, $this->table['mutators'])) {
			// Does a SET mutator exist?
			if(array_key_exists("SET", $this->table['mutators'][$field])) {
				// Call it and return the value.
				$mutator = $this->table['mutators'][$field]['SET'];
				return $this->$mutator($value);
			} else {
				// No mutator. Return the value.
				return $value;
			}
		} else {
			// No mutator. Return the value.
			return $value;
		}
	}
	/**
	 * Calls any assigned 'mutators' (functions which alter data) on this field.
	 * This is the GET variant, so calls a mutator which alters data as it's being retrieved.
	 *
	 * @param string $name The field name to access.
	 * @param string $value The value to set in this field.
	 */
	protected function _call_get_mutator($field) {
		// Does a mutator for this field exist?
		if(array_key_exists($field, $this->table['mutators'])) {
			// Does a GET mutator exist?
			if(array_key_exists("GET", $this->table['mutators'][$field])) {
				// Call it and return the value.
				$mutator = $this->table['mutators'][$field]['GET'];
				return $this->$mutator($this->data[$field]);
			} else {
				// No mutator. Return the value.
				return $this->data[$field];
			}
		} else {
			// No mutator. Return the value.
			return $this->data[$field];
		}
	}
	/**
	 * ----------------------------------------------------
	 * STATIC METHODS & PROPERTIES (TABLE)
	 * ----------------------------------------------------
	 */
	/**
	 * Stores a reference to the global CI instance.
	 */
	protected static $CI;
	/**
	 * Stores a unique identifier, this is used in all of the has_field/has_* functions so it knows where to
	 * stores its data. The UID is the class name of the object which fired the initializer, and is cleared
	 * after the set_table_definition function is called.
	 */
	protected static $UID = NULL;
	/**
	 * Initializes the object, setting the CI reference and any unique identifiers up.
	 */
	public static final function initialize($uid = "") {
		// Set up the CI reference if needed.
		if(isset(self::$CI) == FALSE) {
			// Set it up.
			self::$CI =& get_instance();
		}
		// If the UID isn't empty, set it.
		if(empty($uid) == FALSE) {
			// Set UID.
			self::$UID = $uid;
			// Set up the default array for this table definition.
			self::$CI->database->maintenance->tables[self::$UID] = array(
				'fields' => array(),
				'name' => array(),
				'relations' => array(),
				'mutators' => array(),
				'timestamps' => FALSE,
				'identifier' => TRUE,
				'id' => 'id'
			);
		}
	}
	/**
	 * Called after set_table_definition is completed. Removes the UID, preventing any further changes to the
	 * table structure.
	 */
	public static final function uninitialize() {
		// Do we want an identifer field?
		if(self::$CI->database->maintenance->tables[self::$UID]['identifier'] == TRUE) {
			// Add one.
			self::has_field('id', 'int', 9, array('primary' => TRUE, 'auto_increment' => TRUE));
		}
		// Unset the UID.
		self::$UID = NULL;
	}
	/**
	 * Called during table setup, this function should be used to set up the table's registered columns,
	 * relations, name, etc.
	 */
	public static abstract function set_table_definition();
	/**
	 * Adds a field to the list of fields for this table, and should be set in the initializer of the table.
	 *
	 * @param string $name		The name of the field you are creating.
	 * @param string $type		The data type of the field you are creating.
	 * @param int $size			The maximum size of the data that can fit in this field. NULL can be passed.
	 * @param array $params		Extra parameters to pass along with this field.
	 *							Possible parameters:
	 *
	 *							unsigned [true|false]
	 *							default [value]
	 *							null [true|false]
	 *							auto_increment [true|false]
	 *							unique [true|false]
	 *							primary [true|false]
	 */
	protected static final function has_field($name, $type, $size, $params = array()) {
		// Only continue if we've got a UID. Prevents possible 'issues'.
		if(self::$UID != NULL) {
			// Continue. We're putting this field into self::$CI->database->maintenance->tables[UID]['fields'];
			// That is a mouthful. So we'll make a reference to it and name the variable $registry.
			$registry =& self::$CI->database->maintenance->tables[self::$UID]['fields'];
			// Right, put in some data for this field.
			$field = array(
				'type' => strtoupper($type),
				'size' => ($size == NULL) ? NULL : $size,
				'primary' => (isset($params['primary']) ? $params['primary'] : FALSE),
				'null' => (isset($params['null']) ? $params['null'] : FALSE),
				'unsigned' => (isset($params['unsigned']) ? $params['unsigned'] : FALSE),
				'unique' => (isset($params['unique']) ? $params['unique'] : FALSE),
				'auto_increment' => (isset($params['auto_increment']) ? $params['auto_increment'] : FALSE),
				'default' => (isset($params['default']) ? $params['default'] : '')
			);
			// If the field name is ID, then we want to be...Awkward, and place it at the beginning :P
			if($name == "id")
				$registry = array_merge(array($name => $field), $registry);
			else
				$registry[$name] = $field;
			// Was this field a primary key?
			if(isset($params['primary']) && $params['primary'] == TRUE) {
				// It was! WOW! Include a link to this field as the ID.
				self::$CI->database->maintenance->tables[self::$UID]['id'] = $name;
			}
		} else {
			// Error out. Do a fatal one too! :D
			self::$CI->error->show('database_table_initialized', $name);
		}
	}
	/**
	 * This function defines whether or not the table has timestamp fields (created_at and updated_at).
	 *
	 * @param bool $timestamps If true, this table should include timestamp fields.
	 */
	protected static final function has_timestamps($timestamps) {
		// Only continue if we've got a UID. Prevents possible 'issues'.
		if(self::$UID != NULL) {
			// DO IT.
			self::$CI->database->maintenance->tables[self::$UID]['timestamps'] = (bool)$timestamps;
		} else {
			// Error out. Do a fatal one too! :D
			self::$CI->error->show('database_table_initialized', $name);
		}
	}
	/**
	 * Does what it says on the tin, and sets this record to have the given table name.
	 *
	 * @param string $name The name of this table.
	 */
	protected static final function set_table_name($name) {
		// Only continue if we've got a UID. Prevents possible 'issues'.
		if(self::$UID != NULL) {
			// Simply update the name var. Index 0 is name, 1 is full name (prefix).
			self::$CI->database->maintenance->tables[self::$UID]['name'][0] = $name;
			self::$CI->database->maintenance->tables[self::$UID]['name'][1] = self::$CI->db->dbprefix . $name;
		} else {
			// Error out. Do a fatal one too! :D
			self::$CI->error->show('database_table_initialized', $name);
		}
	}
	/**
	 * This function adds a foreign/primary key relationship to the current table when the create function
	 * is called.
	 *
	 * @param string $local			The name of the foreign key, eg. "category_id". Located in THIS table.
	 * @param string $foreign		The name of the primary key. Often "id", located in ANOTHER table.
	 * @param string $foreign_table	The name of the table housing the primary key.
	 * @param string $cascade		If set to NONE, UPDATE or DELETE then the cascading will be set to that.
	 *								Defaults to ALL (update/deletes cascade).
	 */
	protected static final function has_relation($local, $foreign, $foreign_table, $cascade_type = "ALL") {
		// Only continue if we've got a UID. Prevents possible 'issues'.
		if(self::$UID != NULL) {
			// Continue. We're putting this relation into self::$CI->database->maintenance->tables[UID]['relations'];
			// That is a mouthful. So we'll make a reference to it and name the variable $registry.
			$registry =& self::$CI->database->maintenance->tables[self::$UID]['relations'];
			// Put this into the registry.
			$registry[$local] = array(
				'local' => $local,
				'foreign' => $foreign,
				'foreign_table' => self::$CI->db->dbprefix . $foreign_table,
				'cascade' => $cascade_type
			);
			// Done.
		} else {
			// Error out. Do a fatal one too! :D
			self::$CI->error->show('database_table_initialized', $name);
		}
	}
	/**
	 * This function adds a 'mutator' to the given field. A mutator alters data as it is set or retrieved
	 * from a record. To add a mutator, give the name of the function as a string and the field it belongs to.
	 * 
	 * The default type of mutator is SET, which alters data as it is put into the record. You can also
	 * define GET mutators which alter data as it is retrieved.
	 *
	 * @param string $field			The name of the field to assign this mutator to.
	 * @param string $function		The name of the function to call. This function should be defined in the
	 *								model, as it will be called via "$this->{function}();".
	 * @param string $type			The type of mutator. Default is SET, but can be changed to GET.
	 */
	protected static final function has_mutator($field, $function, $type = "SET") {
		// Only continue if we've got a UID. Prevents possible 'issues'.
		if(self::$UID != NULL) {
			// If the type isn't GET or SET, default to SET.
			if($type != ("SET" || "GET")) {
				$type = "SET";
			}
			// Continue. We're putting this relation into self::$CI->database->maintenance->tables[UID]['mutators'];
			// That is a mouthful. So we'll make a reference to it and name the variable $registry.
			$registry =& self::$CI->database->maintenance->tables[self::$UID]['mutators'];
			// Put this into the registry.
			$registry[$field][$type] = $function;
		}
	}
	/**
	 * By default, all table models are given an ID field automatically. You can override this behaviour
	 * by setting has_identifer to FALSE.
	 *
	 * This is required if you want to make another field a primary key, and it is recommended that you do so
	 * if this is set to FALSE.
	 *
	 * @param bool $status TRUE if this table has an automatically created ID field, FALSE otherwise.
	 */
	protected static final function has_identifer($status) {
		// Only continue if we've got a UID. Prevents possible 'issues'.
		if(self::$UID != NULL) {
			// Put this into the registry.
			self::$CI->database->maintenance->tables[self::$UID]['identifier'] = $status;
		}
	}
}
/* End of file db_table.php */
/* Location: ./apps/models/database/db_table.php */