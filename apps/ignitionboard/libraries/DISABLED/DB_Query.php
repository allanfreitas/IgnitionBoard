<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * The Database query class acts more like a model, but we leave it with the libraries so it makes sense.
 * You create one of these each time you want to kick some ass, and execute a query.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class DB_Query {
	/**
	 * Stores a reference to the global CI instance.
	 */
	protected $CI;
	/**
	 * Stores a reference to the database connection.
	 */
	protected $connection;
	/**
	 * Constructor
	 */
	public final function __construct() {
		// Reference the CI super object.
		$this->CI =& get_instance();
		// Reference the connection object.
		$this->connection =& $this->CI->database->connection;
	}
	/**
	 * Executes a query given earlier either by active record classes or query preperation.
	 * Can also execute a query given via a parameter, and return only a string result rather than
	 * a result object.
	 *
	 * @param string $custom_query The custom query to quickly execute.
	 */
	public final function execute($custom_query = NULL) {
		// Are we doing a custom query?
		if($custom_query != NULL) {
			// Seems so. Execute this query.
			return $this->connection->query($custom_query);
		} else {
			// NYI
			$this->CI->error->show('function_not_yet_implemented', array('%f' => 'execute', '%c' => 'database_query'));
		}
	}
}
/* End of file DB_Query.php */
/* Location: ./apps/libraries/database/DB_Query.php */