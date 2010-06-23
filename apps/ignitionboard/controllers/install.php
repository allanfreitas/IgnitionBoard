<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Install controller is the board installation manager.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0.0
 */
class Install extends Controller {
	/**
	 * Main view of the Install controller. Load the main install page.
	 */
	function index() {
		// Set up some data.
		$data = array();
		// Create a challenge auth variable. Used in confirming ajax requests.
		// Store challenge in $data and session.
		$data['CHALLENGE'] = $this->security->generate_challenge('install');
		// Parse main install page.
		$this->parser->add('install/install', $data)->parse();
	}
	/**
	 * Loads the intro view of the installer.
	 */
	function intro() {
		// Does the challenge match the server one?
		if(!$this->security->check_challenge('challenge', 'install')) {
			// Go to end if done.
			if($this->config->board->core->installed) {
				// GO TO END I SAY
				$this->complete();
			} else {
				// Continue. Set up some data.
				$data = array('CHALLENGE' => $this->input->post('challenge'));
				// Calculate installer progress.
				$this->_get_install_progress($data);
				// Parse the DB install view.
				$this->parser->add('install/install-intro', $data, TRUE);
			}
		} else {
			// Error out.
			show_404();
		}
	}
	/**
	 * Database Config input page.
	 */
	function conf() {
		// Does the challenge match the server one?
		if($this->security->check_challenge('challenge', 'install')) {
			// Only do this if the config needs sorting.
			if($this->config->_check_dbconf()) {
				// Config sorted. Go to make tables.
				$this->tables();
			} else {
				// Check post. Have we started the input process?
				if($this->input->post('crConf')) {
					// Set up some data. Get DB settings too.
					$data = array(
						'CHALLENGE' => $this->input->post('challenge', TRUE),
						'HOSTNAME' => $this->input->post('dbhost', TRUE),
						'USERNAME' => $this->input->post('dbuser', TRUE),
						'PASSWORD' => $this->input->post('dbpass', TRUE),
						'DBNAME' => $this->input->post('dbname', TRUE),
						'DBPREFIX' => $this->input->post('dbprefix', TRUE)
					);
					// If any of the fields which have defaults are blank, fill them in. Fixes a bug with set_value();
					if(empty($data['HOSTNAME']))
						$data['HOSTNAME'] = 'localhost';
					if(empty($data['USERNAME']))
						$data['USERNAME'] = 'root';
					if(empty($data['DBNAME']))
						$data['DBNAME'] = 'ignitebb';
					if(empty($data['DBPREFIX']))
						$data['DBPREFIX'] = 'ibb_';
					// Load form validation stuff.
					$this->_set_form_validation('conf');
					// Validator run successful? Only run if a field was submitted (as challenge causes it to run too)
					if(!isset($_POST['dbpass']) || ($this->form_validation->run() == FALSE)) {
						// Parse the DB install view.
						$this->parser->add('install/install-db-conf-form', $data, TRUE);
					} else {
						// Test this data.
						if($this->_test_database($data)) {
							// Good! Write the settings to the file.
							if($this->_write_dbconf($data)) {
								// Tell user they're AWESOME.
								$this->parser->add('install/install-db-conf-success', $data, TRUE);
							} else {
								// Error writing to conf file.
								$this->parser->add('install/install-db-conf-file-error', $data, TRUE);
							}
						} else {
							// Parse DB install error view.
							$this->parser->add('install/install-db-conf-error', $data, TRUE);
						}
					}
				} else {
					// Continue. Set up some data.
					$data = array('CHALLENGE' => $this->input->post('challenge', TRUE));
					// Parse the DB install "welcome" view.
					$this->parser->add('install/install-db-conf', $data, TRUE);
				}
			}
		} else {
			// Error out.
			show_404();
		}
	}
	/**
	 * Create-tables intermediary view.
	 */
	function tables() {
		// Does the challenge match the server one?
		if($this->security->check_challenge('challenge', 'install')) {
			// Only do this if the tables need creating.
			if($this->db->table_exists('config')) {
				// Tables already made. Go to admin.
				$this->admin();
			} else {
				// Continue. Set up some data.
				$data = array('CHALLENGE' => $this->input->post('challenge', TRUE));
				// Check post. Was the "make tables" button sent?
				if($this->input->post('crTab')) {
					// Make the tables.
					$this->_load_records();
					$this->_create_tables();
					// Generate a salt. It's time.
					$this->security->generate_salt_keypair();
					// Tell the user I want to marry them.
					// Load table setup view.
					$this->parser->add('install/install-db-tables-success', $data, TRUE);
					
				} else {
					// Load table setup view.
					$this->parser->add('install/install-db-tables', $data, TRUE);
				}
			}
		} else {
			// Error out.
			show_404();
		}
	}
	/**
	 * Loads the admin account creation form.
	 */
	function admin() {
		// Does the challenge match the server one?
		if($this->security->check_challenge('challenge', 'install')) {
			// Only do this if the admin account needs creating.
			if($this->config->_check_dbconf() && ($this->db->count_all('user') > 0)) {
				// GO TO BOARD, DO NOT PASS GO, DO NOT COLLECT £200.
				$this->board();
			} else {
				// Continue. Set up some data.
				$data = array(
					'CHALLENGE' => $this->input->post('challenge', TRUE),
					'EMAIL' => $this->input->post('email', TRUE),
					'PASSWORD' => $this->input->post('password', TRUE),
					'DISPLAYNAME' => $this->input->post('display_name', TRUE)
				);
				// Display welcome or form?
				if($this->input->post('crAdm')) {
					// Load form validation stuff.
					$this->_set_form_validation('admin');
					// Validator run successful? Only run if a field was submitted (as challenge causes it to run too)
					if(!isset($_POST['email']) || ($this->form_validation->run() == FALSE)) {
						// Parse the DB install view.
						$this->parser->add('install/install-admin-form', $data, TRUE);
					} else {
						// Create user.
						if($this->user->create()) {
							// Good!
							$this->parser->add('install/install-admin-success', $data, TRUE);
						} else {
							// Error? Weird.
							$this->parser->add('install/install-admin-error', $data, TRUE);
						}
					}
				} else {
					// Load a "hello" page.
					$this->parser->add('install/install-admin', $data, TRUE);
				}
			}
		} else {
			// Error out.
			show_404();
		}
	}
	/**
	 * Loads the admin account creation form.
	 */
	function board() {
		// Does the challenge match the server one?
		if($this->security->check_challenge('challenge', 'install')) {
			// Only do this if the board needs informashunz.
			if($this->config->board->core->installed == TRUE) {
				// GO TO BOARD, DO NOT PASS GO, DO NOT COLLECT £200.
				$this->complete();
			} else {
				// Continue. Set up some data.
				$data = array(
					'CHALLENGE' => $this->input->post('challenge', TRUE),
					'BOARDNAME' => $this->input->post('board_name', TRUE),
				);
				// Display welcome or form?
				if($this->input->post('crBrd')) {
					// Load form validation stuff.
					$this->_set_form_validation('board');
					// Validator run successful? Only run if a field was submitted (as challenge causes it to run too)
					if(!isset($_POST['board_name']) || ($this->form_validation->run() == FALSE)) {
						// Parse the DB install view.
						$this->parser->add('install/install-board-form', $data, TRUE);
					} else {
						// Put info into an array.
						$installed = array(
							'setting' => 'installed',
							'value' => '1',
							'category' => 'board',
							'subcategory' => 'core'
						);
						$title = array(
							'setting' => 'title',
							'value' => $this->input->post('board_name', TRUE),
							'category' => 'board',
							'subcategory' => 'text'
						);
						// Put this into database.
						$this->db->insert('config', $installed);
						$this->db->insert('config', $title);
						// Done!
						$this->parser->add('install/install-board-success', $data, TRUE);
					}
				} else {
					// Load a "hello" page.
					$this->parser->add('install/install-board', $data, TRUE);
				}
			}
		} else {
			// Error out.
			show_404();
		}
	}
	/**
	 * Final "yay well done" view.
	 */
	function complete() {
		// Does the challenge match the server one?
		if($this->security->check_challenge('challenge', 'install')) {
			// Unset the challenge var.
			$this->security->unset_challenge('install');
			// Tell the user they rock.
			$this->parser->add('install/install-complete', NULL, TRUE);
		} else {
			// Error out.
			show_404();
		}
	}
	/**
	 * Calculates the installer's progress and returns the data in the given array.
	 * 
	 * @param $data The array to append the data too. This is passed by reference.
	 */
	function _get_install_progress(&$data) {
		// Progress #1: Is DB Conf. file writeable?
		$data['WRITEABLE'] = is_writeable(APPPATH . 'config/database.php') ?
				'<li class="good">The config file at "' . APPPATH . 'config/database.php" is writeable.</li>' :
				'<li class="exclamation">The config file at "' . APPPATH . 'config/database.php" is not writeable.</li>';
		// Progress #2: Are conf. settings valid?
		$data['DBCONFSTATE'] = $this->config->_check_dbconf() ?
				'<li class="good">Valid database connection settings are located in your config files.</li>' :
				'<li class="bad">No valid connection settings were found in your config files.</li>' ;
		// Progress #3: Database tables set up?
		$data['DBSTATE'] = ($this->config->_check_dbconf() && ($this->db->table_exists('config'))) ?
				'<li class="good">The database tables have been set up.</li>' :
				'<li class="bad">The database tables have not yet been set up.</li>';
		// Progress #4: Admin account set up?
		$data['ADMINSTATE'] = ($this->config->_check_dbconf() && ($this->db->table_exists('config') && ($this->db->count_all('user') > 0))) ?
				'<li class="good">An administrator account has been set up.</li>' :
				'<li class="bad">An administrator account has not yet been set up.</li>';
		// Progress #5: Board installed?
		$data['BOARDSTATE'] = ($this->config->board->core->installed) ?
				'<li class="good">The board has been configured.</li>' :
				'<li class="bad">The board has not been configured.</li>';
	}
	/**
	 * Tests the given database connection details and returns FALSE if a connection could not be made.
	 *
	 * @param array $db The database connection information to test. 
	 */
	function _test_database($db) {
		// Attempt to make a connection.
		if(@mysql_connect($db['HOSTNAME'], $db['USERNAME'], $db['PASSWORD'], TRUE) != FALSE) {
			// Good. Try switching to the db.
			if(@mysql_select_db($db['DBNAME']) != FALSE) {
				// Good, they worked.
				mysql_close();
				return TRUE;
			} else {
				// Error!
				mysql_close();
				return FALSE;
			}
		} else {
			// Error!
			return FALSE;
		}
	}
	/**
	 * Writes a large string to a big file. It could be cleaner, but it'd be slower.
	 */
	function _write_dbconf($data) {
		// Ready? Here's the DB Conf file.
		$file = <<<EOF
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the "Database Connection"
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|
| The \$active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the "default" group).
|
| The \$active_record variables lets you determine whether or not to load
| the active record class
*/

\$active_group = "default";
\$active_record = TRUE;

\$db['default']['hostname'] = "$data[HOSTNAME]";
\$db['default']['username'] = "$data[USERNAME]";
\$db['default']['password'] = "$data[PASSWORD]";
\$db['default']['database'] = "$data[DBNAME]";
\$db['default']['dbdriver'] = "mysql";
\$db['default']['dbprefix'] = "$data[DBPREFIX]";
\$db['default']['pconnect'] = FALSE;
\$db['default']['db_debug'] = TRUE;
\$db['default']['cache_on'] = FALSE;
\$db['default']['cachedir'] = "";
\$db['default']['char_set'] = "utf8";
\$db['default']['dbcollat'] = "utf8_general_ci";


/* End of file database.php */
/* Location: ./apps/config/database.php */
EOF;
		// Now write that.
		if(is_writable(APPPATH . 'config/database.php')) {
			file_put_contents(APPPATH . 'config/database.php', $file);
			return TRUE;
		} else {
			$this->error->show('file_not_writeable', APPPATH . 'config/database.php');
			return FALSE;
		}
	}
}

/* End of file: login.php */
/* Location: ./apps/controllers/ */