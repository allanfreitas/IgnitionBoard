<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Extension of the Controller library to add some sorely needed functionality.
 *
 * @author Daniel Yates & Dale Emasiri
 * @version 1.0
 */
class IBB_Controller extends Controller {
	/**
	 * Debug property, tests to see if controller extends properly.
	 *
	 * @var string Test string.
	 */
	protected $lolwhut = "LOL WHUT!?";
	/**
	 * Does this controller need the user to log in?
	 *
	 * @var bool TRUE if this controller requires a logged in user, FALSE otherwise.
	 */
	public $login = FALSE;
	/**
	 * Array of methods in this controller which need the user to be logged in. Only applies to views.
	 * If empty, and $login is TRUE, then all methods need a login.
	 * If not empty and $login is TRUE, then only methods in the array need a login.
	 * If either empty or not empty, and $login is FALSE then logins are NOT enforced.
	 *
	 * @var array List of methods for login requirement.
	 */
	public $login_methods = array();
}
/* End of file ibb_controller.php */
/* Location: ./apps/libraries/ibb_controller.php */